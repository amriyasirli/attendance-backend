<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class Officers extends Seeder
{
    public function run()
    {
        // Load the Faker library
        $faker = \Faker\Factory::create('id_ID'); // Menggunakan locale Indonesia

        $data = [];
        for ($i = 0; $i < 20; $i++) {
            $data[] = [
                'name'       => $faker->name(),
                'email'      => $faker->unique()->email(),
                'phone'      => $faker->numerify('0812########'), // Nomor HP Indonesia dengan 12 digit
                'created_at' => $faker->dateTimeThisYear()->format('Y-m-d H:i:s'),
                'updated_at' => $faker->dateTimeThisYear()->format('Y-m-d H:i:s'),
            ];
        }

        // Insert data ke tabel officers
        $this->db->table('officers')->insertBatch($data);
    }
}
