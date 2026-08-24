<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY role VARCHAR(50) NOT NULL DEFAULT 'wali_murid'");
        DB::table('users')->where('role', 'user')->update(['role' => 'wali_murid']);
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY role VARCHAR(50) NOT NULL DEFAULT 'user'");
        DB::table('users')->where('role', 'wali_murid')->update(['role' => 'user']);
    }
};