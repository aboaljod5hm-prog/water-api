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
        // ── المستخدمون ──
        User::create(['name' => 'أحمد الإدارة', 'username' => 'admin',   'password' => Hash::make('admin123'),   'role' => 'admin_general']);
        User::create(['name' => 'محمد المدير',  'username' => 'manager', 'password' => Hash::make('manager123'), 'role' => 'admin']);
        User::create(['name' => 'علي العامل',   'username' => 'worker1', 'password' => Hash::make('worker123'),  'role' => 'worker']);
        User::create(['name' => 'حسن القراءة',  'username' => 'worker2', 'password' => Hash::make('worker123'),  'role' => 'worker']);

        // ── المشتركون ──
        Subscriber::create(['name' => 'محمد عبدالله الأحمد', 'meter_no' => 'M-10021', 'section_no' => 'Q-01', 'reading' => 1540, 'reading_date' => '2025-01-15']);
        Subscriber::create(['name' => 'سالم علي السالم',     'meter_no' => 'M-10022', 'section_no' => 'Q-01', 'reading' => 980,  'reading_date' => '2025-01-16']);
        Subscriber::create(['name' => 'خالد محمد الخالد',    'meter_no' => 'M-10023', 'section_no' => 'Q-02', 'reading' => 2100, 'reading_date' => '2025-01-16']);
        Subscriber::create(['name' => 'عبدالرحمن فهد',       'meter_no' => 'M-10024', 'section_no' => 'Q-02', 'reading' => 670,  'reading_date' => '2025-01-17']);
        Subscriber::create(['name' => 'فيصل سعود المطيري',   'meter_no' => 'M-10025', 'section_no' => 'Q-03', 'reading' => 3200, 'reading_date' => '2025-01-18']);
        Subscriber::create(['name' => 'نواف عبدالعزيز',      'meter_no' => 'M-10026', 'section_no' => 'Q-03', 'reading' => 450,  'reading_date' => null]);
    }
}
