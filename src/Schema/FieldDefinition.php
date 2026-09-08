<?php

namespace FlussiH\Validator\Schema;

final class FieldDefinition
{
    public $reference;
    public $name;
    public $start;
    public $length;
    public $type;
    public $mode;

    public function __construct(
        string $reference,
        string $name,
        int $start,
        int $length,
        string $type,
        string $mode
    ) {
        $this->reference = $reference;
        $this->name = $name;
        $this->start = $start;
        $this->length = $length;
        $this->type = $type;
        $this->mode = $mode;
    }

    public function end(): int
    {
        return $this->start + $this->length - 1;
    }
}
