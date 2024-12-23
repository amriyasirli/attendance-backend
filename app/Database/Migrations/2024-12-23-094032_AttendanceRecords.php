<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AttendanceRecords extends Migration
{
    public function up()
    {
        // Create 'attendance_records' table
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'student_id' => ['type' => 'INT', 'unsigned' => true,],
            'attendance_date' => ['type' => 'DATE', 'null' => true],
            'check_in_time' => ['type' => 'TIME', 'null' => true],
            'check_out_time' => ['type' => 'TIME', 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'officer_id' => ['type' => 'INT', 'unsigned' => true,],
            'created_at' => ['type' => 'TIMESTAMP', 'null' => true],
            'updated_at' => ['type' => 'TIMESTAMP', 'null' => true],
            'deleted_at' => ['type' => 'TIMESTAMP', 'null' => true],
        ]);
        $this->forge->addKey('id');
        $this->forge->addForeignKey('student_id', 'students', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('officer_id', 'officers', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('attendance_records');
    }

    public function down()
    {
        $this->forge->dropTable('attendance_records');
    }
}
