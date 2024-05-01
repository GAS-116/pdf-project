<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSftpSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sftp_settings', function (Blueprint $table) {
            $table->char('id', 32)->primary();
            $table->char('user_id', 32);
            $table->string('host');
            $table->integer('port')->default(22);
            $table->string('username');
            $table->longText('private_key');
            $table->string('passphrase')->nullable();
            $table->string('root_path');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sftp_settings');
    }
}
