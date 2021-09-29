<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProdutosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('produtos', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->bigInteger('id_magento')->unsigned()->index('idx_id_magento');
            $table->string('sku')->index('idx_sku');
            $table->string('sku_pai')->index('idx_skupai')->nullable();
            $table->string('tipo');
            $table->integer('composicao')->default(1)->comment('quantidade');
            $table->string('tipo_composicao')->default('no')->comment('no, duplo_v, duplo_h...');
            $table->string('categorias_ids')->nullable();
            $table->string('material')->nullable();
            $table->string('acabamento')->nullable();
            $table->string('tamanho')->nullable();
            $table->string('url')->nullable();
            $table->text('op_conf')->nullable()->comment('Opções Configuraveis Json');
            $table->text('op_filhos')->nullable();
            $table->string('status')->default('pendente')->comment('pendente, gerado, sincronizado');
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
        Schema::dropIfExists('produtos');
    }
}
