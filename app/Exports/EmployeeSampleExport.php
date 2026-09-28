<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeeSampleExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    public function collection()
    {
        return collect([
            [
                'Employee ID' => 'EMP001',
                'Name' => 'John Doe',
                'Department' => 'IT',
                'Division' => 'Software',
                'Designation' => 'Software Engineer',
                'Shift' => 'Morning Shift',
                'Location' => 'Headquarters',
                'ID Number' => 'ID-1001',
                'Person ID' => 'P-001',
                'Face IDs' => 'FID-001, FID-002',
                'Status' => 'Active',
                'Is Salesman' => 'No',
            ],
            [
                'Employee ID' => 'EMP002',
                'Name' => 'Jane Smith',
                'Department' => 'Sales',
                'Division' => 'Retail',
                'Designation' => 'Sales Executive',
                'Shift' => 'General Shift',
                'Location' => 'Branch Office',
                'ID Number' => 'ID-1002',
                'Person ID' => 'P-002',
                'Face IDs' => 'FID-003',
                'Status' => 'Active',
                'Is Salesman' => 'Yes',
            ],
        ]);
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
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1B6EC2']
                ]
            ],
        ];
    }
}
