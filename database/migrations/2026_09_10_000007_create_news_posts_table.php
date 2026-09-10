<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // author
            $table->string('title');
            $table->text('body');
            // announcement | sports | academic | campus_life | other
            $table->string('category')->default('announcement');
            $table->string('campus')->nullable();
            $table->timestamps();

            $table->index('category');
            $table->index('campus');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_posts');
    }
};
