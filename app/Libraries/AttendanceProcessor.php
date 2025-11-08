<?php

namespace App\Libraries;

use App\Models\AttendanceRecordsModel;
use App\Models\StudentModel;
use CodeIgniter\Model;
use CodeIgniter\I18n\Time;

class AttendanceProcessor
{
    protected $attendanceModel;
    protected $studentsModel;

    public function __construct()
    {
        $this->attendanceModel = new AttendanceRecordsModel();
        $this->studentsModel = new StudentModel();
    }

    /**
     * Process attendance based on RFID card and current time
     */
    public function processAttendance(string $rfidCardId, string $status, int $officerId): array
    {
        // Validate RFID card
        $student = $this->findStudentByRfid($rfidCardId);
        if (!$student) {
            throw new \Exception("RFID Card belum terdaftar: {$rfidCardId}");
        }

        $currentTime = Time::now('Asia/Jakarta');
        $attendanceType = $this->determineAttendanceType($currentTime);

        // Find or create attendance record
        $attendance = $this->findOrCreateAttendance($student['id'], $currentTime->toDateString());

        // Process based on attendance type
        $result = $this->processByAttendanceType($attendance, $attendanceType, $currentTime, $status, $officerId);

        return [
            'student' => $student,
            'message' => $result['message'],
            'attendance_type' => $attendanceType,
            'action' => $result['action']
        ];
    }

    /**
     * Find student by RFID card ID
     */
    private function findStudentByRfid(string $rfidCardId): ?array
    {
        return $this->studentsModel
            ->select('students.*, class_name')
            ->join('classes', 'classes.id = students.class_id')
            ->where('rfid_card_id', $rfidCardId)
            ->first();
    }

    /**
     * Determine if it's check-in or check-out time
     */
    private function determineAttendanceType(Time $currentTime): string
    {
        $currentHour = $currentTime->getHour();

        if ($currentHour >= 6 && $currentHour < 9) {
            return 'check_in';
        } elseif ($currentHour >= 9) {
            return 'check_out';
        } else {
            throw new \Exception('Belum waktunya absen. Jam absen: 06:00-09:00 untuk check-in, setelah 09:00 untuk check-out.');
        }
    }

    /**
     * Find existing attendance or create new template
     */
    private function findOrCreateAttendance(int $studentId, string $date): array
    {
        $attendance = $this->attendanceModel
            ->where('student_id', $studentId)
            ->where('attendance_date', $date)
            ->first();

        if (!$attendance) {
            return [
                'student_id' => $studentId,
                'attendance_date' => $date,
                'check_in_time' => null,
                'check_out_time' => null,
                'is_new' => true
            ];
        }

        $attendance['is_new'] = false;
        return $attendance;
    }

    /**
     * Process attendance based on type (check-in/check-out)
     */
    private function processByAttendanceType(array $attendance, string $type, Time $currentTime, string $status, int $officerId): array
    {
        $currentTimeString = $currentTime->toTimeString();
        $updateData = [
            'status' => $status,
            'officer_id' => $officerId
        ];

        if ($type === 'check_in') {
            return $this->processCheckIn($attendance, $currentTimeString, $updateData);
        } else {
            return $this->processCheckOut($attendance, $currentTimeString, $updateData);
        }
    }

    /**
     * Process check-in attendance
     */
    private function processCheckIn(array $attendance, string $currentTime, array $updateData): array
    {
        $updateData['check_in_time'] = $currentTime;

        if ($attendance['is_new']) {
            $this->createAttendanceRecord(array_merge($attendance, $updateData));
            return [
                'message' => 'melakukan check-in!',
                'action' => 'created'
            ];
        } else {
            $this->updateAttendanceRecord($attendance['id'], $updateData);
            return [
                'message' => 'memperbarui waktu check-in!',
                'action' => 'updated'
            ];
        }
    }

    /**
     * Process check-out attendance
     */
    private function processCheckOut(array $attendance, string $currentTime, array $updateData): array
    {
        $updateData['check_out_time'] = $currentTime;

        if ($attendance['is_new']) {
            $this->createAttendanceRecord(array_merge($attendance, $updateData));
            return [
                'message' => 'melakukan check-out!',
                'action' => 'created'
            ];
        } else {
            $isFirstCheckOut = $attendance['check_out_time'] === null;
            $this->updateAttendanceRecord($attendance['id'], $updateData);

            $message = $isFirstCheckOut ? 'melakukan check-out!' : 'memperbarui waktu check-out!';
            return [
                'message' => $message,
                'action' => 'updated'
            ];
        }
    }

    /**
     * Create new attendance record
     */
    private function createAttendanceRecord(array $data): bool
    {
        try {
            return $this->attendanceModel->insert($data);
        } catch (\Exception $e) {
            throw new \Exception('Gagal membuat record absensi: ' . $e->getMessage());
        }
    }

    /**
     * Update existing attendance record
     */
    private function updateAttendanceRecord(int $attendanceId, array $data): bool
    {
        try {
            return $this->attendanceModel->update($attendanceId, $data);
        } catch (\Exception $e) {
            throw new \Exception('Gagal memperbarui record absensi: ' . $e->getMessage());
        }
    }
}
