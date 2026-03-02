<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Subscriber;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── المستخدمونl ──
        User::updateOrCreate(
    ['username' => 'hazem270'],
    [
        'name' => 'حازم الشلاف',
        'password' => Hash::make('ahm139713ahm'),
        'role' => 'admin_general'
    ]
);
    
User::updateOrCreate(
    ['username' => 'it123321'],
    [
        'name' => 'معلوماتية',
        'password' => Hash::make('it123321'),
        'role' => 'admin_general'
    ]
);
    }
}
