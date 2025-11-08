<?php

namespace App\Controllers\Api;

use App\Models\AttendanceRecordsModel;
use App\Models\StudentModel;
use CodeIgniter\HTTP\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\I18n\Time;
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
        $attendaces = $this->Model->select('attendance_records.*, name')->join('students', 'students.id=attendance_records.student_id')->orderBy('id', 'DESC')->findAll();
        $present = $this->Model->where('status', 'present')->countAllResults();
        $total = $this->Students->countAllResults();
        $absent = $total - $present;

        $data = [
            'data' => $attendaces,
            'present' => $present,
            'absent' => $absent,
            'total' => $total,
        ];

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
            return $this->failNotFound('RFID Card belum terdaftar!' . $rfid_card_id);
        }

        $currentTime = Time::now('Asia/Jakarta');
        $currentDate = $currentTime->toDateString();
        $currentHour = $currentTime->getHour();
        $currentTimeString = $currentTime->toTimeString();

        // Tentukan apakah ini check-in (pagi) atau absen pulang (pulang)
        $isCheckInTime = ($currentHour >= 6 && $currentHour < 9); // Jam 6-9 untuk check-in
        $isCheckOutTime = ($currentHour >= 9); // Jam 9 ke atas untuk absen pulang

        // Cek apakah siswa sudah absen hari ini
        $existingAttendance = $this->Model
            ->where('student_id', $student['id'])
            ->where('attendance_date', $currentDate)
            ->first();

        if ($existingAttendance) {
            // Jika sudah ada absensi hari ini
            if ($isCheckInTime) {
                // Jika masih jam check-in (6-9), update check_in_time saja
                $updateData = [
                    'check_in_time' => $currentTimeString,
                    'status' => $this->request->getVar('status'),
                    'officer_id' => $this->request->getVar('user_id')
                ];

                // Validasi update
                if (!$this->Model->validate($updateData)) {
                    return $this->failValidationErrors($this->Model->errors());
                }

                // Update data
                if (!$this->Model->update($existingAttendance['id'], $updateData)) {
                    return $this->fail('Failed to update attendance record.', 500);
                }

                $studentName = word_limiter($student['name'], 1, '...');
                $msg = "$studentName memperbarui waktu check-in!";
                return $this->respondCreated($msg);
            } else if ($isCheckOutTime) {
                // Jika sudah jam absen pulang (9 ke atas), update check_out_time
                $updateData = [
                    'check_out_time' => $currentTimeString,
                    'status' => $this->request->getVar('status'),
                    'officer_id' => $this->request->getVar('user_id')
                ];

                // Validasi update
                if (!$this->Model->validate($updateData)) {
                    return $this->failValidationErrors($this->Model->errors());
                }

                // Update data
                if (!$this->Model->update($existingAttendance['id'], $updateData)) {
                    return $this->fail('Failed to update attendance record.', 500);
                }

                $studentName = word_limiter($student['name'], 1, '...');

                // Cek apakah ini update absen pulang atau pertama kali absen pulang
                if ($existingAttendance['check_out_time'] === null) {
                    $msg = "$studentName melakukan absen pulang!";
                } else {
                    $msg = "$studentName memperbarui absen pulang!";
                }
                return $this->respondCreated($msg);
            }
        } else {
            // Jika belum ada absensi hari ini, buat record baru
            if (!$isCheckInTime && !$isCheckOutTime) {
                // Di luar jam absen (sebelum jam 6)
                return $this->fail('Belum waktunya absen. Jam absen: 06:00-09:00 untuk check-in, setelah 09:00 untuk absen pulang.', 400);
            }

            // Buat record absensi baru
            $data = [
                'student_id' => $student['id'],
                'attendance_date' => $currentDate,
                'check_in_time' => $isCheckInTime ? $currentTimeString : null,
                'check_out_time' => $isCheckOutTime ? $currentTimeString : null,
                'status' => $this->request->getVar('status'),
                'officer_id' => $this->request->getVar('user_id'),
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

            if ($isCheckInTime) {
                $msg = "$studentName melakukan absen datang!";
            } else {
                $msg = "$studentName melakukan absen pulang!";
            }
            return $this->respondCreated($msg);
        }
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
