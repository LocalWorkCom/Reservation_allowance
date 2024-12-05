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
        // // $this->allowedRules = Rule::whereNotIn('id', [1, 2])->pluck('name')->toArray(); // Assuming 'name' is the field you want
        // $this->allowedFlag = ['موظف', 'مستخدم'];
        // $this->allowedTypeMilitary = ViolationTypes::whereJsonContains('type_id', 0)->pluck('name')->toArray();
        $this->allowedDepartments = array_slice($this->allowedDepartments, 0, 50);
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
                $sheet = $event->sheet->getDelegate();
                $workbook = $sheet->getParent();
    
                // **Hidden Sheet for Lists**
                $hiddenSheet = $workbook->createSheet();
                $hiddenSheet->setTitle('HiddenSheet');
    
                // **Populate Department List in Hidden Sheet**
                foreach ($this->allowedDepartments as $index => $department) {
                    $hiddenSheet->setCellValue('A' . ($index + 1), $department);
                }
    
                // **Populate Sector List in Hidden Sheet**
                foreach ($this->allowedSectors as $index => $sector) {
                    $hiddenSheet->setCellValue('B' . ($index + 1), $sector);
                }
    
                // **Define Named Ranges**
                $workbook->addNamedRange(
                    new \PhpOffice\PhpSpreadsheet\NamedRange(
                        'DepartmentsList',
                        $hiddenSheet,
                        '$A$1:$A$' . count($this->allowedDepartments)
                    )
                );
                $workbook->addNamedRange(
                    new \PhpOffice\PhpSpreadsheet\NamedRange(
                        'SectorsList',
                        $hiddenSheet,
                        '$B$1:$B$' . count($this->allowedSectors)
                    )
                );
    
                // **Department Dropdown Validation**
                $departmentValidation = new DataValidation();
                $departmentValidation->setType(DataValidation::TYPE_LIST);
                $departmentValidation->setErrorTitle('ادارة غير صالحة');
                $departmentValidation->setError('الرجاء اختيار ادارة من القائمة');
                $departmentValidation->setFormula1('=DepartmentsList');
                $departmentValidation->setShowDropDown(true);
                $departmentValidation->setShowErrorMessage(true);
    
                for ($row = 2; $row <= 100; $row++) {
                    $sheet->getCell('F' . $row)->setDataValidation($departmentValidation);
                }
    
                // **Sector Dropdown Validation**
                $sectorValidation = new DataValidation();
                $sectorValidation->setType(DataValidation::TYPE_LIST);
                $sectorValidation->setErrorTitle('قطاع غير صالح');
                $sectorValidation->setError('الرجاء اختيار القطاع من القائمة');
                $sectorValidation->setFormula1('=SectorsList');
                $sectorValidation->setShowDropDown(true);
                $sectorValidation->setShowErrorMessage(true);
    
                for ($row = 2; $row <= 100; $row++) {
                    $sheet->getCell('E' . $row)->setDataValidation($sectorValidation);
                }
    
                // **Set Data Types for Specific Columns**
                $columns = ['A', 'B', 'C', 'D']; // Specify columns for which the type should be set
                for ($row = 2; $row <= 100; $row++) {
                    foreach ($columns as $column) {
                        $cell = $sheet->getCell($column . $row);
    
                        // Set specific data types for each column
                        if ($column === 'D' || $column === 'A') { // Numeric columns
                            $cell->setDataType(DataType::TYPE_NUMERIC);
                        } else { // String columns
                            $cell->setDataType(DataType::TYPE_STRING);
                        }
                    }
                }
    
                // Hide the Hidden Sheet
                $hiddenSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);
            },
        ];
    }
    
}