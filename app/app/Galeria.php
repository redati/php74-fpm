<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Galeria extends Model
{
    protected $primaryKey = 'id';
    //protected $table = 'pedidos';
    public $incrementing = true;
    public $timestamps = true;

    public $date_format = 'd/m/Y';

    protected $guarded = ['id', 'created_at', 'update_at'];


    protected $fillable = [
        'id','produto_id','tipo','imagem'
    ];
}
