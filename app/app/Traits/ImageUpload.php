<?php

namespace App\Traits;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Imagecow\Image;

trait ImageUpload
{
    public function UserImageUpload($query, $id, $sku) // Taking input image as parameter
    {

        $diretorio = 'originais';

        Storage::disk('public')->makeDirectory($diretorio.'/'.$id);


        $image_name = $sku."_".$id.'_base';
        $ext = strtolower($query->getClientOriginalExtension()); // You can use also getClientOriginalName()
        $image_full_name = $image_name.'.'.$ext;
        $upload_path =  $diretorio.'/'.$id.'/';     //Creating Sub directory in Public folder to put image
        $image_url = $upload_path.$image_full_name;

        //apaga arquivo caso existe, evita erro em move
        if (file_exists( storage_path('app/public/'. $diretorio.'/'.$id.'/'.$image_full_name) )){
            try {
                Storage::disk('public')->delete($diretorio.'/'.$id.'/'.$image_full_name);
            }
            catch(Exception $e){}
        }

        $success = $query->move( storage_path('app/public/'. $diretorio.'/'.$id).'/', $image_full_name );

        $thumb = Image::fromFile(storage_path('app/public/'.$diretorio.'/'.$id.'/'.$image_full_name));
        $thumb->quality(95);
        $thumb->getCompressed();
        $thumb->format('jpg');
        $thumb->transformImageColorspace('rgb');
        $thumb->resize(1200,0,true);
        $thumb->save(storage_path('app/public/'.$diretorio.'/'.$id.'/'.$image_full_name.'.web.jpg'));
        $thumb->resize(300,0,true);
        $thumb->save(storage_path('app/public/'.$diretorio.'/'.$id.'/'.$image_full_name.'.300.jpg'));
        unset($thumb);

        return $image_url; // Just return image
    }
}

?>
