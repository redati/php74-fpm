<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Processos\ProcessaImagens;
use App\Produtos;
use Exception;

class AutoProcessa extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:processa';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Processa imagens pendentes';

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
        $produtos =   Produtos::where('status','pendente')->where('tipo','configurable')->with(['original'])->get();
        foreach ($produtos as $produto){
            if (count($produto->original)){
                if (file_exists(storage_path('app/public/'.$produto->original[0]->imagem))){
                    try{
                        $processar = new ProcessaImagens();
                        $processar->processar($produto->id);
                    }catch (Exception $e){
                        //debug
                    }
                }
            }
        }
        return 'ok';
    }
}
