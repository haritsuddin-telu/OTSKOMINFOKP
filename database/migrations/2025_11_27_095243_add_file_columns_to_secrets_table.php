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
        Schema::table('secrets', function (Blueprint $table) {
            $table->text('text')->nullable()->change();
            $table->string('file_path')->nullable()->after('text');
            $table->string('original_name')->nullable()->after('file_path');
            $table->string('mime_type')->nullable()->after('original_name');
            $table->bigInteger('file_size')->nullable()->after('mime_type');
        });
    }

    public function down()
    {
        Schema::table('secrets', function (Blueprint $table) {
            $table->text('text')->nullable(false)->change();
            $table->dropColumn(['file_path', 'original_name', 'mime_type', 'file_size']);
        });
    }
};
