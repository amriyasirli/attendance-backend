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
        $data = $this->Model->select('students.*, class_name')
            ->join('classes', 'classes.id=students.class_id')
            ->findAll(50);

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
        $data = $this->Model->find($id);

        if ($data) {
            $msg = 'Data retrieved successfully!';
            return $this->respond($data, 200, $msg);
        }

        $msg = 'Data not found!';
        return $this->failNotFound($msg);
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
            $msg = 'Data not found!';
            return $this->failNotFound($msg);
        }

        // Update data siswa
        if ($this->Model->update($id, $data)) {
            $msg = 'RFID card berhasil diperbarui!';
            return $this->respondCreated($msg);
        }

        return $this->failValidationErrors($this->Model->errors());
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

        if ($this->Model->insert($data)) {
            return $this->failValidationErrors($this->Model->errors());
        }

        if ($this->Model->insert($data)) {
            $msg = 'Student created successfully!';
            return $this->respondCreated($msg);
        }

        $msg = 'Something Wrong!';
        return $this->failServerError('Internal Server Error!', 500, $msg);
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
            $msg = 'Data not found!';
            return $this->failNotFound($msg);
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
            $msg = 'Student updated successfully!';
            return $this->respondCreated($data, $msg);
        }

        return $this->failValidationErrors($this->Model->errors());
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
            $msg = 'Data not found!';
            return $this->failNotFound($msg);
        }


        // Hapus data
        if ($this->Model->delete($id)) {
            $msg = 'Data deleted successfully!';
            return $this->respondDeleted($data, $msg);
        }

        $msg = 'Something Wrong!';
        return $this->failServerError('Internal Server Error!', 500, $msg);
    }
}
