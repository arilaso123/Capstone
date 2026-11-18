<?php

namespace App\Support;

final class Money
{
    /** Compare two monetary values using exact integer cents (rupiah x 100). */
    public static function matches(string|int|float $left, string|int|float $right): bool
    {
        return (int) round((float) $left * 100) === (int) round((float) $right * 100);
    }
}

