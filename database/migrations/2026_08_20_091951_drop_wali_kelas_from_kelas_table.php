<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up():void
    {
        if(Schema::hasColumn('kelas','wali_kelas')){
            Schema::table('kelas',function(Blueprint $table){
                $table->dropColumn('wali_kelas');
            });
        }
    }
    public function down():void
    {
        if(!Schema::hasColumn('kelas','wali_kelas')){
            Schema::table('kelas',function(Blueprint $table){
                $table->string('wali_kelas')->nullable();
            });
        }
    }
};