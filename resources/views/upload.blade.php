<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Validatore Flusso H</title>
    <style>
        :root { color-scheme:light; --charcoal:#3f484a; --paper:#fff; --ink:#252b2d; --muted:#687174; --line:#d9ddde; --error:#9a2d2d; }
        * { box-sizing:border-box; }
        body { margin:0; min-height:100vh; color:var(--ink); font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif; background:#f4f5f5; }
        .topbar { height:5px; background:var(--charcoal); }
        main { width:min(900px,calc(100% - 32px)); margin:48px auto; }
        .eyebrow { color:var(--muted); font-size:.76rem; font-weight:700; letter-spacing:.11em; text-transform:uppercase; }
        h1 { margin:10px 0 28px; color:var(--charcoal); font-size:clamp(2rem,5vw,3rem); font-weight:650; letter-spacing:-.03em; }
        .intro { max-width:700px; margin:0 0 30px; color:var(--muted); font-size:1.05rem; line-height:1.65; }
        .card { overflow:hidden; border:1px solid var(--line); border-radius:8px; background:var(--paper); box-shadow:0 8px 24px rgba(31,40,42,.06); }
        .card-head { display:flex; gap:18px; align-items:center; padding:24px 28px; border-bottom:1px solid var(--line); }
        .badge { display:grid; width:46px; height:46px; flex:0 0 auto; place-items:center; border-radius:6px; color:#fff; background:var(--charcoal); font-weight:800; }
        .card-head h2 { margin:0 0 4px; font-size:1.14rem; }
        .card-head p { margin:0; color:var(--muted); font-size:.9rem; }
        form { padding:28px; }
        .grid { display:grid; grid-template-columns:1fr 1fr; gap:18px; }
        .upload { position:relative; min-height:175px; padding:22px; border:1px dashed #aeb5b7; border-radius:6px; background:#fafafa; transition:.15s ease; }
        .upload:hover,.upload:focus-within { border-color:#687174; background:#f5f6f6; }
        .upload strong { display:block; margin-bottom:7px; color:var(--charcoal); font-size:1rem; }
        .upload small { display:block; margin-bottom:20px; color:var(--muted); line-height:1.5; }
        input[type=file] { width:100%; color:var(--muted); font-size:.86rem; }
        input[type=file]::file-selector-button { margin-right:10px; padding:9px 12px; border:1px solid #d3d7d8; border-radius:5px; color:var(--charcoal); background:#eceeef; font-weight:700; cursor:pointer; }
        .errors { margin:0 28px; padding:15px 18px; border-left:4px solid var(--error); border-radius:8px; color:#7e2020; background:#fff0f0; }
        .errors ul { margin:5px 0 0; padding-left:20px; }
        .actions { display:flex; justify-content:space-between; gap:20px; align-items:center; margin-top:25px; }
        .options { display:flex; align-items:flex-start; gap:9px; margin-top:20px; color:var(--muted); font-size:.86rem; }
        .options input { margin-top:3px; accent-color:var(--charcoal); }
        .privacy { color:var(--muted); font-size:.78rem; line-height:1.45; }
        button { flex:0 0 auto; padding:12px 22px; border:0; border-radius:5px; color:#fff; background:var(--charcoal); font-size:.94rem; font-weight:750; cursor:pointer; }
        button:hover { background:#2f3739; }
        footer { display:flex; justify-content:center; align-items:center; gap:9px; margin-top:24px; color:#718074; font-size:.75rem; }
        .footer-logo { display:inline-flex; align-items:center; }
        .footer-logo img { display:block; width:125px; height:auto; }
        @media (max-width:680px) { main{margin:26px auto}.grid{grid-template-columns:1fr}.actions{align-items:stretch;flex-direction:column}.actions button{width:100%}form,.card-head{padding:22px}.errors{margin:0 22px} }
    </style>
</head>
<body>
<div class="topbar"></div>
<main>
    <div class="eyebrow">Regione Campania · Assistenza riabilitativa</div>
    <h1>Validatore Flusso H</h1>


    
    <section class="card">
        <div class="card-head">
            <div class="badge">H</div>
            <div><h2>Nuova validazione</h2></div>
        </div>

        
        @if ($errors->any())
            <div class="errors" role="alert"><strong>Controlla i file selezionati:</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
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
                <span>Includi anche gli avvertimenti non bloccanti. Lascia deselezionato per ottenere un Excel con i soli errori gravi.</span>
            </label>
            <div class="actions">
                <div class="privacy">I file vengono elaborati durante la richiesta e non sono archiviati dal pacchetto.</div>
                <button type="submit">Valida</button>
            </div>
        </form>
    </section>
    <footer>
        <span>Powered by</span>
        <a class="footer-logo" href="https://www.softwareengine.it/" target="_blank" rel="noopener noreferrer" aria-label="Visita il sito SoftwareEngine">
            <img src="{{ route('flussi-h.brand-logo') }}" alt="SoftwareEngine S.r.l.">
        </a>
    </footer>
</main>
</body>
</html>
