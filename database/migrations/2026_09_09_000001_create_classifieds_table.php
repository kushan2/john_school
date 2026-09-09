<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classifieds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // roommate | for_sale | books | furniture | other
            $table->string('category')->default('other');
            $table->string('title');
            $table->text('body');
            // Only used for buy/sell listings; null otherwise.
            $table->decimal('price', 10, 2)->nullable();
            // Poster's campus, copied from the user at post time so it stays
            // filterable/displayable even if the user later switches campus.
            $table->string('campus')->nullable();
            $table->timestamps();

            $table->index('category');
            $table->index('campus');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classifieds');
    }
};
