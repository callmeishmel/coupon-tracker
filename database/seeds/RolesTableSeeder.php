<?php

use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      DB::table('roles')->insert([
          'name' => 'admin',
          'display_name' => 'Admin',
          'description' => 'Has full control of the application',
          'created_at' => date('Y-m-d H:i:s'),
          'updated_at' => date('Y-m-d H:i:s')
      ]);

      DB::table('roles')->insert([
          'name' => 'data_manager',
          'display_name' => 'Data Manager',
          'description' => 'Has access to the transaction data suite of tools',
          'created_at' => date('Y-m-d H:i:s'),
          'updated_at' => date('Y-m-d H:i:s')
      ]);

      DB::table('roles')->insert([
          'name' => 'customer_client',
          'display_name' => 'Customer Client',
          'description' => 'Front end user for the application - recipient of coupons, deals and financial information',
          'created_at' => date('Y-m-d H:i:s'),
          'updated_at' => date('Y-m-d H:i:s')
      ]);
    }
}
