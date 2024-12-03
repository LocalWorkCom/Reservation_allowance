<?php

namespace App\Exports;

use App\Models\User;
use App\Models\departements; // Ensure to import your Departements model
use App\Models\Rule;
use App\Models\Sector;
use App\Models\ViolationTypes;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation; // Import DataValidation
use PhpOffice\PhpSpreadsheet\Cell\DataType; // Import DataType class
use Illuminate\Support\Collection;

class UsersExport implements FromCollection, WithHeadings, WithEvents
{
    protected $allowedDepartments;
    protected $allowedSectors;
    // protected $allowedRules;
    // protected $allowedFlag;
    // protected $allowedTypeMilitary;

    public function __construct()
    {
        // Get the allowed departments and rules from the database
        $this->allowedDepartments = departements::pluck('name')->toArray(); // Assuming 'name' is the field you want
        $this->allowedSectors = Sector::pluck('name')->toArray(); // Assuming 'name' is the field you want
        // $this->allowedRules = Rule::whereNotIn('id', [1, 2])->pluck('name')->toArray(); // Assuming 'name' is the field you want
        // $this->allowedFlag = ['موظف', 'مستخدم'];
        // $this->allowedTypeMilitary = ViolationTypes::whereJsonContains('type_id', 0)->pluck('name')->toArray();
    }
    /**
     * @return \Illuminate\Support\Collection
     */
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return User::with(['department', 'sectors', 'grade']) // Eager load related models
            ->select('grade_id', 'name', 'file_number', 'sector', 'department_id') // Include foreign keys id
            ->get()
            ->map(function ($user, $index) {
                // Add department name, sector name, and grade name
                $user->num = $index + 1;  // Ensure count starts at 1
                $user->department_name = $user->department ? $user->department->name : null;
                $user->sector_name = $user->sector ? $user->sectors->name : null; // Assuming sector is related
                $user->grade_name = $user->grade_id ? $user->grade->name : null; // Assuming grade is related
    
                // Return the modified user object with all the needed data
                return $user;
            })
            ->map(function ($user) {
                // Return an array structure for export
                return [
                    $user->num,  // Grade name
                    $user->grade_name,  // Grade name
                    $user->name,        // User's name
                    $user->file_number, // User's file number
                    $user->sector_name, // Sector name
                    $user->department_name, // Department name
                ];
            });
    }
    

    /**
     * Set the headings for the Excel sheet.
     */
    public function headings(): array
    {
        return [
            'الرقم التسلسلي',
            'الرتبة',
            'الاسم',
            'رقم الملف',
            'القطاع',
            'الادارة',
        ];
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $departmentRange = 'I2:I100'; // Specify the range for the department dropdown (from row 2 to 100)
                $ruleRange = 'J2:J100'; // Specify the range for the rule dropdown (from row 2 to 100)

                // Data validation for departments
                $departmentValidation = new DataValidation();
                $departmentValidation->setType(DataValidation::TYPE_LIST);
                $departmentValidation->setErrorTitle('ادارة غير صالحة'); // Error title
                $departmentValidation->setError('الرجاء اختيار ادارة من القائمة'); // Error message
                $departmentValidation->setFormula1('"' . implode(',', $this->allowedDepartments) . '"'); // List of allowed departments
                $departmentValidation->setShowDropDown(true);
                $departmentValidation->setShowErrorMessage(true); // Enable error message display

                // Apply validation to the specified range for departments
                for ($row = 2; $row <= 100; $row++) {
                    $event->sheet->getDelegate()->getCell('I' . $row)->setDataValidation($departmentValidation); // Apply validation to each cell in the department range
                }

                $sectorValidation = new DataValidation();
                $sectorValidation->setType(DataValidation::TYPE_LIST);
                $sectorValidation->setErrorTitle('قطاع غير صالح'); // Error title
                $sectorValidation->setError('الرجاء اختيار القطاع من القائمة'); // Error message
                $sectorValidation->setFormula1('"' . implode(',', $this->allowedSectors) . '"'); // List of allowed departments
                $sectorValidation->setShowDropDown(true);
                $sectorValidation->setShowErrorMessage(true); // Enable error message display

                // Apply validation to the specified range for departments
                for ($row = 2; $row <= 100; $row++) {
                    $event->sheet->getDelegate()->getCell('I' . $row)->setDataValidation($sectorValidation); // Apply validation to each cell in the department range
                }

                // // Data validation for rules
                // $ruleValidation = new DataValidation();
                // $ruleValidation->setType(DataValidation::TYPE_LIST);
                // $ruleValidation->setErrorTitle('مهام غير صالحة'); // Error title
                // $ruleValidation->setError('الرجاء اختيار المهام من القائمة'); // Error message
                // $ruleValidation->setFormula1('"' . implode(',', $this->allowedRules) . '"'); // List of allowed rules
                // $ruleValidation->setShowDropDown(true);
                // $ruleValidation->setShowErrorMessage(true); // Enable error message display

                // // Apply validation to the specified range for rules
                // for ($row = 2; $row <= 100; $row++) {
                //     $event->sheet->getDelegate()->getCell('J' . $row)->setDataValidation($ruleValidation); // Apply validation to each cell in the rule range
                // }

                // // Data validation for flags
                // $flagValidation = new DataValidation();
                // $flagValidation->setType(DataValidation::TYPE_LIST);
                // $flagValidation->setErrorTitle('نوع غير صالح');
                // $flagValidation->setError('الرجاء اختيار النوع من القائمة');
                // $flagValidation->setFormula1('"' . implode(',', $this->allowedFlag) . '"');
                // $flagValidation->setShowDropDown(true);
                // $flagValidation->setShowErrorMessage(true);
                // for ($row = 2; $row <= 100; $row++) {
                //     $event->sheet->getDelegate()->getCell('F' . $row)->setDataValidation($flagValidation);
                // }

                // // Data validation for type military
                // $typeMilitaryValidation = new DataValidation();
                // $typeMilitaryValidation->setType(DataValidation::TYPE_LIST);
                // $typeMilitaryValidation->setErrorTitle('نوع عسكري غير صالح');
                // $typeMilitaryValidation->setError('الرجاء اختيار نوع عسكري من القائمة');
                // $typeMilitaryValidation->setFormula1('"' . implode(',', $this->allowedTypeMilitary) . '"');
                // $typeMilitaryValidation->setShowDropDown(true);
                // $typeMilitaryValidation->setShowErrorMessage(true);
                // for ($row = 2; $row <= 100; $row++) {
                //     $event->sheet->getDelegate()->getCell('G' . $row)->setDataValidation($typeMilitaryValidation);
                // }

                // Set data type for the first few columns
                // Define the range of columns
                $columns = range('A', 'H'); // Create an array of columns from A to H

                for ($row = 2; $row <= 100; $row++) {
                    foreach ($columns as $column) { // Use foreach to iterate over the columns
                        $cell = $event->sheet->getDelegate()->getCell($column . $row);
                        $cell->setDataType(DataType::TYPE_STRING); // Set the cell type to string
                    }
                }
            },
        ];
    }
}
