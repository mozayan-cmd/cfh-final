<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FundFlowReportExport implements WithMultipleSheets
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function sheets(): array
    {
        $sheets = [];

        $sheets[] = new class($this->data['summary']) implements FromCollection, WithTitle, WithHeadings {
            protected $summary;
            public function __construct($summary) { $this->summary = $summary; }
            public function collection() {
                return collect([
                    ['Total Inflows', $this->summary['total_inflows']],
                    ['Total Outflows', $this->summary['total_outflows']],
                    ['Net Change', $this->summary['net_change']],
                ]);
            }
            public function headings(): array { return ['Metric', 'Amount']; }
            public function title(): string { return 'Summary'; }
        };

        foreach ($this->data['categories'] as $key => $category) {
            $sheets[] = new class($category) implements FromCollection, WithTitle, WithHeadings {
                protected $category;
                public function __construct($category) { $this->category = $category; }
                public function collection() {
                    return collect($this->category['transactions'])->map(function ($t) {
                        return array_values($t);
                    });
                }
                public function headings(): array {
                    if (empty($this->category['transactions'])) {
                        return ['N/A'];
                    }
                    return array_keys($this->category['transactions'][0]);
                }
                public function title(): string { return substr($this->category['label'], 0, 31); }
            };
        }

        return $sheets;
    }
}
