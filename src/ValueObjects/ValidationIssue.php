<?php

namespace FlussiH\Validator\ValueObjects;

use JsonSerializable;

final class ValidationIssue implements JsonSerializable
{
    public $file;
    public $line;
    public $code;
    public $message;
    public $field;
    public $position;
    public $severity;

    public function __construct(
        string $file,
        int $line,
        string $code,
        string $message,
        ?string $field = null,
        ?int $position = null,
        string $severity = 'error'
    ) {
        $this->file = $file;
        $this->line = $line;
        $this->code = $code;
        $this->message = $message;
        $this->field = $field;
        $this->position = $position;
        $this->severity = $severity;
    }

    /** @return array<string, int|string|null> */
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
