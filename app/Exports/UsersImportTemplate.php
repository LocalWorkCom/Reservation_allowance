<?php

namespace App\Exports;

use App\Models\Country;
use App\Models\departements; // Make sure to import your Departements model
use App\Models\Rule;
use App\Models\Sector;
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
    protected $allowedSectors;
    // protected $allowedRules;
    // protected $allowedFlag;
    // protected $allowedTypeMilitary;
    // protected $countries;

    public function __construct()
    {
        // Get the allowed departments from the database
        $this->allowedDepartments = departements::pluck('name')->toArray(); // Assuming 'name' is the field you want
        $this->allowedSectors = Sector::pluck('name')->toArray(); // Assuming 'name' is the field you want
        // $this->allowedRules = Rule::whereNotIn('id', [1, 2])->pluck('name')->toArray(); // Assuming 'name' is the field you want
        // $this->allowedFlag = ['موظف', 'مستخدم'];
        // $this->allowedTypeMilitary = ViolationTypes::whereJsonContains('type_id', 0)->pluck('name')->toArray();
        // $this->countries = Country::pluck('country_name_ar')->toArray();
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Creating dummy data for the import template
        return collect([
            [
                '1',
                'وكيل اول',
                'نايف', // name
                '123', // name
                'وكيل الوزارة المساعد لشئون الامن العام', // rule_id (leave empty for the dropdown)
                'اداره المبانى' ,// rule_id (leave empty for the dropdown)
                ''
            ],
            [
                '2',
                'عقيد', // name A
                'ابراهيم', // name A
                '2344', // name A
                'قطاع المرور', // rule_id (leave empty for the dropdown) J
                'اداره المبانى', // rule_id (leave empty for the dropdown) K
                'اداره فرعية مطافي' // rule_id (leave empty for the dropdown) K
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
            'الرقم التسلسلي',                // Name
            'الرتبة',                // Name
            'الاسم',                // Name
            'رقم الملف',         // Military Number required
            'القطاع',               // Flag required user or employee
            'الادارة',              // Department ID (Foreign Key) 
            'الادارة الفرعية',              // Department ID (Foreign Key) 

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

                // $departmentValidation = new DataValidation();
                // $departmentValidation->setType(DataValidation::TYPE_LIST);
                // $departmentValidation->setErrorTitle('ادارة غير صالحة'); // Error title
                // $departmentValidation->setError('الرجاء اختيار ادارة من القائمة'); // Error message
                // $departmentValidation->setFormula1('"' . implode(',', $this->allowedDepartments) . '"'); // List of allowed departments
                // $departmentValidation->setShowDropDown(true);
                // $departmentValidation->setShowErrorMessage(true); // Enable error message display

                // // Apply validation to the specified range for departments
                // for ($row = 2; $row <= 100; $row++) {
                //     $event->sheet->getDelegate()->getCell('I' . $row)->setDataValidation($departmentValidation); // Apply validation to each cell in the department range
                // }

                // $sectorValidation = new DataValidation();
                // $sectorValidation->setType(DataValidation::TYPE_LIST);
                // $sectorValidation->setErrorTitle('قطاع غير صالحة'); // Error title
                // $sectorValidation->setError('الرجاء اختيار قطاع من القائمة'); // Error message
                // $sectorValidation->setFormula1('"' . implode(',', $this->allowedSectors) . '"'); // List of allowed departments
                // $sectorValidation->setShowDropDown(true);
                // $sectorValidation->setShowErrorMessage(true); // Enable error message display

                // // Apply validation to the specified range for departments
                // for ($row = 2; $row <= 100; $row++) {
                //     $event->sheet->getDelegate()->getCell('I' . $row)->setDataValidation($sectorValidation); // Apply validation to each cell in the department range
                // }

            },
        ];
    }
}
