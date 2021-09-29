<?php

use Illuminate\Database\Seeder;

class ProinfoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\Proinfo::class,100)->create();
    }
}
