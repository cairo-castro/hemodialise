<?php

namespace App\Filament\Resources\CleaningControlResource\Pages;

use App\Filament\Resources\CleaningControlResource;
use App\Exports\CleaningControlExport;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListCleaningControls extends ListRecords
{
    protected static string $resource = CleaningControlResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            Actions\Action::make('export_excel')
                ->label('Exportar Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->form([
                    DatePicker::make('start_date')
                        ->label('Data Início')
                        ->default(now()->startOfMonth())
                        ->required(),
                    DatePicker::make('end_date')
                        ->label('Data Fim')
                        ->default(now()->endOfMonth())
                        ->required(),
                ])
                ->action(function (array $data) {
                    $records = \App\Models\CleaningControl::query()
                        ->with(['machine', 'user'])
                        ->whereBetween('cleaning_date', [$data['start_date'], $data['end_date']])
                        ->orderBy('cleaning_date')
                        ->get();

                    // Carregar template e preencher
                    $templatePath = storage_path('app/templates/cleaning-control-template.xlsx');

                    if (!file_exists($templatePath)) {
                        throw new \Exception('Template não encontrado em: ' . $templatePath);
                    }

                    $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
                    $spreadsheet = $reader->load($templatePath);
                    $sheet = $spreadsheet->getActiveSheet();

                    // Preencher dados no template
                    $export = new CleaningControlExport($records);
                    $export->fillTemplatePublic($sheet);

                    // Salvar em arquivo temporário
                    $tempFile = tempnam(sys_get_temp_dir(), 'cleaning_');
                    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                    $writer->save($tempFile);

                    $filename = 'controle-limpeza-' . now()->format('Y-m-d-His') . '.xlsx';

                    return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
                }),
        ];
    }
}
