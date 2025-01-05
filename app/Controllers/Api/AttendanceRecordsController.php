<?php

namespace App\Controllers\Api;

use App\Models\AttendanceRecordsModel;
use App\Models\StudentModel;
use CodeIgniter\HTTP\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class AttendanceRecordsController extends ResourceController
{
    use ResponseTrait;

    protected $Model, $Students, $table;

    public function __construct()
    {
        $this->Model = new AttendanceRecordsModel();
        $this->Students = new StudentModel();
        $this->table = 'attendance_records';
    }

    /**
     * Return an array of resource objects, themselves in array format.
     *
     * @return ResponseInterface
     */
    public function index()
    {
        $data = $this->Model->select('attendance_records.*, name')->join('students', 'students.id=attendance_records.student_id')->orderBy('id', 'DESC')->findAll();

        $msg = 'Data retrieved successfully!';
        return $this->respond($data, 200, $msg);
    }

    /**
     * Return the properties of a resource object.
     *
     * @param int|string|null $id
     *
     * @return ResponseInterface
     */
    public function show($id = null)
    {
        //
    }

    /**
     * Return a new resource object, with default properties.
     *
     * @return ResponseInterface
     */
    public function new()
    {
        //
    }

    /**
     * Create a new resource object, from "posted" parameters.
     *
     * @return ResponseInterface
     */
    public function create()
    {

        helper('text');
        // Cari Siswa
        $rfid_card_id = $this->request->getVar('rfid_card_id');
        $student = $this->Students->select('students.*, class_name')->join('classes', 'classes.id=students.class_id')->where('rfid_card_id', $rfid_card_id)->first();

        if (!$student) {
            return $this->failNotFound('RFID Card belum terdaftar!.');
        }

        $data = [
            'student_id' =>  $student['id'],
            'status' =>  $this->request->getVar('status'),
            'user_id' =>  $this->request->getVar('user_id'),
        ];


        // Validasi
        if (!$this->Model->validate($data)) {
            return $this->failValidationErrors($this->Model->errors());
        }

        // Insert data ke database
        if (!$this->Model->insert($data, false)) {
            return $this->fail('Failed to create records.', 500);
        }

        $studentName = word_limiter($student['name'], 1, '...');

        // Respons sukses
        $msg = "$studentName mengambil absen!";
        return $this->respondCreated($msg);
    }

    /**
     * Return the editable properties of a resource object.
     *
     * @param int|string|null $id
     *
     * @return ResponseInterface
     */
    public function edit($id = null)
    {
        //
    }

    /**
     * Add or update a model resource, from "posted" properties.
     *
     * @param int|string|null $id
     *
     * @return ResponseInterface
     */
    public function update($id = null)
    {
        //
    }

    /**
     * Delete the designated resource object from the model.
     *
     * @param int|string|null $id
     *
     * @return ResponseInterface
     */
    public function delete($id = null)
    {
        //
    }
}
