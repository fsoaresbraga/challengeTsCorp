<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class BrazilianDocument implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('The :attribute must be a valid CPF or CNPJ.');

            return;
        }

        $normalized = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $value) ?? '');

        if (strlen($normalized) === 11 && ctype_digit($normalized) && $this->isValidCpf($normalized)) {
            return;
        }

        if (strlen($normalized) === 14 && $this->isValidCnpj($normalized)) {
            return;
        }

        $fail('The :attribute must be a valid CPF or CNPJ.');
    }

    private function isValidCpf(string $cpf): bool
    {
        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            $sum = 0;

            for ($i = 0; $i < $t; $i++) {
                $sum += (int) $cpf[$i] * (($t + 1) - $i);
            }

            $digit = ((10 * $sum) % 11) % 10;

            if ((int) $cpf[$t] !== $digit) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validates CNPJ in numeric (legacy) or alphanumeric format (RFB IN 2.229/2024).
     * Positions 1-12: [0-9A-Z], positions 13-14: numeric check digits.
     * DV calculated via modulo 11 using ord(char) - 48 for each base character.
     */
    private function isValidCnpj(string $cnpj): bool
    {
        if (strlen($cnpj) !== 14) {
            return false;
        }

        $base = substr($cnpj, 0, 12);
        $checkDigits = substr($cnpj, 12, 2);

        if (! preg_match('/^[0-9A-Z]{12}$/', $base)) {
            return false;
        }

        if (! preg_match('/^\d{2}$/', $checkDigits)) {
            return false;
        }

        if (preg_match('/^(.)\1{11}$/', $base)) {
            return false;
        }

        $weightsFirst = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $weightsSecond = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

        $firstDigit = $this->calculateCnpjCheckDigit($base, $weightsFirst);

        if ((int) $cnpj[12] !== $firstDigit) {
            return false;
        }

        $secondDigit = $this->calculateCnpjCheckDigit($base . (string) $firstDigit, $weightsSecond);

        return (int) $cnpj[13] === $secondDigit;
    }

    /** @param list<int> $weights */
    private function calculateCnpjCheckDigit(string $value, array $weights): int
    {
        $sum = 0;

        for ($i = 0, $length = strlen($value); $i < $length; $i++) {
            $sum += (ord($value[$i]) - 48) * $weights[$i];
        }

        $remainder = $sum % 11;

        return $remainder < 2 ? 0 : 11 - $remainder;
    }
}
