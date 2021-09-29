<?php

//https://devdocs.magento.com/guides/v2.3/rest/performing-searches.html
//"eq" => equalValue
// "neq" => notEqualValue
 //"like" => likeValue
 //"nlike" => notLikeValue
 //"is" => isValue
 //"in" => inValues
 //"nin" => notInValues
 //"notnull" => valueIsNotNull
 //"null" => valueIsNull
 //"moreq" => moreOrEqualValue
 //"gt" => greaterValue
 //"lt" => lessValue
 //"gteq" => greaterOrEqualValue
 //"lteq" => lessOrEqualValue
 //"finset" => valueInSet
 //"from" => fromValue, "to" => toValue

namespace App\Http\Controllers;

use App\Magento\Magento;
use Illuminate\Http\Request;
use App\Magento\RestApi;
use App\Processos\ImportarMagento;
use App\Produtos;
use Illuminate\Support\Carbon;

use DateTime;
use Illuminate\Support\Facades\DB;
use Psy\CodeCleaner\FunctionReturnInWriteContextPass;

class ImportarController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        if( !empty($request->input('de') ) && !empty($request->input('para')) or (!empty($request->input('busca')))){

            $importar = new ImportarMagento();
            $importar->importar($request->input('de'), $request->input('para'), $request->input('busca'));

        }
        echo 'ok';

        return view('produtos.importar');


    }

}
