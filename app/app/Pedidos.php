<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pedidos extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'pedidos';
    public $incrementing = true;
    public $timestamps = true;

    public $date_format = 'd/m/Y';

    protected $guarded = ['id', 'created_at', 'update_at'];


    protected $fillable = [
        'id','status','observacoes','observacaointerna','data','numero','loja','numeroPedidoLoja',
        'nome','volumes','nota','trans','tras_nome','trans_serv'

    ];

    public function produtos(){

        return $this->hasMany('App\Produtos','pedidos_id', 'id')->with(['proinfo']);

    }
}
