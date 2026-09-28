<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $employees;

    public function __construct($employees)
    {
        $this->employees = $employees;
    }

    public function collection()
    {
        return $this->employees;
    }

    public function headings(): array
    {
        return [
            'Employee ID',
            'Name',
            'Department',
            'Division',
            'Designation',
            'Shift',
            'Location',
            'ID Number',
            'Person ID',
            'Face IDs',
            'Status',
            'Is Salesman',
            'Created At'
        ];
    }

    public function map($employee): array
    {
        $faceIds = '';
        if (is_array($employee->face_ids)) {
            $faceIds = implode(', ', $employee->face_ids);
        } elseif (!empty($employee->face_ids)) {
            $faceIds = (string)$employee->face_ids;
        }

        return [
            $employee->employee_id ?? '',
            $employee->name ?? '',
            $employee->department->name ?? '',
            $employee->division->name ?? '',
            $employee->designation->name ?? '',
            $employee->shift->name ?? '',
            $employee->location->name ?? '',
            $employee->id_number ?? '',
            $employee->person_id ?? '',
            $faceIds,
            $employee->is_locked ? 'Locked' : 'Open',
            $employee->is_salesman ? 'Yes' : 'No',
            $employee->created_at ? $employee->created_at->format('Y-m-d H:i') : ''
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2B4C7E']
                ]
            ],
        ];
    }
}
