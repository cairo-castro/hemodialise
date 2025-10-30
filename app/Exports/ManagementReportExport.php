<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ManagementReportExport
{
    protected $checklists;
    protected $periodInfo;

    public function __construct($checklists, $periodInfo = [])
    {
        $this->checklists = $checklists;
        $this->periodInfo = $periodInfo;
    }

    public function generate(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();

        // Criar abas
        $this->createSummarySheet($spreadsheet);
        $this->createRawDataSheet($spreadsheet);

        // Definir a aba resumo como ativa
        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    protected function createSummarySheet(Spreadsheet $spreadsheet)
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Resumo Gerencial');

        // Cabeçalho
        $this->addHeader($sheet);

        // KPIs principais
        $this->addKPIs($sheet);

        // Gráfico de conformidade
        $this->addConformityChart($sheet);

        // Estatísticas por turno
        $this->addShiftStatistics($sheet);

        // Incidentes e interrupções
        $this->addIncidentStatistics($sheet);
    }

    protected function addHeader($sheet)
    {
        $sheet->mergeCells('A1:F1');
        $sheet->setCellValue('A1', 'RELATÓRIO GERENCIAL - CHECKLIST DE SEGURANÇA EM HEMODIÁLISE');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('4472C4');
        $sheet->getStyle('A1')->getFont()->getColor()->setRGB('FFFFFF');
        $sheet->getRowDimension(1)->setRowHeight(30);

        // Informações do período
        $row = 2;
        $sheet->mergeCells("A{$row}:F{$row}");
        $periodText = "Período: " . ($this->periodInfo['period'] ?? 'N/A') . " | Unidade: " . ($this->periodInfo['unit'] ?? 'N/A');
        $sheet->setCellValue("A{$row}", $periodText);
        $sheet->getStyle("A{$row}")->getFont()->setItalic(true);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row++;
        $sheet->setCellValue("A{$row}", "Gerado em: " . now()->format('d/m/Y H:i'));
        $sheet->getStyle("A{$row}")->getFont()->setSize(9);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->mergeCells("A{$row}:F{$row}");
    }

    protected function addKPIs($sheet)
    {
        $row = 5;

        // Título da seção
        $sheet->mergeCells("A{$row}:F{$row}");
        $sheet->setCellValue("A{$row}", 'INDICADORES PRINCIPAIS');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle("A{$row}")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E7E6E6');
        $sheet->getRowDimension($row)->setRowHeight(25);

        $row++;

        // Calcular estatísticas
        $stats = $this->calculateStatistics();

        // KPIs em cards
        $kpis = [
            ['label' => 'Total de Checklists', 'value' => $stats['total_checklists'], 'color' => '4472C4'],
            ['label' => 'Total de Pacientes', 'value' => $stats['unique_patients'], 'color' => '70AD47'],
            ['label' => 'Sessões Completas', 'value' => $stats['completed_sessions'], 'color' => '44C467'],
            ['label' => 'Conformidade Média', 'value' => $stats['avg_conformity'] . '%', 'color' => 'FFC000'],
            ['label' => 'Com Incidentes', 'value' => $stats['with_incidents'], 'color' => 'C00000'],
            ['label' => 'Sessões Interrompidas', 'value' => $stats['interrupted'], 'color' => 'FF6B6B'],
        ];

        $col = 'A';
        foreach ($kpis as $kpi) {
            $sheet->setCellValue("{$col}{$row}", $kpi['label']);
            $sheet->getStyle("{$col}{$row}")->getFont()->setBold(true)->setSize(10);
            $sheet->getStyle("{$col}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $nextRow = $row + 1;
            $sheet->setCellValue("{$col}{$nextRow}", $kpi['value']);
            $sheet->getStyle("{$col}{$nextRow}")->getFont()->setBold(true)->setSize(16);
            $sheet->getStyle("{$col}{$nextRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("{$col}{$nextRow}")->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB($kpi['color']);
            $sheet->getStyle("{$col}{$nextRow}")->getFont()->getColor()->setRGB('FFFFFF');

            $col++;
        }

        $sheet->getRowDimension($row)->setRowHeight(20);
        $sheet->getRowDimension($row + 1)->setRowHeight(35);
    }

    protected function addConformityChart($sheet)
    {
        $row = 9;

        // Título
        $sheet->mergeCells("A{$row}:F{$row}");
        $sheet->setCellValue("A{$row}", 'ANÁLISE DE CONFORMIDADE');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle("A{$row}")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E7E6E6');

        $row++;

        // Calcular conformidade por categoria
        $conformityData = $this->calculateConformityBreakdown();

        // Dados para o gráfico
        $sheet->setCellValue("A{$row}", 'Status');
        $sheet->setCellValue("B{$row}", 'Quantidade');
        $sheet->setCellValue("C{$row}", 'Percentual');
        $sheet->getStyle("A{$row}:C{$row}")->getFont()->setBold(true);

        $row++;
        $startRow = $row;

        $sheet->setCellValue("A{$row}", 'Conforme (C)');
        $sheet->setCellValue("B{$row}", $conformityData['conforme']);
        $sheet->setCellValue("C{$row}", $conformityData['conforme_pct'] . '%');
        $sheet->getStyle("A{$row}:C{$row}")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('C6EFCE');

        $row++;
        $sheet->setCellValue("A{$row}", 'Não Conforme (NC)');
        $sheet->setCellValue("B{$row}", $conformityData['nao_conforme']);
        $sheet->setCellValue("C{$row}", $conformityData['nao_conforme_pct'] . '%');
        $sheet->getStyle("A{$row}:C{$row}")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFC7CE');

        $row++;
        $sheet->setCellValue("A{$row}", 'Não se Aplica (NA)');
        $sheet->setCellValue("B{$row}", $conformityData['na']);
        $sheet->setCellValue("C{$row}", $conformityData['na_pct'] . '%');
        $sheet->getStyle("A{$row}:C{$row}")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFEB9C');

        $endRow = $row;

        // Criar gráfico de pizza
        $dataSeriesLabels = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "Resumo Gerencial!\$A\${$startRow}", null, 1),
        ];

        $xAxisTickValues = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "Resumo Gerencial!\$A\${$startRow}:\$A\${$endRow}", null, 3),
        ];

        $dataSeriesValues = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, "Resumo Gerencial!\$B\${$startRow}:\$B\${$endRow}", null, 3),
        ];

        $series = new DataSeries(
            DataSeries::TYPE_PIECHART,
            null,
            range(0, count($dataSeriesValues) - 1),
            $dataSeriesLabels,
            $xAxisTickValues,
            $dataSeriesValues
        );

        $plotArea = new PlotArea(null, [$series]);
        $legend = new Legend(Legend::POSITION_RIGHT, null, false);
        $title = new Title('Distribuição de Conformidade');

        $chart = new Chart(
            'conformity_chart',
            $title,
            $legend,
            $plotArea
        );

        $chart->setTopLeftPosition('E10');
        $chart->setBottomRightPosition('K20');

        $sheet->addChart($chart);
    }

    protected function addShiftStatistics($sheet)
    {
        $row = 22;

        $sheet->mergeCells("A{$row}:F{$row}");
        $sheet->setCellValue("A{$row}", 'ESTATÍSTICAS POR TURNO');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle("A{$row}")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E7E6E6');

        $row++;

        $shiftStats = $this->calculateShiftStatistics();

        $sheet->setCellValue("A{$row}", 'Turno');
        $sheet->setCellValue("B{$row}", 'Quantidade');
        $sheet->setCellValue("C{$row}", 'Conformidade Média');
        $sheet->setCellValue("D{$row}", 'Incidentes');
        $sheet->getStyle("A{$row}:D{$row}")->getFont()->setBold(true);

        $row++;

        foreach ($shiftStats as $shift => $data) {
            $shiftName = match($shift) {
                'manha' => 'Manhã',
                'tarde' => 'Tarde',
                'noite' => 'Noite',
                default => $shift
            };

            $sheet->setCellValue("A{$row}", $shiftName);
            $sheet->setCellValue("B{$row}", $data['count']);
            $sheet->setCellValue("C{$row}", $data['avg_conformity'] . '%');
            $sheet->setCellValue("D{$row}", $data['incidents']);
            $row++;
        }
    }

    protected function addIncidentStatistics($sheet)
    {
        $row = 28;

        $sheet->mergeCells("A{$row}:F{$row}");
        $sheet->setCellValue("A{$row}", 'RESUMO DE INCIDENTES E OBSERVAÇÕES');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle("A{$row}")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E7E6E6');

        $row++;

        $stats = $this->calculateStatistics();

        $sheet->setCellValue("A{$row}", 'Total de checklists com incidentes relatados:');
        $sheet->setCellValue("C{$row}", $stats['with_incidents']);
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);

        $row++;
        $sheet->setCellValue("A{$row}", 'Sessões interrompidas:');
        $sheet->setCellValue("C{$row}", $stats['interrupted']);
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);

        $row++;
        $sheet->setCellValue("A{$row}", 'Checklists com observações:');
        $sheet->setCellValue("C{$row}", $stats['with_observations']);
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);

        // Auto-size colunas
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    protected function createRawDataSheet(Spreadsheet $spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Dados Detalhados');

        // Cabeçalho
        $headers = [
            'ID',
            'Data Sessão',
            'Turno',
            'Unidade',
            'Máquina',
            'Paciente',
            'Data Nascimento',
            'Responsável',
            'Status',
            'Máquina Desinfetada',
            'Linhas Identificadas',
            'Identificação Paciente',
            'Acesso Vascular',
            'Sinais Vitais',
            'Medicações Revisadas',
            'Dialisador Verificado',
            'Equipamento OK',
            'Conformidade %',
            'Tem Incidentes',
            'Incidentes',
            'Interrompido',
            'Observações',
            'Criado em',
        ];

        $sheet->fromArray([$headers], null, 'A1');
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->getFont()->setBold(true);
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('4472C4');
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->getFont()->getColor()->setRGB('FFFFFF');

        // Dados
        $row = 2;
        foreach ($this->checklists as $checklist) {
            $conformity = $this->calculateChecklistConformity($checklist);

            $data = [
                $checklist->id,
                $checklist->session_date?->format('d/m/Y'),
                match($checklist->shift) {
                    'manha' => 'Manhã',
                    'tarde' => 'Tarde',
                    'noite' => 'Noite',
                    default => $checklist->shift
                },
                $checklist->machine->unit->name ?? '',
                $checklist->machine->name ?? '',
                $checklist->patient->full_name ?? '',
                $checklist->patient->birth_date?->format('d/m/Y') ?? '',
                $checklist->user->name ?? '',
                match($checklist->current_phase) {
                    'completed' => 'Completo',
                    'post_dialysis' => 'Pós-Diálise',
                    'during_session' => 'Em Sessão',
                    'pre_dialysis' => 'Pré-Diálise',
                    default => $checklist->current_phase
                },
                $this->formatBoolean($checklist->machine_disinfected),
                $this->formatBoolean($checklist->capillary_lines_identified),
                $this->formatBoolean($checklist->patient_identification_confirmed),
                $this->formatBoolean($checklist->vascular_access_evaluated),
                $this->formatBoolean($checklist->vital_signs_checked),
                $this->formatBoolean($checklist->medications_reviewed),
                $this->formatBoolean($checklist->dialyzer_membrane_checked),
                $this->formatBoolean($checklist->equipment_functioning_verified),
                $conformity . '%',
                $checklist->incidents ? 'Sim' : 'Não',
                $checklist->incidents ?? '',
                $checklist->is_interrupted ? 'Sim' : 'Não',
                $checklist->observations ?? '',
                $checklist->created_at?->format('d/m/Y H:i'),
            ];

            $sheet->fromArray([$data], null, "A{$row}");
            $row++;
        }

        // Auto-size colunas
        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Aplicar bordas
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . ($row - 1))->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);
    }

    protected function calculateStatistics(): array
    {
        $total = $this->checklists->count();
        $uniquePatients = $this->checklists->pluck('patient_id')->unique()->count();
        $completed = $this->checklists->where('current_phase', 'completed')->count();
        $withIncidents = $this->checklists->filter(fn($c) => !empty($c->incidents))->count();
        $interrupted = $this->checklists->where('is_interrupted', true)->count();
        $withObservations = $this->checklists->filter(fn($c) => !empty($c->observations))->count();

        $totalConformity = 0;
        foreach ($this->checklists as $checklist) {
            $totalConformity += $this->calculateChecklistConformity($checklist);
        }
        $avgConformity = $total > 0 ? round($totalConformity / $total) : 0;

        return [
            'total_checklists' => $total,
            'unique_patients' => $uniquePatients,
            'completed_sessions' => $completed,
            'avg_conformity' => $avgConformity,
            'with_incidents' => $withIncidents,
            'interrupted' => $interrupted,
            'with_observations' => $withObservations,
        ];
    }

    protected function calculateConformityBreakdown(): array
    {
        $conforme = 0;
        $naoConforme = 0;
        $na = 0;
        $total = 0;

        $fields = [
            'machine_disinfected',
            'capillary_lines_identified',
            'patient_identification_confirmed',
            'vascular_access_evaluated',
            'vital_signs_checked',
            'medications_reviewed',
            'dialyzer_membrane_checked',
            'equipment_functioning_verified',
        ];

        foreach ($this->checklists as $checklist) {
            foreach ($fields as $field) {
                $total++;
                $value = $checklist->$field;

                if ($value === true) {
                    $conforme++;
                } elseif ($value === false) {
                    $naoConforme++;
                } else {
                    $na++;
                }
            }
        }

        return [
            'conforme' => $conforme,
            'conforme_pct' => $total > 0 ? round(($conforme / $total) * 100) : 0,
            'nao_conforme' => $naoConforme,
            'nao_conforme_pct' => $total > 0 ? round(($naoConforme / $total) * 100) : 0,
            'na' => $na,
            'na_pct' => $total > 0 ? round(($na / $total) * 100) : 0,
        ];
    }

    protected function calculateShiftStatistics(): array
    {
        $stats = [];

        foreach (['manha', 'tarde', 'noite'] as $shift) {
            $shiftChecklists = $this->checklists->where('shift', $shift);
            $count = $shiftChecklists->count();

            if ($count > 0) {
                $totalConformity = 0;
                foreach ($shiftChecklists as $checklist) {
                    $totalConformity += $this->calculateChecklistConformity($checklist);
                }

                $stats[$shift] = [
                    'count' => $count,
                    'avg_conformity' => round($totalConformity / $count),
                    'incidents' => $shiftChecklists->filter(fn($c) => !empty($c->incidents))->count(),
                ];
            }
        }

        return $stats;
    }

    protected function calculateChecklistConformity($checklist): int
    {
        $fields = [
            'machine_disinfected',
            'capillary_lines_identified',
            'patient_identification_confirmed',
            'vascular_access_evaluated',
            'vital_signs_checked',
            'medications_reviewed',
            'dialyzer_membrane_checked',
            'equipment_functioning_verified',
        ];

        $total = count($fields);
        $conformes = 0;

        foreach ($fields as $field) {
            if ($checklist->$field === true) {
                $conformes++;
            }
        }

        return $total > 0 ? round(($conformes / $total) * 100) : 0;
    }

    protected function formatBoolean($value): string
    {
        if ($value === null) {
            return 'NA';
        }
        return $value ? 'C' : 'NC';
    }
}
