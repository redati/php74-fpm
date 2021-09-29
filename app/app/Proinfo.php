<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Proinfo extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'proinfo';
    public $incrementing = true;
    public $timestamps = true;

    public $date_format = 'd/m/Y';

    protected $guarded = ['id', 'created_at', 'update_at'];


    protected $fillable = [
        'id','sku','img','info','tipo','link'

    ];

    public function proinfo(){

        return $this->hasOne('App\Proinfo','proinfo_id', 'id');

    }

    public function pedido(){

        return $this->hasOne('App\Pedido','pedidos_id', 'id');

    }
}
