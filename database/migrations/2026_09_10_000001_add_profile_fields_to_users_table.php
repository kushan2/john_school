<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Relative path on the "public" disk, e.g. avatars/ab12.jpg
            $table->string('avatar')->nullable()->after('campus');
            $table->string('major')->nullable()->after('avatar');
            $table->text('bio')->nullable()->after('major');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['avatar', 'major', 'bio']);
        });
    }
};
