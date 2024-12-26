<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class Classes extends Seeder
{
    public function run()
    {
        $data = [];

        // Generate kelas VII, VIII, dan IX
        foreach (['VII', 'VIII', 'IX'] as $grade) {
            for ($i = 1; $i <= 9; $i++) {
                $data[] = [
                    'class_name'       => "$grade.$i",
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
            }
        }

        // Insert data ke tabel classes
        $this->db->table('classes')->insertBatch($data);
    }
}
