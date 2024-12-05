<?php

namespace App\Imports;

use App\Models\User;
use App\Models\departements;
use App\Models\grade;
use App\Models\Sector;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Validation\Rule;

class UsersImport implements ToModel, WithHeadingRow
{
    protected $allowedDepartments;
    protected $allowedSectors;

    public function __construct()
    {
        // Fetch allowed departments and sectors from the database
        $this->allowedDepartments = departements::pluck('name')->toArray();
        $this->allowedSectors = Sector::pluck('name')->toArray();
    }

    public function model(array $row)
    {
        // Correct mapping: Map Arabic headers (Excel file) to your custom keys
        $headerMapping = [
            'alrkm_altslsly' => 'الرقم التسلسلي',
            'alrtb' => 'الرتبة',
            'alasm' => 'الاسم',
            'rkm_almlf' => 'رقم الملف',
            'alktaaa' => 'القطاع',
            'aladar' => 'الادارة',
            'adara_fraaa' => 'الادارة فرعية'
        ];
        // Transform row keys using the mapping
        $transformedRow = [];
        foreach ($headerMapping as $arabicHeader => $customKey) {
            if (isset($row[$arabicHeader])) {

                $transformedRow[$customKey] = $row[$arabicHeader];
            }
        }
        if (
            empty($transformedRow['الاسم']) &&
            empty($transformedRow['رقم الملف'])

        ) {
            return null; // Skip empty row
        }


        // Debug transformed row
        // dd($transformedRow);
        if (empty($transformedRow['الادارة الفرعية'])) {
            $main = 1;
        } else {
            $main = 0;
        }
        // Create a new User model instance
        return new User([
            'grade_id'      => $this->getGradeIdByRank($transformedRow['الرتبة']),
            'name'          => $transformedRow['الاسم'],
            'file_number'   => $transformedRow['رقم الملف'],
            'sector'        => $this->getSectorIdByName($transformedRow['القطاع']),
            'department_id' => ($main) ? $this->getDepartmentIdByName($transformedRow['الادارة']) : $this->getDepartmentIdByName($transformedRow['الادارة الفرعية']),
        ]);
    }


    // Example method to get grade_id by rank (if applicable)
    protected function getGradeIdByRank($rank)
    {
        $grade = grade::where('name', $rank)->first();
        return $grade ? $grade->id : null;

        // Implement your logic to determine grade_id based on rank
    }


    protected function getDepartmentIdByName($departmentName)
    {
        $department = departements::where('name', $departmentName)->first();
        return $department ? $department->id : null;
    }
    protected function getSectorIdByName($sectorName)
    {
        $sector = Sector::where('name', $sectorName)->first();
        return $sector ? $sector->id : null;
    }


    public function rules(): array
    {
        return [
            'alasm'          => 'required|string',
            'rkm_almlf'      => 'required|string',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'alasm.required'          => 'الاسم مطلوب.',
            'rkm_almlf.required'      => 'رقم الملف مطلوب.'
        ];
    }
}
