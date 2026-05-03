<?php

namespace Database\Seeders;

use App\Models\Visibility;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use function Laravel\Prompts\table;

class VisibilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Visibility::create([
            'company' => 'Bokreah',
            'salary' => 0,
        ]);
      //  DB::table('visibilities')->insert([
      //      'company' => 'Bokreah',
      //      'salary' => 0,
      //  ]);
    }
}
