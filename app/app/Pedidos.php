<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pedidos extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'pedidos';
    public $incrementing = true;
    public $timestamps = false;

    public $date_format = 'd/m/Y';

    protected $fillable = [
        'id','order_id','item_id'

    ];


}
