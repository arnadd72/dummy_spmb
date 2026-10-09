<?php

namespace Database\Seeders;

use App\Models\FormField;
use Illuminate\Database\Seeder;

class FormFieldSeeder extends Seeder
{
    public function run(): void
    {
        foreach (FormField::bawaan() as $field) {
            FormField::updateOrCreate(
                ['kunci' => $field['kunci']],
                array_merge($field, ['is_kustom' => false])
            );
        }
    }
}
