<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->timestamp('read_at')->nullable()->after('attachment_size');
        });

        DB::table('messages')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        Schema::table('users', function (Blueprint $table) {
            $table->text('interest_tags')->nullable()->after('bio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn('read_at');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('interest_tags');
        });
    }
};
