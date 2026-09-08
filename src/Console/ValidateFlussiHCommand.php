<?php

namespace FlussiH\Validator\Console;

use FlussiH\Validator\FlussiHValidator;
use Illuminate\Console\Command;

final class ValidateFlussiHCommand extends Command
{
    protected $signature = 'flussi-h:validate
        {h1 : Percorso del file H1/ANA}
        {h2 : Percorso del file H2/SAN}
        {--json : Restituisce il report in JSON}';

    protected $description = 'Valida una coppia di file H1 (ANA) e H2 (SAN)';

    public function handle(FlussiHValidator $validator): int
    {
        try {
            $result = $validator->validate((string) $this->argument('h1'), (string) $this->argument('h2'));
        } catch (\InvalidArgumentException $exception) {
            $this->error($exception->getMessage());
            return self::FAILURE;
        }

        if ($this->option('json')) {
            $this->line((string) json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        } else {
            if ($result->isValid()) {
                $this->info('File H1 e H2 validi.');
            } else {
                $rows = array_map(static function ($issue): array {
                    return [
                        $issue->file,
                        $issue->line,
                        $issue->severity,
                        $issue->field ?? '-',
                        $issue->position ?? '-',
                        $issue->code,
                        $issue->message,
                    ];
                }, $result->issues());
                $this->table(['File', 'Riga', 'Severità', 'Campo', 'Pos.', 'Codice', 'Messaggio'], $rows);
                $this->error($result->count().' anomalie rilevate.');
            }
        }

        return $result->isValid() ? self::SUCCESS : self::FAILURE;
    }
}
