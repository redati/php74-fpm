<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImpressaosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('impressaos', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('produto_id')->unsigned()->index();
            $table->string('imagem')->nullable();
            $table->foreign('produto_id')->references('id')->on('produtos')->onDelete('no action')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('impressaos');
    }
}
