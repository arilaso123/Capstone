<?php

namespace App\Support;

final class IndonesianPhone
{
    public static function normalize(string $phone): string
    {
        $phone = preg_replace('/[\s().-]+/', '', trim($phone)) ?? '';
        if (str_starts_with($phone, '+62')) {
            return '0' . substr($phone, 3);
        }
        if (str_starts_with($phone, '62')) {
            return '0' . substr($phone, 2);
        }
        return $phone;
    }

    public static function isValid(string $phone): bool
    {
        return preg_match('/^08[1-9][0-9]{7,10}$/', self::normalize($phone)) === 1;
    }
}
