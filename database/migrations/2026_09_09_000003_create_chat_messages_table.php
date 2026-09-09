<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // Poster's campus, copied at send time so the cross-campus label
            // stays correct even if the user later switches campus.
            $table->string('campus')->nullable();
            $table->text('body');
            $table->timestamps();

            $table->index('id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
