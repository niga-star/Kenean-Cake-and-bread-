<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Branch;
use App\Models\User;

class StaffAndSalarySeeder extends Seeder
{
    public function run(): void
    {
        $bole = Branch::where('branch_code', 'BR001')->first();
        $super = User::where('role', 'Super Admin')->first();
        if ($super) {
            $super->update([
                'full_name_am' => 'አበበ ከበደ',
                'salary' => 25000.00,
                'employment_status' => 'Active',
            ]);
        }

        $main = User::where('role', 'Main Branch Manager')->first();
        if ($main) {
            $main->update([
                'full_name_am' => 'ዳዊት ታደሰ',
                'salary' => 18000.00,
                'employment_status' => 'Active',
            ]);
        }

        $boleAdmin = User::where('email', 'bole.admin@goldencrust.com')->first();
        if ($boleAdmin) {
            $boleAdmin->update([
                'role' => 'Branch Manager',
                'full_name_am' => 'አልማዝ ኃይሌ',
                'salary' => 14000.00,
                'employment_status' => 'Active',
            ]);
        }

        $megAdmin = User::where('email', 'megenagna.admin@goldencrust.com')->first();
        if ($megAdmin) {
            $megAdmin->update([
                'role' => 'Branch Manager',
                'full_name_am' => 'ጥግስት ዓለሙ በቀለ',
                'salary' => 14000.00,
                'employment_status' => 'Active',
            ]);
        }

        $piassaAdmin = User::where('email', 'piassa.admin@goldencrust.com')->first();
        if ($piassaAdmin) {
            $piassaAdmin->update([
                'role' => 'Branch Manager',
                'full_name_am' => 'ጥግስት ዓለሙ',
                'salary' => 14000.00,
                'employment_status' => 'Active',
            ]);
        }

        // Branch Seller / Cashier
        User::firstOrCreate(
            ['email' => 'seller.bole@goldencrust.com'],
            [
                'full_name' => 'Kidus Yohannes',
                'full_name_am' => 'ቅዱስ ዮሐንስ',
                'phone' => '+251 92 200 0001',
                'password' => Hash::make('password123'),
                'role' => 'Branch Seller / Cashier',
                'branch_id' => $bole ? $bole->branch_id : 2,
                'salary' => 9000.00,
                'status' => 'Active',
                'employment_status' => 'Active',
                'hire_date' => '2024-03-01',
            ]
        );

        // Servant
        User::firstOrCreate(
            ['email' => 'servant.bole@goldencrust.com'],
            [
                'full_name' => 'Meseret Bekele',
                'full_name_am' => 'መሰረት በቀለ',
                'phone' => '+251 92 200 0002',
                'password' => Hash::make('password123'),
                'role' => 'Servant',
                'branch_id' => $bole ? $bole->branch_id : 2,
                'salary' => 7500.00,
                'status' => 'Active',
                'employment_status' => 'Active',
                'hire_date' => '2024-04-15',
            ]
        );

        // Delivery Staff
        User::firstOrCreate(
            ['email' => 'delivery.bole@goldencrust.com'],
            [
                'full_name' => 'Ermias Teshome',
                'full_name_am' => 'ኤርሚያስ ተሾመ',
                'phone' => '+251 92 200 0003',
                'password' => Hash::make('password123'),
                'role' => 'Delivery Staff',
                'branch_id' => $bole ? $bole->branch_id : 2,
                'salary' => 8000.00,
                'status' => 'Active',
                'employment_status' => 'Active',
                'hire_date' => '2024-05-10',
            ]
        );
    }
}
