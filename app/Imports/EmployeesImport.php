<?php

namespace App\Imports;

use App\Employee;
use App\Department;
use App\Division;
use App\Designation;
use App\Shift;
use App\Location;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EmployeesImport implements ToCollection, WithHeadingRow
{
    public $successCount = 0;
    public $updatedCount = 0;
    public $skippedCount = 0;
    public $errors = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNum = $index + 2; // Account for 1-indexed Excel and header row

            // Get fields with fallback keys
            $employeeId = trim($row['employee_id'] ?? $row['emp_id'] ?? $row['employeeid'] ?? '');
            $name = trim($row['name'] ?? $row['employee_name'] ?? $row['full_name'] ?? '');

            if (empty($employeeId) && empty($name)) {
                // Empty row, skip quietly
                continue;
            }

            if (empty($employeeId)) {
                $this->skippedCount++;
                $this->errors[] = "Row {$rowNum}: Employee ID is required.";
                continue;
            }

            if (empty($name)) {
                $this->skippedCount++;
                $this->errors[] = "Row {$rowNum}: Name is required for Employee ID '{$employeeId}'.";
                continue;
            }

            // Resolve Department
            $deptName = trim($row['department'] ?? $row['dept'] ?? '');
            $departmentId = null;
            if ($deptName !== '') {
                if (is_numeric($deptName)) {
                    $departmentId = (int)$deptName;
                } else {
                    $dept = Department::firstOrCreate(['name' => $deptName]);
                    $departmentId = $dept->id;
                }
            }

            // Resolve Division
            $divName = trim($row['division'] ?? '');
            $divisionId = null;
            if ($divName !== '') {
                if (is_numeric($divName)) {
                    $divisionId = (int)$divName;
                } else {
                    $div = Division::firstOrCreate(['name' => $divName]);
                    $divisionId = $div->id;
                }
            }

            // Resolve Designation
            $desigName = trim($row['designation'] ?? '');
            $designationId = null;
            if ($desigName !== '') {
                if (is_numeric($desigName)) {
                    $designationId = (int)$desigName;
                } else {
                    $desig = Designation::firstOrCreate(['name' => $desigName]);
                    $designationId = $desig->id;
                }
            }

            // Resolve Shift
            $shiftName = trim($row['shift'] ?? '');
            $shiftId = null;
            if ($shiftName !== '') {
                if (is_numeric($shiftName)) {
                    $shiftId = (int)$shiftName;
                } else {
                    $shift = Shift::firstOrCreate(['name' => $shiftName]);
                    $shiftId = $shift->id;
                }
            }

            // Resolve Location
            $locName = trim($row['location'] ?? '');
            $locationId = null;
            if ($locName !== '') {
                if (is_numeric($locName)) {
                    $locationId = (int)$locName;
                } else {
                    $loc = Location::firstOrCreate(['name' => $locName]);
                    $locationId = $loc->id;
                }
            }

            $idNumber = trim($row['id_number'] ?? $row['id_no'] ?? '');
            $personId = trim($row['person_id'] ?? '');

            // Face IDs parsing
            $faceIdsRaw = trim($row['face_ids'] ?? $row['face_id'] ?? $row['faceids'] ?? '');
            $faceIdsArray = null;
            if ($faceIdsRaw !== '') {
                if (str_starts_with($faceIdsRaw, '[') && str_ends_with($faceIdsRaw, ']')) {
                    $decoded = json_decode($faceIdsRaw, true);
                    if (is_array($decoded)) {
                        $faceIdsArray = array_values(array_filter(array_map('trim', $decoded)));
                    }
                } else {
                    $parts = array_filter(array_map('trim', explode(',', $faceIdsRaw)));
                    if (!empty($parts)) {
                        $faceIdsArray = array_values($parts);
                    }
                }
            }

            // Status parsing (Locked vs Active)
            $statusVal = strtolower(trim($row['status'] ?? $row['is_locked'] ?? ''));
            $isLocked = in_array($statusVal, ['locked', '1', 'inactive', 'true', 'yes']) ? 1 : 0;

            // Is Salesman parsing
            $salesmanVal = strtolower(trim($row['is_salesman'] ?? $row['salesman'] ?? ''));
            $isSalesman = in_array($salesmanVal, ['yes', '1', 'true']) ? 1 : 0;

            $data = [
                'name' => $name,
                'department_id' => $departmentId,
                'division_id' => $divisionId,
                'designation_id' => $designationId,
                'shift_id' => $shiftId,
                'location_id' => $locationId,
                'id_number' => $idNumber ?: null,
                'person_id' => $personId ?: null,
                'is_locked' => $isLocked,
                'is_salesman' => $isSalesman,
            ];

            if ($faceIdsArray !== null) {
                $data['face_ids'] = $faceIdsArray;
            }

            $employee = Employee::where('employee_id', $employeeId)->first();
            if ($employee) {
                $employee->update($data);
                $this->updatedCount++;
            } else {
                $data['employee_id'] = $employeeId;
                Employee::create($data);
                $this->successCount++;
            }
        }
    }
}
