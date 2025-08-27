<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Brand::create([
            'name' => 'MTN',
            'description' => 'Leading telecommunications company in Nigeria.',
            'status' => true,
        ]);

        \App\Models\Brand::create([
            'name' => 'Glo',
            'description' => 'Second largest telecom operator in Nigeria.',
            'status' => true,
        ]);

        \App\Models\Brand::create([
            'name' => 'Airtel',
            'description' => 'Third largest mobile network operator in Nigeria.',
            'status' => true,
        ]);

        \App\Models\Brand::create([
            'name' => '9mobile',
            'description' => 'Nigerian private telecommunications company.',
            'status' => true,
        ]);

        \App\Models\Brand::create([
            'name' => 'DSTV',
            'description' => 'Digital Satellite Television.',
            'status' => true,
        ]);

        \App\Models\Brand::create([
            'name' => 'GOTV',
            'description' => 'Digital Terrestrial Television.',
            'status' => true,
        ]);

        \App\Models\Brand::create([
            'name' => 'Startimes',
            'description' => 'Digital terrestrial television and satellite television.',
            'status' => true,
        ]);

        \App\Models\Brand::create([
            'name' => 'WAEC',
            'description' => 'West African Examinations Council.',
            'status' => true,
        ]);

        \App\Models\Brand::create([
            'name' => 'NECO',
            'description' => 'National Examination Council.',
            'status' => true,
        ]);

        \App\Models\Brand::create([
            'name' => 'JAMB',
            'description' => 'Joint Admissions and Matriculation Board.',
            'status' => true,
        ]);

        \App\Models\Brand::create([
            'name' => 'EKEDC',
            'description' => 'Eko Electricity Distribution Company.',
            'status' => true,
        ]);

        \App\Models\Brand::create([
            'name' => 'IKEDC',
            'description' => 'Ikeja Electricity Distribution Company.',
            'status' => true,
        ]);
    }
}
