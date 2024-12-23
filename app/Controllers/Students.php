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
            'status' => 200,
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
                'status' => 200,
                'message' => 'Data retrieved successfully!',
                'data' => $student ?? [],
            ];

            return $this->respond($data, 200);
        }

        $data = [
            'status' => 404,
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
        $data = $this->request->getVar();

        // dd($data);

        if (!$this->Model->insert($data)) {
            return $this->respond([
                'status' => 400,
                'message' => 'Validation failed',
                'errors' => $this->Model->errors(),
            ], 400);
        }

        $this->Model->insert($data);

        return $this->respondCreated([
            'status' => 201,
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
                'status' => 404,
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

        if (!$this->Model->update($id, $data)) {
            return $this->respond([
                'status' => 400,
                'message' => 'Validation failed!',
                'errors' => $this->Model->errors(),
            ], 400);
        }

        $this->Model->update($id, $data);

        return $this->respond([
            'status' => 200,
            'message' => 'Student updated successfully!',
            'data' => $data
        ], 200);
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
                'status' => 404,
                'message' => 'Data not found'
            ], 404);
        }

        // Hapus data
        if ($this->Model->delete($id)) {
            return $this->respondDeleted([
                'status' => 200,
                'message' => 'Data deleted successfully'
            ]);
        }

        return $this->respond([
            'status' => 500,
            'message' => 'Failed to delete data'
        ], 500);
    }
}
