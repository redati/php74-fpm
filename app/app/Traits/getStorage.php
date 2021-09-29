<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;

trait getStorage {

    public function getStorage($pasta = null) :String {
        if (empty($pasta) or $pasta == null){
            return storage_path('app/public/');
        } else {
            return storage_path('app/public/'.$pasta);
        }
    }
    public function getImgproStorage() :String{
        return $this->getStorage('imgpro/');
    }
    public function getImpressaoStorage() :String{
        return $this->getStorage('impressao/');
    }
    public function getOriginalStorage() :String{
        return $this->getStorage('originais/');
    }

}


?>
