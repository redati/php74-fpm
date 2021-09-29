<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(PedidosTableSeeder::class);
        $this->call(ProinfoTableSeeder::class);
        $this->call(ProdutosTableSeeder::class);
    }
}
