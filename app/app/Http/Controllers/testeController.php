<?php



namespace App\Http\Controllers;

use Illuminate\Http\Request;

//use App\Imagem\Processa;

//novo
use App\Imagem\Modelos\CanvasPespectivaBase;
use App\Imagem\Modelos\CanvasFrenteBase;
use App\Imagem\Modelos\CanvasRoloBase;
use App\Imagens_Bases;
use App\Processos\Passo1ImagemBase;
use App\Produtos;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Imagecow\Image;
use Imagick;


class testeController extends Controller
{

    public function index (Request $request){


    // remove imagem base duplicada
    $produtos = Produtos::where('tipo','configurable')->with(['original'])->get();
    echo '<pre>';
    foreach ($produtos as $produto){
        if (count($produto->original) > 1 ){

            for ($i = 0; $i < count($produto->original); $i++){

                if ($i > 0){
                    $id = $produto->original[$i];
                    if ($id->id){
                        echo 'destruindo '. $id->id . ' <br>';
                        Imagens_Bases::destroy($id->id);
                    }
                }

            }

        }
    }

    die();
    //dd(route('produtos.destroy',55));
    ///$produtos = Produtos::where('status','pendente')->where('tipo','configurable')->with(['original'])->get();
    //foreach ($produtos as $p){
    //    if (count($p->original) < 1){
    //        echo '"'.$p->nome.'",'.$p->sku; echo "<br>";
    //    }
    //}
    //dd(count($produtos));
    //die();



    $produtos = Produtos::where('tipo','configurable')->get();
    $arquitovs = Storage::disk('public')->allFiles('Quadros_Decorativos');





    foreach ($produtos as $produto){

        foreach ($arquitovs as $item){



            $item_l = trim($item);
            $item_l = Str::lower($item_l);
            $item_l_1 = explode('/',$item_l);

            // resover problema das imagens com espaços
            $item_l_2 = trim($item);
            $item_l_2 = Str::lower($item_l_2);
            $item_l_2 = explode(" ",$item_l);
            $item_1_2_2 = [];

            foreach ($item_l_2 as $i){
                $cont = explode("/",$i);
                if ($cont > 1){
                    foreach ($cont as $c){
                        $item_1_2_2[] = $c;
                    }
                }else {
                    $item_1_2_2[] = $i;
                }
            }

            $sku = Str::lower($produto->sku);




            if((in_array($sku, $item_l_1 ) or in_array($sku, $item_1_2_2)) && in_array('original.jpg', $item_l_1)){

                echo 'encontrou '.$sku.' em < '.$item. ' > e original.jpg <br>';

            //if (str_contains($item, $produto->sku) && (str_contains($item, 'Original.jpg') or str_contains($item, 'original.jpg'))){


               $diretorio = 'originais/'.$produto->id;
               ///$string_aleatoria = str_random(5);
               $image_name = $produto->sku."_".$produto->id.'.jpg';



               if ((count(Imagens_Bases::where('produto_id', $produto->id)->get()) > 0) and file_exists(storage_path('app/public/'.$diretorio.'/'.$image_name))){
                    continue;
               }

               if (count(Imagens_Bases::where('produto_id', $produto->id)->get()) < 1 and file_exists(storage_path('app/public/'.$diretorio.'/'.$image_name))){
                    //continue;
                    Storage::disk('public')->delete($diretorio.'/'.$image_name);
               }



               if (!is_dir(storage_path('app/public/'.$diretorio))){
                    Storage::disk('public')->makeDirectory($diretorio);
               }



               if (Storage::disk('public')->copy($item, $diretorio.'/'.$image_name)){

                   try {
                        $thumb = Image::fromFile(storage_path('app/public/'.$diretorio.'/'.$image_name));
                        $thumb->resize(300,0,true);
                        $thumb->quality(70);
                        $thumb->format('jpg');
                        //$thumb->transformImageColorspace('rgb');
                        $thumb->save(storage_path('app/public/'.$diretorio.'/'.$image_name.'.300.jpg'));
                        unset($thumb);
                   } catch (Exception $e){
                       //
                   }


                  Imagens_Bases::updateOrCreate(['produto_id' => $produto->id], ['produto_id' => $produto->id, 'imagem' => $diretorio.'/'.$image_name]);

                   // $imagem_base = new Imagens_Bases();
                    //$imagem_base->produto_id = $produto->id;
                    //$imagem_base->imagem = $diretorio.'/'.$image_name;
                    //$imagem_base->save();
               }


            }
        }

    }


}

}
