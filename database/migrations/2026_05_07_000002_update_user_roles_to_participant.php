<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->where(function ($query): void {
                $query->whereNull('role')->orWhere('role', 'user');
            })
            ->update(['role' => 'participant']);
    }

    public function down(): void
    {
        DB::table('users')
            ->where('role', 'participant')
            ->update(['role' => 'user']);
    }
};
