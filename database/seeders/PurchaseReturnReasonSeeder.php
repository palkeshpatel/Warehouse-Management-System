<?php

namespace Database\Seeders;

use App\Models\PurchaseReturnReason;
use Illuminate\Database\Seeder;

class PurchaseReturnReasonSeeder extends Seeder
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
            PurchaseReturnReason::create([
                'name' => $reason,
                'is_active' => true,
            ]);
        }
    }
}
