<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('bookmarks3', function (Blueprint $table) {
        $table->boolean('is_deleted')->default(false)->after('folder_name');
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookmarks3', function (Blueprint $table) {
            //
        });
    }
};
