<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Classes extends Migration
{
    public function up()
    {
        // Create 'classes' table
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'class_name' => ['type' => 'VARCHAR', 'constraint' => 255],
            'created_at' => ['type' => 'TIMESTAMP', 'null' => true],
            'updated_at' => ['type' => 'TIMESTAMP', 'null' => true],
            'deleted_at' => ['type' => 'TIMESTAMP', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('classes');
    }

    public function down()
    {
        $this->forge->dropTable('classes');
    }
}
