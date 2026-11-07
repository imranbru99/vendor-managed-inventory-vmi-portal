<?php

namespace App\Console\Commands;

use Database\Seeders\DemoSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Console\Command;

class VmiDemoSeedCommand extends Command
{
    protected $signature = 'vmi:demo-seed {--fresh : Run migrate:fresh before seeding}';

    protected $description = 'Seed the VMI Portal with demo tenant data, users, and workflow samples';

    public function handle(): int
    {
        if ($this->option('fresh')) {
            $this->call('migrate:fresh', ['--force' => true]);
        }

        $this->call(RolesAndPermissionsSeeder::class);
        $this->call(DemoSeeder::class);

        $this->info('VMI demo data seeded successfully.');

        return self::SUCCESS;
    }
}
