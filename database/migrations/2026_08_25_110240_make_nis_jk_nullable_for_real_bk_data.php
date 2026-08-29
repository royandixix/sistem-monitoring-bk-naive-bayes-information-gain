<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE siswas MODIFY nis VARCHAR(255) NULL");
        DB::statement("ALTER TABLE siswas MODIFY jk ENUM('L','P') NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE siswas MODIFY nis VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE siswas MODIFY jk ENUM('L','P') NOT NULL");
    }
};
