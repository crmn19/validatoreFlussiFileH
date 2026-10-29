@extends($layout)

@push('style')
    <style>
        .flussi-h-validator { --charcoal: #4C184E; --paper: #fff; --ink: #252b2d; --muted: #6c5a6d; --line: #4C184E; --error: #9a2d2d; color: var(--ink); }
        /* Superficie bianca "sospesa", come il pannello di lavoro nell'interfaccia di riferimento. */
        .flussi-h-validator main { width: min(900px, calc(100% - 32px)); margin: 48px auto; padding: 40px; border-radius: 6px; background: var(--paper); box-shadow: 0 10px 30px rgba(31, 40, 42, .08); box-sizing: border-box; }
        .flussi-h-validator .eyebrow { color: var(--muted); font-size: .76rem; font-weight: 700; letter-spacing: .11em; text-transform: uppercase; }
        .flussi-h-validator .notice { margin: -12px 0 20px; padding: 12px 15px; border-left: 4px solid #4C184E; border-radius: 4px; color: #4C184E; font-size: .86rem; line-height: 1.5; }
        .flussi-h-validator .actions .notice { margin: 10px 0 0; }
        .flussi-h-validator .card { overflow: hidden; border: 1px solid var(--line); border-radius: 8px; background: var(--paper); box-shadow: 0 8px 24px rgba(31, 40, 42, .06); }
        .flussi-h-validator .card-head { display: flex; gap: 18px; align-items: center; padding: 24px 28px; border-bottom: 1px solid var(--line); }
        .flussi-h-validator .card-head h1 { margin: 0; color: var(--charcoal); font-size: 1.3rem; font-weight: 650; }
        .flussi-h-validator form { padding: 28px; }
        .flussi-h-validator .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
        .flussi-h-validator .upload { position: relative; min-height: 175px; padding: 22px; border: 1px dashed #4C184E; border-radius: 6px; background: #fafafa; transition: .15s ease; }
        .flussi-h-validator .upload:hover, .flussi-h-validator .upload:focus-within { border-color: #4C184E; background: #f5f6f6; }
        .flussi-h-validator .upload strong { display: block; margin-bottom: 7px; color: var(--charcoal); font-size: 1rem; }
        .flussi-h-validator .upload small { display: block; margin-bottom: 20px; color: var(--muted); line-height: 1.5; }
        .flussi-h-validator input[type=file] { width: 100%; color: var(--muted); font-size: .86rem; }
        .flussi-h-validator input[type=file]::file-selector-button { margin-right: 10px; padding: 8px 12px; border: 1px solid #4C184E; border-radius: 4px; color: #fff; background: #4C184E; font-weight: 700; cursor: pointer; }
        .flussi-h-validator .errors { margin: 0 28px; padding: 15px 18px; border-left: 4px solid var(--error); border-radius: 8px; color: #7e2020; background: #fff0f0; }
        .flussi-h-validator .actions { display: flex; justify-content: space-between; gap: 20px; align-items: center; margin-top: 25px; }
        .flussi-h-validator .options { display: flex; align-items: flex-start; gap: 9px; margin-top: 20px; color: var(--muted); font-size: .86rem; }
        .flussi-h-validator .privacy { color: var(--muted); font-size: .78rem; line-height: 1.45; }
        .flussi-h-validator button { flex: 0 0 auto; padding: 12px 22px; border: 0; border-radius: 5px; color: #fff; background: var(--charcoal); font-size: .94rem; font-weight: 750; cursor: pointer; }
        .flussi-h-validator footer { display: flex; justify-content: center; align-items: center; gap: 9px; margin-top: 24px; color: #718074; font-size: .75rem; }
        .flussi-h-validator .footer-logo img { display: block; width: 125px; height: auto; }
        .flussi-h-validator--embedded { margin-top: 15px; }
        .flussi-h-validator--embedded main { width: 100%; margin: 0; padding: 28px; }
        .flussi-h-validator--embedded .card { box-shadow: none; }
        .flussi-h-validator--embedded .card-head { padding: 16px 20px; }
        .flussi-h-validator--embedded form { padding: 20px; }
        .flussi-h-validator--embedded .upload { min-height: 140px; padding: 18px; }
        .flussi-h-validator--embedded footer { margin-top: 14px; }
        @media (max-width:680px) { .flussi-h-validator main { margin: 26px auto; padding: 20px; } .flussi-h-validator--embedded main { margin: 0; padding: 16px; } .flussi-h-validator .grid { grid-template-columns: 1fr; } .flussi-h-validator .actions { align-items: stretch; flex-direction: column; } .flussi-h-validator .actions button { width: 100%; } .flussi-h-validator form, .flussi-h-validator .card-head { padding: 22px; } .flussi-h-validator--embedded form, .flussi-h-validator--embedded .card-head { padding: 18px; } .flussi-h-validator .errors { margin: 0 22px; } }
    </style>
@endpush

@section('content')
    <div class="flussi-h-validator {{ $embedded ? 'flussi-h-validator--embedded' : '' }}">
    <main>
        <section class="card">
            <div class="card-head">
                <div>
                    <h1>Validatore Flusso H</h1>
                </div>
            </div>


            @if ($errors->any())
                <div class="errors" role="alert"><strong>Controlla i file selezionati:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="post" action="{{ route('flussi-h.validate') }}" enctype="multipart/form-data">
                @csrf
                <div class="grid">
                    <label class="upload">
                        <strong>1. File H1 / ANA</strong>
                        <small>Tracciato contenente i dati anagrafici.</small>
                        <input type="file" name="h1" accept=".h1,.txt,text/plain" required>
                    </label>
                    <label class="upload">
                        <strong>2. File H2 / SAN</strong>
                        <small>Tracciato contenente i dati sanitari.</small>
                        <input type="file" name="h2" accept=".h2,.txt,text/plain" required>
                    </label>
                </div>
                <label class="options">
                    <input type="checkbox" name="include_warnings" value="1">
                    <span>Includi anche gli avvertimenti non bloccanti. Lascia deselezionato per ottenere un report con
                        i soli errori gravi.</span>
                </label>
                <div class="actions">
                    <div>
                        <div class="privacy">I file vengono elaborati durante la richiesta e non sono archiviati dal
                            software.</div>
                        <div class="notice" role="alert">
                            <strong>ATTENZIONE.</strong> Il controllo &egrave; di supporto e potrebbe non intercettare tutte
                            le anomalie o gli aggiornamenti delle specifiche regionali. L'esito non sostituisce la
                            validazione ufficiale del flusso.
                        </div>
                    </div>
                    <button type="submit">Valida</button>
                </div>
            </form>
        </section>
    </main>
    </div>
@endsection
