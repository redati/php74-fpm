<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Magento\RestApi;
use App\Magento\Magento;
use App\Pedidos;

class BuscaPedidos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'busca:pedidos';

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
        $service = new Magento( new RestApi );

        $data['fields'] = 'items[items,entity_id]';

        $data['searchCriteria'] = [
            'filterGroups' => [
                0 => [
                    'filters' => [

                    ]
                ]
            ],
            'pageSize' => 500,
            'currentPage' => 1
        ];

        $result = $service->api->call('orders', $data, 'GET');

        foreach ($result->items as $item){



            foreach ($item->items as $produto){
                Pedidos::updateOrCreate(
                    ['order_id' => $item->entity_id],
                    ['order_id' => $item->entity_id, 'item_id' => $produto->product_id]
                );
            }
        }


    }
}
