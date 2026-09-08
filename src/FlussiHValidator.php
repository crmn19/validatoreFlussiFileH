<?php

namespace FlussiH\Validator;

use DateTimeImmutable;
use FlussiH\Validator\Schema\FieldDefinition;
use FlussiH\Validator\Schema\H1Schema;
use FlussiH\Validator\Schema\H2Schema;
use FlussiH\Validator\Support\ItalianFiscalCode;
use FlussiH\Validator\ValueObjects\ValidationIssue;
use FlussiH\Validator\ValueObjects\ValidationResult;
use InvalidArgumentException;

final class FlussiHValidator
{
    /** @var array<string, mixed> */
    private $config;

    /** @param array<string, mixed> $config */
    public function __construct(array $config = [])
    {
        $defaults = require dirname(__DIR__).'/config/flussi-h.php';
        $this->config = array_replace($defaults, $config);
        $this->config['reference_tables'] = array_replace(
            $defaults['reference_tables'],
            $config['reference_tables'] ?? []
        );
        if (! in_array($this->config['submission_level'], ['provider', 'company', 'region'], true)) {
            throw new InvalidArgumentException('submission_level deve essere provider, company o region.');
        }
    }

    public function validate(string $h1Path, string $h2Path): ValidationResult
    {
        return $this->validateLines($this->linesFromFile($h1Path), $this->linesFromFile($h2Path));
    }

    public function validateContents(string $h1, string $h2): ValidationResult
    {
        return $this->validateLines($this->linesFromString($h1), $this->linesFromString($h2));
    }

    /**
     * @param iterable<int, string> $h1Lines
     * @param iterable<int, string> $h2Lines
     */
    public function validateLines(iterable $h1Lines, iterable $h2Lines): ValidationResult
    {
        $result = new ValidationResult();
        $h1 = $this->parseFile('H1', $h1Lines, H1Schema::LENGTH, H1Schema::fields(), $result);
        $h2 = $this->parseFile('H2', $h2Lines, H2Schema::LENGTH, H2Schema::fields(), $result);
        $result->setRecordCounts(count($h1), count($h2));

        $this->validateSequence('H1', $h1, $result);
        $this->validateSequence('H2', $h2, $result);
        $this->validateGroupConsistency('H2', $h2, [
            'numero_registro', 'data_prescrizione', 'data_inizio_ciclo',
            'data_fine_ciclo', 'data_inizio_periodo', 'data_fine_periodo',
        ], $result);
        $this->validatePairs($h1, $h2, $result);
        $this->validateTotals($h2, $result);

        return $result;
    }

    /** @return \Generator<int, string> */
    private function linesFromFile(string $path): \Generator
    {
        $handle = @fopen($path, 'rb');
        if ($handle === false) {
            throw new InvalidArgumentException("File non leggibile: {$path}");
        }

        try {
            while (($line = fgets($handle)) !== false) {
                yield rtrim($line, "\r\n");
            }
        } finally {
            fclose($handle);
        }
    }

    /** @return list<string> */
    private function linesFromString(string $contents): array
    {
        if ($contents === '') {
            return [];
        }

        return preg_split('/\r\n|\n|\r/', rtrim($contents, "\r\n")) ?: [];
    }

    /**
     * @param iterable<int, string> $lines
     * @param list<FieldDefinition> $fields
     * @return list<array<string, string|int>>
     */
    private function parseFile(string $file, iterable $lines, int $expectedLength, array $fields, ValidationResult $result): array
    {
        $records = [];
        $seenContent = false;

        foreach ($lines as $index => $raw) {
            $line = is_int($index) ? $index + 1 : count($records) + 1;
            if ($line === 1 && substr($raw, 0, 3) === "\xEF\xBB\xBF") {
                $raw = substr($raw, 3);
            }
            if ($raw === '') {
                $result->add(new ValidationIssue($file, $line, 'EMPTY_LINE', 'La riga è vuota.'));
                continue;
            }
            $seenContent = true;
            if (strlen($raw) !== $expectedLength) {
                $result->add(new ValidationIssue(
                    $file,
                    $line,
                    'RECORD_LENGTH',
                    "Lunghezza record non valida: attesi {$expectedLength} byte, trovati ".strlen($raw).'.'
                ));
                continue;
            }

            $record = ['__line' => $line, '__raw' => $raw];
            foreach ($fields as $field) {
                $value = substr($raw, $field->start - 1, $field->length);
                $record[$field->name] = $value;
                $this->validateField($file, $line, $field, $value, $record, $result);
            }
            $this->validateRecord($file, $record, $fields, $result);
            $records[] = $record;
        }

        if (! $seenContent) {
            $result->add(new ValidationIssue($file, 0, 'EMPTY_FILE', 'Il file non contiene record.'));
        }

        return $records;
    }

    /** @param array<string, string|int> $record */
    private function validateField(string $file, int $line, FieldDefinition $field, string $value, array $record, ValidationResult $result): void
    {
        $filler = $field->type === 'N' ? str_repeat('0', $field->length) : str_repeat(' ', $field->length);
        $isFiller = $value === $filler;
        $isBlank = trim($value) === '';
        $required = $this->isRequired($field->mode);
        $mustBeEmpty = $this->mustBeEmpty($field->mode);

        if ($mustBeEmpty && ! $isFiller) {
            $this->fieldIssue($result, $file, $line, 'FIELD_MUST_BE_EMPTY', 'Il campo deve contenere solo il riempitivo previsto.', $field);
            return;
        }
        if ($required && ($field->type === 'AN' ? trim($value) === '' : trim($value) === '')) {
            $this->fieldIssue($result, $file, $line, 'REQUIRED', 'Campo obbligatorio non valorizzato.', $field);
            return;
        }
        if (! $required && ($isFiller || $isBlank)) {
            return;
        }

        $decimalFields = ['importo_compartecipazione', 'importo_totale'];
        if ($field->type === 'N' && ! in_array($field->name, $decimalFields, true) && preg_match('/^\d+$/D', $value) !== 1) {
            $this->fieldIssue($result, $file, $line, 'NUMERIC_FORMAT', 'Il campo deve contenere esclusivamente cifre.', $field);
        }
        if ($field->type === 'AN' && trim($value) !== '' && $value[0] === ' ') {
            $this->fieldIssue($result, $file, $line, 'AN_ALIGNMENT', 'Il campo alfanumerico deve essere allineato a sinistra e completato con spazi.', $field);
        }
    }

    private function isRequired(string $mode): bool
    {
        $level = $this->config['submission_level'];

        return $mode === 'OBB'
            || ($mode === 'OBA' && in_array($level, ['company', 'region'], true))
            || ($mode === 'OBR' && $level === 'region')
            || ($mode === 'OBRA' && in_array($level, ['company', 'region'], true));
    }

    private function mustBeEmpty(string $mode): bool
    {
        return $mode === 'NV';
    }

    /**
     * @param array<string, string|int> $record
     * @param list<FieldDefinition> $fields
     */
    private function validateRecord(string $file, array $record, array $fields, ValidationResult $result): void
    {
        $byName = [];
        foreach ($fields as $field) {
            $byName[$field->name] = $field;
        }
        $line = (int) $record['__line'];
        $value = static function (string $key) use ($record): string { return trim((string) $record[$key]); };

        $this->expect($result, $file, $line, $byName['regione_addebitante'], $value('regione_addebitante') === '150', 'REGE', 'La regione addebitante deve essere 150.');
        $this->inTable($result, $file, $line, $byName['asl_inviante'], $value('asl_inviante'), 'asl', 'ASLE');
        $this->inTable($result, $file, $line, $byName['struttura_erogatrice'], $value('struttura_erogatrice'), 'structures_ria11', 'STRE');
        $this->expect($result, $file, $line, $byName['tipologia_struttura'], in_array($value('tipologia_struttura'), ['1', '2', '3', '4'], true), 'TPSE', 'Tipologia struttura non ammessa.');
        $this->expect($result, $file, $line, $byName['progressivo_riga'], preg_match('/^(0[1-9]|[1-9][0-9])$/', $value('progressivo_riga')) === 1, 'PPRE', 'Progressivo riga non compreso tra 01 e 99.');
        $this->expect($result, $file, $line, $byName['anno'], preg_match('/^(19|20)\d{2}$/', $value('anno')) === 1, 'ANNOE', 'Anno non valido.');
        $this->expect($result, $file, $line, $byName['identificativo_progressivo'], preg_match('/^(?!00000000)\d{8}$/', $value('identificativo_progressivo')) === 1, 'IDPE', 'Progressivo identificativo non valido.');

        $expectedStructure = $value('struttura_erogatrice').($file === 'H1' && $value('stabilimento') !== '' ? $value('stabilimento') : '00');
        $this->expect($result, $file, $line, $byName['identificativo_struttura'], $value('identificativo_struttura') === $expectedStructure, 'STRI', 'La struttura dell’identificativo non coincide con la struttura erogatrice.');

        if ($file === 'H1') {
            $this->validateH1Record($record, $byName, $result);
        } else {
            $this->validateH2Record($record, $byName, $result);
        }
    }

    /** @param array<string, string|int> $r @param array<string, FieldDefinition> $f */
    private function validateH1Record(array $r, array $f, ValidationResult $result): void
    {
        $line = (int) $r['__line'];
        $v = static function (string $key) use ($r): string { return trim((string) $r[$key]); };
        $isClosing = $v('progressivo_riga') === '99';
        foreach (['cognome_assistito' => 'COGNE', 'nome_assistito' => 'NOMEE'] as $field => $code) {
            $this->expect($result, 'H1', $line, $f[$field], preg_match("/^[A-ZÀ-ÖØ-Ý' -]+$/u", $v($field)) === 1, $code, 'Sono ammessi lettere maiuscole, spazi, apostrofo e trattino.');
        }
        $this->expect($result, 'H1', $line, $f['codice_assistito'], ItalianFiscalCode::isValid($v('codice_assistito')), 'CFAE', 'Codice fiscale, STP o ENI non formalmente valido.');
        if ($v('cf_medico_prescrittore') !== '') {
            $this->expect($result, 'H1', $line, $f['cf_medico_prescrittore'], ItalianFiscalCode::isValid($v('cf_medico_prescrittore')), 'CFME', 'Codice fiscale del medico non formalmente valido.');
        } elseif ($this->config['regional_warnings'] && ! $isClosing) {
            $this->fieldIssue($result, 'H1', $line, 'CFMM', 'Codice fiscale medico prescrittore mancante.', $f['cf_medico_prescrittore']);
        }
        if ($v('codice_medico_prescrittore') === '' && $this->config['regional_warnings'] && ! $isClosing) {
            $this->fieldIssue($result, 'H1', $line, 'CDMM', 'Codice medico prescrittore mancante.', $f['codice_medico_prescrittore']);
        } elseif ($v('codice_medico_prescrittore') !== '') {
            $this->inTable($result, 'H1', $line, $f['codice_medico_prescrittore'], $v('codice_medico_prescrittore'), 'doctors_disc20', 'CDME');
        }
        if ($v('sesso_assistito') !== '0') {
            $this->expect($result, 'H1', $line, $f['sesso_assistito'], in_array($v('sesso_assistito'), ['1', '2'], true), 'SESE', 'Sesso assistito non ammesso.');
            $expectedSex = ItalianFiscalCode::sex($v('codice_assistito'));
            if ($expectedSex !== null) {
                $this->expect($result, 'H1', $line, $f['sesso_assistito'], $v('sesso_assistito') === $expectedSex, 'SESI', 'Sesso assistito incongruente con il codice fiscale.');
            }
        }
        if ($v('data_nascita') !== '00000000') {
            $this->validDate($result, 'H1', $line, $f['data_nascita'], $v('data_nascita'), 'DTNE');
        }
        $this->inTable($result, 'H1', $line, $f['distretto_struttura'], $v('distretto_struttura'), 'districts_disc04', 'DSE');
        $this->inTable($result, 'H1', $line, $f['comune_residenza'], $v('comune_residenza'), 'municipalities_disc02', 'COME');
        $this->inTable($result, 'H1', $line, $f['regione_residenza'], $v('regione_residenza'), 'regions_disc01', 'RRE');
        $this->inTable($result, 'H1', $line, $f['asl_residenza'], $v('asl_residenza'), 'asl', 'ASLRE');
        $expectedDistrict = $this->config['structure_districts'][$v('struttura_erogatrice')] ?? null;
        if ($expectedDistrict !== null) {
            $this->expect($result, 'H1', $line, $f['distretto_struttura'], $v('distretto_struttura') === (string) $expectedDistrict, 'DSI', 'Distretto di appartenenza incongruente con la struttura.');
        }
        $expectedType = $this->config['structure_types'][$v('struttura_erogatrice')] ?? null;
        if ($expectedType !== null) {
            $this->expect($result, 'H1', $line, $f['tipologia_struttura'], $v('tipologia_struttura') === (string) $expectedType, 'TPSIU', 'Tipologia struttura non coerente con quella dichiarata.');
        }
        if ($v('tipologia_medico_prescrittore') !== '') {
            $this->inTable($result, 'H1', $line, $f['tipologia_medico_prescrittore'], $v('tipologia_medico_prescrittore'), 'doctor_types_disc07', 'TME');
        }
    }

    /** @param array<string, string|int> $r @param array<string, FieldDefinition> $f */
    private function validateH2Record(array $r, array $f, ValidationResult $result): void
    {
        $line = (int) $r['__line'];
        $v = static function (string $key) use ($r): string { return trim((string) $r[$key]); };
        $isClosing = $v('progressivo_riga') === '99';
        $type = $v('tipo_assistenza');
        $this->expect($result, 'H2', $line, $f['tipo_assistenza'], in_array($type, ['RIA', 'RSD', 'CTD', 'SME', 'ANZ', 'NNA', 'ABA'], true), 'TAEE', 'Tipo assistenza non ammesso.');

        if ($v('menomazione') !== '') {
            $this->expect($result, 'H2', $line, $f['menomazione'], in_array($type, ['RIA', 'NNA', 'ABA'], true), 'MENI', 'La menomazione non è prevista per questo tipo di assistenza.');
            $this->inTable($result, 'H2', $line, $f['menomazione'], $v('menomazione'), 'impairments_disc15', 'MENE');
        }
        $diagnosis = $v('diagnosi_icd9');
        $this->expect($result, 'H2', $line, $f['diagnosi_icd9'], ! in_array($type, ['RIA', 'SME'], true) || $diagnosis !== '', 'DIAM', 'La diagnosi è obbligatoria per RIA e SME.');
        if ($diagnosis !== '') {
            if (! $isClosing) {
                $this->expect($result, 'H2', $line, $f['diagnosi_icd9'], in_array($type, ['RIA', 'SME'], true), 'ICDI', "Diagnosi {$diagnosis} specificata per assistenza {$type}, diversa da RIA o SME.");
            }
            $this->inTable($result, 'H2', $line, $f['diagnosi_icd9'], $diagnosis, 'icd9cm_2002', 'ICDE');
        }

        $register = $v('numero_registro');
        $this->expect($result, 'H2', $line, $f['numero_registro'], preg_match('/^\d{16}$/', $register) === 1, 'NRRE', 'Numero registro non formalmente valido.');
        if (strlen($register) === 16) {
            $this->expect($result, 'H2', $line, $f['numero_registro'], substr($register, 0, 4) === $v('anno'), 'NRREN', 'Anno del registro incoerente con l’identificativo.');
            $this->expect($result, 'H2', $line, $f['numero_registro'], substr($register, 4, 3) === $v('asl_inviante'), 'NRREA', 'ASL del registro incoerente con l’ASL inviante.');
        }
        if ($v('cf_medico_autorizzante') !== '') {
            $this->expect($result, 'H2', $line, $f['cf_medico_autorizzante'], ItalianFiscalCode::isValid($v('cf_medico_autorizzante')), 'CFMAE', 'Codice fiscale del medico autorizzante non valido.');
        } elseif ($this->config['regional_warnings'] && ! $isClosing) {
            $this->fieldIssue($result, 'H2', $line, 'CFMAM', 'Codice fiscale del medico autorizzante mancante.', $f['cf_medico_autorizzante']);
        }
        $firstAdmissionMunicipality = $v('comune_primo_ricovero');
        if ($type === 'ANZ') {
            $this->expect($result, 'H2', $line, $f['comune_primo_ricovero'], $firstAdmissionMunicipality !== '' && $firstAdmissionMunicipality !== '000000', 'COMPM', 'Comune del primo ricovero non specificato per assistenza ANZ.');
        }
        if ($firstAdmissionMunicipality !== '' && $firstAdmissionMunicipality !== '000000') {
            $this->inTable($result, 'H2', $line, $f['comune_primo_ricovero'], $firstAdmissionMunicipality, 'municipalities_disc02', 'COMPR');
        }

        $dates = ['data_prescrizione' => 'DTPRE', 'data_inizio_ciclo' => 'DTICE', 'data_fine_ciclo' => 'DTFCE', 'data_inizio_periodo' => 'DTIFE', 'data_fine_periodo' => 'DTFFE'];
        foreach ($dates as $field => $code) {
            $this->validDate($result, 'H2', $line, $f[$field], $v($field), $code);
        }
        if (! $isClosing) {
            $this->dateOrder($result, $line, $r, $f, 'data_prescrizione', 'data_inizio_ciclo', 'DPDCI', 'La data di prescrizione non può essere successiva alla data di inizio ciclo.');
        }
        $this->dateOrder($result, $line, $r, $f, 'data_inizio_ciclo', 'data_fine_ciclo');
        $this->dateOrder($result, $line, $r, $f, 'data_inizio_periodo', 'data_fine_periodo');
        if (! $isClosing) {
            $this->dateOrder($result, $line, $r, $f, 'data_inizio_ciclo', 'data_inizio_periodo', 'DCDFI', 'La data di inizio ciclo non può essere successiva alla data di inizio fatturazione.');
        }
        $this->dateOrder($result, $line, $r, $f, 'data_fine_periodo', 'data_fine_ciclo');

        $validQuantity = $v('progressivo_riga') === '99'
            ? $v('quantita') === '000'
            : preg_match('/^(?!000)\d{3}$/', $v('quantita')) === 1;
        $this->expect($result, 'H2', $line, $f['quantita'], $validQuantity, 'DTLQM', 'Quantità non valida: usare 001–999 nel dettaglio e 000 nella riga 99.');
        $this->expect($result, 'H2', $line, $f['codifica_nomenclatore'], $v('codifica_nomenclatore') === 't', 'NOMENCLATORE', 'La codifica nomenclatore deve essere “t”.');
        $this->inTable($result, 'H2', $line, $f['codice_prestazione'], $v('codice_prestazione'), 'services_disc08', 'DTLPT');
        $this->validateServiceRule($r, $f, $result);
        $this->expect($result, 'H2', $line, $f['onere_prestazione'], preg_match('/^[0-9]$/', $v('onere_prestazione')) === 1, 'ONEE', 'Onere della prestazione non valido.');
        foreach (['importo_compartecipazione', 'importo_totale'] as $amount) {
            $this->expect($result, 'H2', $line, $f[$amount], preg_match('/^\d{6},\d{2}$/', $v($amount)) === 1, strtoupper($amount).'_FORMAT', 'Importo non valido: usare 6 cifre, virgola e 2 decimali.');
        }
        $position = $v('posizione_compartecipazione');
        $this->expect($result, 'H2', $line, $f['posizione_compartecipazione'], in_array($position, ['0', '1'], true), 'POSCE', 'Posizione compartecipazione non valida.');
        if ($position === '1') {
            $this->expect($result, 'H2', $line, $f['importo_compartecipazione'], $this->cents($v('importo_compartecipazione')) > 0, 'IMPCE', 'Con compartecipazione corrisposta, l’importo deve essere maggiore di zero.');
        }
        $this->expect($result, 'H2', $line, $f['anno_mese_invio'], preg_match('/^(19|20)\d{2}(0[1-9]|1[0-2])$/', $v('anno_mese_invio')) === 1, 'ANME', 'Anno e mese di invio non validi.');
        $this->expect($result, 'H2', $line, $f['asl_iniziale_addebito'], $v('asl_iniziale_addebito') === '000', 'ASL_ADDEBITO', 'L’ASL iniziale di addebito deve essere 000.');
        $expectedType = $this->config['structure_types'][$v('struttura_erogatrice')] ?? null;
        if ($expectedType !== null) {
            $this->expect($result, 'H2', $line, $f['tipologia_struttura'], $v('tipologia_struttura') === (string) $expectedType, 'TPSIU', 'Tipologia struttura non coerente con quella dichiarata.');
        }
        $this->expect($result, 'H2', $line, $f['posizione_contabile'], $r['posizione_contabile'] === ' ', 'POSITION_ACCOUNTING', 'La posizione contabile deve essere blank.');
        for ($i = 1; $i <= 10; $i++) {
            $field = sprintf('err%02d', $i);
            $this->expect($result, 'H2', $line, $f[$field], $r[$field] === ' ', 'ERROR_FIELD_NOT_EMPTY', strtoupper($field).' deve essere blank nel file in ingresso.');
        }
    }

    /** @param list<array<string, string|int>> $records */
    private function validateSequence(string $file, array $records, ValidationResult $result): void
    {
        $groups = [];
        foreach ($records as $record) {
            $groups[$this->groupKey($record)][] = $record;
        }
        foreach ($groups as $group) {
            usort($group, static function (array $a, array $b): int { return ((int) $a['__line']) <=> ((int) $b['__line']); });
            $progressives = array_map(static function (array $r): int { return (int) $r['progressivo_riga']; }, $group);
            if ($progressives[0] !== 1) {
                $result->add(new ValidationIssue($file, (int) $group[0]['__line'], 'SEQUENCE_START', 'La prescrizione deve iniziare con il progressivo 01.', 'progressivo_riga'));
            }
            if (end($progressives) !== 99) {
                $last = end($group);
                $result->add(new ValidationIssue($file, (int) $last['__line'], 'SEQUENCE_END', 'La prescrizione deve terminare con il progressivo 99.', 'progressivo_riga'));
            }
            $expected = 1;
            foreach ($group as $record) {
                $current = (int) $record['progressivo_riga'];
                if ($current !== $expected && $current !== 99) {
                    $result->add(new ValidationIssue($file, (int) $record['__line'], 'SEQUENCE_GAP', sprintf('Atteso progressivo %02d, trovato %02d.', $expected, $current), 'progressivo_riga'));
                }
                if ($current !== 99) {
                    $expected = $current + 1;
                }
            }
        }
    }

    /** @param list<array<string, string|int>> $records @param list<string> $fields */
    private function validateGroupConsistency(string $file, array $records, array $fields, ValidationResult $result): void
    {
        $groups = [];
        foreach ($records as $record) {
            $groups[$this->groupKey($record)][] = $record;
        }
        foreach ($groups as $group) {
            $first = $group[0];
            foreach (array_slice($group, 1) as $record) {
                foreach ($fields as $field) {
                    if ($record[$field] !== $first[$field]) {
                        $result->add(new ValidationIssue(
                            $file,
                            (int) $record['__line'],
                            'GROUP_MISMATCH',
                            "Il campo {$field} deve essere uguale in tutte le righe della prescrizione.",
                            $field,
                        ));
                    }
                }
            }
        }
    }

    /** @param list<array<string, string|int>> $h1 @param list<array<string, string|int>> $h2 */
    private function validatePairs(array $h1, array $h2, ValidationResult $result): void
    {
        $left = $this->indexRecords('H1', $h1, $result);
        $right = $this->indexRecords('H2', $h2, $result);

        foreach ($left as $key => $record) {
            if (! isset($right[$key])) {
                $result->add(new ValidationIssue('H1', (int) $record['__line'], 'ALL', 'Record sanitario H2 corrispondente non trovato.'));
                continue;
            }
            foreach (['regione_addebitante', 'asl_inviante', 'struttura_erogatrice', 'tipologia_struttura'] as $field) {
                if ($record[$field] !== $right[$key][$field]) {
                    $result->add(new ValidationIssue('H2', (int) $right[$key]['__line'], 'ALL', "Il campo {$field} non coincide con H1.", $field));
                }
            }
        }
        foreach (array_diff_key($right, $left) as $record) {
            $result->add(new ValidationIssue('H2', (int) $record['__line'], 'ALL', 'Record anagrafico H1 corrispondente non trovato.'));
        }
    }

    /** @param list<array<string, string|int>> $records @return array<string, array<string, string|int>> */
    private function indexRecords(string $file, array $records, ValidationResult $result): array
    {
        $index = [];
        foreach ($records as $record) {
            $key = $this->recordKey($record);
            if (isset($index[$key])) {
                $result->add(new ValidationIssue($file, (int) $record['__line'], 'IDPD', 'Progressivo annuo e progressivo riga duplicati.'));
            } else {
                $index[$key] = $record;
            }
        }
        return $index;
    }

    /** @param list<array<string, string|int>> $records */
    private function validateTotals(array $records, ValidationResult $result): void
    {
        $groups = [];
        foreach ($records as $record) {
            $groups[$this->groupKey($record)][] = $record;
        }
        foreach ($groups as $group) {
            $closing = null;
            $sum = 0;
            foreach ($group as $record) {
                if (trim((string) $record['progressivo_riga']) === '99') {
                    $closing = $record;
                } else {
                    $sum += $this->cents(trim((string) $record['importo_totale']));
                }
            }
            if ($closing === null) {
                continue;
            }
            if (trim((string) $closing['posizione_compartecipazione']) === '1') {
                $sum -= $this->cents(trim((string) $closing['importo_compartecipazione']));
            }
            if ($sum !== $this->cents(trim((string) $closing['importo_totale']))) {
                $result->add(new ValidationIssue('H2', (int) $closing['__line'], 'IMPNE', 'Importo netto errato: la riga 99 non coincide con la somma prevista.', 'importo_totale', 149));
            }
        }
    }

    /** @param array<string, string|int> $record @param array<string, FieldDefinition> $fields */
    private function validateServiceRule(array $record, array $fields, ValidationResult $result): void
    {
        if (trim((string) $record['progressivo_riga']) === '99') {
            return;
        }
        $service = trim((string) $record['codice_prestazione']);
        $rule = $this->config['service_rules'][$service] ?? null;
        if (! is_array($rule)) {
            return;
        }

        $line = (int) $record['__line'];
        $assistance = trim((string) $record['tipo_assistenza']);
        if (isset($rule['assistance']) && ! in_array($assistance, (array) $rule['assistance'], true)) {
            $this->fieldIssue($result, 'H2', $line, 'DTLPE', "La prestazione {$service} non è erogabile con assistenza {$assistance}.", $fields['codice_prestazione']);
        }

        $quantity = (int) trim((string) $record['quantita']);
        if (isset($rule['unit_amount'])) {
            $expected = $quantity * $this->cents((string) $rule['unit_amount']);
            $actual = $this->cents(trim((string) $record['importo_totale']));
            if ($actual !== $expected) {
                $this->fieldIssue($result, 'H2', $line, 'DTLIE', 'Importo nel dettaglio errato: atteso '.$this->formatCents($expected).'.', $fields['importo_totale']);
            }
        }
        if (isset($rule['copay_unit_amount'])) {
            $expected = $quantity * $this->cents((string) $rule['copay_unit_amount']);
            $actual = $this->cents(trim((string) $record['importo_compartecipazione']));
            if ($actual !== $expected) {
                $this->fieldIssue($result, 'H2', $line, 'IMPCE', 'Importo compartecipazione errato: atteso '.$this->formatCents($expected).'.', $fields['importo_compartecipazione']);
            }
        }
    }

    /** @param array<string, string|int> $record */
    private function groupKey(array $record): string
    {
        return trim((string) $record['anno']).'|'.trim((string) $record['identificativo_struttura']).'|'.trim((string) $record['identificativo_progressivo']);
    }

    /** @param array<string, string|int> $record */
    private function recordKey(array $record): string
    {
        return $this->groupKey($record).'|'.trim((string) $record['progressivo_riga']);
    }

    private function validDate(ValidationResult $result, string $file, int $line, FieldDefinition $field, string $value, string $code): bool
    {
        $date = DateTimeImmutable::createFromFormat('!dmY', $value);
        $valid = $date !== false && $date->format('dmY') === $value;
        $this->expect($result, $file, $line, $field, $valid, $code, 'Data non valida; formato richiesto GGMMAAAA.');
        return $valid;
    }

    /** @param array<string, string|int> $record @param array<string, FieldDefinition> $fields */
    private function dateOrder(
        ValidationResult $result,
        int $line,
        array $record,
        array $fields,
        string $from,
        string $to,
        string $code = 'DATE_ORDER',
        ?string $message = null
    ): void {
        $a = DateTimeImmutable::createFromFormat('!dmY', trim((string) $record[$from]));
        $b = DateTimeImmutable::createFromFormat('!dmY', trim((string) $record[$to]));
        if ($a !== false && $b !== false) {
            $detail = $message ?? "{$from} deve essere precedente o uguale a {$to}.";
            if ($a > $b) {
                $detail .= ' Valori trovati: '.$a->format('d/m/Y').' > '.$b->format('d/m/Y').'.';
            }
            $this->expect($result, 'H2', $line, $fields[$to], $a <= $b, $code, $detail);
        }
    }

    private function cents(string $amount): int
    {
        return preg_match('/^(\d+),(\d{2})$/', $amount, $m) === 1 ? ((int) $m[1] * 100) + (int) $m[2] : 0;
    }

    private function formatCents(int $amount): string
    {
        return number_format($amount / 100, 2, ',', '.');
    }

    private function inTable(ValidationResult $result, string $file, int $line, FieldDefinition $field, string $value, string $table, string $code): void
    {
        $values = $this->config['reference_tables'][$table] ?? [];
        if ($values !== [] && ! in_array($value, $values, true)) {
            $message = $table === 'services_disc08' && preg_match('/^\d{3}\.\d{3}$/', $value) === 1
                ? "Codice prestazione vecchio non ammesso: {$value}. Utilizzare il corrispondente codice nuovo."
                : "Valore non presente nella tabella {$table}.";
            $this->fieldIssue($result, $file, $line, $code, $message, $field);
        }
    }

    private function expect(ValidationResult $result, string $file, int $line, FieldDefinition $field, bool $condition, string $code, string $message): void
    {
        if (! $condition) {
            $this->fieldIssue($result, $file, $line, $code, $message, $field);
        }
    }

    private function fieldIssue(ValidationResult $result, string $file, int $line, string $code, string $message, FieldDefinition $field): void
    {
        $severity = in_array($code, $this->config['warning_codes'], true) ? 'warning' : 'error';
        $result->add(new ValidationIssue($file, $line, $code, $message, $field->name, $field->start, $severity));
    }
}
