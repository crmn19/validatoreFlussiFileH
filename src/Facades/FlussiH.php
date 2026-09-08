<?php

namespace FlussiH\Validator\Facades;

use Illuminate\Support\Facades\Facade;

/** @method static \FlussiH\Validator\ValueObjects\ValidationResult validate(string $h1Path, string $h2Path) */
/** @method static \FlussiH\Validator\ValueObjects\ValidationResult validateContents(string $h1, string $h2) */
final class FlussiH extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'flussi-h-validator';
    }
}
