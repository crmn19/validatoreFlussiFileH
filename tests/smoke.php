<?php

spl_autoload_register(static function (string $class): void {
    $prefix = 'FlussiH\\Validator\\';
    if (substr($class, 0, strlen($prefix)) === $prefix) {
        require dirname(__DIR__).'/src/'.str_replace('\\', '/', substr($class, strlen($prefix))).'.php';
    }
});

use FlussiH\Validator\FlussiHValidator;
use FlussiH\Validator\Reports\XlsxReportExporter;
use FlussiH\Validator\Reference\ServiceRules;
use FlussiH\Validator\Schema\FieldDefinition;
use FlussiH\Validator\Schema\H1Schema;
use FlussiH\Validator\Schema\H2Schema;

$serviceRules = ServiceRules::all();
assert(count($serviceRules) === 102);
assert($serviceRules['NNA.013']['unit_amount'] === '000118,070');
assert($serviceRules['NNA.013']['asl_unit_amount'] === '000059,035');

/** @param list<FieldDefinition> $fields @param array<string, string> $values */
function record(array $fields, int $length, array $values): string
{
    $line = str_repeat(' ', $length);
    foreach ($fields as $field) {
        $default = $field->type === 'N' ? str_repeat('0', $field->length) : str_repeat(' ', $field->length);
        $value = $values[$field->name] ?? $default;
        $value = $field->type === 'N'
            ? str_pad($value, $field->length, '0', STR_PAD_LEFT)
            : str_pad($value, $field->length, ' ', STR_PAD_RIGHT);
        $line = substr_replace($line, $value, $field->start - 1, $field->length);
    }
    return $line;
}

function h1(string $row): string
{
    return record(H1Schema::fields(), H1Schema::LENGTH, [
        'regione_addebitante' => '150', 'asl_inviante' => '101',
        'struttura_erogatrice' => '123456', 'tipologia_struttura' => '2',
        'distretto_struttura' => '00001', 'cognome_assistito' => 'ROSSI',
        'nome_assistito' => 'MARIO LUIGI', 'codice_assistito' => 'RSSMRA80A01H501U',
        'comune_residenza' => '063049', 'regione_residenza' => '150',
        'asl_residenza' => '101', 'progressivo_riga' => $row, 'anno' => '2025',
        'identificativo_struttura' => '12345600', 'identificativo_progressivo' => '00000001',
    ]);
}

/** @param array<string, string> $overrides */
function h2(string $row, string $total, array $overrides = []): string
{
    return record(H2Schema::fields(), H2Schema::LENGTH, array_merge([
        'regione_addebitante' => '150', 'asl_inviante' => '101',
        'struttura_erogatrice' => '123456', 'tipo_assistenza' => 'CTD',
        'tipologia_struttura' => '2', 'numero_registro' => '2025101000000001',
        'comune_primo_ricovero' => '000000', 'data_prescrizione' => '01012025',
        'data_inizio_ciclo' => '01012025', 'data_fine_ciclo' => '31012025',
        'progressivo_riga' => $row, 'data_inizio_periodo' => '01012025',
        'data_fine_periodo' => '31012025', 'quantita' => $row === '99' ? '000' : '001',
        'codifica_nomenclatore' => 't', 'codice_prestazione' => 'RIA.001',
        'onere_prestazione' => '0', 'importo_compartecipazione' => '000000,00',
        'posizione_compartecipazione' => '0', 'importo_totale' => $total,
        'anno' => '2025', 'identificativo_struttura' => '12345600',
        'identificativo_progressivo' => '00000001', 'anno_mese_invio' => '202501',
        'asl_iniziale_addebito' => '000',
    ], $overrides));
}

$validator = new FlussiHValidator(['service_rules' => []]);
$valid = $validator->validateContents(h1('01')."\n".h1('99'), h2('01', '000010,00')."\n".h2('99', '000010,00'));
assert($valid->isValid(), json_encode($valid, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
$warnings = array_filter($valid->issues(), static function ($i) { return $i->severity === 'warning'; });
assert(count($warnings) === 3);

$blankOptionalNumeric = $validator->validateContents(
    h1('01')."\n".h1('99'),
    h2('01', '000010,00', ['comune_primo_ricovero' => '      '])."\n".h2('99', '000010,00', ['comune_primo_ricovero' => '      '])
);
assert($blankOptionalNumeric->isValid(), json_encode($blankOptionalNumeric, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
assert(! in_array('NUMERIC_FORMAT', array_map(static function ($i) { return $i->code; }, $blankOptionalNumeric->issues()), true));

$dateWarning = $validator->validateContents(
    h1('01')."\n".h1('99'),
    h2('01', '000010,00', ['data_prescrizione' => '02012025'])."\n".h2('99', '000010,00', ['data_prescrizione' => '02012025']),
);
assert(in_array('DPDCI', array_map(static function ($i) { return $i->code; }, $dateWarning->issues()), true));

$tariffValidator = new FlussiHValidator([
    'regional_warnings' => false,
    'service_rules' => ['RIA.001' => ['assistance' => ['RIA'], 'unit_amount' => '000010,00']],
]);
$tariffResult = $tariffValidator->validateContents(h1('01')."\n".h1('99'), h2('01', '000009,00')."\n".h2('99', '000009,00'));
$tariffCodes = array_map(static function ($i) { return $i->code; }, $tariffResult->issues());
assert(in_array('DTLPE', $tariffCodes, true));
assert(in_array('DTLIE', $tariffCodes, true));

$legacyServiceResult = (new FlussiHValidator(['regional_warnings' => false, 'service_rules' => []]))->validateContents(
    h1('01')."\n".h1('99'),
    h2('01', '000010,00', ['codice_prestazione' => '001.001'])."\n".h2('99', '000010,00', ['codice_prestazione' => '001.001'])
);
$legacyServiceIssues = array_values(array_filter($legacyServiceResult->issues(), static function ($i) { return $i->code === 'DTLPT'; }));
assert(! $legacyServiceResult->isValid());
assert(count($legacyServiceIssues) === 2);
assert(strpos($legacyServiceIssues[0]->message, 'Codice prestazione vecchio non ammesso') !== false);

$abaResult = (new FlussiHValidator(['regional_warnings' => false, 'service_rules' => []]))->validateContents(
    h1('01')."\n".h1('99'),
    h2('01', '000010,00', [
        'tipo_assistenza' => 'ABA',
        'menomazione' => 'MEE07',
        'diagnosi_icd9' => '2990',
        'codice_prestazione' => '002.000',
    ])."\n".h2('99', '000010,00', [
        'tipo_assistenza' => 'ABA',
        'menomazione' => 'MEE07',
        'diagnosi_icd9' => '2990',
        'codice_prestazione' => '002.000',
    ])
);
$abaBlockingCodes = array_map(static function ($i) { return $i->code; }, array_filter($abaResult->issues(), static function ($i) { return $i->severity === 'error'; }));
assert($abaResult->isValid(), json_encode($abaResult, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
assert(array_intersect(['TAEE', 'MENI', 'DTLPT'], $abaBlockingCodes) === []);

foreach (['003.000', '004.000'] as $allowedLegacyService) {
    $allowedLegacyResult = (new FlussiHValidator(['regional_warnings' => false, 'service_rules' => []]))->validateContents(
        h1('01')."\n".h1('99'),
        h2('01', '000010,00', ['codice_prestazione' => $allowedLegacyService])."\n".h2('99', '000010,00', ['codice_prestazione' => $allowedLegacyService])
    );
    $allowedLegacyCodes = array_map(static function ($i) { return $i->code; }, $allowedLegacyResult->issues());
    assert(! in_array('DTLPT', $allowedLegacyCodes, true));
}

$builtInRateResult = (new FlussiHValidator(['regional_warnings' => false]))->validateContents(
    h1('01')."\n".h1('99'),
    h2('01', '000046,50', ['codice_prestazione' => 'RIA.001'])."\n".h2('99', '000046,50', ['codice_prestazione' => 'RIA.001'])
);
assert($builtInRateResult->isValid(), json_encode($builtInRateResult, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

$fractionResult = (new FlussiHValidator(['regional_warnings' => false]))->validateContents(
    h1('01')."\n".h1('99'),
    h2('01', '000118,07', [
        'codice_prestazione' => 'NNA.013',
        'importo_compartecipazione' => '000059,04',
        'posizione_compartecipazione' => '1',
    ])."\n".h2('99', '000059,03', [
        'codice_prestazione' => 'NNA.013',
        'importo_compartecipazione' => '000059,04',
        'posizione_compartecipazione' => '1',
    ])
);
assert(in_array('CENT_FRACTION', array_map(static function ($i) { return $i->code; }, $fractionResult->issues()), true));

$invalid = $validator->validateContents(substr(h1('01'), 0, -1), h2('01', '000010,00'));
assert(! $invalid->isValid());
assert(in_array('RECORD_LENGTH', array_map(static function ($i) { return $i->code; }, $invalid->issues()), true));

$report = (new XlsxReportExporter())->export($valid, 'esempio.h1', 'esempio.h2');
$zip = new ZipArchive();
assert($zip->open($report) === true);
assert($zip->locateName('xl/worksheets/sheet1.xml') !== false);
assert($zip->locateName('xl/worksheets/sheet2.xml') !== false);
assert(strpos((string) $zip->getFromName('xl/worksheets/sheet2.xml'), 'CFMAM') !== false);
$zip->close();
unlink($report);

echo "Smoke test completato.\n";
