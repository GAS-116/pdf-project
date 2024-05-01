<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePdfTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pdf_templates', function (Blueprint $table) {
            $table->char('id', 32)->primary();
            $table->char('pdf_id', 32);
            $table->string('name');
            $table->string('file_name');
            $table->timestamps();

            $table->foreign('pdf_id')->references('id')->on('pdfs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pdf_templates');
    }
}
