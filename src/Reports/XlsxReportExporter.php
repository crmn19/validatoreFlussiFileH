<?php

namespace FlussiH\Validator\Reports;

use FlussiH\Validator\ValueObjects\ValidationIssue;
use FlussiH\Validator\ValueObjects\ValidationResult;
use RuntimeException;
use ZipArchive;

final class XlsxReportExporter
{
    public function export(ValidationResult $result, string $h1Name, string $h2Name, bool $includeWarnings = true): string
    {
        $path = tempnam(sys_get_temp_dir(), 'flussi-h-');
        if ($path === false) {
            throw new RuntimeException('Impossibile creare il report temporaneo.');
        }

        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Impossibile creare il file Excel.');
        }

        $zip->addFromString('[Content_Types].xml', $this->contentTypes());
        $zip->addFromString('_rels/.rels', $this->rootRelationships());
        $zip->addFromString('xl/workbook.xml', $this->workbook());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRelationships());
        $zip->addFromString('xl/styles.xml', $this->styles());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->summarySheet($result, $h1Name, $h2Name, $includeWarnings));
        $zip->addFromString('xl/worksheets/sheet2.xml', $this->detailSheet($result, $includeWarnings));
        $zip->close();

        return $path;
    }

    private function summarySheet(ValidationResult $result, string $h1Name, string $h2Name, bool $includeWarnings): string
    {
        $counts = $result->recordCounts();
        $rows = [
            [['Report validazione Flusso H', 4]],
            [['Generato il', 1], [date('d/m/Y H:i:s'), 0]],
            [['File H1 / ANA', 1], [$h1Name, 0]],
            [['File H2 / SAN', 1], [$h2Name, 0]],
            [['Esito', 1], [$this->outcome($result), $result->isValid() ? ($result->warningCount() > 0 ? 3 : 0) : 2]],
            [['Record H1', 1], [(string) $counts['h1'], 0], ['Record H2', 1], [(string) $counts['h2'], 0]],
            [['Errori gravi', 1], [(string) $result->errorCount(), 2], ['Avvertimenti', 1], [(string) $result->warningCount(), 3]],
            [['Contenuto report', 1], [$includeWarnings ? 'Errori gravi e avvertimenti' : 'Solo errori gravi; avvertimenti esclusi dal dettaglio', 0]],
            [['Tipo', 1], ['Codice', 1], ['Problema rilevato', 1], ['Come correggere', 1], ['Casi', 1]],
        ];

        $aggregated = [];
        foreach ($result->issues() as $issue) {
            if (! $includeWarnings && $issue->severity === 'warning') {
                continue;
            }
            $key = $issue->severity.'|'.$issue->code;
            if (! isset($aggregated[$key])) {
                $aggregated[$key] = ['count' => 0, 'issue' => $issue];
            }
            $aggregated[$key]['count']++;
        }
        ksort($aggregated);
        foreach ($aggregated as $key => $data) {
            list($severity, $code) = explode('|', $key, 2);
            $style = $severity === 'error' ? 2 : 3;
            $rows[] = [
                [$severity === 'error' ? 'ERRORE GRAVE' : 'AVVERTIMENTO', $style],
                [$code, $style],
                [$this->plainDescription($data['issue']), $style],
                [$this->suggestion($code), $style],
                [(string) $data['count'], $style],
            ];
        }
        if ($aggregated === []) {
            $rows[] = [[$includeWarnings ? 'Nessuna anomalia rilevata.' : 'Nessun errore grave. Gli avvertimenti non bloccanti sono esclusi da questo report.', 0]];
        }

        return $this->worksheet($rows, [20, 16, 58, 68, 12], 9, 'A9:E9');
    }

    private function detailSheet(ValidationResult $result, bool $includeWarnings): string
    {
        $rows = [[
            ['N.', 1], ['Dove', 1], ['Tipo', 1], ['Codice', 1],
            ['Campo e posizione', 1], ['Problema rilevato', 1], ['Come correggere', 1],
        ]];
        $issues = array_values(array_filter($result->issues(), static function (ValidationIssue $issue) use ($includeWarnings): bool {
            return $includeWarnings || $issue->severity === 'error';
        }));
        usort($issues, static function (ValidationIssue $a, ValidationIssue $b): int {
            $severity = ($a->severity === 'error' ? 0 : 1) <=> ($b->severity === 'error' ? 0 : 1);
            return $severity !== 0 ? $severity : [$a->file, $a->line, $a->code] <=> [$b->file, $b->line, $b->code];
        });
        foreach ($issues as $index => $issue) {
            $style = $issue->severity === 'error' ? 2 : 3;
            $location = $issue->line > 0 ? $issue->file.' · riga '.$issue->line : $issue->file;
            $field = $issue->field ?? 'record';
            if ($issue->position !== null) {
                $field .= ' · posizione '.$issue->position;
            }
            $rows[] = [
                [(string) ($index + 1), $style], [$location, $style],
                [$issue->severity === 'error' ? 'ERRORE GRAVE' : 'AVVERTIMENTO', $style],
                [$issue->code, $style], [$field, $style], [$issue->message, $style],
                [$this->suggestion($issue->code), $style],
            ];
        }
        if ($result->issues() === []) {
            $rows[] = [['1', 0], ['', 0], ['OK', 0], ['', 0], ['', 0], [$includeWarnings ? 'Nessuna anomalia rilevata.' : 'Nessun errore grave.', 0], ['', 0]];
        } elseif ($issues === []) {
            $rows[] = [['1', 0], ['', 0], ['OK', 0], ['', 0], ['', 0], ['Nessun errore grave. Sono presenti soltanto avvertimenti non bloccanti, esclusi su richiesta.', 0], ['', 0]];
        }

        return $this->worksheet($rows, [8, 20, 20, 16, 38, 72, 72], 1, 'A1:G1');
    }

    private function outcome(ValidationResult $result): string
    {
        if (! $result->isValid()) {
            return 'NON VALIDO — sono presenti errori gravi';
        }
        return $result->warningCount() > 0
            ? 'VALIDO — presenti solo avvertimenti non bloccanti'
            : 'VALIDO — nessuna anomalia';
    }

    private function plainDescription(ValidationIssue $issue): string
    {
        if ($issue->code === 'NUMERIC_FORMAT') {
            $field = $issue->field === null ? 'indicato' : str_replace('_', ' ', $issue->field);
            $position = $issue->position === null ? '' : ' a partire dalla posizione '.$issue->position;

            return 'Il campo “'.$field.'” del file '.$issue->file.$position.' contiene un valore non numerico.';
        }

        $descriptions = [
            'ALL' => 'H1 e H2 non sono allineati.',
            'CDME' => 'Codice del medico prescrittore non riconosciuto.',
            'CDMM' => 'Codice del medico prescrittore assente.',
            'CFMAM' => 'Codice fiscale del medico autorizzante assente.',
            'CFMM' => 'Codice fiscale del medico prescrittore assente.',
            'DCDFI' => 'Inizio ciclo successivo all’inizio del periodo fatturato.',
            'DPDCI' => 'Prescrizione successiva all’inizio del ciclo.',
            'DSI' => 'Distretto non coerente con la struttura.',
            'DTLIE' => 'Importo di dettaglio diverso da quantità × tariffa.',
            'DTLPE' => 'Prestazione non prevista per struttura o assistenza.',
            'ICDI' => 'Diagnosi ICD9 indicata per un tipo di assistenza diverso da RIA/SME.',
            'IDPD' => 'Progressivo annuo duplicato.',
            'IMPCE' => 'Importo compartecipazione non coerente.',
            'IMPASL' => 'Quota ASL diversa dalla tariffa prevista.',
            'CENT_FRACTION' => 'La tariffa moltiplicata per la quantità produce una frazione di centesimo.',
            'IMPNE' => 'Totale netto della riga 99 non coerente.',
            'REGE' => 'Regione addebitante diversa da 150.',
            'SESI' => 'Sesso non coerente con il codice fiscale.',
            'TPSIU' => 'Tipologia struttura non coerente con l’anagrafica.',
        ];
        return $descriptions[$issue->code] ?? $issue->message;
    }

    private function suggestion(string $code): string
    {
        $suggestions = [
            'ALL' => 'Controllare anno, struttura, progressivo annuo e progressivo riga in entrambi i file.',
            'CDME' => 'Verificare il codice del medico nella tabella regionale DISC/20.',
            'CDMM' => 'Compilare il codice del medico prescrittore se disponibile.',
            'CFMAM' => 'Compilare il codice fiscale del medico autorizzante; è un avvertimento non bloccante.',
            'CFMM' => 'Compilare il codice fiscale del medico prescrittore; è un avvertimento non bloccante.',
            'DCDFI' => 'Verificare le date; l’inizio ciclo dovrebbe precedere o coincidere con l’inizio fatturazione.',
            'DPDCI' => 'Verificare le date; la prescrizione dovrebbe precedere o coincidere con l’inizio ciclo.',
            'DSI' => 'Usare il distretto associato alla struttura nell’anagrafica regionale.',
            'DTLIE' => 'Ricalcolare l’importo moltiplicando quantità e tariffa regionale.',
            'DTLPE' => 'Verificare codice prestazione, struttura e tipo di assistenza.',
            'ICDI' => 'Lasciare blank la diagnosi se non richiesta oppure verificare il tipo di assistenza.',
            'IDPD' => 'Assegnare un progressivo nuovo e univoco per anno e struttura.',
            'IMPCE' => 'Ricalcolare la compartecipazione usando la tariffa prevista.',
            'IMPASL' => 'Verificare che retta totale meno compartecipazione coincida con la quota ASL prevista.',
            'CENT_FRACTION' => 'Non arrotondare: correggere quantità o tariffa affinché il risultato sia espresso in centesimi interi.',
            'IMPNE' => 'Ricalcolare la riga 99 sommando i dettagli e sottraendo la compartecipazione quando dovuto.',
            'REGE' => 'Impostare il campo Regione addebitante a 150.',
            'SESI' => 'Correggere il sesso oppure il codice fiscale dell’assistito.',
            'TPSIU' => 'Usare la tipologia associata alla struttura nell’anagrafica regionale.',
            'NUMERIC_FORMAT' => 'Inserire esclusivamente cifre; se il campo è opzionale può essere lasciato vuoto.',
        ];
        return $suggestions[$code] ?? 'Controllare il valore del campo indicato e confrontarlo con la specifica regionale.';
    }

    /** @param list<list<array{0: string, 1: int}>> $rows @param list<int> $widths */
    private function worksheet(array $rows, array $widths, int $freezeRow, string $filter): string
    {
        $columns = '';
        foreach ($widths as $index => $width) {
            $column = $index + 1;
            $columns .= '<col min="'.$column.'" max="'.$column.'" width="'.$width.'" customWidth="1"/>';
        }
        $sheetRows = '';
        foreach ($rows as $rowIndex => $cells) {
            $number = $rowIndex + 1;
            $sheetRows .= '<row r="'.$number.'">';
            foreach ($cells as $columnIndex => [$value, $style]) {
                $reference = $this->columnName($columnIndex + 1).$number;
                $sheetRows .= '<c r="'.$reference.'" t="inlineStr" s="'.$style.'"><is><t xml:space="preserve">'.$this->xml($value).'</t></is></c>';
            }
            $sheetRows .= '</row>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<sheetViews><sheetView workbookViewId="0"><pane ySplit="'.$freezeRow.'" topLeftCell="A'.($freezeRow + 1).'" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
            .'<cols>'.$columns.'</cols><sheetData>'.$sheetRows.'</sheetData>'
            .'<autoFilter ref="'.$filter.'"/><pageMargins left="0.3" right="0.3" top="0.5" bottom="0.5" header="0.2" footer="0.2"/>'
            .'</worksheet>';
    }

    private function columnName(int $number): string
    {
        $name = '';
        while ($number > 0) {
            $number--;
            $name = chr(65 + ($number % 26)).$name;
            $number = intdiv($number, 26);
        }
        return $name;
    }

    private function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private function contentTypes(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/worksheets/sheet2.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>';
    }

    private function rootRelationships(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>';
    }

    private function workbook(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Riepilogo" sheetId="1" r:id="rId1"/><sheet name="Dettaglio errori" sheetId="2" r:id="rId2"/></sheets></workbook>';
    }

    private function workbookRelationships(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet2.xml"/><Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>';
    }

    private function styles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><fonts count="3"><font><sz val="11"/><name val="Calibri"/></font><font><b/><color rgb="FF303736"/><sz val="11"/><name val="Calibri"/></font><font><b/><color rgb="FF303736"/><sz val="16"/><name val="Calibri"/></font></fonts><fills count="5"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FFE9ECEB"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFFFE8E8"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFFFF5D6"/><bgColor indexed="64"/></patternFill></fill></fills><borders count="2"><border><left/><right/><top/><bottom/><diagonal/></border><border><left style="thin"><color rgb="FFD9DEDC"/></left><right style="thin"><color rgb="FFD9DEDC"/></right><top style="thin"><color rgb="FFD9DEDC"/></top><bottom style="thin"><color rgb="FFD9DEDC"/></bottom><diagonal/></border></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="5"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0" applyAlignment="1"><alignment vertical="top" wrapText="1"/></xf><xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyAlignment="1"><alignment vertical="center" wrapText="1"/></xf><xf numFmtId="0" fontId="0" fillId="3" borderId="1" xfId="0" applyAlignment="1"><alignment vertical="top" wrapText="1"/></xf><xf numFmtId="0" fontId="0" fillId="4" borderId="1" xfId="0" applyAlignment="1"><alignment vertical="top" wrapText="1"/></xf><xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0"/></cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>';
    }
}
