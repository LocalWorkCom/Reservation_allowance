<?php

namespace App\Exports;

use App\Models\Country;
use App\Models\departements; // Make sure to import your Departements model
use App\Models\Rule;
use App\Models\ViolationTypes;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation; // Import DataValidation
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Cell\DataType; // Import DataType class

class UsersImportTemplate implements FromCollection, WithHeadings, WithEvents
{
    protected $allowedDepartments;
    protected $allowedRules;
    protected $allowedFlag;
    protected $allowedTypeMilitary;
    protected $countries;

    public function __construct()
    {
        // Get the allowed departments from the database
        $this->allowedDepartments = departements::pluck('name')->toArray(); // Assuming 'name' is the field you want
        $this->allowedRules = Rule::whereNotIn('id', [1, 2])->pluck('name')->toArray(); // Assuming 'name' is the field you want
        $this->allowedFlag = ['موظف', 'مستخدم'];
        $this->allowedTypeMilitary = ViolationTypes::whereJsonContains('type_id', 0)->pluck('name')->toArray();
        $this->countries = Country::pluck('country_name_ar')->toArray();
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Creating dummy data for the import template
        return collect([
            [
                'نايف', // name
                'john@example.com', // email
                '1234567890', // phone
                '123456', // military_number
                '123456', // military_number
                'مستخدم', // flag
                '', // type_military
                '123', // password (hashed for safety)
                '', // department_id (leave empty for the dropdown)
                '', // rule_id (leave empty for the dropdown)
                '' // rule_id (leave empty for the dropdown)
            ],
            [
                'ابراهيم', // name A
                'jane@example.com', // email B
                '0987654321', // phone C
                '654321', // military_number D
                '654321', // military_number E
                'موظف', // flag F
                '', // type_military G
                '123', // password (hashed for safety) H
                '', // department_id (leave empty for the dropdown) I
                '', // rule_id (leave empty for the dropdown) J
                '' // rule_id (leave empty for the dropdown) K
            ],
            // Add more dummy rows as needed
        ]);
    }

    /**
     * Set the headings for the Excel sheet.
     */
    public function headings(): array
    {
        return [
            'الاسم',                // Name
            'البريد الالكتروني',   // Email required
            'رقم الهاتف',          // Phone e
            'رقم المدني',         // Military Number required
            'رقم الملف',          // file Number required
            'النوع',               // Flag required user or employee
            'نوع العسكري',         // Type Military
            'كلمة المرور',         // Password required in case user بس 
            'الادارة',              // Department ID (Foreign Key) 
            'المهام',     // Role ID (Foreign Key)  required in case user بس 
            'الدولة'               // Role ID (Foreign Key)  required in case user بس 
        ];
    }

    /**
     * Attach event listeners to the export.
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $departmentRange = 'I2:I100'; // Specify the range for the department dropdown (from row 2 to 100)
                $ruleRange = 'J2:J100'; // Specify the range for the rule dropdown (from row 2 to 100)
                // Write the allowed countries into a range in the Excel sheet
                // $countryStartRow = 2; // You can adjust this to avoid overlapping with your main data
                // $countryColumn = 'K'; // Use column L for the list of countries

                // // Write all countries into the specified column
                // foreach ($this->countries as $index => $country) {
                //     $event->sheet->getDelegate()->setCellValue($countryColumn . ($countryStartRow + $index), $country);
                // }
                // Create a new DataValidation object for departments


                // Set data type for الاسم (A column)
                for ($row = 2; $row <= 100; $row++) {
                    $cell = $event->sheet->getDelegate()->getCell('A' . $row);
                    $cell->setDataType(DataType::TYPE_STRING); // Set the cell type to string
                }
                for ($row = 2; $row <= 100; $row++) {
                    $cell = $event->sheet->getDelegate()->getCell('B' . $row);
                    $cell->setDataType(DataType::TYPE_STRING); // Set the cell type to string
                }

                for ($row = 2; $row <= 100; $row++) {
                    $cell = $event->sheet->getDelegate()->getCell('C' . $row);
                    $cell->setDataType(DataType::TYPE_STRING); // Set the cell type to string
                }
                for ($row = 2; $row <= 100; $row++) {
                    $cell = $event->sheet->getDelegate()->getCell('D' . $row);
                    $cell->setDataType(DataType::TYPE_STRING); // Set the cell type to string
                }


                for ($row = 2; $row <= 100; $row++) {
                    $cell = $event->sheet->getDelegate()->getCell('E' . $row);
                    $cell->setDataType(DataType::TYPE_STRING); // Set the cell type to string
                }
                for ($row = 2; $row <= 100; $row++) {
                    $cell = $event->sheet->getDelegate()->getCell('F' . $row);
                    $cell->setDataType(DataType::TYPE_STRING); // Set the cell type to string
                }

                for ($row = 2; $row <= 100; $row++) {
                    $cell = $event->sheet->getDelegate()->getCell('G' . $row);
                    $cell->setDataType(DataType::TYPE_STRING); // Set the cell type to string
                }

                for ($row = 2; $row <= 100; $row++) {
                    $cell = $event->sheet->getDelegate()->getCell('H' . $row);
                    $cell->setDataType(DataType::TYPE_STRING); // Set the cell type to string
                }

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

                // Create a new DataValidation object for rules
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

                // // Set the range for the country dropdown
                // $countryRange = $countryColumn . $countryStartRow . ':' . $countryColumn . ($countryStartRow + count($this->countries) - 1);

                // // Data validation for countries using the defined range
                // $countryValidation = new DataValidation();
                // $countryValidation->setType(DataValidation::TYPE_LIST);
                // $countryValidation->setErrorTitle('دولة غير صالحة');
                // $countryValidation->setError('الرجاء اختيار دولة من القائمة');
                // $countryValidation->setFormula1($countryRange); // Use the defined range for validation
                // $countryValidation->setShowDropDown(true);
                // $countryValidation->setShowErrorMessage(true);

                // // Apply data validation for each cell in column K (country column)
                // for ($row = 2; $row <= 100; $row++) { // Adjust the range as needed
                //     $event->sheet->getDelegate()->getCell('K' . $row)->setDataValidation($countryValidation);
                // }
            },
        ];
    }
}
