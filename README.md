# Validatore Flussi H per Laravel

Pacchetto Laravel per controllare i file del **Flusso H della Regione Campania**:

- H1 / ANA;
- H2 / SAN.

Il validatore controlla struttura e lunghezza dei record, campi obbligatori, date, importi, codici configurati, progressivi, corrispondenza tra H1 e H2 e totali di chiusura. Al termine genera un report Excel con il riepilogo e il dettaglio degli errori trovati.

## Requisiti

- PHP 7.3 o successivo;
- Laravel dalla versione 7 alla 13;
- estensione PHP `zip`;
- Composer.

## Prova rapida

Il repository contiene `demo-app`, un'applicazione Laravel già configurata per usare il pacchetto.

```bash
cd demo-app
composer install
php artisan serve
```

Aprire quindi [http://127.0.0.1:8000/validatore/flussi-h](http://127.0.0.1:8000/validatore/flussi-h). Dalla pagina si caricano insieme il file H1 e il file H2; il risultato viene scaricato in formato `.xlsx`.

## Installazione in un altro progetto Laravel

### Dal repository GitHub privato

Il pacchetto è disponibile nel repository:

```text
https://github.com/crmn19/validatoreFlussiFileH
```

Prima dell'installazione serve un token GitHub autorizzato ad accedere al repository.

Entrare nella cartella principale dell'applicazione Laravel che deve utilizzare il validatore. In questa cartella deve essere presente il file `artisan`:

```bash
cd /percorso/dell/applicazione-laravel
ls artisan
```

Registrare il repository e installare il pacchetto:

```bash
composer config repositories.flussi-h vcs https://github.com/crmn19/validatoreFlussiFileH.git
composer require carmenromano/laravel-flussi-h-validator:dev-main
php artisan optimize:clear
```

Alla prima installazione Composer chiede `Token (hidden):`. Incollare il token GitHub e premere Invio. Composer conserva la credenziale nel proprio file `auth.json`, separato dal progetto.

Il primo comando registra il repository nel `composer.json`; il secondo scarica e installa il pacchetto dal ramo `main`. Non occorre indicare il percorso locale in cui è stato sviluppato il validatore.

Verificare infine che il pacchetto e la rotta siano stati registrati:

```bash
composer show carmenromano/laravel-flussi-h-validator
php artisan route:list --path=validatore/flussi-h
```

Per installare gli aggiornamenti pubblicati sul ramo `main`:

```bash
composer update carmenromano/laravel-flussi-h-validator
php artisan optimize:clear
```

#### Problemi comuni durante l'installazione

`Root package 'carmenromano/laravel-flussi-h-validator' cannot require itself` significa che `composer require` è stato eseguito dentro la cartella del validatore. Spostarsi nella cartella dell'applicazione Laravel e ripetere il comando.

`Could not open input file: artisan` indica ugualmente che il terminale non si trova nella cartella principale di un'applicazione Laravel.

Un errore GitHub `404` sul repository privato o su `contents/composer.json` indica che il token non è autorizzato sul repository oppure non dispone del permesso `Contents`. Controllare il repository selezionato nel token e sostituire l'eventuale credenziale errata:

```bash
composer config --global --auth --unset github-oauth.github.com
composer clear-cache
composer require carmenromano/laravel-flussi-h-validator:dev-main
```

### Da una cartella locale

Questa è la soluzione più semplice durante lo sviluppo. Si supponga di avere le due cartelle affiancate:

```text
progetti/
├── gestionale/
└── validatore_flussi_file_h/
```

Nel `composer.json` del progetto `gestionale` aggiungere il repository locale:

```json
{
  "repositories": [
    {
      "type": "path",
      "url": "../validatore_flussi_file_h",
      "options": {
        "symlink": true
      }
    }
  ]
}
```

La proprietà `repositories` va aggiunta al livello principale del file, accanto a `require`, `autoload` e `scripts`. Se esiste già, basta aggiungere il nuovo elemento all'elenco.

Eseguire poi, dalla cartella del progetto Laravel:

```bash
composer require carmenromano/laravel-flussi-h-validator:@dev
php artisan optimize:clear
```

Il percorso può anche essere assoluto e deve indicare la cartella in cui è stato salvato il pacchetto, per esempio:

```json
"url": "/percorso/completo/validatore_flussi_file_h"
```

Il percorso assoluto cambia da computer a computer. Quando le cartelle sono vicine è preferibile usare il percorso relativo `../validatore_flussi_file_h`, perché rende la configurazione più facile da condividere.

Con `symlink: true`, le modifiche fatte al pacchetto sono subito disponibili nel progetto che lo utilizza.

### Versioni stabili

Per un utilizzo stabile è preferibile creare una versione Git del pacchetto, ad esempio `v1.0.0`, e installarla con:

```bash
composer require carmenromano/laravel-flussi-h-validator:^1.0
```

Anche per installare una versione stabile dal repository privato, Composer deve disporre di un token autorizzato.

### Da Packagist

Dopo aver pubblicato il repository su Packagist, non serve più dichiarare `repositories`. L'installazione diventa semplicemente:

```bash
composer require carmenromano/laravel-flussi-h-validator
```

## Configurazione Laravel

Laravel registra automaticamente il service provider e la facade. Non è necessario modificare `config/app.php`.

Per pubblicare la configurazione:

```bash
php artisan vendor:publish --tag=flussi-h-config
```

Viene creato il file `config/flussi-h.php`. Le impostazioni principali dell'interfaccia sono:

```php
'web' => [
    'enabled' => true,
    'path' => 'validatore/flussi-h',
    'middleware' => ['web'],
    'max_upload_kb' => 51200,
],
```

Per limitare l'accesso agli utenti autenticati:

```php
'middleware' => ['web', 'auth'],
```

Per personalizzare la pagina di caricamento:

```bash
php artisan vendor:publish --tag=flussi-h-views
```

La view viene copiata in `resources/views/vendor/flussi-h/upload.blade.php` del progetto Laravel.

## Interfaccia web e report Excel

Una volta installato il pacchetto, avviare l'applicazione Laravel:

```bash
php artisan serve
```

La pagina è disponibile all'indirizzo `/validatore/flussi-h`. Il report generato contiene:

- **Riepilogo**, con esito della validazione, numero di record, errori gravi e avvertimenti raggruppati;
- **Dettaglio errori**, con file, riga, campo, posizione, codice, descrizione e gravità.

Per impostazione predefinita il dettaglio contiene solo gli errori gravi. Dalla pagina si può scegliere di includere anche gli avvertimenti non bloccanti.

I file caricati vengono letti dalla directory temporanea della richiesta e non sono archiviati dal pacchetto. Anche il report temporaneo viene eliminato dopo il download.

## Utilizzo da codice

Con dependency injection:

```php
use FlussiH\Validator\FlussiHValidator;

public function validateFiles(FlussiHValidator $validator)
{
    $result = $validator->validate(
        storage_path('flussi/H1.h1'),
        storage_path('flussi/H2.h2')
    );

    return response()->json($result, $result->isValid() ? 200 : 422);
}
```

Tramite facade:

```php
use FlussiH\Validator\Facades\FlussiH;

$result = FlussiH::validate($h1Path, $h2Path);
```

Per validare contenuti già presenti in memoria:

```php
$result = $validator->validateContents($h1Contents, $h2Contents);
```

Ogni anomalia contiene `file`, `line`, `code`, `message`, `field`, `position` e `severity`. Gli errori gravi rendono il risultato non valido; gli avvertimenti, da soli, non lo rendono non valido.

## Comando Artisan

La validazione può essere eseguita anche da terminale:

```bash
php artisan flussi-h:validate storage/flussi/H1.h1 storage/flussi/H2.h2
php artisan flussi-h:validate storage/flussi/H1.h1 storage/flussi/H2.h2 --json
```

Il comando restituisce codice di uscita `0` per i file validi e `1` quando sono presenti errori bloccanti.

## Livello di invio

L'opzione `submission_level` stabilisce quali controlli di obbligatorietà applicare ai campi di riepilogo:

- `provider` (predefinito): controlli del livello erogatore;
- `company`: aggiunge i controlli sui campi OBA e OBRA;
- `region`: aggiunge anche i controlli regionali, compreso OBR;

Il valore speciale `NV` richiede che i campi riservati ai livelli superiori siano compilati con il relativo valore vuoto: zeri per i campi numerici e spazi per quelli alfanumerici.

## Tabelle regionali

Alcuni controlli dipendono da elenchi regionali che non sono contenuti nel tracciato tecnico, come DISC e RIA11. Gli elenchi aggiornati possono essere inseriti in `config/flussi-h.php`:

```php
'reference_tables' => [
    'structures_ria11' => ['123456', '654321'],
    'districts_disc04' => ['00001'],
    'municipalities_disc02' => ['063049'],
    'regions_disc01' => ['150'],
    'doctor_types_disc07' => ['M'],
    'impairments_disc15' => ['ABCDE'],
    'services_disc08' => ['DIS.001', 'NNA.001', 'CTD.001', 'HOS.001', 'SME.001', 'RIA.001'],
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

I valori dell'esempio non sono anagrafiche ufficiali. Devono essere sostituiti con quelli validi per il periodo da controllare. Se una tabella è vuota, il formato del campo viene comunque verificato, ma il valore non viene respinto perché assente dall'elenco.

Per il campo H2 `codice_prestazione`, il pacchetto include i 102 valori della colonna **Codice Nuovo**. Sono ammessi anche `002.000`, `003.000` e `004.000` come eccezioni. Gli altri valori della colonna **Codice Vecchio** non sono ammessi e producono l'errore grave `DTLPT`.

## Verifica del pacchetto

Dalla cartella principale del pacchetto:

```bash
composer test
composer check
```

`composer test` esegue i test funzionali. `composer check` controlla anche la validità del file `composer.json`.

## Licenza

Il pacchetto è distribuito con licenza MIT.
