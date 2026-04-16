<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('user')->after('password');
            $table->boolean('notify_on_message')->default(true)->after('bio');
            $table->boolean('notify_on_follow')->default(true)->after('notify_on_message');
            $table->boolean('notify_on_like')->default(true)->after('notify_on_follow');
            $table->boolean('notify_on_comment')->default(true)->after('notify_on_like');
        });

        DB::table('users')->whereNull('role')->update(['role' => 'user']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role',
                'notify_on_message',
                'notify_on_follow',
                'notify_on_like',
                'notify_on_comment',
            ]);
        });
    }
};
