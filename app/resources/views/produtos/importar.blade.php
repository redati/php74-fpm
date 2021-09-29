@extends('layouts.app')

@section('content')



<div class="container-fluid">




    <div class="main">

        <form id="filtro" action="/importar" method="GET">
            <div class="form-group row">
                <div class="offset-12 col-12">

                    <label for="busca">ID no Magento</label>
                    <input type="text" title="ID no Magento" name="busca" value="{{ Request::input('busca') }}"/>
                    <button title="buscar" value="buscar">Buscar por ID</button>
                    |
                    <label for="status" class="col-form-label">Importar por periodo</label>

                    <input type="datetime-local" name="de" value="{{ Request::input('de') }}" />
                    <input type="datetime-local" name="para"  value="{{ Request::input('para') }}" />

                    <button type="submit" title="Importar"> Importar </button>

                </div>
            </div>
          </form>

          <div id="loading"  style="display:none" ><img src="/images/load.gif"  max-width: 90px;" /></div>

    </div>


@endsection


