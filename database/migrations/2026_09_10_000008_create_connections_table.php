<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('connections', function (Blueprint $table) {
            $table->id();
            // user_id = the initiator (requester / blocker),
            // friend_id = the other party (recipient / blocked).
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('friend_id')->constrained('users')->cascadeOnDelete();
            // pending | accepted | blocked
            $table->string('status')->default('pending');
            $table->timestamps();

            // At most one relationship row per ordered pair.
            $table->unique(['user_id', 'friend_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('connections');
    }
};
