<?php

namespace Database\Seeders;

use App\Modules\Core\Models\Package;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            [
                'name' => 'Wedding',
                'default_modules' => ['invitation', 'photobooth', 'guest_photographer', 'voice_note'],
                'default_storage_days' => 90,
            ],
            [
                'name' => 'Birthday',
                'default_modules' => ['invitation', 'photobooth', 'voice_note'],
                'default_storage_days' => 60,
            ],
            [
                'name' => 'Corporate',
                'default_modules' => ['photobooth', 'guest_photographer'],
                'default_storage_days' => 30,
            ],
        ];

        foreach ($packages as $package) {
            Package::updateOrCreate(['name' => $package['name']], $package);
        }
    }
}
