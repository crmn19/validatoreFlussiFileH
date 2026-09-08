<?php

namespace FlussiH\Validator\ValueObjects;

use Countable;
use JsonSerializable;

final class ValidationResult implements Countable, JsonSerializable
{
    private $issues;
    private $h1Records;
    private $h2Records;

    /** @param list<ValidationIssue> $issues */
    public function __construct(
        array $issues = [],
        int $h1Records = 0,
        int $h2Records = 0
    ) {
        $this->issues = $issues;
        $this->h1Records = $h1Records;
        $this->h2Records = $h2Records;
    }

    public function add(ValidationIssue $issue): void
    {
        $this->issues[] = $issue;
    }

    /** @return list<ValidationIssue> */
    public function issues(): array
    {
        return $this->issues;
    }

    public function isValid(): bool
    {
        return ! array_filter($this->issues, static function (ValidationIssue $i): bool { return $i->severity === 'error'; });
    }

    public function count(): int
    {
        return count($this->issues);
    }

    /** @return array{h1: int, h2: int} */
    public function recordCounts(): array
    {
        return ['h1' => $this->h1Records, 'h2' => $this->h2Records];
    }

    public function errorCount(): int
    {
        return count(array_filter($this->issues, static function (ValidationIssue $i): bool { return $i->severity === 'error'; }));
    }

    public function warningCount(): int
    {
        return count(array_filter($this->issues, static function (ValidationIssue $i): bool { return $i->severity === 'warning'; }));
    }

    public function setRecordCounts(int $h1, int $h2): void
    {
        $this->h1Records = $h1;
        $this->h2Records = $h2;
    }

    /** @return array{valid: bool, records: array{h1: int, h2: int}, issues: list<ValidationIssue>} */
    public function jsonSerialize(): array
    {
        return [
            'valid' => $this->isValid(),
            'records' => ['h1' => $this->h1Records, 'h2' => $this->h2Records],
            'issues' => $this->issues,
        ];
    }
}
