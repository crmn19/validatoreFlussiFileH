<?php

return [
    'web' => [
        'enabled' => true,
        'path' => 'validatore/flussi-h',
        'middleware' => ['web'],
        'max_upload_kb' => 51200,
    ],

    /* provider, company o region: determina la gestione di OBA/OBR/OBRA. */
    'submission_level' => 'provider',

    /* Emula gli avvertimenti osservati nei report regionali anche al livello erogatore. */
    'regional_warnings' => true,

    /* Se una tabella è vuota viene controllato solo il formato del campo. */
    'reference_tables' => [
        'asl' => array_merge(
            array_map(static function (int $n): string { return (string) $n; }, range(101, 113)),
            array_map(static function (int $n): string { return (string) $n; }, range(201, 207)),
            array_map(static function (int $n): string { return (string) $n; }, range(901, 912))
        ),
        'structures_ria11' => [],
        'districts_disc04' => [],
        'municipalities_disc02' => [],
        'regions_disc01' => [],
        'doctor_types_disc07' => [],
        'doctors_disc20' => [],
        'impairments_disc15' => [],
        /* Codici nuovi della metaconversione SoftwareEngine, più le eccezioni indicate. */
        'services_disc08' => array_merge(
            ['002.000', '003.000', '004.000'],
            array_map(static function (int $n): string { return sprintf('DIS.%03d', $n); }, range(1, 17)),
            array_map(static function (int $n): string { return sprintf('NNA.%03d', $n); }, range(1, 29)),
            array_map(static function (int $n): string { return sprintf('CTD.%03d', $n); }, range(1, 28)),
            array_map(static function (int $n): string { return sprintf('HOS.%03d', $n); }, range(1, 2)),
            array_map(static function (int $n): string { return sprintf('SME.%03d', $n); }, range(1, 12)),
            array_map(static function (int $n): string { return sprintf('RIA.%03d', $n); }, range(1, 14))
        ),
        'icd9cm_2002' => [],
    ],

    /* Relazioni opzionali ricavate dalle anagrafiche regionali aggiornate. */
    'structure_districts' => [], // es. 'RSA313' => '00051'
    'structure_types' => [],     // es. 'RSA313' => '2'

    /*
     * Regole tariffarie indicizzate per codice prestazione:
     * 'NNA.013' => [
     *     'unit_amount' => '000118,070',
     *     'asl_unit_amount' => '000059,035',
     * ]
     */
    'service_rules' => \FlussiH\Validator\Reference\ServiceRules::all(),

    /* Severità rilevate nei file di restituzione: warning non invalida il risultato. */
    'warning_codes' => [
        'CDME', 'CDMM', 'CFMAM', 'CFMM', 'DCDFI', 'DPDCI', 'ICDI', 'ICD10M',
    ],
];
