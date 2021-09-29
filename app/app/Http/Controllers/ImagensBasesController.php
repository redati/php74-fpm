<?php

namespace App\Http\Controllers;

use App\Imagem\Modelos\ImagemBase;
use App\Imagens_Bases;
use Illuminate\Http\Request;
use App\Traits\ImageUpload;
use Exception;

class ImagensBasesController extends Controller
{
    use ImageUpload; //Using our created Trait to access inside trait method

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        $this->validate($request,[
            'imagem'  =>  'required|image|mimes:jpeg,png,jpg,gif'
        ]);

        if($request->imagem){
           try {
            $filePath = $this->UserImageUpload($request->imagem, trim($request->produto_id), trim($request->sku));

                if ($filePath){
                    $imagem = Imagens_Bases::updateOrCreate(
                        ['produto_id' => $request->produto_id],
                        [
                            'imagem' =>  $filePath,
                            'produto_id' => $request->produto_id

                        ]
                    );
                    return redirect()->back();
                }

            } catch (Exception $e) {

                throw new Exception ($e->getMessage());

            }
        }

    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Imagens_Bases  $imagens_Bases
     * @return \Illuminate\Http\Response
     */
    public function show(Imagens_Bases $imagens_Bases)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Imagens_Bases  $imagens_Bases
     * @return \Illuminate\Http\Response
     */
    public function edit(Imagens_Bases $imagens_Bases)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Imagens_Bases  $imagens_Bases
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Imagens_Bases $imagens_Bases)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Imagens_Bases  $imagens_Bases
     * @return \Illuminate\Http\Response
     */
    public function destroy(Imagens_Bases $imagens_Bases)
    {
        //
    }
}
