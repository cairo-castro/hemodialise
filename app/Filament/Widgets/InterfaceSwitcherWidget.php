<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class InterfaceSwitcherWidget extends Widget
{
    protected static string $view = 'filament.widgets.interface-switcher-widget';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = -10; // High priority to show at top

    public static function canView(): bool
    {
        $user = auth()->user();

        // Só mostrar para usuários que podem alternar interfaces
        return $user && in_array($user->role, ['admin', 'gestor', 'coordenador', 'supervisor']);
    }

    public function getViewData(): array
    {
        $user = auth()->user();

        return [
            'currentInterface' => 'admin',
            'userRole' => $user->role,
            'availableInterfaces' => $this->getAvailableInterfaces($user->role),
            'userName' => $user->name,
        ];
    }

    private function getAvailableInterfaces(string $role): array
    {
        $interfaces = [
            [
                'key' => 'ionic',
                'name' => 'Interface Mobile',
                'description' => 'Otimizada para dispositivos móveis e trabalho de campo',
                'icon' => '📱',
                'color' => 'success',
                'url' => '/mobile'
            ],
            [
                'key' => 'preline',
                'name' => 'Interface Desktop',
                'description' => 'Interface executiva para análise e gestão',
                'icon' => '🖥️',
                'color' => 'primary',
                'url' => '/desktop'
            ]
        ];

        // Only admin, gestor, and coordenador can stay in admin
        if (in_array($role, ['admin', 'gestor', 'coordenador'])) {
            $interfaces[] = [
                'key' => 'admin',
                'name' => 'Painel Administrativo',
                'description' => 'Sistema completo de administração e relatórios',
                'icon' => '⚙️',
                'color' => 'warning',
                'url' => '/admin',
                'current' => true
            ];
        }

        return $interfaces;
    }
}