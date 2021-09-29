<?php



namespace App\Processos;

use App\Magento\Magento;
use Illuminate\Http\Request;
use App\Magento\RestApi;
use App\Produtos;
use \Imagecow\Image;
use Illuminate\Support\Facades\Cache;
use Throwable;
use Exception;
use Illuminate\Support\Facades\Log;

class EnviarImagemMagento {

    private $service;

    public function __construct()
    {
        $this->service = new \App\Magento\Magento(new \App\Magento\RestApi);
    }

    public function Enviar(\App\Produtos $produto){

        if ($produto->tipo == 'simple'){
            $this->UploadImagemMagento($produto);
        }else {
            $principal = \App\Produtos::where('id', $produto->id)->with(['galeria'])->get()->first();
            $this->UploadImagemMagento($principal);

            $produtos = \App\Produtos::where('sku_pai', $produto->id)->with(['galeria'])->get();

            foreach ($produtos as $item){

                try {
                    Log::debug("Enviando produto ".$item->id);
                    $this->UploadImagemMagento($item);
                }catch(Throwable $e){
                    Log::debug("Impossível enviar produto ".$item->id);
                    report($e);
                }

            }
        }

        $produto->status = 'sincronizado';
        $produto->save();

    }

    private function CarregaImagensMagento(String $sku){
        $result = $this->service->api->call('products/'.$sku.'/media', null, 'GET');
        return $result;
    }

    private function IdExistente($label, $sku){

        $img_magento = $this->CarregaImagensMagento($sku);
        $id_existente = 0;
        if (count($img_magento)){
            foreach ($img_magento as $img){
                $id_existente = 0;
                // verifica as imagens adicionais
                if ($img->label == $label){
                   return $img->id;
                }
                // verifica se o tipo é thumb no magento e se o label é thumb aqui, porém no envio, é enviado o nome do produto
                if(count($img->types)){
                    if (in_array('thumbnail',$img->types) && $label == 'thumbnail'){
                        return $img->id;
                    }
                }
            }
        }

        return $id_existente;

    }
    //retorna string da imagem em cache com base64
    // converte em string, base64encode e salva em cache
    public function CacheImagem(String $caminho) :String {
        $cache_key = 'IMG_CACHE_64_'.base64_encode($caminho);
        if (Cache::has($cache_key)){
            return  Cache::get($cache_key);
        } else {
            $arquivo = $this->CarregaImagem($caminho);
            $arquivo = base64_encode($arquivo->getString());
            Cache::put($cache_key, $arquivo , 120);
            return $arquivo;
        }
    }

    public function CarregaImagem(String $imagem) :Image {
        if (file_exists($imagem)){
            return  Image::fromFile($imagem);
        }else {
            throw new Exception('imagem original não existe '.$imagem);
        }
    }
    private function UploadImagemMagento($produto){

        foreach ($produto->galeria as $item){

            // envia apenas a thumb
            //if ($item->tipo != 'thumbnail'){
            //    continue;
            //}

            //THUMB PODE FAZER CACHE
            $imagem_base64 = '';
            if ($item->tipo == 'thumbnail'){
                $imagem_base64 = $this->CacheImagem(storage_path('app/public/'.$item->imagem));
            } else {
                $imagem_base64 = base64_encode( $this->CarregaImagem(storage_path('app/public/'.$item->imagem))->getString() );
            }

            $nome_imagem = trim($produto->sku).'_'.$produto->material.$produto->tamanho.'_'.$produto->acabamento.'_'.$item->tipo.'.jpg';



            if (empty($imagem_base64)){
                throw("erro ao converter em 64");
            }

            $types = $item->tipo == 'thumbnail' ? ['image', 'small_image', 'thumbnail', 'swatch_image'] : [];

            $id_existente = 0;
            $id_existente = $this->IdExistente($item->tipo, $produto->sku);

            // fix seo, alt de thumb como nome do produto, pois thumb pode ser verificado em types.
            $alt = $item->tipo;
            if ($item->tipo == 'thumbnail'){
                $alt = $produto->nome;
            }

            $dados = [
                'entry' => [
                    'media_type' => 'image',
                    'label' => $alt,
                    'position' => 0,
                    'disabled' => false,
                    'types' => $types,
                    'file' => '',
                    'content' =>[
                        'base64_encoded_data' => $imagem_base64,
                        'name' => $nome_imagem,
                        'type' => 'image/jpeg'
                    ]
                ]
            ];

            if ($id_existente != 0 && is_numeric($id_existente)){
                $dados['entry']['id'] = $id_existente;
                $this->service->api->call('products/'.$produto->sku.'/media/'.$id_existente, $dados, 'PUT');
            }else {
                $this->service->api->call('products/'.$produto->sku.'/media/', $dados, 'POST');
            }
        }
    }

}

?>
