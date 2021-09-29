<?php

namespace App\Processos;

use App\Processos\GerarGalerias;
use App\Produtos;
use Exception;
use App\Processos\ImportarMagento;

class ProcessaImagens {

    private $tentativa_produto_magento = 0;

    public function processar($id){

        $produto = $this->getProduto($id, 'id');

        //Passo 1 Define sku_pai nos produtos filhos
        $this->DefiniSkuPai($produto);
        $retorno[] = 'Passo 1 ok - Definição de produtos filhos.';

        try{

            $modelo = new GerarModelo();
            $modelo->Gerar($produto);

            $produto->status = 'gerado';
            $produto->save();

            //Passo 2 - Gerar Imagens Produtos Filhos
            //$galerias = new GerarGalerias();
            //$galerias->GerarGaleriaSimples($id);

            //$produto = Produtos::where('id',$id)->get()->first();
            //$produto->status = 'gerado';
            //$produto->save();

            return 'ok';

        } catch(Exception $e){
            return $e->getMessage();
        }
    }


    private function getProduto ($produto_id, $filtro)  {

        $produto = Produtos::where($filtro, $produto_id)->with(['original'])->get()->first();
        if ($produto){
            return Produtos::where($filtro, $produto_id)->with(['original'])->get()->first();
        }else {

            return $this->ProdutoMagento($produto_id);
        }

    }

    private function DefiniSkuPai(Produtos $produto_principal){
        $produtos_filhos = json_decode($produto_principal->op_filhos);
        foreach ($produtos_filhos as $produto){
            $filho = $this->getProduto($produto,'id_magento');
            if ($filho){
                //vincula sku produtos simples e configuraveis
                $filho->sku_pai = $produto_principal->id;
                $filho->save();
                unset($filho);
            }
        }

    }

    private function ProdutoMagento($id){

        if($this->tentativa_produto_magento > 3){
            throw new Exception('não conseguiu buscar produto no magento: '.$id);
        }

        $importar = new ImportarMagento();
        if ($importar->importar(null, null, $id)){
            return Produtos::where('id_magento', $id)->with(['original'])->get()->first();
        }

        $this->tentativa_produto_magento++;

    }

}
