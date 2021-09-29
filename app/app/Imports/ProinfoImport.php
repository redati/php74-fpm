<?php

namespace App\Imports;

use App\Proinfo;
use Maatwebsite\Excel\Concerns\ToModel;

class ProinfoImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        if(!isset($row[1])){
            return null;
        }
        Proinfo::updateOrCreate(
            ['sku' => $row[0]],
            [
                'sku' =>  $row[0],
                'img' =>  $row[1]
            ]
        );
    }
}
