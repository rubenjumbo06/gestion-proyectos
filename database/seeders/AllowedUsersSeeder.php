<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AllowedUsersSeeder extends Seeder
{
    public function run()
    {
        DB::table('allowed_users')->whereNull('permissions')->update(['permissions' => json_encode(['view'])]);
    }
}