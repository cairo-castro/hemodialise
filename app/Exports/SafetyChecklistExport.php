<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeWriting;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SafetyChecklistExport implements WithEvents
{
    protected $checklists;

    public function __construct($checklists)
    {
        $this->checklists = $checklists;
    }

    public function registerEvents(): array
    {
        return [
            BeforeWriting::class => function(BeforeWriting $event) {
                // Carregar o template
                $templatePath = storage_path('app/templates/safety-checklist-template.xlsx');

                if (file_exists($templatePath)) {
                    $reader = IOFactory::createReader('Xlsx');
                    $templateSpreadsheet = $reader->load($templatePath);

                    // Preencher os dados no template
                    $sheet = $templateSpreadsheet->getActiveSheet();
                    $this->fillTemplate($sheet);

                    // Substituir o spreadsheet padrão pelo template preenchido
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
        // Preencher informações do paciente (primeira checklist)
        if ($this->checklists->isNotEmpty()) {
            $firstChecklist = $this->checklists->first();

            // Linha 7, Colunas A-E: Nome completo do paciente
            $sheet->mergeCells('A7:E7');
            $sheet->setCellValue('A7', 'NOME COMPLETO: ' . ($firstChecklist->patient->full_name ?? ''));
            $sheet->getStyle('A7')->getFont()->setBold(true);

            // Linha 7, Colunas F-H: Data de nascimento
            $sheet->mergeCells('F7:H7');
            $birthDate = $firstChecklist->patient->birth_date ? $firstChecklist->patient->birth_date->format('d/m/Y') : '';
            $sheet->setCellValue('F7', 'DATA DE NASCIMENTO: ' . $birthDate);
            $sheet->getStyle('F7')->getFont()->setBold(true);
        }

        // Para cada checklist, preencher uma coluna começando da coluna C
        $currentCol = 'C';

        foreach ($this->checklists as $index => $checklist) {
            if ($index >= 6) break; // Máximo 6 checklists (colunas C até H)

            $this->fillChecklistColumn($sheet, $checklist, $currentCol);

            $currentCol++;
        }
    }

    protected function fillChecklistColumn($sheet, $checklist, string $col)
    {
        // Linha 7 - Nome do paciente (assumindo que está na linha 7)
        // Ajuste estas linhas conforme a estrutura real do seu template

        // Informações do cabeçalho da coluna
        $sessionDate = $checklist->session_date ? $checklist->session_date->format('d/m/Y') : '';
        $shift = match($checklist->shift) {
            'manha' => 'Manhã',
            'tarde' => 'Tarde',
            'noite' => 'Noite',
            default => $checklist->shift
        };

        // Exemplo: Preencher data na linha 8 (ajuste conforme seu template)
        $sheet->setCellValue("{$col}8", "Data: {$sessionDate}\nTurno: {$shift}");
        $sheet->getStyle("{$col}8")->getAlignment()->setWrapText(true);
        $sheet->getStyle("{$col}8")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Preencher checkboxes com C/NC/NA
        // PRÉ-DIÁLISE (ajuste as linhas conforme seu template)
        $preDialysisData = [
            9 => $checklist->machine_disinfected,
            10 => $checklist->capillary_lines_identified,
            11 => null, // Teste de reagente
            12 => null, // Sensores
            13 => null, // Detector de bolhas
            14 => $checklist->patient_identification_confirmed,
            15 => null, // Lavagem do braço
            16 => null, // Pesagem
            17 => $checklist->vital_signs_checked,
            18 => $checklist->vascular_access_evaluated,
            19 => $checklist->medications_reviewed,
        ];

        foreach ($preDialysisData as $row => $value) {
            $cellValue = $this->getCheckValue($value);
            $sheet->setCellValue("{$col}{$row}", $cellValue);
            $sheet->getStyle("{$col}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // DURANTE A SESSÃO (ajuste as linhas conforme seu template)
        $duringSessionData = [
            21 => $checklist->dialysis_parameters_verified ?? null,
            22 => $checklist->patient_comfort_assessed ?? null,
            23 => $checklist->fluid_balance_monitored ?? null,
            24 => $checklist->alarms_responded ?? null,
        ];

        foreach ($duringSessionData as $row => $value) {
            $cellValue = $this->getCheckValue($value);
            $sheet->setCellValue("{$col}{$row}", $cellValue);
            $sheet->getStyle("{$col}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // PÓS-DIÁLISE (ajuste as linhas conforme seu template)
        $postDialysisData = [
            26 => $checklist->session_completed_safely ?? null,
            27 => $checklist->vascular_access_secured ?? null,
            28 => $checklist->patient_vital_signs_stable ?? null,
            29 => null, // Complicações avaliadas
            30 => $checklist->equipment_cleaned ?? null,
        ];

        foreach ($postDialysisData as $row => $value) {
            $cellValue = $this->getCheckValue($value);
            $sheet->setCellValue("{$col}{$row}", $cellValue);
            $sheet->getStyle("{$col}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // Assinatura do profissional (ajuste a linha conforme seu template)
        $userName = $checklist->user->name ?? '';
        $sheet->setCellValue("{$col}31", $userName);
        $sheet->getStyle("{$col}31")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    /**
     * Converte o valor booleano para a legenda apropriada
     * C (Conforme), NC (Não conforme), NA (Não se aplica)
     */
    protected function getCheckValue($check): string
    {
        if ($check === null) {
            return 'NA'; // Não se aplica
        }
        return $check ? 'C' : 'NC'; // Conforme ou Não conforme
    }
}
