<?php
namespace App\Imagem\Modelos;

use Exception;
use \Imagecow\Image;
use Illuminate\Support\Facades\Log;
use App\Traits\MapeamentoAtributos;
use App\Traits\getStorage;
use Throwable;
use App\Galeria;
use Illuminate\Support\Facades\Cache;

class ModeloBase {
    use MapeamentoAtributos, getStorage;

    //Quadrado, Retangular, Panoramico
    public $formato;

    //Horizontal, Vertical
    public $orientacao;


     //Objeto Imagem
     public $imagem_original;

    public $altura_base_web;
    public $largura_base_web;

    public function __construct()
    {
        $this->altura_base_web = 1000;
        $this->largura_base_web = 1000;
    }

    public function CarregaImagem(String $imagem) :Image {
        if (file_exists($imagem)){
            return  Image::fromFile($imagem);
        }else {
            throw new Exception('imagem original não existe '.$imagem);
        }
    }

    public function CarregarImagemWeb(Image $imagem) :Image {
        $imagem->transformImageColorspace('rgb');
        $imagem->format('png');
        $imagem->resize($this->altura_base_web,$this->largura_base_web,true);
        return $imagem;
    }
    public function OtimizaImagemWeb(Image $imagem) :Image {
        $imagem->format('jpg');
        $imagem->transformImageColorspace('rgb');
        $imagem->quality(85);
        $imagem->getCompressed();
        $imagem->progressive(true);
        return $imagem;
    }

    // Preeche as variaveis de acordo com imagem
    public function DefinirPropriedades(Image $imagem) :void {
        $this->formato = $this->Formato($imagem);
        $this->orientacao = $this->Orientacao($imagem);
    }

    public function Orientacao(Image $imagem) :string {
        return  $imagem->getHeight() > $imagem->getWidth() ? 'vertical' : 'horizontal';
    }

    public function Formato(Image $imagem) :string {

        // calcula a diferença e retorna sempre como valor positivo
        $diferenca = $imagem->getHeight() - $imagem->getWidth();
        $diferenca = $diferenca < 0 ? $diferenca * -1 : $diferenca;

        //quadrado com diferença máxima de 10px

        if ($imagem->getHeight() == $imagem->getWidth() or $diferenca < 20 ){
            return 'quadrado';
        }
        // obviamente um panoramico, primeiro verifica o lado maior e menor
        $maior = $imagem->getWidth() > $imagem->getHeight() ? $imagem->getWidth() : $imagem->getHeight();
        $menor = $imagem->getWidth() < $imagem->getHeight() ? $imagem->getWidth() : $imagem->getHeight();

        if ( ($maior /  $menor) > 3 ){
           return 'panoramico';
        } else {
            return 'retangular';
        }
    }

    public function DescobreProporcao($largura, $altura) :String {

        // define o maior e menor lado
        $maior = $largura > $altura ? $largura : $altura;
        $menor = $largura < $altura ? $largura : $altura;

        // diferença em porcentagem 0 x 100;
        $diferenca_perc = ($menor * 100 ) / $maior;

        // quadrados são 100%
        if ($diferenca_perc > 95 and $diferenca_perc < 110){
            return 'quadrado';
        }
        // proporcao_a a 20x30, 45x30, 63x42, 95x63, 120x80 66%
        if ($diferenca_perc >= 61 and $diferenca_perc <= 73 ){
            return 'proporcao_a';
        }
        // proporcao_b a 100x30 120x35 70%
        if ($diferenca_perc >= 28 and $diferenca_perc <= 42 ){
            return 'proporcao_b';
        }

        $msg = " Proporção não reconhecida l {$largura}, a {$altura} diferença {$diferenca_perc} ";
        Log::debug($msg);
        throw new Exception($msg);
    }

    public function DefineTamanhoImageBase (Image $imagem, Array $tamanho_info = []) :Image {

        $this->DefinirPropriedades($imagem);

        $largura = $imagem->getWidth();
        $altura = $imagem->getHeight();
        $base_x = 0;
        $base_y = 0;

        // resize da imagem base de acordo com mapeamento de atributos, caso não seja possível, de acordo com dados da imagem (acontece em produtos configuraveis )
        if (!isset($tamanho_info['base']['x']) or !isset($tamanho_info['base']['y'])){
            $proporcao = $this->TamanhoBaseImagem($this->DescobreProporcao($largura, $altura ));
            $base_x =  $proporcao['x'];
            $base_y =  $proporcao['y'];
        } else {
            $base_x = $tamanho_info['base']['x'];
            $base_y = $tamanho_info['base']['y'];
        }
        // descobre qual o maior lado para receber o valor correto do mapeamento dos tamanhos
        if ($largura > $altura ){
           $imagem->resize($base_x, $base_y, true);
        } else {
            $imagem->resize($base_y, $base_x, true);
        }
        return $imagem;
    }


     // Carrega imagem com fundo cinza 1000x1000
     public function CarregaImagemBaseWeb() :Image {
        $imagem_base  = $this->CarregaImagem(storage_path('baseimages/bg2.png'));
        return $this->CarregarImagemWeb($imagem_base);
    }


    public function AdicionaPespectiva(Image $imagem) :Image{
        $imagem->pesp(3,97);
        return $imagem;
    }

    // Cache da imagem base, ja com tamanho defninido
    public function ImagemBaseCache(String $caminho, Array $tamanho_info = [] ) :Image {
        $this->imagem_base_cache = 'IMG_BASE_'.base64_encode($caminho);
        if (Cache::has($this->imagem_base_cache)){
            $imagem_cache = Image::fromString(Cache::get($this->imagem_base_cache));
            $this->DefinirPropriedades($imagem_cache);

        } else {
            //$arquivo = $this->CarregaImagem($caminho);
            $arquivo = $this->CacheImagem($caminho);
            $imagem_cache = $this->DefineTamanhoImageBase($arquivo, $tamanho_info);
            Cache::put($this->imagem_base_cache, $imagem_cache->getString(), 60);
        }
        return $imagem_cache;
    }

    public function SalvaGaleriaDb($tag, $id, $imagem){
        try {
            Galeria::updateOrCreate(['tipo' => $tag, 'produto_id' => $id],['produto_id' => $id,'imagem' =>  $imagem]);
        }catch (Throwable $e){
            Log::debug("Não foi possível salvar galeria do produto {$id} tag {$tag} imagem {$imagem}.");
            report($e);
        }
    }

    //Cache de todas as imagens por 2 minutos
    //Caminho completo
    // retorna Image
    public function CacheImagem(String $caminho) :Image {
        $cache_key = 'IMG_CACHE_'.base64_encode($caminho);
        if (Cache::has($cache_key)){
            return  Image::fromString(Cache::get($cache_key));
        } else {
            $arquivo = $this->CarregaImagem($caminho);
            Cache::put($cache_key, $arquivo->getString(), 120);
            return $arquivo;
        }
    }

    // Recebe uma copia da imagem base e altura da imagem base
    public function GerarFilete(Image $filete, int $altura) :Image {
        //gera filete
        $filete->crop(15, $altura, 'left','middle');
        $filete->pesp(30,65);
        $filete->flop();
        $filete->resize(10, $altura + 2, true);
        return $filete;
    }

}

?>
