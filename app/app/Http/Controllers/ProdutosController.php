<?php

namespace App\Http\Controllers;

use App\DataTables\ProdutosDataTable;
use App\Galeria;
use App\Imagens_Bases;
use App\Produtos;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProdutosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(ProdutosDataTable $datatable)
    {
        return $datatable->render('produtos.lista');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Produtos  $produtos
     * @return \Illuminate\Http\Response
     */
    public function show(Produtos $produtos)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Produtos  $produtos
     * @return \Illuminate\Http\Response
     */
    public function edit(Produtos $produtos)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Produtos  $produtos
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        if (isset($id)){
            if (empty($id)){
                return;
            }
        }
        $produtos = Produtos::findOrFail($id);
        $produtos->status = $request->input('status') ? $request->input('status') : $request->status;
        $produtos->save();
        return $id;

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Produtos  $produtos
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (isset($id)){
            if (empty($id)){
                return;
            }
        } else {
            return;
        }
        $produto = Produtos::findOrFail($id);

        $this->apagaGaleria($id);
        $this->apagaImagemBase($id);
        $produto->delete();

        $produtos = Produtos::where('sku_pai',$id)->get();

        if (count($produtos)){
            foreach ($produtos as $p){
                $p->delete();
                $this->apagaGaleria($p->id);
                $this->apagaImagemBase($p->id);
            }
        }


       return  'ok';

    }

    private function apagaImagemBase ($id){
        if (isset($id)){
            if (empty($id)){
                return;
            }
        } else {
            return;
        }

        $imagens_bases = Imagens_Bases::where('produto_id',$id)->get();

        if (count($imagens_bases)){
            foreach($imagens_bases as $im){
                if ($im->id){
                    Imagens_Bases::destroy($im->id);
                }
            }
            try {
                if(is_dir(storage_path('app/public/originais/'.$id))){
                    Storage::disk('public')->deleteDirectory('originais/'.$id);
                }
            }catch(Exception $e){ }
        }

    }
    private function apagaGaleria ($id){

        if (isset($id)){
            if (empty($id)){
                return;
            }
        } else {
            return;
        }

        $galeria = Galeria::where('produto_id',$id)->get();
        if (count($galeria)){
            foreach($galeria as $im){
                if ($im->id){
                    Galeria::destroy($im->id);
                }
            }
            try {
                if(is_dir(storage_path('app/public/imgpro/'.$id))){
                    Storage::disk('public')->deleteDirectory('imgpro/'.$id);
                }
            }catch(Exception $e){ }
        }
    }


}
