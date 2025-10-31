<?php

namespace App\Enums;

enum PatientStatus: string
{
    case ATIVO = 'ativo';
    case INATIVO = 'inativo';
    case TRANSFERIDO = 'transferido';
    case ALTA = 'alta';
    case OBITO = 'obito';

    /**
     * Get all status values
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get status label in Portuguese
     */
    public function label(): string
    {
        return match($this) {
            self::ATIVO => 'Ativo',
            self::INATIVO => 'Inativo',
            self::TRANSFERIDO => 'Transferido',
            self::ALTA => 'Alta Médica',
            self::OBITO => 'Óbito',
        };
    }

    /**
     * Get status color for UI
     */
    public function color(): string
    {
        return match($this) {
            self::ATIVO => 'success',     // Verde
            self::INATIVO => 'warning',   // Amarelo
            self::TRANSFERIDO => 'info',  // Azul
            self::ALTA => 'primary',      // Azul escuro
            self::OBITO => 'danger',      // Vermelho
        };
    }

    /**
     * Get status icon
     */
    public function icon(): string
    {
        return match($this) {
            self::ATIVO => 'checkmark-circle',
            self::INATIVO => 'pause-circle',
            self::TRANSFERIDO => 'swap-horizontal',
            self::ALTA => 'arrow-up-circle',
            self::OBITO => 'close-circle',
        };
    }

    /**
     * Check if patient can have new sessions
     */
    public function canHaveSessions(): bool
    {
        return in_array($this, [self::ATIVO]);
    }

    /**
     * Check if status is terminal (final state)
     */
    public function isTerminal(): bool
    {
        return in_array($this, [self::ALTA, self::OBITO]);
    }
}
