<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Departement; // Ensure to import your Departements model
use App\Models\departements;
use App\Models\Sector;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Validation\Rule;

class UsersImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $allowedDepartments;
    protected $allowedSectors;

    public function __construct()
    {
        // Fetch allowed departments and sectors from the database
        $this->allowedDepartments = departements::pluck('name')->toArray(); // Departments
        $this->allowedSectors = Sector::pluck('name')->toArray(); // Sectors
    }

    /**
     * This method is used to transform each row from the Excel file into a model (User).
     */
    public function model(array $row)
    {
        return new User([
            'grade_id'      => $row['الرتبة'],  // Assuming 'الرتبة' corresponds to the grade_id
            'name'          => $row['الاسم'],
            'file_number'   => $row['رقم الملف'],
            'sector'        => $row['القطاع'],  // Assuming this is the sector's name
            'department_id' => $this->getDepartmentIdByName($row['الادارة']), // Get department ID by name
        ]);
    }

    /**
     * Fetch department ID by department name.
     */
    public function getDepartmentIdByName($departmentName)
    {
        $department = departements::where('name', $departmentName)->first();
        return $department ? $department->id : null; // Return the ID if found, otherwise return null
    }

    /**
     * Validation rules for the import fields.
     */
    public function rules(): array
    {
        return [
            'الرقم التسلسلي' => 'required|numeric',  // Serial number should be numeric
            'الرتبة' => 'required|string',  // Grade should be a string
            'الاسم' => 'required|string',  // Name should be a string
            'رقم الملف' => 'required|string',  // File number should be a string
            'القطاع' => ['required', Rule::in($this->allowedSectors)], // Ensure the sector is in the allowed sectors
            'الادارة' => ['required', Rule::in($this->allowedDepartments)], // Ensure the department is in the allowed departments
        ];
    }

    /**
     * Custom validation messages (optional).
     */
    public function customValidationMessages()
    {
        return [
            'القطاع.in' => 'القطاع المدخل غير صالح',  // Invalid sector
            'الادارة.in' => 'الادارة المدخلة غير صالحة',  // Invalid department
        ];
    }
}
