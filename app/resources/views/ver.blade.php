@extends('layouts.app')

@section('content')



<div class="container">


    <div class="main">
        <h3>Informe o ID do Pedido ou Código de Rastreio</h3>
        <form action="{{ route('get') }}" method="GET" id="getInfo">
            @csrf
            <div class="form-group">
              <label for="pedido_id">Id do pedido com 9 dígitos, ou Código de Rastreio. Caso não haja resultado,
                verifique a informação digitada ou o pedido ainda não foi coletado pela transportadora. </label>
              <input type="text" class="form-control" name="id" id="id" aria-describedby="emailHelp" placeholder="Digite Aqui" style=" max-width: 300px; ">
            </div>

            <button type="submit" class="btn btn-primary">Enviar</button>
          </form>
          <hr>
            <div id="volumes" class="hide">

                <h4>Volumes:</h4>
                <table id="table" class="table table-responsive-sm table-stripe">
                    <thead>
                      <tr>
                        <th scope="col">Nome</th>
                        <th scope="col">Nota Fiscal</th>

                        <th scope="col">Código Rastreio</th>
                        <th scope="col">Status do Pedido</th>

                        <th scope="col">Transportadora</th>
                        <th scope="col">Status do Transporte</th>
                      </tr>
                    </thead>
                    <tbody>



                    </tbody>
                  </table>


            </div>

          <hr>


            <div class="container">
              <h4 >Status do Pedido</h4>

              <p class="lead">Status (Aguardando): Aguardando informação do envio na transportadora.</p>
              <p class="lead">Status (Transportando): Transportadora está encaminhando seu pedido.</p>
              <p class="lead">Status (Verificar): Houve uma exceção, entre em contato para mais informações. </p>
              <p class="lead">Status (Entregue): Pedido entregue. </p>
              <h4 >Status do Transporte</h4>
                <p>
                    Status do pedido na transportadora (Emcaminhado, em rota, entregue, custodia...)
                </p>
                <hr>
                <h4 >Privacidade</h4>
              <p>Nenhuma informação sensível é acessível nesta página.</p>

                <hr>
                contato@decoraonline.com.br - 31 33845000
              </div>

    </div>

@endsection

@push('scripts')

<script>
//$("#volumes").hide();

    $("#getInfo").submit(function(e) {

        e.preventDefault();

        var form = $(this);
        var url = form.attr('action');

        $.ajax({
           type: "GET",
           url: url,
           data: form.serialize(), // serializes the form's elements.
           success: function(data)
           {

                data.forEach(imprimir);

                function imprimir(item){


                         $('#table > tbody').append('<tr> <td>'+item.cliente_nome+'</td> <td>'+item.pedido_nf+'</td> <td>'+item.codigo_rastreio+'</td> <td>'+item.status+'</td> <td>'+item.transportadora+'</td> <td>'+item.status_transportadora+'</td></tr>');

                }

                $("#volumes").show();


               //console.log(data);

               //$("#log").html(data);
           }
         });

    });
</script>


@endpush


