<?php

namespace App\DataTables;

use App\Imagem\Modelos\ImagemBase;
use \App\Produtos;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class ProdutosDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {




        if (request()->has('tipo')) {
                $query->where('tipo', 'like', "%" . request('tipo') . "%");
        }
        if (request()->has('id')) {
            $query->where('sku_pai', 'like', request('id'));
        }
        if (request()->has('status')) {
            $query->where('status', 'like', "%" . request('status') . "%");
        }


        //if (request()->has('status')) {
        //    if( request('status') != 'todos' and request('status') != 'padrao'){
        //        $query->where('status', 'like', "%" . request('status') . "%");
        //    }
        //} else {
        //    $query->where('status', 'like', "%" . 'produzir' . "%");
                //->orWhere('status', 'like', "%" . 'faturar' . "%")
                //->orWhere('status', 'like', "%" . 'pendente' . "%")
                //->orWhere('status', 'like', "%" . 'enviar' . "%");
        //}

        return datatables()
            ->eloquent($query)
            ->rawColumns(['action', 'original', 'galeria', 'sku'])
            ->editColumn('sku', function($query){
                return '<a href="https://decoraonline.com.br/'.$query->url.'" target="_blank">'.$query->sku.' <i class="fas fa-external-link-alt"> </i> </a> ';
            })
            ->addColumn('galeria', function($query){

                $result = '<div class="container"><div class="row">';

                if (count($query->galeria)){
                    foreach ($query->galeria as $im){

                        $result .= '<div class="col-sm"><div class="card" style="width: 110px; height: auto; float:left">';
                        $result .= '<a href="/storage/'.$im->imagem.'?image-type=image" data-lightbox="/storage/'.$im->imagem.'">';
                        $result .= '<img src="/storage/'.$im->imagem.'" style=" width: 100%; height: auto; max-height: 130px;" />';
                        $result .= '</a>';
                        $result .= '</div></div>';
                    }

                }


                $result .= '</div></div>';

                return $result;

            })
            ->addColumn('original', function($query){

                $result = '<div class="container"><div class="row">';

                 $btnfile = '<form name="imageUpload" class="enviaarquivo" action="'.route('ImagensBases.store').'" method="post" enctype="multipart/form-data">';
                 $btnfile .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
                 $btnfile .= '<input type="hidden" name="produto_id" value="'.$query->id.'" />';
                 $btnfile .= '<input type="hidden" name="sku" value="'.$query->sku.'" />';
                 $btnfile .= '<div class="file  btn-sm btnf"> <input type="file" name="imagem" /></div> <input type="submit" value="enviar"> ';
                 $btnfile .= '</form>';

                if ($query->tipo == 'configurable'){
                    if ( count($query->original) ){
                        foreach ($query->original as $im){

                            $result .= '<div class="card" style="width: 110px; height: auto; float:left" >';

                            $result .= '

                            <img src="/storage/'.$im->imagem.'.300.jpg" style=" width: 100%; height: auto;" />

                            ';

                            //$result .= '<div class="file btn btn-lg btn-primary btn-sm btnf"> Enviar Imagem  <input type="file" name="imagem" /> </div>';
                            $result .= $btnfile;

                        }
                    }
                } else {
                    $produto_pai = Produtos::where('id', $query->sku_pai)->with(['original'])->get()->first();


                    if ( isset($produto_pai->original) ){

                        foreach ($produto_pai->original as $im){

                            $result .= '<div class="card" style="width: 110px; height: auto; float:left" >';

                            $result .= '<img src="/storage/'.$im->imagem.'.300.jpg" style=" width: 100%; height: auto;" />';

                            $result .= ' </div>';

                        }
                    }


                }





                $result .= '</div>';

                //$ordem = count($query->original) +1;
                if ($query->tipo == 'configurable' && (count($query->original) < $query->composicao)){

                    $result .= $btnfile;

                }

                return $result;

            })
            ->addColumn('action', function($query){
                $cor = 'danger';
                if ($query->status == 'pendente'){ $cor = 'secondary';  }
                if ($query->status == 'gerado'){ $cor = 'primary';  }
                if ($query->status == 'sincronizado'){ $cor = 'info';  }

                $result =  '

                <div class="btn-group " role="group">

                    <button id="btnGroupDrop1" type="button" class="btn btn-'.$cor.' dropdown-toggle btn-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <p style=" font-size: 18px; ">'.$query->status.' </p>
                    </button>
                    <div class="dropdown-menu " aria-labelledby="btnGroupDrop1">
                    <button type="button" onClick="alteraStatus('.$query->id.',\'pendente\')" class="dropdown-item btn-sm" style=" font-size: 18px; ">Pendente</button>
                    <button type="button" onClick="alteraStatus('.$query->id.',\'gerado\')" class="dropdown-item btn-sm" style=" font-size: 18px; ">Gerado</button>
                    <button type="button" onClick="alteraStatus('.$query->id.',\'sincronizado\')" class="dropdown-item btn-sm" style=" font-size: 18px; ">Sincronizado</button>
                    </div>
                </div>


                ';

                if($query->tipo == 'configurable'){

                    $result .= '<a class="btn btn-primary btn-sm" href="/?id='.$query->id.'" target="_blank"> Variações </a>';

                    $result .= ' | <a id="btnp-'.$query->id.'" class="btn btn-primary btn-sm" href="javascript:processar('.$query->id.')" "> 1º Processar </a>';
                    $result .= ' | <a  id="btne-'.$query->id.'" class="btn btn-primary btn-sm" href="javascript:enviarMagento('.$query->id.')" >2º Enviar Loja </a>';

                    $result .= ' | > > <a  id="btne-'.$query->id.'" class="btn btn-danger btn-sm" href="javascript:deletar('.$query->id.')" > Del </a>';

                }

                return $result;
            });
             /*
              ->editColumn('produtos', function ($query) {


              $itens = '';

              foreach ($query->produtos as $item){

                    $itens .= '<div class="row border" style="min-width: 560px;">';
                    if (!empty($item->proinfo->img)){
                        $itens .= '<div class="col-3">

                        <a href="'.$item->proinfo->img.'?image-type=image" data-lightbox="'.$item->proinfo->img.'">
                        <img src="'.$item->proinfo->img.'?image-type=thumbnail" class="rounded" style="max-width: 100px;"  />
                        </a>
                        </div>';
                    }
                    $itens .= '<div class="col-9 ">';
                    $itens .= 'Produto: '. $item->nome . '<br>';
                    $itens .= 'SKU: ' . $item->sku . '<br>';
                    $itens .= 'QTD: ' . $item->qtd . '<br>';
                    if ( $item->link ){
                        $itens .= '<a href="' . $item->link . '" target="_blank"> Link </a><br>';
                    }


                    $itens .= ' Produção:
                        <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" id="prom-'.$item->id.'" value="option1">
                        <label class="form-check-label" for="prom-'.$item->id.'">Materiais</label>
                        </div> |
                        <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" id="proi-'.$item->id.'" value="option2">
                        <label class="form-check-label" for="proi-'.$item->id.'">Impressão</label>
                        </div> |
                        <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" id="prop-'.$item->id.'" value="option2">
                        <label class="form-check-label" for="prop-'.$item->id.'">Pronto</label>
                        </div>';
                    $itens .= '</div> ';
                    $itens .= '</div>';
              }

                return $itens;
            }); */
    }

    /**
     * Get query source of dataTable.
     *
     * @param \Produtos $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Produtos $model)
    {


        //return $model->newQuery();

        return  Produtos::with(['original','galeria']);
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {


        return $this->builder()
                    ->setTableId('produtos-table')
                    ->columns($this->getColumns())
                    ->addTableClass(' table-responsive-sm')
                    ->minifiedAjax()
                    ->dom('Bfrtip')
                    ->orderBy(1,'asc')
                    ->language(["url" => "/DataTable-Portuguese-Brasil.json"])
                    ->responsive(true)
                    ->deferRender(true)
                    ->addTableClass('table table-hover table-striped table-sm')
                    ->select(true)
                    ->select([
                         'style'=>'multi',
                         'selector' => 'td:first-child'
                    ])
                    ->paging(true)
                    ->pageLength('50')
                    ->stateSave(true)
                    ->minifiedAjax()
                    ->buttons(
                        Button::make('reset')
                    );
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {



        return [

            Column::make('id')->title('Nome')->class('small')->width('20'),
            Column::make('nome')->title('Nome')->class('small')->width('200'),
            Column::make('sku')->title('SKU')->class('small')->width('100'),
            Column::make('original')->title('IM Original')->class('small'),
            Column::make('galeria')->title('Galeria Site')->class('small'),
            Column::make('action')->title('Andamento')->class('small')->width(500)

        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'Produtos_' . date('YmdHis');
    }
}
