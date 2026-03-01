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
    Schema::table('subscribers', function (Blueprint $table) {
        $table->unique('section_no');
    });
}

public function down()
{
    Schema::table('subscribers', function (Blueprint $table) {
        $table->dropUnique(['section_no']);
    });
}
};
