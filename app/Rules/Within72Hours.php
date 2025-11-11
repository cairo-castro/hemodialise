<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Carbon\Carbon;

class Within72Hours implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            $date = Carbon::parse($value);
            $now = Carbon::now();
            $minDate = $now->copy()->subHours(72);

            // Check if date is within the last 72 hours
            if ($date->lt($minDate)) {
                $fail('A data não pode ser anterior a 72 horas (3 dias) atrás.');
                return;
            }

            // Check if date is not in the future
            if ($date->gt($now)) {
                $fail('A data não pode ser no futuro.');
                return;
            }
        } catch (\Exception $e) {
            $fail('A data fornecida é inválida.');
        }
    }
}
