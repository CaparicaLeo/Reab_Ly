<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Treatment;
use App\Models\TreatmentItem;

class TreatmentItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Treatment::all()->each(function (Treatment $treatment) {
            // 2 exercise-style items
            TreatmentItem::factory()
                ->count(2)
                ->exercise()
                ->for($treatment)
                ->create();

            // 1 timed item
            TreatmentItem::factory()
                ->timed()
                ->for($treatment)
                ->create();
        });
    }
}
