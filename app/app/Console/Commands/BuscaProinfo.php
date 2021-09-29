<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Imports\ProinfoImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class BuscaProinfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'buscar:proinfo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

 	$arrContextOptions=array(
            "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ),
       );


       $url = "https://www.decoraonline.com.br/amfeed/feed/download?id=16&file=sku-imagens.csv";
       $contents = file_get_contents($url, true, stream_context_create($arrContextOptions));
       $name = 'Proinfo.csv';
       Storage::disk('public')->put($name, $contents);

        //falha
        //$produtos = Storage::disk('public')->get('Recomendacao.csv'); //storage_path('app\public\Recomendacao.csv');

        $produtos = '/usr/share/nginx/produc/storage/app/public/Proinfo.csv'; //storage_path('app\public\Proinfo.csv');
        //dd($produtos);
        Excel::import(new ProinfoImport,  $produtos);
        echo 'ok';
    }
}
