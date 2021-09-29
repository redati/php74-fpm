<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Processos\EnviarImagemMagento;
use App\Produtos;
use Exception;

class EnviarMagentoController extends Controller
{
    public function index (Request $request){

        $id = $request->input('id');

        if (empty($id) ){
            throw new Exception('id inválido');
        }
        $produto = Produtos::where('id',$id)->with(['galeria'])->get()->first();
        if ($produto){
            $enviar = new EnviarImagemMagento();
            $enviar->Enviar($produto);
        }
        return 'ok';

    }







}

