<?php

namespace FlussiH\Validator\Schema;

final class H1Schema
{
    public const LENGTH = 266;

    /** @return list<FieldDefinition> */
    public static function fields(): array
    {
        return [
            new FieldDefinition('1.0', 'regione_addebitante', 1, 3, 'AN', 'OBB'),
            new FieldDefinition('2.0', 'asl_inviante', 4, 3, 'AN', 'OBB'),
            new FieldDefinition('3.0', 'struttura_erogatrice', 7, 6, 'AN', 'OBB'),
            new FieldDefinition('4.0', 'stabilimento', 13, 2, 'AN', 'NV'),
            new FieldDefinition('5.0', 'tipologia_struttura', 15, 1, 'N', 'OBB'),
            new FieldDefinition('6.0', 'distretto_struttura', 16, 5, 'AN', 'OBB'),
            new FieldDefinition('100.0', 'medico_induttore', 21, 8, 'AN', 'NV'),
            new FieldDefinition('7.0', 'cf_medico_prescrittore', 29, 16, 'AN', 'OBRA'),
            new FieldDefinition('8.0', 'codice_medico_prescrittore', 45, 8, 'AN', 'OSP'),
            new FieldDefinition('9.0', 'tipologia_medico_prescrittore', 53, 1, 'AN', 'OBRA'),
            new FieldDefinition('10.0', 'cognome_assistito', 54, 40, 'AN', 'OBB'),
            new FieldDefinition('11.0', 'nome_assistito', 94, 40, 'AN', 'OBB'),
            new FieldDefinition('12.0', 'codice_assistito', 134, 16, 'AN', 'OBB'),
            new FieldDefinition('13.0', 'sesso_assistito', 150, 1, 'N', 'OBR'),
            new FieldDefinition('14.0', 'data_nascita', 151, 8, 'N', 'OBR'),
            new FieldDefinition('15.0', 'cittadinanza', 159, 1, 'AN', 'NV'),
            new FieldDefinition('16.0', 'comune_residenza', 160, 6, 'N', 'OBB'),
            new FieldDefinition('17.0', 'regione_residenza', 166, 3, 'N', 'OBB'),
            new FieldDefinition('18.0', 'asl_residenza', 169, 3, 'N', 'OBB'),
            new FieldDefinition('19.0', 'tipo_ricetta', 172, 2, 'AN', 'NV'),
            new FieldDefinition('20.0', 'istituzione_competente', 174, 28, 'AN', 'NV'),
            new FieldDefinition('21.0', 'stato_estero', 202, 2, 'AN', 'NV'),
            new FieldDefinition('22.0', 'identificazione_personale', 204, 20, 'AN', 'NV'),
            new FieldDefinition('23.0', 'identificazione_tessera', 224, 20, 'AN', 'NV'),
            new FieldDefinition('24.0', 'progressivo_riga', 244, 2, 'N', 'OBB'),
            new FieldDefinition('25.0', 'stampa_informatizzata', 246, 1, 'N', 'NV'),
            new FieldDefinition('26.0', 'anno', 247, 4, 'N', 'OBB'),
            new FieldDefinition('27.0', 'identificativo_struttura', 251, 8, 'AN', 'OBB'),
            new FieldDefinition('28.0', 'identificativo_progressivo', 259, 8, 'N', 'OBB'),
        ];
    }
}
