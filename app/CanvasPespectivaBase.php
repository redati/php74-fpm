<?php
namespace App\Imagem\Modelos;

use \Imagecow\Image;
use \App\Imagem\Modelos\ImagemBase;

class CanvasPespectivaBase extends ImagemBase {


    public function GerarModelo(Array $caminho_imagem) :Image{

        $qtd = count($caminho_imagem);


        //imagem única ou composição
        if ($qtd == 1){

            $fundo_base  = $this->CarregaImagem(storage_path('baseimages/bg.png'));
            $fundo_base  = $this->CarregarImagemWeb($fundo_base);

            $imagem_base = $this->GerarBase($caminho_imagem[0]);
            $imagem_base = $this->AdicionaPespectiva($imagem_base);

            $filete_base = $this->GerarBase($caminho_imagem[0]);
            $filete = $this->GerarFilete($filete_base, $imagem_base->getHeight());


            $distanciaFilete = $this->DistanciaFileteImagem($imagem_base);

            $fundo_base->watermark($filete, $distanciaFilete,'middle');
            $fundo_base->watermark($imagem_base,'center','middle');

            return $fundo_base;

        }

        // $this->imagem_original com imagem base pronta para web

    }

    public function DistanciaFileteImagem(Image $imagem) :int{
        $distancia = 0;
        if ($this->formato == 'quadrado'){
            $distancia = 75;
        }else if ($this->formato == 'panoramico' && $this->orientacao == 'vertical'){
            $distancia = 351;
        }else if ($this->formato == 'panoramico' && $this->orientacao == 'horizontal'){
            $distancia = 105;
        }else if ($this->formato == 'retangular' && $this->orientacao == 'vertical'){
            $distancia = 190;
        }else if ($this->formato == 'retangular' && $this->orientacao == 'horizontal'){
            $distancia = 66;
        }
        return $distancia;
    }


    // Recebe uma copia da imagem base e altura da imagem base
    public function GerarFilete(Image $filete, int $altura) :Image{
        //gera filete
        $filete->crop(15, $filete->getHeight(), 'left');
        $filete->pesp(30,55);
        $filete->flop();

        $filete->resize(10, $filete->getHeight(), true);
        return $filete;
    }

    public function AdicionaPespectiva(Image $imagem) :Image{
        $imagem->pesp(3,97);
        return $imagem;
    }



}


?>
