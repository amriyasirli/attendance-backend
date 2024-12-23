<?php

namespace App\Models;

use CodeIgniter\Model;

class StudentModel extends Model
{
    protected $table            = 'students';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nis',
        'nisn',
        'name',
        'gender',
        'class_id',
        'rfid_card_id',
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
        'nis'           => 'permit_empty|numeric|min_length[3]|is_unique[students.nis]',
        'nisn'          => 'required|numeric|min_length[3]|is_unique[students.nisn]',
        'name'          => 'required',
        'gender'        => 'required|max_length[1]',
        'class_id'      => 'permit_empty|numeric',
        'rfid_card_id'  => 'permit_empty|max_length[10]|is_unique[students.rfid_card_id]',
    ];
    protected $validationMessages   = [
        'nis' => [
            'required'    => 'Field NIS is required.',
            'numeric'     => 'Field NIS must contain only numbers.',
            'min_length'  => 'Field NIS must be at least 3 digits long.',
            'is_unique'   => 'Field NIS must be unique; the provided NIS already exists.'
        ],
        'nisn' => [
            'required'    => 'Field NISN is required.',
            'numeric'     => 'Field NISN must contain only numbers.',
            'min_length'  => 'Field NISN must be at least 3 digits long.',
            'is_unique'   => 'Field NISN must be unique; the provided NISN already exists.'
        ],
        'name' => [
            'required'    => 'Field Name is required.'
        ],
        'gender' => [
            'required'    => 'Field Gender is required.',
            'max_length'  => 'Field Gender can only be a single character (e.g., L or P).'
        ],
        'class_id' => [
            'required'    => 'Field Class ID is required.',
            'numeric'     => 'Field Class ID must contain only numbers.'
        ],
        'rfid_card_id' => [
            'max_length'  => 'Field RFID Card ID must not exceed 10 characters.',
            'is_unique'   => 'Field RFID Card ID must be unique; the provided ID already exists.'
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
