<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFontTypeToFontsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fonts', function (Blueprint $table) {
            $table->string('font_type')->default(\App\Models\Enums\FontTypesEnum::PHP);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fonts', function (Blueprint $table) {
            $table->dropColumn('font_type');
        });
    }
}
