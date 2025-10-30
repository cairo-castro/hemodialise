<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Conditional;
use PhpOffice\PhpSpreadsheet\Style\Color;

class BulkSafetyChecklistExport
{
    protected $checklists;
    protected $periodInfo;

    public function __construct($checklists, array $periodInfo)
    {
        $this->checklists = $checklists;
        $this->periodInfo = $periodInfo;
    }

    /**
     * Gera o workbook completo com 2 abas
     */
    public function generate(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();

        // Aba 1: Resumo por Paciente
        $this->createSummarySheet($spreadsheet);

        // Aba 2: Dados Completos
        $this->createDetailSheet($spreadsheet);

        // Ativar primeira aba por padrão
        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    /**
     * Cria aba de resumo por paciente (300 linhas)
     */
    protected function createSummarySheet(Spreadsheet $spreadsheet)
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Resumo por Paciente');

        // Adicionar logo EMSERH no topo ESQUERDO
        $this->addLogo($sheet, 'A1');

        // Cabeçalho do relatório
        $sheet->mergeCells('C1:M1');
        $sheet->setCellValue('C1', 'CHECKLIST DE SEGURANÇA DO PACIENTE EM HEMODIÁLISE');
        $sheet->getStyle('C1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('C1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Informações do período
        $sheet->mergeCells('C2:M2');
        $periodText = sprintf(
            'Unidade: %s | Período: %s/%s',
            $this->periodInfo['unit_name'],
            $this->periodInfo['month_name'],
            $this->periodInfo['year']
        );
        $sheet->setCellValue('C2', $periodText);
        $sheet->getStyle('C2')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('C2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Linha 3: vazia para espaçamento
        $sheet->getRowDimension(3)->setRowHeight(5);

        // Linha 4: Cabeçalhos (Paciente PRIMEIRO, depois Unidade)
        $headers = [
            'Paciente',
            'Unidade',
            'Data Nascimento',
            'Tipo Sanguíneo',
            'Total Sessões',
            'Completas',
            'Interrompidas',
            'Conformidade %',
            'Pré-Diálise %',
            'Durante %',
            'Pós-Diálise %',
            'Incidentes',
            'Observações',
            'Primeira Sessão',
            'Última Sessão'
        ];

        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '4', $header);
            $col++;
        }

        // Estilizar cabeçalhos
        $sheet->getStyle('A4:O4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // Agrupar checklists por paciente e calcular estatísticas
        $patientStats = $this->calculatePatientStatistics();

        // Preencher dados (Paciente PRIMEIRO, depois Unidade)
        $row = 5;
        foreach ($patientStats as $stats) {
            $sheet->setCellValue('A' . $row, $stats['patient_name']);
            $sheet->setCellValue('B' . $row, $stats['unit_name']);
            $sheet->setCellValue('C' . $row, $stats['birth_date']);
            $sheet->setCellValue('D' . $row, $stats['blood_type']);
            $sheet->setCellValue('E' . $row, $stats['total_sessions']);
            $sheet->setCellValue('F' . $row, $stats['completed_sessions']);
            $sheet->setCellValue('G' . $row, $stats['interrupted_sessions']);
            $sheet->setCellValue('H' . $row, $stats['avg_conformity']);
            $sheet->setCellValue('I' . $row, $stats['pre_dialysis_conformity']);
            $sheet->setCellValue('J' . $row, $stats['during_conformity']);
            $sheet->setCellValue('K' . $row, $stats['post_conformity']);
            $sheet->setCellValue('L' . $row, $stats['incidents_count']);
            $sheet->setCellValue('M' . $row, $stats['observations_count']);
            $sheet->setCellValue('N' . $row, $stats['first_session']);
            $sheet->setCellValue('O' . $row, $stats['last_session']);

            // Aplicar bordas
            $sheet->getStyle('A' . $row . ':O' . $row)->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]]
            ]);

            $row++;
        }

        // Formatação condicional para conformidade (colunas H-K agora)
        $lastRow = $row - 1;
        $this->applyConditionalFormattingToConformity($sheet, 'H5:K' . $lastRow);

        // Freeze panes (linha 4 e colunas A-B: Paciente e Unidade)
        $sheet->freezePane('C5');

        // Auto-size colunas
        foreach (range('A', 'O') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Ativar auto-filtro
        $sheet->setAutoFilter('A4:O' . $lastRow);
    }

    /**
     * Cria aba de dados completos (3.600+ linhas)
     */
    protected function createDetailSheet(Spreadsheet $spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Dados Completos');

        // Adicionar logo EMSERH no topo ESQUERDO
        $this->addLogo($sheet, 'A1');

        // Cabeçalho do relatório
        $sheet->mergeCells('C1:AL1');
        $sheet->setCellValue('C1', 'CHECKLIST DE SEGURANÇA - DADOS COMPLETOS');
        $sheet->getStyle('C1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('C1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Informações do período
        $sheet->mergeCells('C2:AL2');
        $periodText = sprintf(
            'Unidade: %s | Período: %s/%s | Gerado em: %s',
            $this->periodInfo['unit_name'],
            $this->periodInfo['month_name'],
            $this->periodInfo['year'],
            now()->format('d/m/Y H:i')
        );
        $sheet->setCellValue('C2', $periodText);
        $sheet->getStyle('C2')->getFont()->setBold(true)->setSize(10);
        $sheet->getStyle('C2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Linha 3: vazia
        $sheet->getRowDimension(3)->setRowHeight(5);

        // Linha 4: Cabeçalhos (40 colunas) - Paciente PRIMEIRO
        $headers = [
            // Paciente (1)
            'Paciente Nome',
            // Identificação (6)
            'Unidade', 'ID', 'Data Sessão', 'Turno', 'Fase Atual', 'Máquina', 'Responsável',
            // Paciente Info (3)
            'Data Nascimento', 'Tipo Sanguíneo', 'Alergias',
            // Pré-Diálise (13 campos)
            'Máquina Desinfetada', 'Linhas Identificadas', 'Teste Reagente', 'Sensores Pressão',
            'Detector Bolhas', 'ID Paciente Confirmada', 'Acesso Vascular Avaliado',
            'Braço Lavado', 'Paciente Pesado', 'Sinais Vitais', 'Medicações Revisadas',
            'Dialisador Verificado', 'Equipamento OK',
            // Durante Sessão (8 campos)
            'Parâmetros Verificados', 'Heparina Dupla Check', 'Antissepsia',
            'Acesso Monitorado', 'Sinais Vitais Durante', 'Conforto Avaliado',
            'Balanço Hídrico', 'Alarmes Respondidos',
            // Pós-Diálise (5 campos)
            'Desconexão Segura', 'Hemostasia/Curativo', 'Sinais Vitais Estáveis',
            'Complicações Avaliadas', 'Materiais Descartados',
            // Cálculos (1)
            'Conformidade %',
            // Timestamps (6)
            'Pré-Diálise Início', 'Pré-Diálise Fim', 'Durante Início', 'Durante Fim', 'Pós Início', 'Pós Fim',
            // Status (3)
            'Interrompida', 'Motivo Interrupção', 'Observações/Incidentes',
            // Auditoria (2)
            'Criado Em', 'Atualizado Em'
        ];

        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '4', $header);
            $col++;
        }

        // Estilizar cabeçalhos
        $lastCol = 'AN'; // 40 colunas
        $sheet->getStyle('A4:' . $lastCol . '4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E75B5']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);
        $sheet->getRowDimension(4)->setRowHeight(30);

        // Preencher dados
        $row = 5;
        foreach ($this->checklists as $checklist) {
            $this->fillDetailRow($sheet, $row, $checklist);
            $row++;
        }

        $lastRow = $row - 1;

        // Freeze panes (linha 4 + colunas A-B: Paciente e Unidade)
        $sheet->freezePane('C5');

        // Auto-size colunas com limite
        foreach (range('A', 'Z') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        foreach (['AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN'] as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Ativar auto-filtro
        $sheet->setAutoFilter('A4:' . $lastCol . $lastRow);

        // Formatação condicional para colunas de check (C/NC/NA) - ajustado para nova ordem
        // Colunas K até Y agora (Máquina Desinfetada até Equipamento Limpo = 16 checks)
        $this->applyCheckColumnFormatting($sheet, 'K5:Z' . $lastRow);
    }

    /**
     * Preenche uma linha de dados detalhados
     */
    protected function fillDetailRow($sheet, int $row, $checklist)
    {
        $colIndex = 0;
        $columns = [];

        // Paciente PRIMEIRO (1)
        $columns[] = $checklist->patient->full_name ?? '';

        // Identificação (6)
        $columns[] = $checklist->machine->unit->name ?? '';
        $columns[] = $checklist->id;
        $columns[] = $checklist->session_date ? $checklist->session_date->format('d/m/Y') : '';
        $columns[] = $this->formatShift($checklist->shift);
        $columns[] = $this->formatPhase($checklist->current_phase);
        $columns[] = $checklist->machine->name ?? '';
        $columns[] = $checklist->user->name ?? '';

        // Paciente Info (3)
        $columns[] = $checklist->patient->birth_date ? $checklist->patient->birth_date->format('d/m/Y') : '';
        $columns[] = ($checklist->patient->blood_group ?? '') . ($checklist->patient->rh_factor ?? '');
        $columns[] = $checklist->patient->allergies ?? '';

        // Pré-Diálise (13 campos)
        $columns[] = $this->formatCheck($checklist->machine_disinfected);
        $columns[] = $this->formatCheck($checklist->capillary_lines_identified);
        $columns[] = $this->formatCheck($checklist->reagent_test_performed);
        $columns[] = $this->formatCheck($checklist->pressure_sensors_verified);
        $columns[] = $this->formatCheck($checklist->air_bubble_detector_verified);
        $columns[] = $this->formatCheck($checklist->patient_identification_confirmed);
        $columns[] = $this->formatCheck($checklist->vascular_access_evaluated);
        $columns[] = $this->formatCheck($checklist->av_fistula_arm_washed);
        $columns[] = $this->formatCheck($checklist->patient_weighed);
        $columns[] = $this->formatCheck($checklist->vital_signs_checked);
        $columns[] = $this->formatCheck($checklist->medications_reviewed);
        $columns[] = $this->formatCheck($checklist->dialyzer_membrane_checked);
        $columns[] = $this->formatCheck($checklist->equipment_functioning_verified);

        // Durante Sessão (8 campos)
        $columns[] = $this->formatCheck($checklist->dialysis_parameters_verified);
        $columns[] = $this->formatCheck($checklist->heparin_double_checked);
        $columns[] = $this->formatCheck($checklist->antisepsis_performed);
        $columns[] = $this->formatCheck($checklist->vascular_access_monitored);
        $columns[] = $this->formatCheck($checklist->vital_signs_monitored_during);
        $columns[] = $this->formatCheck($checklist->patient_comfort_assessed);
        $columns[] = $this->formatCheck($checklist->fluid_balance_monitored);
        $columns[] = $this->formatCheck($checklist->alarms_responded);

        // Pós-Diálise (5 campos)
        $columns[] = $this->formatCheck($checklist->session_completed_safely);
        $columns[] = $this->formatCheck($checklist->vascular_access_secured);
        $columns[] = $this->formatCheck($checklist->patient_vital_signs_stable);
        $columns[] = $this->formatCheck($checklist->complications_assessed);
        $columns[] = $this->formatCheck($checklist->equipment_cleaned);

        // Conformidade %
        $columns[] = $this->calculateConformity($checklist);

        // Timestamps (6)
        $columns[] = $checklist->pre_dialysis_started_at ? $checklist->pre_dialysis_started_at->format('d/m/Y H:i') : '';
        $columns[] = $checklist->pre_dialysis_completed_at ? $checklist->pre_dialysis_completed_at->format('d/m/Y H:i') : '';
        $columns[] = $checklist->during_session_started_at ? $checklist->during_session_started_at->format('d/m/Y H:i') : '';
        $columns[] = $checklist->during_session_completed_at ? $checklist->during_session_completed_at->format('d/m/Y H:i') : '';
        $columns[] = $checklist->post_dialysis_started_at ? $checklist->post_dialysis_started_at->format('d/m/Y H:i') : '';
        $columns[] = $checklist->post_dialysis_completed_at ? $checklist->post_dialysis_completed_at->format('d/m/Y H:i') : '';

        // Status (3)
        $columns[] = $checklist->is_interrupted ? 'Sim' : 'Não';
        $columns[] = $checklist->interruption_reason ?? '';
        $columns[] = trim(($checklist->observations ?? '') . ' ' . ($checklist->incidents ?? ''));

        // Auditoria (2)
        $columns[] = $checklist->created_at ? $checklist->created_at->format('d/m/Y H:i') : '';
        $columns[] = $checklist->updated_at ? $checklist->updated_at->format('d/m/Y H:i') : '';

        // Escrever valores
        $col = 'A';
        foreach ($columns as $value) {
            $sheet->setCellValue($col . $row, $value);
            $col++;
        }

        // Aplicar bordas
        $sheet->getStyle('A' . $row . ':AN' . $row)->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']]]
        ]);
    }

    /**
     * Calcula estatísticas agregadas por paciente
     */
    protected function calculatePatientStatistics(): array
    {
        $patientGroups = $this->checklists->groupBy('patient_id');
        $stats = [];

        foreach ($patientGroups as $patientId => $checklists) {
            $firstChecklist = $checklists->first();
            $patient = $firstChecklist->patient;

            $totalSessions = $checklists->count();
            $completedSessions = $checklists->where('current_phase', 'completed')->count();
            $interruptedSessions = $checklists->where('is_interrupted', true)->count();

            // Calcular conformidade média
            $conformities = $checklists->map(function ($c) {
                return $this->calculateConformity($c);
            });
            $avgConformity = $conformities->avg();

            // Calcular conformidades por fase
            $preDialysisConformity = $this->calculatePhaseConformity($checklists, 'pre_dialysis');
            $duringConformity = $this->calculatePhaseConformity($checklists, 'during');
            $postConformity = $this->calculatePhaseConformity($checklists, 'post_dialysis');

            // Contar incidentes e observações
            $incidentsCount = $checklists->filter(fn($c) => !empty($c->incidents))->count();
            $observationsCount = $checklists->filter(fn($c) => !empty($c->observations))->count();

            $stats[] = [
                'patient_name' => $patient->full_name ?? '',
                'birth_date' => $patient->birth_date ? $patient->birth_date->format('d/m/Y') : '',
                'blood_type' => ($patient->blood_group ?? '') . ($patient->rh_factor ?? ''),
                'total_sessions' => $totalSessions,
                'completed_sessions' => $completedSessions,
                'interrupted_sessions' => $interruptedSessions,
                'avg_conformity' => round($avgConformity, 1),
                'pre_dialysis_conformity' => round($preDialysisConformity, 1),
                'during_conformity' => round($duringConformity, 1),
                'post_conformity' => round($postConformity, 1),
                'incidents_count' => $incidentsCount,
                'observations_count' => $observationsCount,
                'first_session' => $checklists->min('session_date')?->format('d/m/Y') ?? '',
                'last_session' => $checklists->max('session_date')?->format('d/m/Y') ?? '',
                'unit_name' => $firstChecklist->machine->unit->name ?? '',
            ];
        }

        return $stats;
    }

    /**
     * Calcula conformidade de uma fase específica
     */
    protected function calculatePhaseConformity($checklists, string $phase): float
    {
        $fields = match($phase) {
            'pre_dialysis' => [
                'machine_disinfected', 'capillary_lines_identified', 'reagent_test_performed',
                'pressure_sensors_verified', 'air_bubble_detector_verified', 'patient_identification_confirmed',
                'vascular_access_evaluated', 'av_fistula_arm_washed', 'patient_weighed',
                'vital_signs_checked', 'medications_reviewed', 'dialyzer_membrane_checked',
                'equipment_functioning_verified'
            ],
            'during' => [
                'dialysis_parameters_verified', 'heparin_double_checked', 'antisepsis_performed',
                'vascular_access_monitored', 'vital_signs_monitored_during', 'patient_comfort_assessed',
                'fluid_balance_monitored', 'alarms_responded'
            ],
            'post_dialysis' => [
                'session_completed_safely', 'vascular_access_secured', 'patient_vital_signs_stable',
                'complications_assessed', 'equipment_cleaned'
            ],
            default => []
        };

        $conformities = $checklists->map(function ($checklist) use ($fields) {
            $total = count($fields);
            $conformes = 0;

            foreach ($fields as $field) {
                if ($checklist->$field === true) {
                    $conformes++;
                }
            }

            return $total > 0 ? ($conformes / $total) * 100 : 0;
        });

        return $conformities->avg();
    }

    /**
     * Calcula conformidade geral de um checklist (agora com 26 campos)
     */
    protected function calculateConformity($checklist): int
    {
        $fields = [
            // Pré-diálise (13)
            'machine_disinfected', 'capillary_lines_identified', 'reagent_test_performed',
            'pressure_sensors_verified', 'air_bubble_detector_verified', 'patient_identification_confirmed',
            'vascular_access_evaluated', 'av_fistula_arm_washed', 'patient_weighed',
            'vital_signs_checked', 'medications_reviewed', 'dialyzer_membrane_checked',
            'equipment_functioning_verified',
            // Durante (8)
            'dialysis_parameters_verified', 'heparin_double_checked', 'antisepsis_performed',
            'vascular_access_monitored', 'vital_signs_monitored_during', 'patient_comfort_assessed',
            'fluid_balance_monitored', 'alarms_responded',
            // Pós-diálise (5)
            'session_completed_safely', 'vascular_access_secured', 'patient_vital_signs_stable',
            'complications_assessed', 'equipment_cleaned'
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

    /**
     * Formata valor de check para C/NC/NA
     */
    protected function formatCheck($value): string
    {
        if ($value === null) {
            return 'NA';
        }
        return $value ? 'C' : 'NC';
    }

    /**
     * Formata turno
     */
    protected function formatShift($shift): string
    {
        return match($shift) {
            'manha' => 'Manhã',
            'tarde' => 'Tarde',
            'noite' => 'Noite',
            default => $shift ?? ''
        };
    }

    /**
     * Formata fase
     */
    protected function formatPhase($phase): string
    {
        return match($phase) {
            'completed' => 'Completo',
            'post_dialysis' => 'Pós-Diálise',
            'during_session' => 'Em Sessão',
            'pre_dialysis' => 'Pré-Diálise',
            default => $phase ?? ''
        };
    }

    /**
     * Adiciona logo EMSERH ao topo direito
     */
    protected function addLogo($sheet, string $cell)
    {
        $logoPath = public_path('images/emserh-logo.png');

        if (!file_exists($logoPath)) {
            return; // Não adicionar logo se arquivo não existe
        }

        $drawing = new Drawing();
        $drawing->setName('EMSERH Logo');
        $drawing->setDescription('EMSERH - Empresa Maranhense de Serviços Hospitalares');
        $drawing->setPath($logoPath);
        $drawing->setCoordinates($cell);
        $drawing->setHeight(60); // 60 pixels de altura
        $drawing->setWorksheet($sheet);
    }

    /**
     * Aplica formatação condicional para células de conformidade (%)
     */
    protected function applyConditionalFormattingToConformity($sheet, string $range)
    {
        // Verde: >= 90%
        $condition1 = new Conditional();
        $condition1->setConditionType(Conditional::CONDITION_CELLIS);
        $condition1->setOperatorType(Conditional::OPERATOR_GREATERTHANOREQUAL);
        $condition1->addCondition('90');
        $condition1->getStyle()->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C6EFCE']],
            'font' => ['color' => ['rgb' => '006100'], 'bold' => true]
        ]);

        // Amarelo: 70-89%
        $condition2 = new Conditional();
        $condition2->setConditionType(Conditional::CONDITION_CELLIS);
        $condition2->setOperatorType(Conditional::OPERATOR_BETWEEN);
        $condition2->addCondition('70');
        $condition2->addCondition('89');
        $condition2->getStyle()->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFEB9C']],
            'font' => ['color' => ['rgb' => '9C6500'], 'bold' => true]
        ]);

        // Vermelho: < 70%
        $condition3 = new Conditional();
        $condition3->setConditionType(Conditional::CONDITION_CELLIS);
        $condition3->setOperatorType(Conditional::OPERATOR_LESSTHAN);
        $condition3->addCondition('70');
        $condition3->getStyle()->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFC7CE']],
            'font' => ['color' => ['rgb' => '9C0006'], 'bold' => true]
        ]);

        $sheet->getStyle($range)->setConditionalStyles([$condition1, $condition2, $condition3]);
    }

    /**
     * Aplica formatação condicional para colunas de check (C/NC/NA)
     */
    protected function applyCheckColumnFormatting($sheet, string $range)
    {
        // Verde: C (Conforme)
        $condition1 = new Conditional();
        $condition1->setConditionType(Conditional::CONDITION_CELLIS);
        $condition1->setOperatorType(Conditional::OPERATOR_EQUAL);
        $condition1->addCondition('"C"');
        $condition1->getStyle()->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C6EFCE']],
            'font' => ['color' => ['rgb' => '006100'], 'bold' => true]
        ]);

        // Vermelho: NC (Não Conforme)
        $condition2 = new Conditional();
        $condition2->setConditionType(Conditional::CONDITION_CELLIS);
        $condition2->setOperatorType(Conditional::OPERATOR_EQUAL);
        $condition2->addCondition('"NC"');
        $condition2->getStyle()->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFC7CE']],
            'font' => ['color' => ['rgb' => '9C0006'], 'bold' => true]
        ]);

        // Cinza: NA (Não Aplica)
        $condition3 = new Conditional();
        $condition3->setConditionType(Conditional::CONDITION_CELLIS);
        $condition3->setOperatorType(Conditional::OPERATOR_EQUAL);
        $condition3->addCondition('"NA"');
        $condition3->getStyle()->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F2F2']],
            'font' => ['color' => ['rgb' => '666666']]
        ]);

        $sheet->getStyle($range)->setConditionalStyles([$condition1, $condition2, $condition3]);
    }
}
