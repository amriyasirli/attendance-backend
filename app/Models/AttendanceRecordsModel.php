<?php

namespace App\Models;

use CodeIgniter\Model;

class AttendanceRecordsModel extends Model
{
    protected $table            = 'attendance_records';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'student_id',
        'attendance_date',
        'check_in_time',
        'check_out_time',
        'status',
        'description',
        'officer_id'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'student_id' => 'required|numeric',
        'status' => 'required|in_list[present,absent,late,sick]',
        'officer_id' => 'required|numeric'
    ];
    protected $validationMessages   = [
        'student_id' => [
            'required' => 'Student Not Found!',
            'numeric' => 'Student ID not is Numeric!',
        ],
        'status' => [
            'required' => 'Please enter status',
            'in_list' => 'Data status tidak valid',
        ],
        'officer_id' => [
            'required' => 'Please enter Officer',
            'numeric' => 'User ID not is Numeric!',
        ]
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];
}
