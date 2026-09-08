<?php

namespace FlussiH\Validator\Schema;

final class H2Schema
{
    public const LENGTH = 197;

    /** @return list<FieldDefinition> */
    public static function fields(): array
    {
        $fields = [
            new FieldDefinition('1.0', 'regione_addebitante', 1, 3, 'AN', 'OBB'),
            new FieldDefinition('2.0', 'asl_inviante', 4, 3, 'AN', 'OBB'),
            new FieldDefinition('3.0', 'struttura_erogatrice', 7, 6, 'AN', 'OBB'),
            new FieldDefinition('87.0', 'tipo_assistenza', 13, 3, 'AN', 'OBB'),
            new FieldDefinition('5.0', 'tipologia_struttura', 16, 1, 'N', 'OBB'),
            new FieldDefinition('88.0', 'menomazione', 17, 5, 'AN', 'OSP'),
            new FieldDefinition('89.0', 'diagnosi_icd9', 22, 10, 'AN', 'OSP'),
            new FieldDefinition('90.0', 'numero_registro', 32, 16, 'AN', 'OBB'),
            new FieldDefinition('91.0', 'cf_medico_autorizzante', 48, 16, 'AN', 'OBA'),
            new FieldDefinition('92.0', 'codice_medico_autorizzante', 64, 7, 'AN', 'OSP'),
            new FieldDefinition('93.0', 'comune_primo_ricovero', 71, 6, 'N', 'OSP'),
            new FieldDefinition('38.0', 'data_prescrizione', 77, 8, 'AN', 'OBB'),
            new FieldDefinition('94.0', 'data_inizio_ciclo', 85, 8, 'AN', 'OBB'),
            new FieldDefinition('95.0', 'data_fine_ciclo', 93, 8, 'AN', 'OBB'),
            new FieldDefinition('24.0', 'progressivo_riga', 101, 2, 'N', 'OBB'),
            new FieldDefinition('96.0', 'data_inizio_periodo', 103, 8, 'AN', 'OBB'),
            new FieldDefinition('97.0', 'data_fine_periodo', 111, 8, 'AN', 'OBB'),
            new FieldDefinition('43.0', 'quantita', 119, 3, 'N', 'OBB'),
            new FieldDefinition('41.0', 'codifica_nomenclatore', 122, 1, 'AN', 'OBB'),
            new FieldDefinition('42.0', 'codice_prestazione', 123, 7, 'AN', 'OBB'),
            new FieldDefinition('44.0', 'posizione_ticket', 130, 1, 'AN', 'NV'),
            new FieldDefinition('45.0', 'codice_esenzione', 131, 6, 'AN', 'NV'),
            new FieldDefinition('46.0', 'esenzione_reddito', 137, 1, 'AN', 'NV'),
            new FieldDefinition('47.0', 'onere_prestazione', 138, 1, 'N', 'OBB'),
            new FieldDefinition('98.0', 'importo_compartecipazione', 139, 9, 'N', 'OBB'),
            new FieldDefinition('99.0', 'posizione_compartecipazione', 148, 1, 'N', 'OBB'),
            new FieldDefinition('52.0', 'importo_totale', 149, 9, 'N', 'OBB'),
            new FieldDefinition('58.0', 'posizione_contabile', 158, 1, 'AN', 'NOB'),
        ];

        for ($i = 1; $i <= 10; $i++) {
            $fields[] = new FieldDefinition('N.C.', sprintf('err%02d', $i), 158 + $i, 1, 'AN', 'NOB');
        }

        return array_merge($fields, [
            new FieldDefinition('26.0', 'anno', 169, 4, 'N', 'OBB'),
            new FieldDefinition('27.0', 'identificativo_struttura', 173, 8, 'AN', 'OBB'),
            new FieldDefinition('28.0', 'identificativo_progressivo', 181, 8, 'N', 'OBB'),
            new FieldDefinition('59.0', 'anno_mese_invio', 189, 6, 'N', 'OBB'),
            new FieldDefinition('61.0', 'asl_iniziale_addebito', 195, 3, 'N', 'NOB'),
        ]);
    }
}
