<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // organizer
            $table->string('title');
            $table->text('description')->nullable();
            // party | sports | concert | academic | other
            $table->string('category')->default('other');
            $table->string('location')->nullable();
            $table->string('campus')->nullable();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();
            $table->timestamps();

            $table->index('starts_at');
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
