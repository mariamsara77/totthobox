<?php

// app/Exports/ContactExport.php
namespace App\Exports;

use App\Models\ContactNumber;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

class ContactExport implements FromCollection, WithMapping, WithHeadings, WithCustomCsvSettings
{
    public function collection()
    {
        return ContactNumber::with(['division', 'district', 'thana'])->get();
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->name,
            $row->phone,
            $row->division?->name, // বর্তমান নাম
            $row->district?->name,
            $row->thana?->name,
            $row->division_id, // এডিট করার জন্য ID
            $row->district_id,
            $row->thana_id,
        ];
    }

    public function headings(): array
    {
        return ['ID', 'Name', 'Phone', 'Div_Name', 'Dist_Name', 'Thana_Name', 'Div_ID', 'Dist_ID', 'Thana_ID'];
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ',',
            'use_bom' => true, // এটি বাংলায় ডাটা ঠিক রাখতে সাহায্য করে
        ];
    }
}