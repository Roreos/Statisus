<?php

namespace Database\Seeders;

use App\Models\Monitor;
use Illuminate\Database\Seeder;

class MonitorSeeder extends Seeder
{
    public function run(): void
    {
        $monitors = [
            ['name' => 'Google HTTPS',    'type' => 'https', 'target' => 'https://www.google.com',  'interval' => 60,  'expected_status_code' => 200],
            ['name' => 'Cloudflare DNS',  'type' => 'https', 'target' => 'https://1.1.1.1',          'interval' => 30,  'expected_status_code' => 200],
            ['name' => 'Google DNS Ping', 'type' => 'ping',  'target' => '8.8.8.8',                  'interval' => 30],
            ['name' => 'Google DNS TCP',  'type' => 'tcp',   'target' => '8.8.8.8',                  'interval' => 60,  'port' => 53],
            ['name' => 'Example DNS',     'type' => 'dns',   'target' => 'example.com',              'interval' => 300, 'dns_record_type' => 'A'],
        ];

        foreach ($monitors as $data) {
            Monitor::firstOrCreate(['name' => $data['name']], $data);
        }
    }
}
