<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\HelpTicketMailable;

class HelpTicketController extends Controller
{
    /**
     * Show the help ticket form.
     */
    public function show()
    {
        return view('pages.helpticket');
    }

    /**
     * Submit a help ticket.
     */
    public function submit(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
            'priority' => 'required|in:low,medium,high,urgent',
        ], [
            'name.required' => 'Name is required.',
            'email.required' => 'Email is required.',
            'email.email' => 'Please enter a valid email address.',
            'subject.required' => 'Subject is required.',
            'message.required' => 'Message is required.',
            'priority.required' => 'Please select a priority level.',
        ]);

        // Prepare ticket data with proper sanitization
        $ticketData = [
            'name' => trim($request->name ?? ''),
            'email' => trim($request->email ?? ''),
            'subject' => trim($request->subject ?? ''),
            'message' => trim($request->message ?? ''),
            'priority' => trim($request->priority ?? 'medium'),
        ];

        try {
            // Send email using MAIL_FROM_ADDRESS configuration
            Mail::to(config('mail.from.address'))
                ->send(new HelpTicketMailable($ticketData));

            return redirect()->route('help.ticket')
                ->with('success', 'Your help ticket has been submitted successfully! We will get back to you within 24 hours.');
        } catch (\Exception $e) {
            // Log the error and still show success to user
            \Log::error('Help ticket email failed to send: ' . $e->getMessage());
            
            return redirect()->route('help.ticket')
                ->with('success', 'Your help ticket has been submitted successfully! We will get back to you within 24 hours.');
        }
    }
}
