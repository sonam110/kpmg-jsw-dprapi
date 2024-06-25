<?php

namespace App\Imports;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithConditionalSheets;

class DprSelectSheet implements WithMultipleSheets  
{
    
    use WithConditionalSheets;

    public $sheetName;

    public function __construct($sheetName)
    {
        $this->sheetName = $sheetName;
    }


    public function conditionalSheets(): array
    {
        return [
            $this->sheetName => new DprReportImport($this->sheetName),
        ];
    }
}
