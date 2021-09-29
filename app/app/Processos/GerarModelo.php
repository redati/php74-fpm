<?php
namespace App\Processos;

use App\Galeria;
use Illuminate\Support\Facades\Storage;
use App\Imagem\Modelos\Modelo;
use App\Produtos;
use Exception;
use Illuminate\Support\Facades\Log;
use Throwable;

class GerarModelo {

    private $produto_variacoes;
    private $produto_principal;
    private $imagens_base;

    // Model Produtos
    // Com relacionamento originais with(['original'])
    public function Gerar(Produtos $produto_pri)
    {
        $produto_id = $produto_pri->id;

        if (empty($produto_id) or !is_numeric($produto_id) or $produto_id < 1 or $produto_id > 9999999999999999){
            throw new Exception('id vazio ou inválido');
        }

        //carrega variações do produto $produto_variacoes Produto
        $this->produto_variacoes = $this->CarregaProdutoVariacoes($produto_id);

        // carrega produto principal  $produto_principal Produto
        $this->produto_principal = $produto_pri;

        // carrega imagens base (imagem original) $imagens_base Imagem_bases
        $this->imagens_base = $produto_pri->original;

        $cat_ids =  json_decode($this->produto_principal->categorias_ids);

        if (count($this->produto_variacoes)){

            $caminho_base = 'imgpro/'.$produto_id;
            $caminho_base_impre = 'impressao/'.$produto_id;

            //excluí o diretório, se ele existe e imagens do banco de dados
            try {
                if ($this->DeletaCriaDiretorio($caminho_base)){
                    $this->DeletaGaleriaDb($produto_id);
                }
            }catch(Throwable $e){
                Log::debug("Impossível deletar diretorio ou banco de dados de galeria do produto ".$produto_id);
                report($e);
            }

            //deleta entradas no banco de dados
            foreach ($this->produto_variacoes as $produto){
                // deleta galeria da variação
                if ($produto->id){
                    $this->DeletaGaleriaDb($produto->id);
                }
            }

            // para verificar se a combinação de tamanho, acabamento e material ja foram criadas
            $combinacao_criada = [];

            $modelo_thumbnail = new Modelo();
            try {
                $modelo_thumbnail->GerarModelo($this->imagens_base, null, null, null, 'thumbnail', null, $produto_id, null, $this->produto_principal->sku, $caminho_base, $caminho_base_impre, null, $cat_ids);
            } catch (Throwable $e){
                Log::debug($produto_id." Não foi possível chamar Gerar Modelo para thumbnail principal ");
                report($e);
            }
            unset($modelo_thumbnail);

            foreach ($this->produto_variacoes as $produto){

                // Zera e Reatribui atributos
                $material   = ''; $material =   $produto->material;
                $acabamento = ''; $acabamento = $produto->acabamento;
                $tamanho    = ''; $tamanho =    $produto->tamanho;

                $comb_thumb = $material.$acabamento;
                $comb_amb = $material.$acabamento.$tamanho;
                $comb_matam = $material.$tamanho;

                if (empty($material) or empty($acabamento) or empty($tamanho)){
                    $msg = $produto->id.' Dados do produto ausente m'.$produto->material.' a '.$produto->acabamento.' t '.$produto->tamanho;
                    Log::debug($msg);
                    throw new Exception($msg);
                }

                if (empty($material) or empty($acabamento) or empty($tamanho)){
                    $msg = $produto->id .' com material, tamanho ou acabamento faltando - ERRO ';
                    Log::debug($msg);
                    throw new Exception($msg);
                }

                // combinação de material e acabamento
                if (!in_array($comb_thumb, $combinacao_criada)){
                    Log::debug($produto->id." Iniciando Canvas Chassi Interno e chassi moldura combinação Material e Acabamento thumb ".$comb_thumb);

                    $modelo_thumb_simples = new Modelo();
                    $modelo_thumb_simples->GerarModelo($this->imagens_base, $acabamento, $material, $tamanho, 'thumbnail', $produto->tipo_composicao, $produto->id, $produto->sku_pai, $produto->sku, $caminho_base, $caminho_base_impre, $this->produto_variacoes, $cat_ids);
                    unset($modelo_thumb_simples);

                    $combinacao_criada[] = $comb_thumb;
                }
            }
        } else {
            Log::debug("Sem variações.");
            throw new Exception('Sem variações');
        }
    }

    public function getProduto ($produto_id, $filtro){
        return Produtos::where($filtro, $produto_id)->with(['original'])->get();
    }

    public function getProdutoUnico ($produto_id) :Produtos{
        return Produtos::where('id', $produto_id)->get()->first();
    }

    public function SalvaBD ($tipo,$imagem,$material,$acabamento, $sku_pai){
        $produtos = Produtos::where('material', $material)->where('acabamento',$acabamento)->where('sku_pai',$sku_pai)->get();
        foreach ($produtos as $produto){
            Galeria::updateOrCreate(['tipo' => $tipo, 'produto_id' => $produto->id],['produto_id' => $produto->id,'imagem' => $imagem]);
        }
    }
    private function CarregaProdutoVariacoes($produto_id) {
        try {
            return $this->getProduto($produto_id, 'sku_pai');
        } catch(Exception $e){
            Log::debug("Não foi possível buscar as variações do produto ".$produto_id);
            throw new Exception($e->getMessage());
        }
    }

    // ex imgpro/22 em storage/app/public
    private function DeletaCriaDiretorio($caminho){
        try{
            if ( is_dir(storage_path('app/public/'.$caminho)) && !empty($caminho) ){
                $files = Storage::disk('public')->allFiles($caminho);
                if (count($files)){
                    foreach ($files as $file){
                        Storage::disk('public')->delete($file);
                    }
                }
                //Storage::disk('public')->deleteDirectory($caminho);
            } else {
                Storage::disk('public')->makeDirectory($caminho);
            }
            return true;

        } catch (Throwable $e){
            Log::debug("Impossível criar ou deletar diretório ".$caminho);
            report($e);
        }
        return false;
    }

    private function DeletaGaleriaDb($produto_id){
        $galeria_del =  Galeria::where('produto_id', $produto_id)->get();
        if (count($galeria_del) > 0 ){
            foreach ($galeria_del as $g){
                 $g->delete();
            }
        }
        unset($galeria_del);
    }
}

?>
