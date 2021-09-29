<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Imagens_Bases extends Model
{
    protected $fillable = [
        'id','produto_id','imagem'

    ];
}
