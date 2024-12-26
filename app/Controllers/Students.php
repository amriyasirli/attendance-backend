<?php

namespace App\Controllers;

use App\Models\StudentModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;

class Students extends ResourceController
{
    use ResponseTrait;

    protected $Model, $table;

    public function __construct()
    {
        $this->Model = new StudentModel();
        $this->table = 'students';
    }
    /**
     * Return an array of resource objects, themselves in array format.
     *
     * @return ResponseInterface
     */
    public function index()
    {
        $students = $this->Model->findAll(20);

        $data = [
            'status' => true,
            'message' => 'Data retrieved successfully!',
            'data' => $students ?? [],
        ];
        return $this->respond($data, 200);
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
        $student = $this->Model->find($id);

        if ($student) {
            $data = [
                'status' => true,
                'message' => 'Data retrieved successfully!',
                'data' => $student ?? [],
            ];

            return $this->respond($data, 200);
        }

        $data = [
            'status' => false,
            'message' => 'Data not found!',
            'data' => [],
        ];

        return $this->respond($data, 404);
    }

    /**
     * Return a new resource object, with default properties.
     *
     * @return ResponseInterface
     */
    public function updateRfid($id = null)
    {

        // Ambil data dari request (JSON body)
        $rfid_card_id = $this->request->getVar('rfid_card_id');

        // Update aturan validasi untuk mengabaikan nilai unik dari record yang sedang diupdate
        $this->Model->setValidationRules([
            'rfid_card_id' => [
                "rules" => "required|numeric|min_length[8]|max_length[10]|is_unique[students.rfid_card_id,id,{$id}]",
                "errors" => [
                    'required' => 'RFID card wajib diisi.',
                    'numeric' => 'RFID card harus berupa angka.',
                    'min_length' => 'RFID card tidak boleh lebih dari 8 karakter.',
                    'max_length' => 'RFID card tidak boleh lebih dari 10 karakter.',
                    'is_unique' => 'RFID card sudah digunakan oleh siswa lain.'
                ]
            ],
        ]);

        // Cek apakah siswa dengan ID tersebut ada
        $student = $this->Model->find($id);
        $data = [
            'rfid_card_id' => $rfid_card_id,
        ];

        if (!$student) {
            return $this->respond([
                'status' => false,
                'message' => 'Siswa tidak ditemukan.'
            ], 404);
        }

        // Update data siswa
        if ($this->Model->update($id, $data)) {
            return $this->respond([
                'status' => true,
                'message' => 'RFID card berhasil diperbarui.',
                'data' => [
                    'id' => $student['id'],
                    'nis' => $student['nis'],
                    'nisn' => $student['nisn'],
                    'name' => $student['name'],
                    'gender' => $student['gender'],
                    'rfid_card_id' => $rfid_card_id
                ]
            ], 200);
        }

        return $this->respond([
            'status' => false,
            'message' => 'Validation failed!',
            'errors' => $this->Model->errors(),
        ], 400);
    }

    /**
     * Create a new resource object, from "posted" parameters.
     *
     * @return ResponseInterface
     */
    public function create()
    {
        $data = $this->request->getVar();

        // dd($data);

        if (!$this->Model->insert($data)) {
            return $this->respond([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $this->Model->errors(),
            ], 400);
        }

        $this->Model->insert($data);

        return $this->respondCreated([
            'status' => true,
            'message' => 'Student created successfully',
        ]);
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
        $data = $this->request->getVar();

        // Ambil data lama dari database
        $existingStudent = $this->Model->find($id);

        if (!$existingStudent) {
            return $this->respond([
                'status' => false,
                'message' => 'Data not found!'
            ], 404);
        }

        // Update aturan validasi untuk mengabaikan nilai unik dari record yang sedang diupdate
        $this->Model->setValidationRules([
            'nis' => "required|numeric|min_length[3]|is_unique[students.nis,id,{$id}]",
            'nisn' => "required|numeric|min_length[3]|is_unique[students.nisn,id,{$id}]",
            'name' => "required",
            'gender' => "required|max_length[1]",
            'class_id' => "required|numeric",
            'rfid_card_id' => "permit_empty|numeric|max_length[10]|is_unique[students.rfid_card_id,id,{$id}]",
        ]);

        if ($this->Model->update($id, $data)) {
            return $this->respond([
                'status' => true,
                'message' => 'Student updated successfully!',
                'data' => $data
            ], 200);
        }

        return $this->respond([
            'status' => false,
            'message' => 'Validation failed!',
            'errors' => $this->Model->errors(),
        ], 400);
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
        // Cek apakah data dengan ID yang diberikan ada
        $data = $this->Model->find($id);

        if (!$data) {
            return $this->respond([
                'status' => false,
                'message' => 'Data not found'
            ], 404);
        }

        // Hapus data
        if ($this->Model->delete($id)) {
            return $this->respondDeleted([
                'status' => true,
                'message' => 'Data deleted successfully'
            ]);
        }

        return $this->respond([
            'status' => false,
            'message' => 'Failed to delete data'
        ], 500);
    }
}
