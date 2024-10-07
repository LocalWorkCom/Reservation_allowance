<?php

namespace App\Exports;

use App\Models\User;
use App\Models\Departements; // Ensure to import your Departements model
use App\Models\Rule;
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
    protected $allowedRules;
    protected $allowedFlag;
    protected $allowedTypeMilitary;

    public function __construct()
    {
        // Get the allowed departments and rules from the database
        $this->allowedDepartments = Departements::pluck('name')->toArray(); // Assuming 'name' is the field you want
        $this->allowedRules = Rule::whereNotIn('id', [1, 2])->pluck('name')->toArray(); // Assuming 'name' is the field you want
        $this->allowedFlag = ['موظف', 'مستخدم'];
        $this->allowedTypeMilitary = ViolationTypes::whereJsonContains('type_id', 0)->pluck('name')->toArray();
    }
    /**
     * @return \Illuminate\Support\Collection
     */
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return User::with(['department', 'rule']) // Eager load related models
            ->select('name', 'email', 'phone', 'military_number', 'flag', 'type_military', 'employee_type', 'password', 'rule_id', 'department_id') // Include foreign keys
            ->get()
            ->map(function ($user) {
                // Check and replace the `flag` value with Arabic equivalents
                if ($user->flag == 'user') {
                    $user->flag = 'مستخدم';
                } elseif ($user->flag == 'employee') {
                    $user->flag = 'موظف';
                }

                // Modify the `type_military` value with Arabic equivalents
                if ($user->type_military == 'police') {
                    $user->type_military = 'ظابط';
                } elseif ($user->type_military == 'police_') {
                    $user->type_military = 'صف ظابط';
                }

                // Modify the `employee_type` with Arabic equivalents
                if ($user->employee_type == 'civil') {
                    $user->employee_type  = 'مدني';
                } elseif ($user->employee_type == 'military') {
                    $user->employee_type  = 'عسكري';
                }

                // Append related department and role names
                $user->department_name = $user->department ? $user->department->name : null; // Use a new variable for department name
                $user->rule_name = $user->rule ? $user->rule->name : null; // Use a new variable for role name

                return $user;
            })
            ->map(function ($user) {
                // Create a new array with the required fields for the export
                return [
                    $user->name,
                    $user->email,
                    $user->phone,
                    $user->military_number,
                    $user->flag,
                    $user->type_military,
                    $user->employee_type,
                    $user->password, // Keep password, if you want it in the export
                    $user->department_name, // Add the department name to the export
                    $user->rule_name // Add the role name to the export
                ];
            });
    }

    /**
     * Set the headings for the Excel sheet.
     */
    public function headings(): array
    {
        return [
            'الاسم',
            'البريد الالكتروني',
            'رقم الهاتف',
            'رقم العسكري',
            'النوع',
            'نوع العسكري',
            'نوع الموظف',
            'كلمة المرور',
            'الادارة',
            'المهام'
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

                // Data validation for rules
                $ruleValidation = new DataValidation();
                $ruleValidation->setType(DataValidation::TYPE_LIST);
                $ruleValidation->setErrorTitle('مهام غير صالحة'); // Error title
                $ruleValidation->setError('الرجاء اختيار المهام من القائمة'); // Error message
                $ruleValidation->setFormula1('"' . implode(',', $this->allowedRules) . '"'); // List of allowed rules
                $ruleValidation->setShowDropDown(true);
                $ruleValidation->setShowErrorMessage(true); // Enable error message display

                // Apply validation to the specified range for rules
                for ($row = 2; $row <= 100; $row++) {
                    $event->sheet->getDelegate()->getCell('J' . $row)->setDataValidation($ruleValidation); // Apply validation to each cell in the rule range
                }

                // Data validation for flags
                $flagValidation = new DataValidation();
                $flagValidation->setType(DataValidation::TYPE_LIST);
                $flagValidation->setErrorTitle('نوع غير صالح');
                $flagValidation->setError('الرجاء اختيار النوع من القائمة');
                $flagValidation->setFormula1('"' . implode(',', $this->allowedFlag) . '"');
                $flagValidation->setShowDropDown(true);
                $flagValidation->setShowErrorMessage(true);
                for ($row = 2; $row <= 100; $row++) {
                    $event->sheet->getDelegate()->getCell('F' . $row)->setDataValidation($flagValidation);
                }

                // Data validation for type military
                $typeMilitaryValidation = new DataValidation();
                $typeMilitaryValidation->setType(DataValidation::TYPE_LIST);
                $typeMilitaryValidation->setErrorTitle('نوع عسكري غير صالح');
                $typeMilitaryValidation->setError('الرجاء اختيار نوع عسكري من القائمة');
                $typeMilitaryValidation->setFormula1('"' . implode(',', $this->allowedTypeMilitary) . '"');
                $typeMilitaryValidation->setShowDropDown(true);
                $typeMilitaryValidation->setShowErrorMessage(true);
                for ($row = 2; $row <= 100; $row++) {
                    $event->sheet->getDelegate()->getCell('G' . $row)->setDataValidation($typeMilitaryValidation);
                }

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
