<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CustomUser extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            "fullname" => [
                "type"       => "VARCHAR",
                "constraint" => "255",
                "null"       => true,
                "after"      => "username",
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', ['fullname']);
    }
}
