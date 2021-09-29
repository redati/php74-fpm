@extends('layouts.app')

@section('content')



<div class="container-fluid">




    <div class="main">


        <form id="filtro" action="/" method="GET">
            <div class="form-group row">
                <div class=" col-3">
                    <a class="btn btn-sm btn-primary" href="/importar" target="_blank"> Importar Produtos do Magento </a> |
                    <label for="tipo" class="col-form-label">filtrar por tipo</label>

                    <select id="tipo" name="tipo" class="custom-select" style="max-width: 140px;font-size: 18px;">
                        <option value="" @if( request()->get('tipo') == '' ) selected @endif>Todos</option>
                        <option value="simple"  @if( request()->get('tipo') =='simple' ) selected @endif>Simples</option>
                        <option value="configurable" @if( request()->get('tipo') =='configurable' ) selected @endif>Configuraveis</option>
                    </select>
                </div>

                <div class="col-3">
                    <label for="tipo" class="col-form-label">filtrar por status</label>

                    <select id="status" name="status" class="custom-select" style="max-width: 140px;font-size: 18px;">
                        <option value="" @if( request()->get('status') == '' ) selected @endif>Todos</option>
                        <option value="pendente"  @if( request()->get('status') =='pendente' ) selected @endif>Pendente</option>
                        <option value="gerado"  @if( request()->get('status') =='gerado' ) selected @endif>Gerado</option>
                        <option value="sincronizado" @if( request()->get('status') =='sincronizado' ) selected @endif>Sincronizado</option>
                    </select>
                </div>

            </div>
          </form>

          <div id="loading"  style="display:none" ><img src="/images/load.gif"  max-width: 90px;" /></div>
        {{$dataTable->table()}}
    </div>


@endsection

@push('scripts')
{{$dataTable->scripts()}}


<script>

$.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
        });

//gera as imagens
function processar(id){

    $btn = "#btnp-"+id;

    $($btn).addClass('disabled');

    $.ajax({
        type: "GET",
        url: '/processar?id='+id,
        contentType: false,
        processData: false,
        success: function(data)
          {
            $($btn).removeClass('disabled');
            if(data){
                 alert(data);
                 window.LaravelDataTables['produtos-table'].draw(false);

              }
          }

    });
}
//fim

//envia para megento
function enviarMagento(id){

    $btn = "#btne-"+id;

    $($btn).addClass('disabled');

    $.ajax({
        type: "GET",
        url: '/enviaimagem?id='+id,
        contentType: false,
        processData: false,
        success: function(data)
          {
            $($btn).removeClass('disabled');
           if(data){
                 alert(data);
                 window.LaravelDataTables['produtos-table'].draw(false);

              }
          }

    });
}
//fim


//filtra
$('#tipo').on('change', function(){
    $(this).closest('#filtro').submit();
});

$('#status').on('change', function(){
    $(this).closest('#filtro').submit();
});
//fim








    function  alteraStatus(id,status){
      var fdata = new FormData();

      fdata.append('id',id);
      fdata.append('status',status);
      fdata.append('_method','PATCH');
      $.ajax({
          type: "POST",
          url: '/produtos/'+id,
          contentType: false,
          processData: false,
          data: fdata,
          success: function(data)
          {
              if(data){
                  window.LaravelDataTables['produtos-table'].draw(false);
              }
          }
      });

    }


    function  deletar(id){

        if (window.confirm("Deseja realmente apagar o item?")) {

            var fdata = new FormData();
            fdata.append('id',id);
            fdata.append('_method','DELETE');
            $.ajax({
                type: "POST",
                url: '/produtos/'+id,
                contentType: false,
                processData: false,
                data: fdata,
                success: function(data)
                {
                    if(data){
                        window.LaravelDataTables['produtos-table'].draw(false);
                    }
                }
            });

        }

    }


  </script>
  <style>

.btnf input {
    cursor: pointer;
  font-size: 12px;

}</style>
@endpush
