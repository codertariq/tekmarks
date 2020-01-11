<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConfigTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('config', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->nullable();
            $table->bigInteger('numeric_value')->nullable();
            $table->mediumText('text_value')->nullable();
            $table->boolean('is_private')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_preferences', function (Blueprint $table) {
            $table->dropForeign('user_preferences_user_id_foreign');
        });
        Schema::dropIfExists('config');
    }
}
