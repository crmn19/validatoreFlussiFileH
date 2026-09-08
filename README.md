# Laravel Flussi H Validator

Compatibile con **PHP 7.3 o successivo** e **Laravel dalla versione 7 alla 13**.

Pacchetto Laravel per validare i file a lunghezza fissa del **Flusso H della Regione Campania**:

- H1 / ANA, record da 266 byte nel tracciato regionale corrente;
- H2 / SAN, record da 197 byte.

L'implementazione segue le specifiche del documento “Specifiche tecniche del Sistema Informativo Sanitario Regionale”, Allegato 1, ed è stata confrontata con report di restituzione regionali reali. Controlla lunghezza, posizione, tipo, obbligatorietà, valori noti, date, importi, identificativi, sequenze `01…99`, corrispondenza H1/H2 e totale della riga 99.

## Installazione

Durante lo sviluppo locale:

```bash
composer require carmenromano/laravel-flussi-h-validator
```

La discovery Laravel registra automaticamente provider e facade. Per pubblicare la configurazione:

```bash
php artisan vendor:publish --tag=flussi-h-config
```

## Uso

### Interfaccia web

Il pacchetto registra automaticamente la pagina:

```text
/flussi-h
```

Nel repository è inclusa anche `demo-app`, un'applicazione Laravel già collegata al pacchetto. Per provarla subito:

```bash
cd demo-app
php artisan serve
```

Avvia il progetto Laravel:

```bash
php artisan serve
```

Apri quindi [http://127.0.0.1:8000/flussi-h](http://127.0.0.1:8000/flussi-h), seleziona il file H1/ANA e il file H2/SAN e premi **Valida e scarica Excel**.

Viene scaricato un file `.xlsx` con:

- foglio **Riepilogo**, con esito, numero dei record, errori gravi e avvertimenti aggregati;
- foglio **Dettaglio errori**, con file, riga, campo, posizione, codice regionale, descrizione e severità.

Per impostazione predefinita il dettaglio mostra solo gli **errori gravi**, così un file valido non produce migliaia di righe poco utili. Dalla pagina si può selezionare “Includi anche gli avvertimenti non bloccanti” per ottenere un report analogo alla restituzione regionale completa.

Il percorso, la dimensione massima e i middleware sono modificabili nella configurazione:

```php
'web' => [
    'enabled' => true,
    'path' => 'flussi-h',
    'middleware' => ['web', 'auth'], // rimuovere auth se la pagina deve essere pubblica
    'max_upload_kb' => 51200,
],
```

Per personalizzare la pagina Blade:

```bash
php artisan vendor:publish --tag=flussi-h-views
```

I file caricati vengono letti dalla directory temporanea della richiesta e non vengono conservati dal pacchetto. Il report Excel temporaneo viene eliminato dopo il download.

### Uso da codice

Con dependency injection:

```php
use FlussiH\Validator\FlussiHValidator;

public function validateFiles(FlussiHValidator $validator)
{
    $result = $validator->validate(
        storage_path('flussi/H1.txt'),
        storage_path('flussi/H2.txt'),
    );

    return response()->json($result, $result->isValid() ? 200 : 422);
}
```

Oppure tramite facade:

```php
$result = FlussiH::validate($h1Path, $h2Path);
```

Per contenuti già caricati in memoria:

```php
$result = $validator->validateContents($h1Contents, $h2Contents);
```

Ogni anomalia espone `file`, `line`, `code`, `message`, `field`, `position` e `severity`. Gli avvertimenti regionali non rendono da soli il risultato non valido; gli errori gravi sì. Il risultato implementa `JsonSerializable`:

```php
if (! $result->isValid()) {
    foreach ($result->issues() as $issue) {
        logger()->warning($issue->message, $issue->jsonSerialize());
    }
}
```

È disponibile anche un comando Artisan, con codice di uscita `0` per file validi e `1` per file non validi:

```bash
php artisan flussi-h:validate storage/flussi/H1.txt storage/flussi/H2.txt
php artisan flussi-h:validate storage/flussi/H1.txt storage/flussi/H2.txt --json
```

## Livello di invio

`submission_level` gestisce i campi OBA, OBR e OBRA:

- `provider` (predefinito): i campi aziendali/regionali devono essere vuoti;
- `company`: OBA e OBRA sono obbligatori, OBR vuoti;
- `region`: OBA, OBR e OBRA sono obbligatori.

Per i campi numerici “vuoto” significa zeri; per gli alfanumerici significa spazi, come indicato dalla specifica.

## Tabelle regionali

Il PDF nomina le tabelle DISC e RIA11 ma non ne include i valori. In `config/flussi-h.php` si possono caricare gli elenchi aggiornati:

```php
'reference_tables' => [
    'structures_ria11' => ['123456', '654321'],
    'districts_disc04' => ['00001'],
    'municipalities_disc02' => ['063049'],
    'regions_disc01' => ['150'],
    'doctor_types_disc07' => ['M'],
    'impairments_disc15' => ['ABCDE'],
    'services_disc08' => ['ABC1234'],
    'icd9cm_2002' => ['123.45'],
],

'structure_districts' => [
    'RSA313' => '00051',
],

'structure_types' => [
    'RSA313' => '2',
],

'service_rules' => [
    'NNA.013' => [
        'assistance' => ['RSD'],
        'unit_amount' => '000118,07',
        'copay_unit_amount' => '000059,04',
    ],
],
```

Se una tabella o una relazione è vuota, il campo conserva i controlli di struttura ma non viene respinto per assenza dall'elenco. Tariffe e abbinamenti mostrati sopra sono solo esempi di configurazione: vanno caricati dalle anagrafiche regionali applicabili al periodo.

## Controlli riscontrati nei report regionali

Il pacchetto riproduce, quando dispone dei dati di riferimento necessari, anche i codici osservati nei file di restituzione: `ALL`, `CDME`, `CDMM`, `CFMAM`, `CFMM`, `DCDFI`, `DPDCI`, `DSI`, `DTLIE`, `DTLPE`, `ICDI`, `IDPD`, `IMPCE`, `IMPNE`, `REGE`, `SESI` e `TPSIU`.

I report recenti contengono anche `ICD10M`, ma il tracciato H2 del PDF allegato non definisce un campo ICD-10 distinto. Il validatore non inventa una posizione record: quel controllo potrà essere attivato quando sarà disponibile il tracciato regionale aggiornato o la relativa regola di derivazione.

## Verifica del pacchetto

```bash
composer check
```

I file di restituzione regionali possono essere usati per affinare la mappatura dei codici errore senza modificare l'interfaccia pubblica del validatore.
