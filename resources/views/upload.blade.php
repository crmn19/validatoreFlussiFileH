@extends($layout)

@push('style')
    <style>
        .flussi-h-validator { --charcoal: #3f484a; --paper: #fff; --ink: #252b2d; --muted: #687174; --line: #d9ddde; --error: #9a2d2d; color: var(--ink); }
        .flussi-h-validator main { width: min(900px, calc(100% - 32px)); margin: 48px auto; }
        .flussi-h-validator .eyebrow { color: var(--muted); font-size: .76rem; font-weight: 700; letter-spacing: .11em; text-transform: uppercase; }
        .flussi-h-validator h1 { margin: 10px 0 28px; color: var(--charcoal); font-size: clamp(2rem, 5vw, 3rem); font-weight: 650; letter-spacing: -.03em; }
        .flussi-h-validator .card { overflow: hidden; border: 1px solid var(--line); border-radius: 8px; background: var(--paper); box-shadow: 0 8px 24px rgba(31, 40, 42, .06); }
        .flussi-h-validator .card-head { display: flex; gap: 18px; align-items: center; padding: 24px 28px; border-bottom: 1px solid var(--line); }
        .flussi-h-validator .badge { display: grid; width: 46px; height: 46px; flex: 0 0 auto; place-items: center; border-radius: 6px; color: #fff; background: var(--charcoal); font-weight: 800; }
        .flussi-h-validator .card-head h2 { margin: 0; font-size: 1.14rem; }
        .flussi-h-validator form { padding: 28px; }
        .flussi-h-validator .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
        .flussi-h-validator .upload { position: relative; min-height: 175px; padding: 22px; border: 1px dashed #aeb5b7; border-radius: 6px; background: #fafafa; transition: .15s ease; }
        .flussi-h-validator .upload:hover, .flussi-h-validator .upload:focus-within { border-color: #687174; background: #f5f6f6; }
        .flussi-h-validator .upload strong { display: block; margin-bottom: 7px; color: var(--charcoal); font-size: 1rem; }
        .flussi-h-validator .upload small { display: block; margin-bottom: 20px; color: var(--muted); line-height: 1.5; }
        .flussi-h-validator input[type=file] { width: 100%; color: var(--muted); font-size: .86rem; }
        .flussi-h-validator .errors { margin: 0 28px; padding: 15px 18px; border-left: 4px solid var(--error); border-radius: 8px; color: #7e2020; background: #fff0f0; }
        .flussi-h-validator .actions { display: flex; justify-content: space-between; gap: 20px; align-items: center; margin-top: 25px; }
        .flussi-h-validator .options { display: flex; align-items: flex-start; gap: 9px; margin-top: 20px; color: var(--muted); font-size: .86rem; }
        .flussi-h-validator .privacy { color: var(--muted); font-size: .78rem; line-height: 1.45; }
        .flussi-h-validator button { flex: 0 0 auto; padding: 12px 22px; border: 0; border-radius: 5px; color: #fff; background: var(--charcoal); font-size: .94rem; font-weight: 750; cursor: pointer; }
        .flussi-h-validator footer { display: flex; justify-content: center; align-items: center; gap: 9px; margin-top: 24px; color: #718074; font-size: .75rem; }
        .flussi-h-validator .footer-logo img { display: block; width: 125px; height: auto; }
        .flussi-h-validator--embedded main { width: 100%; margin: 0; }
        .flussi-h-validator--embedded h1 { margin: 6px 0 20px; font-size: 2rem; }
        .flussi-h-validator--embedded .card { box-shadow: none; }
        .flussi-h-validator--embedded .card-head { padding: 16px 20px; }
        .flussi-h-validator--embedded form { padding: 20px; }
        .flussi-h-validator--embedded .upload { min-height: 140px; padding: 18px; }
        .flussi-h-validator--embedded footer { margin-top: 14px; }
        @media (max-width:680px) { .flussi-h-validator main { margin: 26px auto; } .flussi-h-validator--embedded main { margin: 0; } .flussi-h-validator .grid { grid-template-columns: 1fr; } .flussi-h-validator .actions { align-items: stretch; flex-direction: column; } .flussi-h-validator .actions button { width: 100%; } .flussi-h-validator form, .flussi-h-validator .card-head { padding: 22px; } .flussi-h-validator--embedded form, .flussi-h-validator--embedded .card-head { padding: 18px; } .flussi-h-validator .errors { margin: 0 22px; } }
    </style>
@endpush

@section('content')
    <div class="flussi-h-validator {{ $embedded ? 'flussi-h-validator--embedded' : '' }}">
    <main>
        <div class="eyebrow">Regione Campania · Assistenza riabilitativa</div>
        <h1>Validatore Flusso H</h1>



        <section class="card">
            <div class="card-head">
                <div class="badge">H</div>
                <div>
                    <h2>Nuova validazione</h2>
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
                    <div class="privacy">I file vengono elaborati durante la richiesta e non sono archiviati dal
                        software.</div>
                    <button type="submit">Valida</button>
                </div>
            </form>
        </section>
        <footer>
            <span>Powered by</span>
            <a class="footer-logo" href="https://www.softwareengine.it/" target="_blank" rel="noopener noreferrer"
                aria-label="Visita il sito SoftwareEngine">
                <img src="{{ route('flussi-h.brand-logo') }}" alt="SoftwareEngine S.r.l.">
            </a>
        </footer>
    </main>
    </div>
@endsection
