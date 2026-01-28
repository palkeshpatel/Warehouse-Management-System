<?php

namespace Database\Seeders;

use App\Models\SalesReturnReason;
use Illuminate\Database\Seeder;

class SalesReturnReasonSeeder extends Seeder
{
    public function run(): void
    {
        $reasons = [
            'Panel Damage (CRACK)',
            'Hot-spot/Panel Burnt',
            'Junction Box Burnt/ Voltage Issue',
            'Wrong Product',
            'Other',
        ];

        foreach ($reasons as $reason) {
            SalesReturnReason::create([
                'name' => $reason,
                'is_active' => true,
            ]);
        }
    }
}
