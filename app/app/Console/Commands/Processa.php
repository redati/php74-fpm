<?php

namespace App\Console\Commands;

use App\Processos\EnviarImagemMagento;
use App\Processos\ProcessaImagens;
use App\Produtos;
use Illuminate\Console\Command;
use Exception;

class Processa extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:reprocessa';

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

       // $produtos =   Produtos::where('status','sincronizado')->where('tipo','configurable')->orderBy('updated_at','asc')->limit(1)->get();
        //foreach ($produtos as $produto){
          //     try {
          //          $processar = new ProcessaImagens();
          //          $processar->processar($produto->id);
                    //echo 'processado produto '.$produto->nome;
          //      }catch (Exception $e){
                    //debug
         //       }

        //    }


        $produtos =   Produtos::where('status','gerado')->where('tipo','configurable')->orderBy('updated_at','asc')->limit(1)->get();
        foreach ($produtos as $produto){
               try {
                    $envia = new EnviarImagemMagento();
                    $envia->Enviar($produto);

                    //echo 'processado produto '.$produto->nome;
                }catch (Exception $e){
                    $produto->status = 'pendente';
                    $produto->save();
                }

            }


        return 0;
    }
}
