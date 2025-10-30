<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SafetyChecklistExport implements FromCollection, WithEvents, WithCustomStartCell
{
    protected $checklists;

    public function __construct($checklists)
    {
        $this->checklists = $checklists;
    }

    public function collection()
    {
        // Retorna coleção vazia pois vamos preencher manualmente
        return collect([]);
    }

    public function startCell(): string
    {
        return 'A1';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Configurar larguras das colunas
                $sheet->getColumnDimension('A')->setWidth(60);
                $sheet->getColumnDimension('B')->setWidth(5);
                for ($col = 'C'; $col <= 'H'; $col++) {
                    $sheet->getColumnDimension($col)->setWidth(12);
                }

                // Cabeçalho - linhas 2-6
                $this->addHeader($sheet);

                // Para cada checklist, criar uma coluna de dados
                $currentCol = 'C';
                foreach ($this->checklists as $index => $checklist) {
                    if ($index == 0) {
                        // Primeira checklist - preencher informações do paciente e estrutura
                        $this->addPatientInfo($sheet, $checklist);
                        $this->addChecklistStructure($sheet);
                    }

                    // Adicionar dados da checklist na coluna
                    $this->addChecklistData($sheet, $checklist, $currentCol);

                    $currentCol++;
                    if ($currentCol > 'H') break; // Máximo 6 checklists por planilha
                }

                // Aplicar bordas e formatação
                $this->applyFormatting($sheet);
            },
        ];
    }

    protected function addHeader(Worksheet $sheet)
    {
        $sheet->mergeCells('A2:H2');
        $sheet->setCellValue('A2', 'ESTADO DO MARANHÃO');

        $sheet->mergeCells('A3:H3');
        $sheet->setCellValue('A3', 'SECRETARIA DE ESTADO DA SAÚDE');

        $sheet->mergeCells('A4:H4');
        $sheet->setCellValue('A4', 'SECRETARIA ADJUNTA DE ASSISTÊNCIA À SAÚDE');

        $sheet->mergeCells('A5:H5');
        $sheet->setCellValue('A5', 'COORDENAÇÃO DOS SERVIÇOS DE NEFROLOGIA');

        $sheet->mergeCells('A6:H6');
        $sheet->setCellValue('A6', 'CHECKLIST PARA SEGURANÇA DO PACIENTE EM HEMODIÁLISE');

        // Centralizar e negrito no cabeçalho
        $sheet->getStyle('A2:A6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A2:A6')->getFont()->setBold(true);
        $sheet->getStyle('A6')->getFont()->setSize(12);
    }

    protected function addPatientInfo(Worksheet $sheet, $checklist)
    {
        $sheet->mergeCells('A7:E7');
        $sheet->setCellValue('A7', 'NOME COMPLETO: ' . ($checklist->patient->full_name ?? ''));

        $sheet->mergeCells('F7:H7');
        $birthDate = $checklist->patient->birth_date ? $checklist->patient->birth_date->format('d/m/Y') : '';
        $sheet->setCellValue('F7', 'DATA DE NASCIMENTO: ' . $birthDate);

        $sheet->getStyle('A7:H7')->getFont()->setBold(true);
    }

    protected function addChecklistStructure(Worksheet $sheet)
    {
        // Linha 8 - Cabeçalho de seção
        $sheet->mergeCells('A8:B8');
        $sheet->setCellValue('A8', 'PRÉ DIÁLISE - Preparação da máquina e avaliação inicial do paciente');
        $sheet->getStyle('A8')->getFont()->setBold(true);
        $sheet->getStyle('A8')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E7E6E6');

        // Itens da pré-diálise
        $preDialysisItems = [
            '1. Máquina de diálise desinfectada.',
            '2. Capilar e linhas identificados corretamente (quando há reuso).',
            '3. Teste de reagente realizado (pré/pós) (quando há reuso).',
            '4. Sensores verificados: pressão venosa, pressão arterial, pressão transmembrana.',
            '5. Detector de bolhas de ar funcional e alinhado à câmara venosa (catabolha) verificado.',
            '6. Identificação do paciente conferida: crachá, pulseira, cadeira e confirmação verbal.',
            '7. Lavagem do braço da fístula arteriovenosa (quando aplicável).',
            '8. Pesagem do paciente realizada.',
            '9. Sinais vitais conferidos conforme protocolo.',
            '10. Condições do acesso vascular verificadas: curativo, sinais de infecção, integridade, heparina no lúmen (se cateter).',
            '11. Prescrição médica conferida: parâmetros da máquina e medicações corretamente conferidas.',
        ];

        $row = 9;
        foreach ($preDialysisItems as $item) {
            $sheet->mergeCells("A{$row}:B{$row}");
            $sheet->setCellValue("A{$row}", $item);
            $row++;
        }

        // Linha 20 - Durante a sessão
        $sheet->mergeCells('A20:H20');
        $sheet->setCellValue('A20', 'DURANTE A SESSÃO - Instalação e monitoramento do processo dialítico');
        $sheet->getStyle('A20')->getFont()->setBold(true);
        $sheet->getStyle('A20')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E7E6E6');

        // Itens durante sessão
        $duringSessionItems = [
            '1. Dupla checagem de heparina realizada.',
            '2. Antissepsia da pele ou do cateter antes da punção da fístula ou conexão do cateter realizada conforme protocolo.',
            '3. Acesso vascular monitorado durante a sessão (fluxo sanguíneo, fixação das agulhas, conexão das linhas, sangramento ou descolamento).',
            '4. Sinais vitais conforme quadro clínico, protocolo ou prescrição.',
        ];

        $row = 21;
        foreach ($duringSessionItems as $item) {
            $sheet->mergeCells("A{$row}:B{$row}");
            $sheet->setCellValue("A{$row}", $item);
            $row++;
        }

        // Linha 25 - Pós diálise
        $sheet->mergeCells('A25:H25');
        $sheet->setCellValue('A25', 'PÓS DIÁLISE - Desconexão da máquina e avaliação pós procedimento');
        $sheet->getStyle('A25')->getFont()->setBold(true);
        $sheet->getStyle('A25')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E7E6E6');

        // Itens pós diálise
        $postDialysisItems = [
            '1. Desconexão realizada de forma segura: sem perda de sangue e sem risco de embolia.',
            '2. Hemostasia e curativo do acesso realizado conforme protocolo.',
            '3. Sinais vitais conferidos conforme protocolo ou prescrição.',
            '4. Paciente avaliado para detecção de complicações: (sangramento, instabilidade hemodiâmica, risco de queda, outros).',
            '5. Materiais descartados corretamente e máquina programada para desinfecção e materiais encaminhados para reprocessamento, quando aplicável.',
        ];

        $row = 26;
        foreach ($postDialysisItems as $item) {
            $sheet->mergeCells("A{$row}:B{$row}");
            $sheet->setCellValue("A{$row}", $item);
            $row++;
        }

        // Linha 31 - Assinatura
        $sheet->mergeCells('A31:B31');
        $sheet->setCellValue('A31', 'ASSINATURA DO PROFISSIONAL:');
        $sheet->getStyle('A31')->getFont()->setBold(true);
    }

    protected function addChecklistData(Worksheet $sheet, $checklist, string $col)
    {
        // Adicionar data da sessão no cabeçalho
        $sessionDate = $checklist->session_date ? $checklist->session_date->format('d/m/Y') : '';
        $shift = match($checklist->shift) {
            'manha' => 'Manhã',
            'tarde' => 'Tarde',
            'noite' => 'Noite',
            default => $checklist->shift
        };

        $sheet->setCellValue("{$col}8", "Data: {$sessionDate}\nTurno: {$shift}");
        $sheet->getStyle("{$col}8")->getAlignment()->setWrapText(true);
        $sheet->getStyle("{$col}8")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("{$col}8")->getFont()->setBold(true)->setSize(9);

        // Pré-diálise checks (linhas 9-19) - 11 itens
        $preDialysisChecks = [
            $checklist->machine_disinfected, // 1
            $checklist->capillary_lines_identified, // 2
            null, // 3 - Teste de reagente (não existe no modelo)
            null, // 4 - Sensores verificados (não existe)
            null, // 5 - Detector de bolhas (não existe)
            $checklist->patient_identification_confirmed, // 6
            null, // 7 - Lavagem do braço (não existe)
            null, // 8 - Pesagem (não existe)
            $checklist->vital_signs_checked, // 9
            $checklist->vascular_access_evaluated, // 10
            $checklist->medications_reviewed, // 11
        ];

        $row = 9;
        foreach ($preDialysisChecks as $check) {
            $value = $check ? 'X' : '';
            $sheet->setCellValue("{$col}{$row}", $value);
            $sheet->getStyle("{$col}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row++;
        }

        // Durante sessão checks (linhas 21-24)
        $duringSessionChecks = [
            $checklist->dialysis_parameters_verified,
            $checklist->patient_comfort_assessed,
            $checklist->fluid_balance_monitored,
            $checklist->alarms_responded,
        ];

        $row = 21;
        foreach ($duringSessionChecks as $check) {
            $value = $check ? 'X' : '';
            $sheet->setCellValue("{$col}{$row}", $value);
            $sheet->getStyle("{$col}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row++;
        }

        // Pós diálise checks (linhas 26-30) - 5 itens
        $postDialysisChecks = [
            $checklist->session_completed_safely, // 1
            $checklist->vascular_access_secured, // 2
            $checklist->patient_vital_signs_stable, // 3
            null, // 4 - Complicações avaliadas (não existe no modelo)
            $checklist->equipment_cleaned, // 5
        ];

        $row = 26;
        foreach ($postDialysisChecks as $check) {
            $value = $check ? 'X' : '';
            $sheet->setCellValue("{$col}{$row}", $value);
            $sheet->getStyle("{$col}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row++;
        }

        // Assinatura do profissional
        $userName = $checklist->user->name ?? '';
        $sheet->setCellValue("{$col}31", $userName);
        $sheet->getStyle("{$col}31")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("{$col}31")->getFont()->setSize(9);
    }

    protected function applyFormatting(Worksheet $sheet)
    {
        // Bordas em toda a área de dados
        $sheet->getStyle('A8:H31')->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Alinhar texto à esquerda nas descrições
        $sheet->getStyle('A9:B31')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('A9:B31')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A9:B31')->getAlignment()->setWrapText(true);

        // Ajustar altura das linhas
        for ($row = 9; $row <= 31; $row++) {
            $sheet->getRowDimension($row)->setRowHeight(30);
        }

        $sheet->getRowDimension(8)->setRowHeight(35);
    }
}
