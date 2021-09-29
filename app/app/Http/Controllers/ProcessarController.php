<?php

namespace App\Http\Controllers;

use App\Processos\ProcessaImagens;
use Illuminate\Http\Request;


class ProcessarController extends Controller
{

    public function processar (Request $request){

        $id = intval ( $request->input('id') );

        if (!is_numeric($id)){
            return 'id inválido';
        }

        $processar = new ProcessaImagens();
        $processar->processar($id);

        return 'ok';

    }

}
