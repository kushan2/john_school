<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // creator/owner
            $table->string('name');
            $table->text('description')->nullable();
            // study_group | club | student_union | other
            $table->string('category')->default('club');
            // Creator's campus at creation time (informational — members may be
            // from any campus, which is the whole point of cross-campus groups).
            $table->string('campus')->nullable();
            $table->timestamps();

            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
