<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Produtos extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'produtos';
    public $incrementing = true;
    public $timestamps = true;

    //public $date_format = 'd/m/Y';

    protected $guarded = ['id', 'created_at', 'update_at'];


    protected $fillable = [
        'id','nome','id_magento','sku','tipo','composicao','categorias_ids','material',
        'acabamento','tamanho','url','op_conf','op_filhos','status','tipo_composicao'
    ];

    public function original (){
        return $this->HasMany('App\Imagens_Bases','produto_id','id');
    }

    public function galeria (){
        return $this->HasMany('App\Galeria','produto_id','id');
    }
}
