<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class Students extends Seeder
{
    public function run()
    {
        // Load the Faker library
        $faker = \Faker\Factory::create('id_ID'); // Menggunakan local bahasa Indonesia

        $data = [];
        for ($i = 0; $i < 500; $i++) {
            $data[] = [
                'nis'           => $faker->numerify('##########'), // Generate NIS dengan 10 digit angka
                'nisn'          => $faker->numerify('############'), // Generate NISN dengan 12 digit angka
                'name'          => $faker->name(),
                'gender'        => $faker->randomElement(['L', 'P']),
                'class_id'      => $faker->numberBetween(1, 27), // Asumsikan ada 10 kelas dalam tabel classes
                'rfid_card_id'  => null, // Generate RFID ID dengan angka 10 digit
                'created_at'    => $faker->dateTimeThisYear()->format('Y-m-d H:i:s'),
                'updated_at'    => $faker->dateTimeThisYear()->format('Y-m-d H:i:s'),
            ];
        }

        // Insert data ke tabel students
        $this->db->table('students')->insertBatch($data);
    }
}
