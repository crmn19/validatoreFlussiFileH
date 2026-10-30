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
        .flussi-h-validator .upload { position: relative; display: flex; min-height: 225px; padding: 22px; flex-direction: column; justify-content: center; align-items: center; border: 1px dashed #4C184E; border-radius: 14px; background: #fafafa; text-align: center; transition: .15s ease; }
        .flussi-h-validator .upload:hover, .flussi-h-validator .upload:focus-within, .flussi-h-validator .upload.is-dragging { border-color: #4C184E; background: #f5f6f6; }
        .flussi-h-validator .upload:focus-within { outline: 2px solid var(--charcoal); outline-offset: 3px; }
        .flussi-h-validator .upload input[type=file] { position: absolute; z-index: 1; inset: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }
        .flussi-h-validator .upload-icon { width: 38px; height: 38px; margin-bottom: 13px; color: var(--muted); }
        .flussi-h-validator .upload strong { display: block; margin-bottom: 7px; color: var(--charcoal); font-size: 1.05rem; }
        .flussi-h-validator .upload small { display: block; margin-bottom: 15px; color: var(--muted); line-height: 1.5; }
        .flussi-h-validator .upload-field { min-width: 0; }
        .flussi-h-validator .file-format { display: flex; margin: 8px 3px 0; align-items: center; gap: 6px; color: var(--muted); font-size: .78rem; }
        .flussi-h-validator .file-format svg { width: 15px; height: 15px; flex: 0 0 auto; }
        .flussi-h-validator .file-format strong { color: var(--charcoal); }
        .flussi-h-validator .drop-prompt { display: block; margin-bottom: 12px; color: var(--muted); line-height: 1.5; }
        .flussi-h-validator .browse-button { display: inline-block; padding: 8px 16px; border: 1px solid #4C184E; border-radius: 9px; color: #fff; background: #4C184E; font-weight: 700; }
        .flussi-h-validator .file-name { display: none; max-width: 100%; margin-top: 12px; overflow-wrap: anywhere; color: var(--muted); font-size: .86rem; }
        .flussi-h-validator .upload.has-file .file-name { display: block; }
        .flussi-h-validator .errors { margin: 0 28px; padding: 15px 18px; border-left: 4px solid var(--error); border-radius: 8px; color: #7e2020; background: #fff0f0; }
        .flussi-h-validator .actions { display: flex; justify-content: space-between; gap: 20px; align-items: center; margin-top: 25px; }
        .flussi-h-validator .options { display: flex; align-items: flex-start; gap: 9px; margin-top: 20px; color: var(--muted); font-size: .86rem; }
        .flussi-h-validator .privacy { color: var(--muted); font-size: .78rem; line-height: 1.45; }
        .flussi-h-validator button { flex: 0 0 auto; padding: 12px 22px; border: 0; border-radius: 5px; color: #fff; background: var(--charcoal); font-size: .94rem; font-weight: 750; cursor: pointer; }
        .flussi-h-validator footer { display: flex; justify-content: center; align-items: center; gap: 9px; margin-top: 24px; color: #718074; font-size: .75rem; }
        .flussi-h-validator .footer-logo img { display: block; width: 125px; height: auto; }
        @media (max-width:680px) { .flussi-h-validator main { margin: 26px auto; padding: 20px; } .flussi-h-validator .grid { grid-template-columns: 1fr; } .flussi-h-validator .actions { align-items: stretch; flex-direction: column; } .flussi-h-validator .actions button { width: 100%; } .flussi-h-validator form, .flussi-h-validator .card-head { padding: 22px; } .flussi-h-validator .errors { margin: 0 22px; } }
    </style>
@endpush

@section('content')
    <div class="flussi-h-validator {{ $embedded ? 'flussi-h-validator--embedded' : '' }}">
    <main>
        <section class="card">
            <div class="card-head">
                <div>
                    <h1>Validatore Flussi H</h1>
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
                    <div class="upload-field">
                    <label class="upload" data-drop-zone>
                        <input type="file" name="h1" accept=".h1,.txt,text/plain" aria-label="Seleziona o trascina il file H1 / ANA" required>
                        <svg class="upload-icon" viewBox="0 0 40 40" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M12 4h11l8 8v22a2 2 0 0 1-2 2H12a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z"/><path d="M23 4v9h8"/><circle cx="20" cy="22" r="3.2"/><path d="M14.5 31c.7-3.2 2.5-4.8 5.5-4.8s4.8 1.6 5.5 4.8"/>
                        </svg>
                        <strong>H1 / ANA</strong>
                        <small>Dati anagrafici</small>
                        <span class="drop-prompt">Trascina il file qui</span>
                        <span class="browse-button">Sfoglia file</span>
                        <span class="file-name" aria-live="polite"></span>
                    </label>
                    <p class="file-format"><svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="8" cy="8" r="6.25"/><path d="M8 7v4M8 4.8h.01" stroke-linecap="round"/></svg><span>Formato richiesto: <strong>.h1</strong></span></p>
                    </div>
                    <div class="upload-field">
                    <label class="upload" data-drop-zone>
                        <input type="file" name="h2" accept=".h2,.txt,text/plain" aria-label="Seleziona o trascina il file H2 / SAN" required>
                        <svg class="upload-icon" viewBox="0 0 40 40" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M12 4h11l8 8v22a2 2 0 0 1-2 2H12a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z"/><path d="M23 4v9h8"/><path d="M20 18v10M15 23h10"/>
                        </svg>
                        <strong>H2 / SAN</strong>
                        <small>Dati sanitari</small>
                        <span class="drop-prompt">Trascina il file qui</span>
                        <span class="browse-button">Sfoglia file</span>
                        <span class="file-name" aria-live="polite"></span>
                    </label>
                    <p class="file-format"><svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="8" cy="8" r="6.25"/><path d="M8 7v4M8 4.8h.01" stroke-linecap="round"/></svg><span>Formato richiesto: <strong>.h2</strong></span></p>
                    </div>
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
    <script>
        document.querySelectorAll('.flussi-h-validator [data-drop-zone]').forEach(function (zone) {
            var input = zone.querySelector('input[type="file"]');
            var name = zone.querySelector('.file-name');

            function showFile(file) {
                name.textContent = file ? file.name : '';
                zone.classList.toggle('has-file', Boolean(file));
            }

            input.addEventListener('change', function () {
                showFile(input.files && input.files[0]);
            });
            zone.addEventListener('dragover', function (event) {
                event.preventDefault();
                zone.classList.add('is-dragging');
            });
            zone.addEventListener('dragleave', function (event) {
                if (!zone.contains(event.relatedTarget)) zone.classList.remove('is-dragging');
            });
            zone.addEventListener('drop', function (event) {
                event.preventDefault();
                zone.classList.remove('is-dragging');
                var file = event.dataTransfer.files && event.dataTransfer.files[0];
                if (!file) return;

                var transfer = new DataTransfer();
                transfer.items.add(file);
                input.files = transfer.files;
                input.dispatchEvent(new Event('change', { bubbles: true }));
            });
        });
    </script>
@endsection
