<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeWriting;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ChemicalDisinfectionExport implements WithEvents
{
    protected $records;

    public function __construct($records)
    {
        $this->records = $records;
    }

    public function registerEvents(): array
    {
        return [
            BeforeWriting::class => function(BeforeWriting $event) {
                $templatePath = storage_path('app/templates/chemical-disinfection-template.xlsx');

                if (file_exists($templatePath)) {
                    $reader = IOFactory::createReader('Xlsx');
                    $templateSpreadsheet = $reader->load($templatePath);

                    $sheet = $templateSpreadsheet->getActiveSheet();
                    $this->fillTemplate($sheet);

                    $event->writer->reopen($templateSpreadsheet, Xlsx::class);
                }
            },
        ];
    }

    public function fillTemplatePublic($sheet)
    {
        $this->fillTemplate($sheet);
    }

    protected function fillTemplate($sheet)
    {
        $row = 10; // Linha inicial de dados (ajuste conforme o template)

        foreach ($this->records as $record) {
            $sheet->setCellValue("A{$row}", $record->machine->name ?? '');
            $sheet->setCellValue("B{$row}", $record->disinfection_date?->format('d/m/Y'));
            $sheet->setCellValue("C{$row}", match($record->shift) {
                'manha' => 'Manhã',
                'tarde' => 'Tarde',
                'noite' => 'Noite',
                default => $record->shift
            });
            $sheet->setCellValue("D{$row}", $record->start_time);
            $sheet->setCellValue("E{$row}", $record->end_time);
            $sheet->setCellValue("F{$row}", $record->chemical_product);
            $sheet->setCellValue("G{$row}", $record->concentration . ' ' . $record->concentration_unit);
            $sheet->setCellValue("H{$row}", $record->contact_time_minutes . ' min');
            $sheet->setCellValue("I{$row}", $record->initial_temperature ? $record->initial_temperature . '°C' : '');
            $sheet->setCellValue("J{$row}", $record->final_temperature ? $record->final_temperature . '°C' : '');
            $sheet->setCellValue("K{$row}", $record->circulation_verified ? 'Sim' : 'Não');
            $sheet->setCellValue("L{$row}", $record->rinse_performed ? 'Sim' : 'Não');
            $sheet->setCellValue("M{$row}", $record->effectiveness_verified ? 'Sim' : 'Não');
            $sheet->setCellValue("N{$row}", $record->observations ?? '');
            $sheet->setCellValue("O{$row}", $record->user->name ?? '');

            $row++;
        }
    }
}
