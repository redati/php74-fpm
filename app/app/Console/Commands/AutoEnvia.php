<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Processos\EnviarImagemMagento;
use App\Produtos;
use Exception;

class AutoEnvia extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:envia';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia imagens para magento';

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
        // Pega todos os produtos com imagens geradas, verifica se tem galeria e então envia para o magento.
        $produtos = Produtos::where('status','gerado')->where('tipo','configurable')->with(['original','galeria'])->get();
        foreach ($produtos as $produto){
            if (count($produto->original) && count($produto->galeria)){
                $produto = Produtos::where('id', $produto->id)->get()->first();
                if ($produto){
                    try{
                        $envia = new EnviarImagemMagento();
                        $envia->Enviar($produto);
                        sleep(1);
                    }catch(Exception $e){
                        //debug
                    }
                }
            }
        }
        return 'ok';
    }
}
