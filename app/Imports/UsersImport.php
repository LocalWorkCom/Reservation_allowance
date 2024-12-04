<?php

namespace App\Imports;

use App\Models\User;
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
        $this->allowedDepartments = departements::pluck('name')->toArray();
        $this->allowedSectors = Sector::pluck('name')->toArray();
    }

   public function model(array $row)
{
    \Log::info('Processing Row: ', $row); // Log the row data for debugging

    return new User([
        'grade_id'      => $row['الرتبة'],
        'name'          => $row['الاسم'],
        'file_number'   => $row['رقم الملف'],
        'sector'        => $row['القطاع'],
        'department_id' => $this->getDepartmentIdByName($row['الادارة']),
    ]);
}

    protected function getDepartmentIdByName($departmentName)
    {
        $department = departements::where('name', $departmentName)->first();
        return $department ? $department->id : null;
    }

    public function rules(): array
    {
        return [
            'الرقم التسلسلي' => 'required|numeric',
            'الرتبة'         => 'required|string',
            'الاسم'          => 'required|string',
            'رقم الملف'      => 'required|string',
            'القطاع'         => ['required', Rule::in($this->allowedSectors)],
            'الادارة'        => ['required', Rule::in($this->allowedDepartments)],
        ];
    }

  public function customValidationMessages()
{
    return [
        'الرقم التسلسلي.required' => 'الرقم التسلسلي مطلوب.',
        'الرتبة.required'         => 'الرتبة مطلوبة.',
        'الاسم.required'          => 'الاسم مطلوب.',
        'رقم الملف.required'      => 'رقم الملف مطلوب.',
        'القطاع.required'         => 'القطاع مطلوب.',
        'القطاع.in'               => 'القطاع المدخل غير صالح.',
        'الادارة.required'        => 'الادارة مطلوبة.',
        'الادارة.in'              => 'الادارة المدخلة غير صالحة.',
    ];
}

}
