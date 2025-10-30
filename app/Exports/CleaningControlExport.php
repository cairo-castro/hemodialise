<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeWriting;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class CleaningControlExport implements WithEvents
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
                $templatePath = storage_path('app/templates/cleaning-control-template.xlsx');

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
            $sheet->setCellValue("B{$row}", $record->cleaning_date?->format('d/m/Y'));
            $sheet->setCellValue("C{$row}", match($record->shift) {
                'manha' => 'Manhã',
                'tarde' => 'Tarde',
                'noite' => 'Noite',
                default => $record->shift
            });
            $sheet->setCellValue("D{$row}", $record->cleaning_time);
            $sheet->setCellValue("E{$row}", $record->daily_cleaning ? 'Sim' : 'Não');
            $sheet->setCellValue("F{$row}", $record->weekly_cleaning ? 'Sim' : 'Não');
            $sheet->setCellValue("G{$row}", $record->monthly_cleaning ? 'Sim' : 'Não');
            $sheet->setCellValue("H{$row}", $record->external_cleaning_done ? 'Sim' : 'Não');
            $sheet->setCellValue("I{$row}", $record->internal_cleaning_done ? 'Sim' : 'Não');
            $sheet->setCellValue("J{$row}", $record->filter_replacement ? 'Sim' : 'Não');
            $sheet->setCellValue("K{$row}", $record->system_disinfection ? 'Sim' : 'Não');
            $sheet->setCellValue("L{$row}", $record->observations ?? '');
            $sheet->setCellValue("M{$row}", $record->user->name ?? '');

            $row++;
        }
    }
}
