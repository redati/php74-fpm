<?php
namespace App\Imagem\Modelos;

use App\Galeria;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use \Imagecow\Image;
use App\Traits\MapeamentoAtributos;
use App\Traits\getStorage;
use Exception;
use Illuminate\Support\Facades\Cache;
use Throwable;

class Modelo extends ModeloBase {
    use MapeamentoAtributos, getStorage;

    public $imagem_base_cache;
    public $imagem_pesp_cache;

    public function GerarModelo($caminho_imagem, $acabamento, $material, $tamanho, $label, $composicao = null, $id, $id_pai = null, $sku, $caminho_base,  $caminho_base_impre, $variacoes, $cat_ids) {

            // produto sem tamanho e cabamento é um produto configuravel
            if (($tamanho == null && $acabamento == null && $material == null) && $label == 'thumbnail'){
                try {
                    $this->GeraImagemThumbPrincipal($caminho_imagem, $composicao, $sku, $label, $caminho_base, $id);
                }catch(Throwable $e){
                    Log::debug(" Problema ao criar thumb principal (configuravel) para id ".$id);
                    report($e);
                }
            }

            // geração de imagens dos produtos simples, variações
            if (isset($material) && isset($acabamento) && isset($tamanho)) {
                try {
                    $material_info =   $this->AttrInfo('materiais', $material);
                    $acabamento_info = $this->AttrInfo('acabamentos', $acabamento);
                    $tamanho_info =    $this->AttrInfo('tamanhos', $tamanho);

                    $posicao = 'frente';
                    // produto que não é composição, é thmb de canvas, é pespectiva (exceção de rolo)
                    if ($label == 'thumbnail' && $material == 1893 && $tamanho_info['composicao'] == false && $acabamento != 198){
                        // thumb da tela canvas é pespectiva
                        $posicao = 'pesp';
                    }

                    //fix para não carregar imagem em cache no cdn
                    $x1 = rand(0,100);
                    $x1 .= rand(0,100);
                    $nome_imagem = trim($sku).'_'.$material_info['nome'].'_'.$acabamento_info['nome'].'_'.$tamanho_info['nome'].'_'.$label.'_'. $x1.'.jpg';

                    // fundo cinza para todas as imagens
                    $imagem_base = $this->CarregaImagemBaseWeb();

                    if ($composicao != null && count($caminho_imagem) > 1){
                        // lógica para composição
                    }else {
                        try {
                            // RETORNA IMAGEM BASE COM ACABAMENTO
                            $imagem = $this->GeraMontagemUnica($caminho_imagem[0]->imagem, $material_info, $acabamento_info, $tamanho_info, $posicao, $caminho_base );
                            $imagem_base->resize(1100,1100);
                            $imagem_base->watermark($imagem, 'center','middle');
                            $imagem_base = $this->OtimizaImagemWeb($imagem_base);

                            if ($imagem_base->save($this->getStorage($caminho_base.'/'.$nome_imagem))){
                                foreach ($variacoes as $produto){
                                    if($produto->material == $material && $produto->acabamento == $acabamento){
                                        $this->SalvaGaleriaDb($label, $produto->id, $caminho_base.'/'.$nome_imagem);
                                    }
                                }
                            }
                            //definido em mapeamento de atributos, adiciona tarja e salva na galeria do configurável
                            if($acabamento_info['thumb_principal']){
                                //garate que a montagem dos canvas não venham na montagem em pespectiva
                                if ($posicao == 'pesp'){
                                    $imagem = $this->GeraMontagemUnica($caminho_imagem[0]->imagem, $material_info, $acabamento_info, $tamanho_info, false, $caminho_base );
                                }
                                $this->AdicionaTarja($imagem_base, $material, $acabamento_info, $caminho_base, $id_pai);
                                $this->AdicinaAmbiente( $imagem , $material, $acabamento_info, $caminho_base, $id_pai, $tamanho_info, $cat_ids);
                            }
                        }catch(Throwable $e){
                            report($e);
                            Log::debug($id. " Erro no processamento da imagem (unica) do produto simples, ações de corte e marca dgua.");
                        }
                    }
                }catch(Throwable $e){
                    report($e);
                    Log::debug($id. " Não foi possivel gerar imagem do produto simples para {$material_info['nome']} {$acabamento_info['nome']} {$tamanho_info['nome']} ");
                }
            }
        return true;
    }

    private function AdicinaAmbiente (Image $imagem_base, $material, $acabamento_info, $caminho_base, $id_pai, $tamanho_info, $cat_ids){
        if (!isset($id_pai) or empty($id_pai)){
            Log::debug("Marcado para criar ACABAMENTO, mas sku pai não definido. ERRO!");
            throw new Exception('id_pai não definido na criação da tarja.');
        }

        // correção de tamanho para ambiente
        $proporcao = $this->DescobreProporcao($imagem_base->getWidth(), $imagem_base->getHeight());
        $orientacao = $this->Orientacao($imagem_base);
        $tamanho = 460;
        $distancia_y = 0;

        $subpasta = $material;
        // para quadros panoramicos na horizontal
        if ($proporcao ==  "proporcao_b" && $orientacao == "vertical"){
            $subpasta = "parede";
        }

        $tipo_ambiente = 'generico';
        // verifica em mapeamento de atributos, se categoria possui ambiente especifico
        if (is_array($cat_ids)){
            foreach ($cat_ids as $cat){
                $amb = $this->Categorias($cat);
                if ($amb){
                    $tipo_ambiente = $amb['ambiente'];
                }
            }
        }



        $files = Storage::disk('baseimages')->allFiles('ambientes/'.$subpasta.'/'.$tipo_ambiente);

        if (count($files) < 1 or ! count($files)){
            Log::debug("Ambiente não encontrado para pasta ".'ambientes/'.$subpasta.'/'.$tipo_ambiente);
        }



        $imagem_aleatoria = rand(1, count($files));
        //RAND 1 - MAX - FIX -1 OFSET
        $imagem_aleatoria--;

        Log::debug("IMA {$imagem_aleatoria} Encontrado ".count($files).' em ambientes/'.$subpasta.'/'.$tipo_ambiente);

        $ambiente = $this->CarregaImagem(storage_path('baseimages/'.$files[$imagem_aleatoria]));
        $ambiente->resize(1000,1000);


        switch($proporcao){
            case 'quadrado':
                $tamanho = 350;
                $distancia_y = 60;
                break;
            case 'proporcao_a':
                $tamanho = 340;
                $distancia_y = 60;
                break;
            case 'proporcao_b':
                $tamanho = 290;
                $distancia_y = 35;
            break;
        }

        // diminui o tamanho dos quadrinhos
        $tamanho = $material == 1893 ? $tamanho : $tamanho - 95;

        // sobe os verticais e abaixa os outros
        if ($orientacao == 'vertical'){
            $imagem_base->resize($tamanho,  0);
            $distancia_y -= 10;

        } else {
            $imagem_base->resize(0, $tamanho );
            $distancia_y += 100;
        }

        // quadrinhos são menores, então ficam mais baixo, ja que os verticais são puxados para cima
        if ($material == '1894' && $orientacao == 'vertical'){
            $distancia_y += 40;
        }

        // definição da tarja
        $nome_tarja = $this->RetornaNomeTarja($material, $acabamento_info);
        $tarja = $this->RetornaTarja($nome_tarja);

        // resoolver essa tag do db
        $tag = 'ambiente_'.$material.'_'.$acabamento_info['nome'];



        $ambiente->watermark($imagem_base, 'center', $distancia_y);

        $ambiente->watermark($tarja, 'center', 'bottom');

        $imagem_base = $this->OtimizaImagemWeb($imagem_base);

        $x1 = rand(0,100);
        $x1 .= rand(0,100);
        $nome_ambiente = $tag.$x1.'.jpg';
        if ($ambiente->save($this->getStorage($caminho_base.'/'.$nome_ambiente))){
            Galeria::updateOrCreate(['tipo' => $tag, 'produto_id' => $id_pai],['produto_id' => $id_pai,'imagem' =>  $caminho_base.'/'.$nome_ambiente]);
        }
    }

    // mesmo nome do arquivo da tarja
    private function RetornaNomeTarja($material, $acabamento_info){
        $tag = '';
        if ($material == '1893' && $acabamento_info['modelo'] == 'chassi'){
            $tag = 'tarja_canvas_chassi';
        } else if ($material == '1893' && $acabamento_info['modelo'] == 'moldura'){
            $tag = 'canvas_moldura';
        } else if ($material == '1894' && $acabamento_info['modelo'] == 'moldura'){
            $tag = 'quadrinho';
        }
        return $tag;
    }
    private function RetornaTarja ($tag) :Image {
        $tarja = $this->CacheImagem(storage_path('baseimages/tarjas/'.$tag.'.png'));
        if ($tarja) {
            return  $tarja;
        }else {
            throw new Exception('não foi possivel carregar tarja.');
        }
    }

    private function AdicionaTarja(Image $imagem_base, $material, $acabamento_info, $caminho_base, $id_pai){
        if (!isset($id_pai) or empty($id_pai)){
            Log::debug("Marcado para criar produto principal com tarja, mas sku pai não definido. ERRO!");
            throw new Exception('id_pai não definido na criação da tarja.');
        }
        // importante, manter mesma tag sempre para sincronizar corretamente com laber no magento

        $tag = $this->RetornaNomeTarja($material, $acabamento_info);
        $tarja = $this->RetornaTarja($tag);
        //$tarja = $this->CarregaImagem(storage_path('baseimages/tarjas/'.$tag.'.png'));

        // carrega imagem de fundo transparente
        $fundo = $this->CarregaImagemBaseWeb();
        // aumenta fundo com 50px a mais que imagem base em cada lado
        $fundo->resize($imagem_base->getWidth() + 50, $imagem_base->getHeight() + 50);
        $tarja->resize($fundo->getWidth() - 15 ,$fundo->getHeight() - 15);

        $fundo->watermark($imagem_base, 'center','middle');
        $fundo->watermark($tarja, 'center','bottom');

        $fundo = $this->OtimizaImagemWeb($fundo);
        $x1 = rand(0,100);
        $x1 .= rand(0,100);
        $nome_imagem_tarja = $tag.'_tarja_'.$x1.'.jpg';
        if ($fundo->save($this->getStorage($caminho_base.'/'.$nome_imagem_tarja))){
            Galeria::updateOrCreate(['tipo' => $tag, 'produto_id' => $id_pai],['produto_id' => $id_pai,'imagem' =>  $caminho_base.'/'.$nome_imagem_tarja]);
        }
    }


    // $imagem CAMINHO DA IMAGEM
    // $material_info, $acabamento_info, $tamanho_info arrays
    // $posicao frente/pesp
    private function GeraMontagemUnica (String $imagem, Array $material_info, Array $acabamento_info, Array $tamanho_info, $posicao = 'frente', $caminho_base) :Image {

        // resize de acordo com proporção definida em mapeamentos de atributos
        // carrega imagem e suas propriedades
        $arquivo_imagem = $this->ImagemBaseCache($this->getStorage($imagem), $tamanho_info);

        $formato = $this->Formato($arquivo_imagem);
        $orientecao = $formato == 'quadrado' ? '' : $this->Orientacao($arquivo_imagem);

        // carrega imagem de fundo transparente
        $fundo = $this->CacheImagem(storage_path('baseimages/bg_trans.png'));
        // aumenta fundo com 50px a mais que imagem base em cada lado
        $fundo->resize($arquivo_imagem->getWidth() + 80, $arquivo_imagem->getHeight() + 80);


        // Adiciona pespectiva na imagem base
        if ($posicao == 'pesp'){
            // criar arquivo de cache para canvas chassi interno, usado 4x
            $this->imagem_pesp_cache = 'IMG_PESP_'.base64_encode($caminho_base.$imagem.$posicao);
            if (Cache::has($this->imagem_pesp_cache)){
                $cache_imagem = Image::fromString(Cache::get($this->imagem_pesp_cache));
                $fundo->watermark($cache_imagem, 'center','middle');
            } else {
                // cria um fundo um pouco maior na lateral para o filete
                $fundo_pesp = $this->CacheImagem(storage_path('baseimages/bg_trans.png'));
                $fundo_pesp->resize($arquivo_imagem->getWidth() + 30, $arquivo_imagem->getHeight());

                // cria o filete
                //$filete_base = $this->CarregaImagem($this->getStorage($imagem));
                //$filete_base = $this->DefineTamanhoImageBase($filete_base, $tamanho_info);
                $filete_base = $this->ImagemBaseCache($this->getStorage($imagem));

                $filete_base = $this->GerarFilete($filete_base, $filete_base->getHeight());
                // adiciona a imagem e o filete no fundo maior na lateral
                $fundo_pesp->watermark($filete_base, 21,'middle');
                $fundo_pesp->watermark($arquivo_imagem, 'right','middle');
                // adiciona pespectiva no fundo com imagem e filete
                $fundo_pesp = $this->AdicionaPespectiva($fundo_pesp);

                Cache::put($this->imagem_pesp_cache, $fundo_pesp->getString(), 60);
                //adiciona a imagem com pespectiva ( fundo pesp, imagem, filete) no fundo principal
                $fundo->watermark($fundo_pesp, 'center','middle');
            }

            //exibir imagem sem moldura para debug
            //debugg
            //$fundo->show();
            //debug

            $mascara_arquivo = 'baseimages/canvas_mascara/'.$tamanho_info['base']['proporcao'].'_'.$orientecao.'_'.$acabamento_info['nome'].'.png';
            $mascara = $this->CarregaImagem(storage_path($mascara_arquivo));
            $mascara->resize($fundo->getWidth(), $fundo->getHeight(), false);
            $fundo->watermark($mascara, 'center','middle');
            unset($mascara);
            return $fundo;
        }
        //canvas em rolo não é usado em composição, pode ser retornado a montagem pronta
        else if ($acabamento_info['nome'] == 'canvas_rolo'){
            return $this->GerarRoloCanvas($arquivo_imagem);
        } else {

            // se não for canvas rolo, canvas thumb pespectiva, retorna imagem com mascara frontal, pode ser quadrinho ou canvas
            // poderá ser adicionado ao fundo cinza ou em ambiente

            // aumento a area transparente para caber a sombra
            $fundo->resize($arquivo_imagem->getWidth() + 150, $arquivo_imagem->getHeight() + 150);

            //debug
            //$fundo->watermark($arquivo_imagem, 'center','middle');
            //$fundo->show();
            //debug

            // material_acabamento_proporcao_orientecao
            $orientecao = $tamanho_info['base']['proporcao'] == 'quadrado' ? '' : $this->Orientacao($arquivo_imagem);
            $mascara_frontal_nome = $material_info['nome'].'_'.$acabamento_info['nome'].'_'.$tamanho_info['base']['proporcao'].'_'.$orientecao.'.png';
            $mascara_frontal_arquivo = $this->CarregaImagem(storage_path('baseimages/mascara_frete/'.$mascara_frontal_nome));
            $mascara_frontal_arquivo->resize($fundo->getWidth(), $fundo->getHeight(), false);

            // se canvas chassi intgerno, não tem mascara, tem sombra, e deve ficar por baixo da imagem
            if ($acabamento_info['nome'] == 'chassi_interno'){
                $fundo->watermark($mascara_frontal_arquivo, 'center','middle');
                $fundo->watermark($arquivo_imagem, 'center','middle');
            } else {
                // adiciona arquivo da imagem no fundo transparente um pouco maior
                $fundo->watermark($arquivo_imagem, 'center','middle');
                $fundo->watermark($mascara_frontal_arquivo, 'center','middle');
            }



            return $fundo;
        }

        return $fundo;

    }

    public function GerarRoloCanvas(Image $imagem_base) :Image {
            $fundo_base  = $this->CarregaImagem(storage_path('baseimages/canvas_rolo.png'));
            $fundo_base  = $this->CarregarImagemWeb($fundo_base);
            $resize = '95%';
            if ($this->formato($imagem_base) == 'quadrado'){
                $resize = '78%';
            }
            if ($this->Orientacao($imagem_base) == 'horizontal'){
                $resize = '68%';
            }
            $imagem_base->resize($resize);
            $imagem_base->rotate(-4);
            $fundo_base->watermark($imagem_base,'center','middle');
            return $fundo_base;
    }

    private function GeraImagemThumbPrincipal($caminho_imagem, $composicao, $sku, $tag, $caminho_base, $id){
        //fix para não carregar imagem em cache no cdn
        $x1 = rand(0,100);
        $x1 .= rand(0,100);
        $nome_imagem = trim($sku).'_'.$tag.'_'.$x1.'.jpg';
        $imagem_fundo = $this->CarregaImagemBaseWeb();
        if (count($caminho_imagem) > 1) {
            // composição
            // criar lógica para composição

        } else {
            $imagem_original = $this->ImagemBaseCache($this->getStorage($caminho_imagem[0]->imagem.'.web.jpg'));
            $imagem_original->resize('95%');
            $maior = $imagem_original->getWidth() > $imagem_original->getHeight() ? $imagem_original->getWidth() :  $imagem_original->getHeight();
            $imagem_fundo->resize($maior + 15);
            $imagem_fundo->watermark($imagem_original,'center','middle');
            $imagem_fundo->resize(1000,1000);
            $imagem_fundo = $this->OtimizaImagemWeb($imagem_fundo);
        }

        if ($imagem_fundo->save($this->getStorage($caminho_base.'/'.$nome_imagem))){
            $this->SalvaGaleriaDb($tag, $id, $caminho_base.'/'.$nome_imagem);
        }
        unset($imagem_fundo);
        return true;
    }



}

?>
