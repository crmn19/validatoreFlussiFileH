@extends($layout)

@push('style')
    <style>
        .flussi-h-validator { --charcoal: #4C184E; --paper: #fff; --ink: #252b2d; --muted: #6c5a6d; --line: #4C184E; --success: #28a745; --error: #9a2d2d; color: var(--ink); font-size: 16px; line-height: 1.5; }
        /* Superficie bianca "sospesa", come il pannello di lavoro nell'interfaccia di riferimento. */
        .flussi-h-validator main { width: min(1400px, calc(100% - 32px)); margin: 48px auto; padding: 40px; border-radius: 6px; background: var(--paper); box-shadow: 0 10px 30px rgba(31, 40, 42, .08); box-sizing: border-box; }
        .flussi-h-validator .page-intro { margin: 0 0 24px; }
        .flussi-h-validator .page-intro h1 { margin: 0; color: var(--charcoal); font-size: 2.35em; font-weight: 750; line-height: 1.2; }
        .flussi-h-validator .page-intro p { margin: 7px 0 0; color: var(--muted); font-size: .95em; }
        .flussi-h-validator .notice { margin: 18px 0 0; padding: 14px 16px; border-left: 4px solid #4C184E; border-radius: 4px; color: #4C184E; background: #fafafa; font-size: .9em; line-height: 1.5; }
        .flussi-h-validator .card { overflow: hidden; border: 1px solid var(--line); border-radius: 12px; background: var(--paper); box-shadow: 0 8px 24px rgba(31, 40, 42, .06); }
        .flussi-h-validator .card-head { display: flex; justify-content: space-between; gap: 18px; align-items: center; padding: 20px 28px; border-bottom: 1px solid var(--line); }
        .flussi-h-validator .card-head h2 { margin: 0; color: var(--charcoal); font-size: 2.35em; font-weight: 750; line-height: 1.2; letter-spacing: .025em; }
        .flussi-h-validator .card-head p { margin: 4px 0 0; color: var(--muted); font-size: .86em; }
        .flussi-h-validator .required-files { flex: 0 0 auto; color: var(--muted); font-size: .82em; }
        .flussi-h-validator form { padding: 30px; }
        .flussi-h-validator .upload-step { display: flex; justify-content: space-between; align-items: center; gap: 18px; margin: 0 0 22px; }
        .flussi-h-validator .upload-step-title { display: flex; min-width: 0; align-items: center; gap: 14px; color: var(--ink); font-size: 1em; font-weight: 700; }
        .flussi-h-validator .step-number { display: inline-flex; width: 42px; height: 42px; flex: 0 0 auto; justify-content: center; align-items: center; border-radius: 50%; color: #fff; background: var(--charcoal); font-size: .94em; font-weight: 700; }
        .flussi-h-validator .file-count { flex: 0 0 auto; padding: 8px 14px; border-radius: 999px; color: #526276; background: #f1f5f9; font-size: .84em; font-weight: 650; }
        .flussi-h-validator .validator-layout { display: grid; grid-template-columns: minmax(0, 1fr) 300px; gap: 20px; align-items: start; }
        .flussi-h-validator .validator-main { min-width: 0; }
        .flussi-h-validator .validator-aside { display: flex; min-width: 0; flex-direction: column; gap: 16px; margin-top: 64px; }
        .flussi-h-validator .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
        .flussi-h-validator .upload { position: relative; display: flex; min-height: 270px; padding: 22px; flex-direction: column; justify-content: flex-start; align-items: stretch; border: 1px dashed #4C184E; border-radius: 14px; background: #fafafa; text-align: left; transition: .15s ease; }
        .flussi-h-validator .upload:hover, .flussi-h-validator .upload:focus-within, .flussi-h-validator .upload.is-dragging { border-color: #4C184E; background: #f5f2f6; }
        .flussi-h-validator .upload.has-file { border: 1px solid var(--success); background: #f3fbf5; }
        .flussi-h-validator .upload.has-file:hover, .flussi-h-validator .upload.has-file:focus-within, .flussi-h-validator .upload.has-file.is-dragging { border-color: #218838; background: #edf8f0; }
        .flussi-h-validator .upload:focus-within { outline: 2px solid var(--charcoal); outline-offset: 3px; }
        .flussi-h-validator .upload input[type=file] { position: absolute; z-index: 1; inset: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }
        .flussi-h-validator .upload-head { display: flex; min-width: 0; align-items: center; gap: 12px; }
        .flussi-h-validator .upload-icon-wrap { display: inline-flex; width: 48px; height: 48px; flex: 0 0 auto; justify-content: center; align-items: center; border: 1px solid #e1e8f0; border-radius: 12px; color: var(--charcoal); background: #fff; }
        .flussi-h-validator .upload-icon { width: 25px; height: 25px; }
        .flussi-h-validator .upload-title { min-width: 0; }
        .flussi-h-validator .upload strong { display: block; margin: 0; color: var(--ink); font-size: 1em; }
        .flussi-h-validator .upload small { display: block; margin: 2px 0 0; color: var(--muted); font-size: .82em; line-height: 1.5; }
        .flussi-h-validator .extension-badge { margin-left: auto; padding: 5px 9px; border: 1px solid #e1e8f0; border-radius: 9px; color: #526276; background: #fff; font-size: .78em; font-weight: 700; }
        .flussi-h-validator .upload-content { display: flex; flex: 1 1 auto; flex-direction: column; justify-content: center; align-items: center; padding: 20px 0 2px; text-align: center; }
        .flussi-h-validator .cloud-icon { width: 48px; height: 48px; margin-bottom: 9px; color: #b78abc; }
        .flussi-h-validator .upload-field { min-width: 0; }
        .flussi-h-validator .file-format { display: flex; margin: 8px 3px 0; align-items: center; gap: 6px; color: var(--muted); font-size: .78em; }
        .flussi-h-validator .file-format svg { width: 15px; height: 15px; flex: 0 0 auto; }
        .flussi-h-validator .file-format strong { color: var(--charcoal); }
        .flussi-h-validator .drop-prompt { display: block; margin-bottom: 12px; color: var(--muted); font-size: .9em; line-height: 1.5; }
        .flussi-h-validator .browse-button { display: inline-block; padding: 8px 16px; border: 1px solid #4C184E; border-radius: 9px; color: #fff; background: #4C184E; font-size: .9em; font-weight: 700; }
        .flussi-h-validator .file-selected { display: none; min-width: 0; align-items: center; gap: 12px; margin-top: auto; padding: 13px 14px; border: 1px solid #dce9df; border-radius: 11px; background: #fff; }
        .flussi-h-validator .upload.has-file .upload-content { display: none; }
        .flussi-h-validator .upload.has-file .file-selected { display: flex; }
        .flussi-h-validator .file-check { display: inline-flex; width: 42px; height: 42px; flex: 0 0 auto; justify-content: center; align-items: center; border-radius: 9px; color: var(--success); background: #d8f5e1; }
        .flussi-h-validator .file-check svg { width: 22px; height: 22px; }
        .flussi-h-validator .file-selected-copy { min-width: 0; }
        .flussi-h-validator .file-name { max-width: 100%; overflow-wrap: anywhere; color: var(--ink); font-size: .88em; font-weight: 650; }
        .flussi-h-validator .file-selected small { color: #218838; }
        .flussi-h-validator .upload-hint { display: none; margin: 12px 2px 0; align-items: center; gap: 6px; color: var(--charcoal); font-size: .8em; font-weight: 650; }
        .flussi-h-validator .upload.has-file .upload-hint { display: flex; }
        .flussi-h-validator .upload-hint svg { width: 15px; height: 15px; flex: 0 0 auto; }
        .flussi-h-validator .errors { margin: 0 28px; padding: 15px 18px; border-left: 4px solid var(--error); border-radius: 8px; color: #7e2020; background: #fff0f0; }
        .flussi-h-validator .actions { display: flex; justify-content: flex-end; gap: 20px; align-items: center; margin-top: 24px; }
        .flussi-h-validator .options-section { margin-top: 48px; padding: 18px 20px 20px; border: 1px solid #e1e8f0; border-radius: 12px; background: #fff; }
        .flussi-h-validator--embedded .options-section { margin-top: 8px; }
        .flussi-h-validator .options-title { display: flex; align-items: center; gap: 14px; margin: 0; color: var(--ink); font-size: 1em; font-weight: 700; }
        .flussi-h-validator .options-title .step-number { width: 38px; height: 38px; font-size: .98em; }
        .flussi-h-validator .options { display: flex; align-items: flex-start; gap: 10px; margin: 16px 0 0; padding: 13px 15px; border: 1px solid #e1e8f0; border-radius: 9px; color: var(--muted); background: #fafbfc; font-size: .88em; }
        .flussi-h-validator .options input { margin-top: 4px; accent-color: var(--charcoal); }
        .flussi-h-validator .summary-card { padding: 18px; border: 1px solid #e1e8f0; border-radius: 12px; background: #fff; }
        .flussi-h-validator .summary-title { margin: 0 0 14px; color: var(--ink); font-size: 1em; font-weight: 700; }
        .flussi-h-validator .summary-files { display: flex; flex-direction: column; gap: 12px; }
        .flussi-h-validator .summary-file { display: grid; grid-template-columns: 18px minmax(0, 1fr) auto; gap: 8px; align-items: center; color: var(--muted); font-size: .78em; }
        .flussi-h-validator .summary-file-icon { display: inline-flex; width: 16px; height: 16px; justify-content: center; align-items: center; border: 1.5px solid #b8c3d1; border-radius: 50%; color: transparent; }
        .flussi-h-validator .summary-file-icon svg { display: none; width: 11px; height: 11px; }
        .flussi-h-validator .summary-file.is-ready .summary-file-icon { border-color: var(--success); color: #fff; background: var(--success); }
        .flussi-h-validator .summary-file.is-ready .summary-file-icon svg { display: block; }
        .flussi-h-validator .summary-file.is-ready [data-summary-state] { color: var(--success); font-weight: 700; }
        .flussi-h-validator .summary-file-label { color: #526276; }
        .flussi-h-validator .summary-divider { height: 1px; margin: 14px 0; background: #e7ebf0; }
        .flussi-h-validator .summary-detail { display: flex; justify-content: space-between; gap: 12px; margin-top: 10px; color: var(--muted); font-size: .76em; }
        .flussi-h-validator .summary-detail strong { color: var(--ink); font-weight: 650; text-align: right; }
        .flussi-h-validator .summary-card .actions { margin-top: 18px; }
        .flussi-h-validator .summary-card button { width: 100%; min-width: 0; min-height: 48px; padding: 12px 14px; font-size: .9em; }
        .flussi-h-validator .summary-hint { margin: 10px 0 0; color: var(--muted); font-size: .73em; text-align: center; }
        .flussi-h-validator .download-status { margin: 12px 0 0; padding: 10px 12px; border-radius: 8px; color: #236b3d; background: #eef9f1; font-size: .78em; line-height: 1.45; }
        .flussi-h-validator .download-status.is-error { color: #7e2020; background: #fff0f0; }
        .flussi-h-validator .download-status[hidden] { display: none; }
        .flussi-h-validator .aside-note { display: flex; align-items: flex-start; gap: 10px; margin: 0; padding: 12px 13px; border: 1px solid #eaddec; border-radius: 10px; color: var(--muted); background: #faf7fb; font-size: .78em; line-height: 1.5; }
        .flussi-h-validator .aside-note-icon { width: 21px; height: 21px; flex: 0 0 auto; margin-top: 1px; color: var(--charcoal); }
        .flussi-h-validator .aside-note-copy { min-width: 0; }
        .flussi-h-validator .aside-note-title { display: block; margin-bottom: 4px; color: var(--charcoal); font-size: 1em; }
        .flussi-h-validator .support-note { margin-top: 16px; }
        .flussi-h-validator button { display: inline-flex; min-width: 250px; min-height: 56px; box-sizing: border-box; flex: 0 0 auto; justify-content: center; align-items: center; gap: 10px; padding: 15px 26px; border: 0; border-radius: 8px; color: #fff; background: var(--charcoal); box-shadow: 0 5px 14px rgba(76, 24, 78, .22); font-size: 1.05em; font-weight: 750; cursor: pointer; transition: box-shadow .15s ease, transform .15s ease; }
        .flussi-h-validator button:hover { box-shadow: 0 7px 18px rgba(76, 24, 78, .28); transform: translateY(-1px); }
        .flussi-h-validator button:active { box-shadow: 0 3px 8px rgba(76, 24, 78, .2); transform: translateY(0); }
        .flussi-h-validator button:focus-visible { outline: 3px solid var(--charcoal); outline-offset: 3px; }
        .flussi-h-validator button svg { width: 18px; height: 18px; flex: 0 0 auto; }
        @keyframes validator-ready-pulse { 0%, 100% { background: var(--charcoal); box-shadow: 0 5px 14px rgba(76, 24, 78, .28); } 50% { background: var(--success); box-shadow: 0 0 0 4px rgba(40, 167, 69, .16), 0 7px 20px rgba(40, 167, 69, .35); } }
        .flussi-h-validator .summary-card button.is-ready { animation: validator-ready-pulse 1.8s ease-in-out infinite; }
        @media (prefers-reduced-motion: reduce) { .flussi-h-validator .summary-card button.is-ready { animation: none; background: var(--success); box-shadow: 0 5px 14px rgba(40, 167, 69, .28); } }
        .flussi-h-validator footer { display: flex; justify-content: center; align-items: center; gap: 9px; margin-top: 24px; color: #718074; font-size: .75em; }
        .flussi-h-validator .footer-logo img { display: block; width: 125px; height: auto; }
        .flussi-h-validator--embedded { margin-top: 0; }
        .flussi-h-validator--embedded { font-size: 15px; }
        .flussi-h-validator--embedded .card-head h2 { font-size: 1.8em; }
        .flussi-h-validator--embedded main { width: 100%; margin: 0; padding: 32px; }
        .flussi-h-validator--embedded form { padding: 24px; }
        .flussi-h-validator--embedded .card-head { padding: 14px 24px; }
        .flussi-h-validator--embedded .upload { min-height: 240px; padding: 18px; }
        .flussi-h-validator--embedded .upload-step { margin-bottom: 16px; }
        .flussi-h-validator--embedded .upload-content { padding-top: 14px; }
        .flussi-h-validator--embedded .options-section { padding: 15px 18px 18px; }
        .flussi-h-validator--embedded .options { padding: 11px 13px; }
        .flussi-h-validator--embedded .support-note { margin-top: 12px; padding: 10px 12px; }
        .flussi-h-validator--embedded .page-intro { width: min(1400px, calc(100% - 32px)); margin-right: auto; margin-left: auto; }
        .flussi-h-validator--embedded .card { width: min(1400px, calc(100% - 32px)); margin: 0 auto; box-sizing: border-box; }
        @media (max-width:980px) { .flussi-h-validator .validator-layout { grid-template-columns: 1fr; } .flussi-h-validator .validator-aside { margin-top: 0; } .flussi-h-validator .options-section { margin-top: 24px; } }
        @media (max-width:680px) { .flussi-h-validator main { margin: 26px auto; padding: 20px; } .flussi-h-validator--embedded main { margin: 0; padding: 20px; } .flussi-h-validator--embedded .page-intro, .flussi-h-validator--embedded .card { width: 100%; } .flussi-h-validator .page-intro h1 { font-size: 1.85em; } .flussi-h-validator .card-head h2 { font-size: 1.85em; } .flussi-h-validator .validator-aside, .flussi-h-validator .grid { grid-template-columns: 1fr; } .flussi-h-validator .upload { min-height: 220px; } .flussi-h-validator .upload-step { align-items: flex-start; } .flussi-h-validator .upload-step-title { gap: 10px; font-size: 1em; } .flussi-h-validator .step-number { width: 36px; height: 36px; } .flussi-h-validator .file-count { padding: 7px 10px; font-size: .76em; } .flussi-h-validator .actions { align-items: stretch; flex-direction: column; } .flussi-h-validator .actions button { width: 100%; min-width: 0; } .flussi-h-validator form, .flussi-h-validator .card-head { padding: 22px; } .flussi-h-validator .card-head { align-items: flex-start; flex-direction: column; gap: 5px; } .flussi-h-validator .errors { margin: 0 22px; } }
        @media (max-width:430px) { .flussi-h-validator .upload-step { flex-wrap: wrap; } .flussi-h-validator .file-count { margin-left: 46px; } .flussi-h-validator .upload { padding: 17px; } }
        @media (max-width:680px) { .flussi-h-validator--embedded form { padding: 18px; } .flussi-h-validator--embedded .card-head { padding: 14px 18px; } .flussi-h-validator--embedded .upload { min-height: 220px; padding: 16px; } }
    </style>
@endpush

@section('content')
    <div class="flussi-h-validator {{ $embedded ? 'flussi-h-validator--embedded' : '' }}">
    <main>
        <section class="card">
            <div class="card-head">
                <div>
                    <h2>Validazione Flussi H</h2>
                    <p>Verifica preliminare dei tracciati H1 (anagrafici) e H2 (sanitari).</p>
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
                <div class="validator-layout">
                <div class="validator-main">
                <div class="upload-step">
                    <div class="upload-step-title"><span class="step-number" aria-hidden="true">1</span><span>Carica entrambi i file del flusso</span></div>
                    <span class="file-count" data-upload-count role="status" aria-live="polite">0 di 2 caricati</span>
                </div>
                <div class="grid">
                    <div class="upload-field">
                    <label class="upload" data-drop-zone>
                        <input type="file" name="h1" accept=".h1,.txt,text/plain" aria-label="Seleziona o trascina il file H1 / ANA" required>
                        <span class="upload-head">
                            <span class="upload-icon-wrap"><svg class="upload-icon" viewBox="0 0 40 40" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4h11l8 8v22a2 2 0 0 1-2 2H12a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z"/><path d="M23 4v9h8"/><circle cx="20" cy="22" r="3.2"/><path d="M14.5 31c.7-3.2 2.5-4.8 5.5-4.8s4.8 1.6 5.5 4.8"/></svg></span>
                            <span class="upload-title"><strong>H1 · ANA</strong><small>Dati anagrafici</small></span>
                            <span class="extension-badge">.h1</span>
                        </span>
                        <span class="upload-content">
                            <svg class="cloud-icon" viewBox="0 0 40 40" fill="currentColor" aria-hidden="true"><path d="M12.4 31.5h16.2a7.4 7.4 0 0 0 1.1-14.7A10.1 10.1 0 0 0 10.6 15a8.4 8.4 0 0 0 1.8 16.5Z"/><path d="M20 28V17m0 0-4.2 4.2M20 17l4.2 4.2" fill="none" stroke="#fff" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <span class="drop-prompt">Trascina qui il file oppure</span>
                            <span class="browse-button">Sfoglia file H1</span>
                        </span>
                        <span class="file-selected" aria-live="polite"><span class="file-check"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 4.5 4.5L19 7"/></svg></span><span class="file-selected-copy"><span class="file-name"></span><small>File selezionato</small></span></span>
                        <span class="upload-hint"><svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M13.5 7.5a5.5 5.5 0 0 0-9.3-3.9L2.5 5.3"/><path d="M2.5 2.8v2.5H5M2.5 8.5a5.5 5.5 0 0 0 9.3 3.9l1.7-1.7"/><path d="M13.5 13.2v-2.5H11"/></svg>Clicca o trascina per sostituire il file</span>
                    </label>
                    <p class="file-format"><svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="8" cy="8" r="6.25"/><path d="M8 7v4M8 4.8h.01" stroke-linecap="round"/></svg><span>Formato richiesto: <strong>.h1</strong></span></p>
                    </div>
                    <div class="upload-field">
                    <label class="upload" data-drop-zone>
                        <input type="file" name="h2" accept=".h2,.txt,text/plain" aria-label="Seleziona o trascina il file H2 / SAN" required>
                        <span class="upload-head">
                            <span class="upload-icon-wrap"><svg class="upload-icon" viewBox="0 0 40 40" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4h11l8 8v22a2 2 0 0 1-2 2H12a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z"/><path d="M23 4v9h8"/><path d="M20 18v10M15 23h10"/></svg></span>
                            <span class="upload-title"><strong>H2 · SAN</strong><small>Dati sanitari</small></span>
                            <span class="extension-badge">.h2</span>
                        </span>
                        <span class="upload-content">
                            <svg class="cloud-icon" viewBox="0 0 40 40" fill="currentColor" aria-hidden="true"><path d="M12.4 31.5h16.2a7.4 7.4 0 0 0 1.1-14.7A10.1 10.1 0 0 0 10.6 15a8.4 8.4 0 0 0 1.8 16.5Z"/><path d="M20 28V17m0 0-4.2 4.2M20 17l4.2 4.2" fill="none" stroke="#fff" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <span class="drop-prompt">Trascina qui il file oppure</span>
                            <span class="browse-button">Sfoglia file H2</span>
                        </span>
                        <span class="file-selected" aria-live="polite"><span class="file-check"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 4.5 4.5L19 7"/></svg></span><span class="file-selected-copy"><span class="file-name"></span><small>File selezionato</small></span></span>
                        <span class="upload-hint"><svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M13.5 7.5a5.5 5.5 0 0 0-9.3-3.9L2.5 5.3"/><path d="M2.5 2.8v2.5H5M2.5 8.5a5.5 5.5 0 0 0 9.3 3.9l1.7-1.7"/><path d="M13.5 13.2v-2.5H11"/></svg>Clicca o trascina per sostituire il file</span>
                    </label>
                    <p class="file-format"><svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="8" cy="8" r="6.25"/><path d="M8 7v4M8 4.8h.01" stroke-linecap="round"/></svg><span>Formato richiesto: <strong>.h2</strong></span></p>
                    </div>
                </div>
                <section class="options-section" aria-labelledby="options-title">
                    <h3 class="options-title" id="options-title"><span class="step-number" aria-hidden="true">2</span><span>Parametri di analisi</span></h3>
                    <label class="options">
                        <input type="checkbox" name="include_warnings" value="1">
                        <span><strong>Includi gli avvertimenti non bloccanti</strong><br>Lascia deselezionato per ottenere un report con i soli errori gravi.</span>
                    </label>
                </section>
                <div class="aside-note support-note" role="note">
                    <svg class="aside-note-icon" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="10" cy="10" r="8"/><path d="M10 5.8v5"/><path d="M10 14.5h.01"/></svg>
                    <span class="aside-note-copy"><strong class="aside-note-title">Controllo di supporto</strong>Potrebbe non intercettare tutte le anomalie o gli aggiornamenti delle specifiche regionali. L'esito non sostituisce la validazione ufficiale del flusso.</span>
                </div>
                </div>
                <aside class="validator-aside" aria-label="Riepilogo e informazioni">
                    <section class="summary-card" aria-labelledby="summary-title">
                        <h3 class="summary-title" id="summary-title">Riepilogo</h3>
                        <div class="summary-files">
                            <div class="summary-file" data-summary-file="h1">
                                <span class="summary-file-icon"><svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 8 3 3 7-7"/></svg></span>
                                <span class="summary-file-label">File H1 · ANA</span>
                                <span data-summary-state>In attesa</span>
                            </div>
                            <div class="summary-file" data-summary-file="h2">
                                <span class="summary-file-icon"><svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 8 3 3 7-7"/></svg></span>
                                <span class="summary-file-label">File H2 · SAN</span>
                                <span data-summary-state>In attesa</span>
                            </div>
                        </div>
                        <div class="summary-divider"></div>
                        <div class="summary-detail"><span>Report</span><strong data-report-option>Solo errori gravi</strong></div>
                        <div class="summary-detail"><span>Esito</span><strong>Report Excel (.xlsx)</strong></div>
                        <div class="actions">
                            <button type="submit">
                                <span>Avvia validazione</span>
                                <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3.5 10h12M10 4.5l5.5 5.5-5.5 5.5"/></svg>
                            </button>
                        </div>
                        <p class="summary-hint" data-summary-hint>Carica entrambi i file per avviare la validazione.</p>
                        <p class="download-status" data-download-status role="status" aria-live="polite" hidden></p>
                    </section>
                    <div class="aside-note" role="note">
                        <svg class="aside-note-icon" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 2.2 16 4.4v4.4c0 4-2.5 7.1-6 9-3.5-1.9-6-5-6-9V4.4l6-2.2Z"/></svg>
                        <span class="aside-note-copy">I file vengono elaborati durante la richiesta e non sono archiviati dal software.</span>
                    </div>
                </aside>
                </div>
            </form>
        </section>
    </main>
    </div>
    <script>
        var validator = document.querySelector('.flussi-h-validator');
        var fileCount = validator && validator.querySelector('[data-upload-count]');
        var reportCheckbox = validator && validator.querySelector('input[name="include_warnings"]');
        var validationForm = validator && validator.querySelector('form');
        var downloadStatus = validator && validator.querySelector('[data-download-status]');

        function updateFileCount() {
            if (!validator || !fileCount) return;
            var inputs = validator.querySelectorAll('input[type="file"]');
            var selected = 0;
            inputs.forEach(function (input) {
                if (input.files && input.files.length) selected++;
            });
            fileCount.textContent = selected + ' di 2 caricati';
            var validateButton = validator.querySelector('.summary-card button');
            if (validateButton) validateButton.classList.toggle('is-ready', selected === 2);

            ['h1', 'h2'].forEach(function (key) {
                var input = validator.querySelector('input[name="' + key + '"]');
                var row = validator.querySelector('[data-summary-file="' + key + '"]');
                if (!input || !row) return;
                var hasFile = Boolean(input.files && input.files.length);
                row.classList.toggle('is-ready', hasFile);
                row.querySelector('[data-summary-state]').textContent = hasFile ? 'Caricato' : 'In attesa';
            });

            var summaryHint = validator.querySelector('[data-summary-hint]');
            if (summaryHint) summaryHint.textContent = selected === 2
                ? 'Pronto per avviare la validazione.'
                : 'Carica entrambi i file per avviare la validazione.';
        }

        function updateReportOption() {
            if (!validator || !reportCheckbox) return;
            var reportOption = validator.querySelector('[data-report-option]');
            if (reportOption) reportOption.textContent = reportCheckbox.checked ? 'Tutti gli errori' : 'Solo errori gravi';
        }

        if (validationForm && window.fetch && window.URL && window.URL.createObjectURL) {
            validationForm.addEventListener('submit', async function (event) {
                event.preventDefault();
                var button = validationForm.querySelector('.summary-card button');
                var buttonLabel = button && button.querySelector('span');
                var originalLabel = buttonLabel ? buttonLabel.textContent : '';

                if (downloadStatus) {
                    downloadStatus.hidden = false;
                    downloadStatus.classList.remove('is-error');
                    downloadStatus.textContent = 'Elaborazione in corso…';
                }
                if (button) {
                    button.disabled = true;
                    button.classList.remove('is-ready');
                    button.setAttribute('aria-busy', 'true');
                }
                if (buttonLabel) buttonLabel.textContent = 'Elaborazione…';

                try {
                    var response = await fetch(validationForm.action, {
                        method: 'POST',
                        body: new FormData(validationForm),
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (!response.ok) {
                        var errorPayload = await response.json().catch(function () { return {}; });
                        var messages = [];
                        if (errorPayload.errors) {
                            Object.keys(errorPayload.errors).forEach(function (key) {
                                messages = messages.concat(errorPayload.errors[key]);
                            });
                        }
                        throw new Error(messages.length ? messages.join(' ') : 'Non è stato possibile completare la validazione. Riprova.');
                    }

                    var contentType = response.headers.get('Content-Type') || '';
                    if (contentType.indexOf('spreadsheet') === -1) {
                        throw new Error('La risposta ricevuta non contiene il report Excel. Riprova.');
                    }

                    var report = await response.blob();
                    var disposition = response.headers.get('Content-Disposition') || '';
                    var filenameMatch = disposition.match(/filename\*?=(?:UTF-8''|\")?([^;\"]+)/i);
                    var filename = filenameMatch ? decodeURIComponent(filenameMatch[1].replace(/"/g, '').trim()) : 'report-errori-flusso-h.xlsx';
                    var downloadUrl = URL.createObjectURL(report);
                    var downloadLink = document.createElement('a');
                    downloadLink.href = downloadUrl;
                    downloadLink.download = filename;
                    downloadLink.style.display = 'none';
                    document.body.appendChild(downloadLink);
                    downloadLink.click();
                    downloadLink.remove();
                    window.setTimeout(function () { URL.revokeObjectURL(downloadUrl); }, 60000);

                    if (downloadStatus) downloadStatus.textContent = 'Download avviato.';
                } catch (error) {
                    if (downloadStatus) {
                        downloadStatus.classList.add('is-error');
                        downloadStatus.textContent = error.message || 'Errore durante la validazione. Riprova.';
                    }
                } finally {
                    if (button) {
                        button.disabled = false;
                        button.removeAttribute('aria-busy');
                    }
                    if (buttonLabel) buttonLabel.textContent = originalLabel;
                    updateFileCount();
                }
            });
        }

        document.querySelectorAll('.flussi-h-validator [data-drop-zone]').forEach(function (zone) {
            var input = zone.querySelector('input[type="file"]');
            var name = zone.querySelector('.file-name');

            function showFile(file) {
                name.textContent = file ? file.name : '';
                zone.classList.toggle('has-file', Boolean(file));
            }

            input.addEventListener('change', function () {
                showFile(input.files && input.files[0]);
                updateFileCount();
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

        if (reportCheckbox) reportCheckbox.addEventListener('change', updateReportOption);
    </script>
@endsection
