<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $languages = [
            [
                'name' => 'English',
                'lang' => 'en',
                'slug' => 'en',
                'default' => 1,
                'status' => 1
            ],
            [
                'name' => 'Vietnamese',
                'lang' => 'vi',
                'slug' => 'vi',
                'default' => 0,
                'status' => 1
            ]
        ];

        foreach ($languages as $lang) {
            Language::create($lang);
        }
    }
}
