<?php

namespace App\Filament\Resources\SafetyChecklistResource\Pages;

use App\Filament\Resources\SafetyChecklistResource;
use App\Exports\SafetyChecklistExport;
use App\Exports\ManagementReportExport;
use App\Exports\BulkSafetyChecklistExport;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListSafetyChecklists extends ListRecords
{
    protected static string $resource = SafetyChecklistResource::class;

    /**
     * Calcula a conformidade geral do checklist (% de itens conformes)
     */
    protected function calculateConformity($checklist): int
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

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            Actions\ActionGroup::make([
                // Exportar com formato personalizado (SafetyChecklistExport)
                Actions\Action::make('export_standard')
                    ->label('Exportar Formato Padrão')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->modalWidth('2xl')
                    ->modalHeading('Exportar Checklist de Segurança')
                    ->modalDescription('Configure os filtros para exportar o relatório em formato Excel')
                    ->modalSubmitActionLabel('Exportar Excel')
                    ->modalIcon('heroicon-o-document-arrow-down')
                    ->form([
                        \Filament\Forms\Components\Section::make('Filtros de Exportação')
                            ->description('Selecione a unidade e o período para exportar os checklists')
                            ->icon('heroicon-o-funnel')
                            ->schema([
                                \Filament\Forms\Components\Select::make('unit_id')
                                    ->label('Unidade')
                                    ->options(function () {
                                        // Apenas unidades que possuem checklists
                                        return \App\Models\Unit::whereHas('machines.safetyChecklists')
                                            ->pluck('name', 'id');
                                    })
                                    ->searchable()
                                    ->required()
                                    ->placeholder('Selecione uma unidade')
                                    ->helperText('Apenas unidades com checklists cadastrados')
                                    ->columnSpanFull()
                                    ->live()
                                    ->afterStateUpdated(function (\Filament\Forms\Set $set, $state) {
                                        if (!$state) {
                                            return;
                                        }

                                        // Auto-selecionar ano atual se houver dados
                                        $currentYear = now()->year;
                                        $hasCurrentYear = \App\Models\SafetyChecklist::query()
                                            ->whereHas('machine', function ($query) use ($state) {
                                                $query->where('unit_id', $state);
                                            })
                                            ->whereYear('session_date', $currentYear)
                                            ->exists();

                                        if ($hasCurrentYear) {
                                            $set('year', $currentYear);

                                            // Auto-selecionar mês atual se houver dados
                                            $currentMonth = now()->format('m');
                                            $hasCurrentMonth = \App\Models\SafetyChecklist::query()
                                                ->whereHas('machine', function ($query) use ($state) {
                                                    $query->where('unit_id', $state);
                                                })
                                                ->whereYear('session_date', $currentYear)
                                                ->whereMonth('session_date', $currentMonth)
                                                ->exists();

                                            if ($hasCurrentMonth) {
                                                $set('month', $currentMonth);
                                            } else {
                                                // Se não tiver dados no mês atual, selecionar o mês mais recente
                                                $latestMonth = \App\Models\SafetyChecklist::query()
                                                    ->whereHas('machine', function ($query) use ($state) {
                                                        $query->where('unit_id', $state);
                                                    })
                                                    ->whereYear('session_date', $currentYear)
                                                    ->selectRaw('MONTH(session_date) as month')
                                                    ->orderBy('session_date', 'desc')
                                                    ->first();

                                                if ($latestMonth) {
                                                    $set('month', str_pad($latestMonth->month, 2, '0', STR_PAD_LEFT));
                                                }
                                            }
                                        } else {
                                            // Se não tiver dados no ano atual, pegar o ano mais recente
                                            $latestYear = \App\Models\SafetyChecklist::query()
                                                ->whereHas('machine', function ($query) use ($state) {
                                                    $query->where('unit_id', $state);
                                                })
                                                ->selectRaw('YEAR(session_date) as year')
                                                ->orderBy('session_date', 'desc')
                                                ->first();

                                            if ($latestYear) {
                                                $set('year', $latestYear->year);

                                                // Selecionar o mês mais recente desse ano
                                                $latestMonth = \App\Models\SafetyChecklist::query()
                                                    ->whereHas('machine', function ($query) use ($state) {
                                                        $query->where('unit_id', $state);
                                                    })
                                                    ->whereYear('session_date', $latestYear->year)
                                                    ->selectRaw('MONTH(session_date) as month')
                                                    ->orderBy('session_date', 'desc')
                                                    ->first();

                                                if ($latestMonth) {
                                                    $set('month', str_pad($latestMonth->month, 2, '0', STR_PAD_LEFT));
                                                }
                                            }
                                        }
                                    }),
                            ]),

                        \Filament\Forms\Components\Section::make('Período de Exportação')
                            ->description('Escolha entre selecionar um mês completo ou definir datas específicas')
                            ->icon('heroicon-o-calendar')
                            ->schema([
                                \Filament\Forms\Components\Toggle::make('use_custom_dates')
                                    ->label('Usar período personalizado')
                                    ->helperText('Ative para definir datas específicas de início e fim')
                                    ->live()
                                    ->default(false)
                                    ->inline(false)
                                    ->columnSpanFull(),

                                // Seleção por mês/ano (padrão)
                                \Filament\Forms\Components\Grid::make(2)
                                    ->schema([
                                        \Filament\Forms\Components\Select::make('year')
                                            ->label('Ano')
                                            ->options(function (\Filament\Forms\Get $get) {
                                                $unitId = $get('unit_id');

                                                if (!$unitId) {
                                                    return [];
                                                }

                                                // Buscar anos que possuem checklists para esta unidade
                                                $years = \App\Models\SafetyChecklist::query()
                                                    ->whereHas('machine', function ($query) use ($unitId) {
                                                        $query->where('unit_id', $unitId);
                                                    })
                                                    ->selectRaw('DISTINCT YEAR(session_date) as year')
                                                    ->orderBy('year', 'desc')
                                                    ->pluck('year', 'year');

                                                return $years->isEmpty() ? [now()->year => now()->year] : $years;
                                            })
                                            ->default(now()->year)
                                            ->required()
                                            ->native(false)
                                            ->disabled(fn (\Filament\Forms\Get $get) => !$get('unit_id'))
                                            ->helperText(fn (\Filament\Forms\Get $get) =>
                                                !$get('unit_id') ? 'Selecione uma unidade primeiro' : 'Anos com dados disponíveis'
                                            )
                                            ->live()
                                            ->afterStateUpdated(function (\Filament\Forms\Set $set) {
                                                // Limpar o mês quando o ano mudar
                                                $set('month', null);
                                            }),

                                        \Filament\Forms\Components\Select::make('month')
                                            ->label('Mês')
                                            ->options(function (\Filament\Forms\Get $get) {
                                                $unitId = $get('unit_id');
                                                $year = $get('year');

                                                $allMonths = [
                                                    '01' => 'Janeiro',
                                                    '02' => 'Fevereiro',
                                                    '03' => 'Março',
                                                    '04' => 'Abril',
                                                    '05' => 'Maio',
                                                    '06' => 'Junho',
                                                    '07' => 'Julho',
                                                    '08' => 'Agosto',
                                                    '09' => 'Setembro',
                                                    '10' => 'Outubro',
                                                    '11' => 'Novembro',
                                                    '12' => 'Dezembro',
                                                ];

                                                if (!$unitId || !$year) {
                                                    return [];
                                                }

                                                // Buscar meses que possuem checklists para esta unidade/ano
                                                $availableMonths = \App\Models\SafetyChecklist::query()
                                                    ->whereHas('machine', function ($query) use ($unitId) {
                                                        $query->where('unit_id', $unitId);
                                                    })
                                                    ->whereYear('session_date', $year)
                                                    ->selectRaw('DISTINCT MONTH(session_date) as month')
                                                    ->orderBy('month')
                                                    ->pluck('month')
                                                    ->map(fn ($m) => str_pad($m, 2, '0', STR_PAD_LEFT))
                                                    ->toArray();

                                                if (empty($availableMonths)) {
                                                    return [];
                                                }

                                                // Retornar apenas os meses disponíveis
                                                return collect($allMonths)
                                                    ->filter(fn ($name, $key) => in_array($key, $availableMonths))
                                                    ->toArray();
                                            })
                                            ->required()
                                            ->placeholder('Selecione o mês')
                                            ->helperText(fn (\Filament\Forms\Get $get) =>
                                                !$get('unit_id') || !$get('year')
                                                    ? 'Selecione unidade e ano primeiro'
                                                    : 'Apenas meses com dados (dia 1 ao 31)'
                                            )
                                            ->disabled(fn (\Filament\Forms\Get $get) => !$get('unit_id') || !$get('year'))
                                            ->native(false)
                                            ->live(),
                                    ])
                                    ->hidden(fn (\Filament\Forms\Get $get) => $get('use_custom_dates')),

                                // Seleção por datas personalizadas
                                \Filament\Forms\Components\Grid::make(2)
                                    ->schema([
                                        DatePicker::make('start_date')
                                            ->label('Data Início')
                                            ->default(now()->startOfMonth())
                                            ->required()
                                            ->native(false)
                                            ->displayFormat('d/m/Y')
                                            ->maxDate(fn (\Filament\Forms\Get $get) => $get('end_date')),

                                        DatePicker::make('end_date')
                                            ->label('Data Fim')
                                            ->default(now()->endOfMonth())
                                            ->required()
                                            ->native(false)
                                            ->displayFormat('d/m/Y')
                                            ->minDate(fn (\Filament\Forms\Get $get) => $get('start_date')),
                                    ])
                                    ->visible(fn (\Filament\Forms\Get $get) => $get('use_custom_dates')),
                            ]),

                        \Filament\Forms\Components\Placeholder::make('info')
                            ->label('')
                            ->content(function (\Filament\Forms\Get $get) {
                                $unitId = $get('unit_id');

                                if (!$unitId) {
                                    return '⚠️ Selecione uma unidade para ver os dados disponíveis';
                                }

                                // Calcular período
                                if ($get('use_custom_dates')) {
                                    $startDate = $get('start_date');
                                    $endDate = $get('end_date');

                                    if (!$startDate || !$endDate) {
                                        return '📊 Exportará checklists do período selecionado';
                                    }
                                } else {
                                    $year = $get('year');
                                    $month = $get('month');

                                    if (!$year || !$month) {
                                        return '📅 Selecione ano e mês para continuar';
                                    }

                                    $startDate = "{$year}-{$month}-01";
                                    $endDate = "{$year}-{$month}-31";
                                }

                                // Contar registros disponíveis
                                $count = \App\Models\SafetyChecklist::query()
                                    ->whereHas('machine', function ($query) use ($unitId) {
                                        $query->where('unit_id', $unitId);
                                    })
                                    ->whereBetween('session_date', [$startDate, $endDate])
                                    ->count();

                                if ($count === 0) {
                                    return '❌ Nenhum checklist encontrado para este período';
                                }

                                $icon = $get('use_custom_dates') ? '📊' : '📅';
                                return "{$icon} {$count} " . ($count === 1 ? 'checklist disponível' : 'checklists disponíveis') . ' para exportação';
                            })
                            ->columnSpanFull(),
                    ])
                    ->action(function (array $data) {
                        // Determinar o período de datas
                        if ($data['use_custom_dates']) {
                            $startDate = $data['start_date'];
                            $endDate = $data['end_date'];
                        } else {
                            // Criar período do mês selecionado (dia 1 ao 31)
                            $year = $data['year'];
                            $month = $data['month'];
                            $startDate = "{$year}-{$month}-01";
                            $endDate = "{$year}-{$month}-31";
                        }

                        // Buscar checklists filtrados por unidade e período
                        $checklists = \App\Models\SafetyChecklist::query()
                            ->with(['patient', 'machine', 'user'])
                            ->whereHas('machine', function ($query) use ($data) {
                                $query->where('unit_id', $data['unit_id']);
                            })
                            ->whereBetween('session_date', [$startDate, $endDate])
                            ->orderBy('session_date')
                            ->get();

                        if ($checklists->isEmpty()) {
                            \Filament\Notifications\Notification::make()
                                ->warning()
                                ->title('Nenhum registro encontrado')
                                ->body('Não há checklists para o período e unidade selecionados.')
                                ->send();
                            return;
                        }

                        // Carregar template e preencher
                        $templatePath = storage_path('app/templates/safety-checklist-template.xlsx');

                        if (!file_exists($templatePath)) {
                            throw new \Exception('Template não encontrado em: ' . $templatePath);
                        }

                        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
                        $spreadsheet = $reader->load($templatePath);
                        $sheet = $spreadsheet->getActiveSheet();

                        // Preencher dados no template
                        $export = new SafetyChecklistExport($checklists);
                        $export->fillTemplatePublic($sheet);

                        // Salvar em arquivo temporário
                        $tempFile = tempnam(sys_get_temp_dir(), 'checklist_');
                        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                        $writer->save($tempFile);

                        $unitName = \App\Models\Unit::find($data['unit_id'])->name ?? 'unidade';
                        $filename = 'checklist-seguranca-' . \Illuminate\Support\Str::slug($unitName) . '-' . now()->format('Y-m-d-His') . '.xlsx';

                        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
                    }),

                // Exportar formato gerencial
                Actions\Action::make('export_management')
                    ->label('Exportar Formato Gerencial')
                    ->icon('heroicon-o-chart-bar')
                    ->color('info')
                    ->modalWidth('2xl')
                    ->modalHeading('Exportar Relatório Gerencial')
                    ->modalDescription('Relatório resumido com estatísticas e indicadores para gestão')
                    ->modalSubmitActionLabel('Exportar Relatório')
                    ->modalIcon('heroicon-o-chart-bar')
                    ->form([
                        \Filament\Forms\Components\Section::make('Filtros do Relatório')
                            ->description('Configure os filtros para gerar o relatório gerencial')
                            ->icon('heroicon-o-funnel')
                            ->schema([
                                \Filament\Forms\Components\Select::make('unit_id')
                                    ->label('Unidade')
                                    ->options(\App\Models\Unit::whereHas('machines.safetyChecklists')->pluck('name', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->placeholder('Selecione uma unidade')
                                    ->columnSpanFull()
                                    ->live()
                                    ->afterStateUpdated(function (\Filament\Forms\Set $set, $state) {
                                        if (!$state) return;
                                        $currentYear = now()->year;
                                        $currentMonth = now()->format('m');
                                        $hasData = \App\Models\SafetyChecklist::query()
                                            ->whereHas('machine', fn($q) => $q->where('unit_id', $state))
                                            ->whereYear('session_date', $currentYear)
                                            ->whereMonth('session_date', $currentMonth)
                                            ->exists();
                                        if ($hasData) {
                                            $set('year', $currentYear);
                                            $set('month', $currentMonth);
                                        }
                                    }),

                                \Filament\Forms\Components\Grid::make(2)
                                    ->schema([
                                        \Filament\Forms\Components\Select::make('year')
                                            ->label('Ano')
                                            ->options(function (\Filament\Forms\Get $get) {
                                                $unitId = $get('unit_id');
                                                if (!$unitId) return [];
                                                return \App\Models\SafetyChecklist::query()
                                                    ->whereHas('machine', fn($q) => $q->where('unit_id', $unitId))
                                                    ->selectRaw('DISTINCT YEAR(session_date) as year')
                                                    ->orderBy('year', 'desc')
                                                    ->pluck('year', 'year');
                                            })
                                            ->required()
                                            ->disabled(fn (\Filament\Forms\Get $get) => !$get('unit_id'))
                                            ->live(),

                                        \Filament\Forms\Components\Select::make('month')
                                            ->label('Mês')
                                            ->options(function (\Filament\Forms\Get $get) {
                                                $unitId = $get('unit_id');
                                                $year = $get('year');
                                                if (!$unitId || !$year) return [];

                                                $allMonths = [
                                                    '01' => 'Janeiro', '02' => 'Fevereiro', '03' => 'Março',
                                                    '04' => 'Abril', '05' => 'Maio', '06' => 'Junho',
                                                    '07' => 'Julho', '08' => 'Agosto', '09' => 'Setembro',
                                                    '10' => 'Outubro', '11' => 'Novembro', '12' => 'Dezembro',
                                                ];

                                                $availableMonths = \App\Models\SafetyChecklist::query()
                                                    ->whereHas('machine', fn($q) => $q->where('unit_id', $unitId))
                                                    ->whereYear('session_date', $year)
                                                    ->selectRaw('DISTINCT MONTH(session_date) as month')
                                                    ->pluck('month')
                                                    ->map(fn($m) => str_pad($m, 2, '0', STR_PAD_LEFT))
                                                    ->toArray();

                                                return collect($allMonths)->filter(fn($n, $k) => in_array($k, $availableMonths))->toArray();
                                            })
                                            ->required()
                                            ->disabled(fn (\Filament\Forms\Get $get) => !$get('unit_id') || !$get('year'))
                                            ->native(false),
                                    ]),
                            ]),
                    ])
                    ->action(function (array $data) {
                        $year = $data['year'];
                        $month = $data['month'];
                        $startDate = "{$year}-{$month}-01";
                        $endDate = "{$year}-{$month}-31";

                        $checklists = \App\Models\SafetyChecklist::query()
                            ->with(['patient', 'machine.unit', 'user'])
                            ->whereHas('machine', fn($q) => $q->where('unit_id', $data['unit_id']))
                            ->whereBetween('session_date', [$startDate, $endDate])
                            ->orderBy('session_date')
                            ->get();

                        if ($checklists->isEmpty()) {
                            \Filament\Notifications\Notification::make()
                                ->warning()
                                ->title('Nenhum registro encontrado')
                                ->body('Não há dados para o período selecionado.')
                                ->send();
                            return;
                        }

                        // Preparar informações do período para o relatório
                        $unit = \App\Models\Unit::find($data['unit_id']);
                        $monthNames = [
                            '01' => 'Janeiro', '02' => 'Fevereiro', '03' => 'Março',
                            '04' => 'Abril', '05' => 'Maio', '06' => 'Junho',
                            '07' => 'Julho', '08' => 'Agosto', '09' => 'Setembro',
                            '10' => 'Outubro', '11' => 'Novembro', '12' => 'Dezembro',
                        ];

                        $periodInfo = [
                            'unit_name' => $unit->name ?? 'Unidade',
                            'month_name' => $monthNames[$month] ?? $month,
                            'year' => $year,
                            'start_date' => $startDate,
                            'end_date' => $endDate,
                        ];

                        // Gerar relatório gerencial usando a classe especializada
                        $export = new ManagementReportExport($checklists, $periodInfo);
                        $spreadsheet = $export->generate();

                        // Salvar em arquivo temporário
                        $tempFile = tempnam(sys_get_temp_dir(), 'management_');
                        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                        $writer->save($tempFile);

                        $unitName = $unit->name ?? 'unidade';
                        $filename = 'relatorio-gerencial-' . \Illuminate\Support\Str::slug($unitName) . '-' . $year . '-' . $month . '.xlsx';

                        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
                    }),

                // Exportar formato simplificado (todos os pacientes)
                Actions\Action::make('export_bulk')
                    ->label('Exportar Todos os Pacientes (Simplificado)')
                    ->icon('heroicon-o-table-cells')
                    ->color('primary')
                    ->modalWidth('2xl')
                    ->modalHeading('Exportar Todos os Pacientes')
                    ->modalDescription('Formato simplificado e escalável para 300+ pacientes')
                    ->modalSubmitActionLabel('Gerar Exportação')
                    ->modalIcon('heroicon-o-table-cells')
                    ->form([
                        \Filament\Forms\Components\Section::make('Filtros do Relatório')
                            ->description('Selecione a unidade e o período para exportar')
                            ->icon('heroicon-o-funnel')
                            ->schema([
                                \Filament\Forms\Components\Select::make('unit_id')
                                    ->label('Unidade')
                                    ->options(\App\Models\Unit::whereHas('machines.safetyChecklists')->pluck('name', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->placeholder('Selecione uma unidade')
                                    ->columnSpanFull()
                                    ->live()
                                    ->afterStateUpdated(function (\Filament\Forms\Set $set, $state) {
                                        if (!$state) return;
                                        $currentYear = now()->year;
                                        $currentMonth = now()->format('m');
                                        $hasData = \App\Models\SafetyChecklist::query()
                                            ->whereHas('machine', fn($q) => $q->where('unit_id', $state))
                                            ->whereYear('session_date', $currentYear)
                                            ->whereMonth('session_date', $currentMonth)
                                            ->exists();
                                        if ($hasData) {
                                            $set('year', $currentYear);
                                            $set('month', $currentMonth);
                                        }
                                    }),

                                \Filament\Forms\Components\Grid::make(2)
                                    ->schema([
                                        \Filament\Forms\Components\Select::make('year')
                                            ->label('Ano')
                                            ->options(function (\Filament\Forms\Get $get) {
                                                $unitId = $get('unit_id');
                                                if (!$unitId) return [];
                                                return \App\Models\SafetyChecklist::query()
                                                    ->whereHas('machine', fn($q) => $q->where('unit_id', $unitId))
                                                    ->selectRaw('DISTINCT YEAR(session_date) as year')
                                                    ->orderBy('year', 'desc')
                                                    ->pluck('year', 'year');
                                            })
                                            ->required()
                                            ->disabled(fn (\Filament\Forms\Get $get) => !$get('unit_id'))
                                            ->live(),

                                        \Filament\Forms\Components\Select::make('month')
                                            ->label('Mês')
                                            ->options(function (\Filament\Forms\Get $get) {
                                                $unitId = $get('unit_id');
                                                $year = $get('year');
                                                if (!$unitId || !$year) return [];

                                                $allMonths = [
                                                    '01' => 'Janeiro', '02' => 'Fevereiro', '03' => 'Março',
                                                    '04' => 'Abril', '05' => 'Maio', '06' => 'Junho',
                                                    '07' => 'Julho', '08' => 'Agosto', '09' => 'Setembro',
                                                    '10' => 'Outubro', '11' => 'Novembro', '12' => 'Dezembro',
                                                ];

                                                $availableMonths = \App\Models\SafetyChecklist::query()
                                                    ->whereHas('machine', fn($q) => $q->where('unit_id', $unitId))
                                                    ->whereYear('session_date', $year)
                                                    ->selectRaw('DISTINCT MONTH(session_date) as month')
                                                    ->pluck('month')
                                                    ->map(fn($m) => str_pad($m, 2, '0', STR_PAD_LEFT))
                                                    ->toArray();

                                                return collect($allMonths)->filter(fn($n, $k) => in_array($k, $availableMonths))->toArray();
                                            })
                                            ->required()
                                            ->disabled(fn (\Filament\Forms\Get $get) => !$get('unit_id') || !$get('year'))
                                            ->native(false),
                                    ]),
                            ]),

                        \Filament\Forms\Components\Placeholder::make('info')
                            ->label('')
                            ->content(function (\Filament\Forms\Get $get) {
                                $unitId = $get('unit_id');
                                $year = $get('year');
                                $month = $get('month');

                                if (!$unitId || !$year || !$month) {
                                    return '⚠️ Selecione unidade, ano e mês para ver o resumo';
                                }

                                $startDate = "{$year}-{$month}-01";
                                $endDate = "{$year}-{$month}-31";

                                $totalChecklists = \App\Models\SafetyChecklist::query()
                                    ->whereHas('machine', fn($q) => $q->where('unit_id', $unitId))
                                    ->whereBetween('session_date', [$startDate, $endDate])
                                    ->count();

                                $totalPatients = \App\Models\SafetyChecklist::query()
                                    ->whereHas('machine', fn($q) => $q->where('unit_id', $unitId))
                                    ->whereBetween('session_date', [$startDate, $endDate])
                                    ->distinct('patient_id')
                                    ->count('patient_id');

                                if ($totalChecklists === 0) {
                                    return '❌ Nenhum dado encontrado para este período';
                                }

                                $estimatedSize = round(($totalChecklists * 0.001) + 2, 1); // Estimativa: ~1KB por checklist + 2MB base
                                $estimatedTime = round($totalChecklists / 100); // Estimativa: ~100 checklists por segundo

                                return sprintf(
                                    '📊 **%d pacientes** | **%d checklists** | Tamanho estimado: **~%s MB** | Tempo: **~%d segundos**',
                                    $totalPatients,
                                    $totalChecklists,
                                    $estimatedSize,
                                    max(10, $estimatedTime)
                                );
                            })
                            ->columnSpanFull(),
                    ])
                    ->action(function (array $data) {
                        $year = $data['year'];
                        $month = $data['month'];
                        $startDate = "{$year}-{$month}-01";
                        $endDate = "{$year}-{$month}-31";

                        // Buscar todos os checklists do período
                        $checklists = \App\Models\SafetyChecklist::query()
                            ->with(['patient', 'machine.unit', 'user'])
                            ->whereHas('machine', fn($q) => $q->where('unit_id', $data['unit_id']))
                            ->whereBetween('session_date', [$startDate, $endDate])
                            ->orderBy('patient_id')
                            ->orderBy('session_date')
                            ->get();

                        if ($checklists->isEmpty()) {
                            \Filament\Notifications\Notification::make()
                                ->warning()
                                ->title('Nenhum registro encontrado')
                                ->body('Não há dados para o período selecionado.')
                                ->send();
                            return;
                        }

                        // Preparar informações do período
                        $unit = \App\Models\Unit::find($data['unit_id']);
                        $monthNames = [
                            '01' => 'Janeiro', '02' => 'Fevereiro', '03' => 'Março',
                            '04' => 'Abril', '05' => 'Maio', '06' => 'Junho',
                            '07' => 'Julho', '08' => 'Agosto', '09' => 'Setembro',
                            '10' => 'Outubro', '11' => 'Novembro', '12' => 'Dezembro',
                        ];

                        $periodInfo = [
                            'unit_name' => $unit->name ?? 'Unidade',
                            'month_name' => $monthNames[$month] ?? $month,
                            'year' => $year,
                            'start_date' => $startDate,
                            'end_date' => $endDate,
                        ];

                        // Gerar exportação usando a nova classe
                        $export = new BulkSafetyChecklistExport($checklists, $periodInfo);
                        $spreadsheet = $export->generate();

                        // Salvar em arquivo temporário
                        $tempFile = tempnam(sys_get_temp_dir(), 'bulk_checklist_');
                        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                        $writer->save($tempFile);

                        $unitName = $unit->name ?? 'unidade';
                        $filename = 'checklists-simplificado-' . \Illuminate\Support\Str::slug($unitName) . '-' . $year . '-' . $month . '.xlsx';

                        // Notificar sucesso
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Exportação concluída!')
                            ->body(sprintf('Exportados %d pacientes com %d checklists',
                                $checklists->unique('patient_id')->count(),
                                $checklists->count()
                            ))
                            ->send();

                        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
                    }),
            ])
                ->label('Exportar Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->button(),
        ];
    }
}
