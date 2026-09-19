<?php

namespace App\Http\Controllers;

use App\Services\Assistant\AssistantAccess;
use App\Services\Assistant\AssistantTools;
use Anthropic\Client;
use Anthropic\Messages\ToolResultBlockParam;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * AI assistant endpoint.
 *
 * NOTE: this is the site's AI helper, distinct from ChatController (the human
 * campus live-chat). It answers questions about the user's campus data by
 * calling tools that read through the access-scoped {@see AssistantAccess}.
 */
class AssistantController extends Controller
{
    /** Safety cap on tool round-trips per request. */
    private const MAX_STEPS = 6;

    public function chat(Request $request): JsonResponse
    {
        $data = $request->validate([
            'message'            => ['required', 'string', 'max:2000'],
            'history'            => ['sometimes', 'array', 'max:20'],
            'history.*.role'     => ['required_with:history', 'in:user,assistant'],
            'history.*.content'  => ['required_with:history', 'string', 'max:6000'],
        ]);

        $apiKey = config('services.anthropic.key');
        if (! $apiKey) {
            return response()->json([
                'reply' => 'The assistant is not configured yet. Please set ANTHROPIC_API_KEY.',
            ], 503);
        }

        $user   = $request->user();
        $tools  = new AssistantTools(new AssistantAccess($user));
        $client = new Client(apiKey: $apiKey);

        // Rebuild the conversation: prior plain-text turns (client-supplied, for
        // context only) followed by the new user message. Access control lives
        // in the tools, so untrusted history cannot widen what the model sees.
        $messages = [];
        foreach ($data['history'] ?? [] as $turn) {
            $messages[] = ['role' => $turn['role'], 'content' => $turn['content']];
        }
        $messages[] = ['role' => 'user', 'content' => $data['message']];

        try {
            $reply = $this->runLoop($client, $tools, $messages);
        } catch (\Throwable $e) {
            Log::error('Assistant error', ['exception' => $e]);

            return response()->json([
                'reply' => 'Sorry — the assistant hit an error. Please try again.',
            ], 500);
        }

        return response()->json(['reply' => $reply]);
    }

    /**
     * Manual tool-use loop: call the model, run any tools it requests, feed the
     * results back, repeat until it produces a final answer.
     *
     * @param  array<int,mixed>  $messages
     */
    private function runLoop(Client $client, AssistantTools $tools, array $messages): string
    {
        for ($step = 0; $step < self::MAX_STEPS; $step++) {
            $response = $client->messages->create(
                model: config('services.anthropic.model'),
                maxTokens: 1024,
                system: $this->systemPrompt(),
                tools: $tools->definitions(),
                messages: $messages,
            );

            if ($response->stopReason !== 'tool_use') {
                return $this->extractText($response->content) ?: 'I don\'t have an answer for that.';
            }

            // Preserve the assistant turn (contains the tool_use blocks), then
            // answer every tool call in a single following user turn.
            $messages[] = ['role' => 'assistant', 'content' => $response->content];

            $toolResults = [];
            foreach ($response->content as $block) {
                if (($block->type ?? null) === 'tool_use') {
                    $output = $tools->handle($block->name, (array) $block->input);
                    $toolResults[] = ToolResultBlockParam::with(
                        toolUseID: $block->id,
                        content: $output,
                    );
                }
            }
            $messages[] = ['role' => 'user', 'content' => $toolResults];
        }

        return 'That took too many steps to answer — could you narrow it down?';
    }

    /** Concatenate the text blocks of a response. */
    private function extractText(array $content): string
    {
        $out = '';
        foreach ($content as $block) {
            if (($block->type ?? null) === 'text') {
                $out .= $block->text;
            }
        }

        return trim($out);
    }

    private function systemPrompt(): string
    {
        return <<<'PROMPT'
        You are the in-app assistant for a SUNY student community platform.

        You help the logged-in student with information about THEIR campus:
        events, groups and clubs, classifieds/roommate listings, news, the
        student directory, and their own memberships and connections.

        Rules:
        - Use the provided tools to look up real data. Never invent events,
          groups, listings, people, dates, or prices. If a tool returns nothing,
          say so plainly.
        - Everything the tools return is already limited to what this user is
          allowed to see (their campus and their own data). Do not claim to
          access other campuses or private contact details — you cannot.
        - Call get_current_context when the question depends on who the user is,
          their campus, or today's date.
        - Be concise and friendly. Prefer short lists. Format dates readably.
        - For actions (creating events, messaging people, RSVPing) you cannot
          perform them — point the user to the relevant page instead.
        PROMPT;
    }
}
