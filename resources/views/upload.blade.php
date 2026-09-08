<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Validatore Flusso H</title>
    <style>
        :root { color-scheme: light; --navy:#17324d; --blue:#176b87; --paper:#fff; --ink:#182430; --muted:#607080; --line:#dbe3ea; --error:#a52a2a; }
        * { box-sizing:border-box; }
        body { margin:0; min-height:100vh; color:var(--ink); font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif; background:linear-gradient(145deg,#edf4f7 0%,#f8fafb 55%,#e8f0f4 100%); }
        .topbar { height:8px; background:linear-gradient(90deg,var(--navy),var(--blue)); }
        main { width:min(900px,calc(100% - 32px)); margin:56px auto; }
        .eyebrow { color:var(--blue); font-size:.78rem; font-weight:800; letter-spacing:.13em; text-transform:uppercase; }
        h1 { margin:10px 0 12px; color:var(--navy); font-family:Georgia,"Times New Roman",serif; font-size:clamp(2rem,5vw,3.4rem); font-weight:600; letter-spacing:-.03em; }
        .intro { max-width:700px; margin:0 0 30px; color:var(--muted); font-size:1.05rem; line-height:1.65; }
        .card { overflow:hidden; border:1px solid rgba(23,50,77,.12); border-radius:18px; background:rgba(255,255,255,.94); box-shadow:0 24px 60px rgba(23,50,77,.12); }
        .card-head { display:flex; gap:18px; align-items:center; padding:24px 28px; border-bottom:1px solid var(--line); }
        .badge { display:grid; width:48px; height:48px; flex:0 0 auto; place-items:center; border-radius:13px; color:#fff; background:var(--navy); font-weight:800; }
        .card-head h2 { margin:0 0 4px; font-size:1.14rem; }
        .card-head p { margin:0; color:var(--muted); font-size:.9rem; }
        form { padding:28px; }
        .grid { display:grid; grid-template-columns:1fr 1fr; gap:18px; }
        .upload { position:relative; min-height:175px; padding:22px; border:1.5px dashed #aebdca; border-radius:14px; background:#f9fbfc; transition:.2s ease; }
        .upload:hover,.upload:focus-within { border-color:var(--blue); background:#f1f8fa; transform:translateY(-1px); }
        .upload strong { display:block; margin-bottom:7px; color:var(--navy); font-size:1rem; }
        .upload small { display:block; margin-bottom:20px; color:var(--muted); line-height:1.5; }
        input[type=file] { width:100%; color:var(--muted); font-size:.86rem; }
        input[type=file]::file-selector-button { margin-right:10px; padding:9px 12px; border:0; border-radius:8px; color:var(--navy); background:#e2edf2; font-weight:700; cursor:pointer; }
        .errors { margin:0 28px; padding:15px 18px; border-left:4px solid var(--error); border-radius:8px; color:#7e2020; background:#fff0f0; }
        .errors ul { margin:5px 0 0; padding-left:20px; }
        .actions { display:flex; justify-content:space-between; gap:20px; align-items:center; margin-top:25px; }
        .privacy { color:var(--muted); font-size:.78rem; line-height:1.45; }
        button { flex:0 0 auto; padding:13px 22px; border:0; border-radius:10px; color:#fff; background:var(--blue); box-shadow:0 8px 20px rgba(23,107,135,.22); font-size:.94rem; font-weight:800; cursor:pointer; }
        button:hover { background:#125b74; }
        footer { margin-top:20px; color:#708090; text-align:center; font-size:.78rem; }
        @media (max-width:680px) { main{margin:30px auto}.grid{grid-template-columns:1fr}.actions{align-items:stretch;flex-direction:column}.actions button{width:100%}form,.card-head{padding:22px}.errors{margin:0 22px} }
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
            <div class="actions">
                <div class="privacy">I file vengono elaborati durante la richiesta e non sono archiviati dal pacchetto.</div>
                <button type="submit">Valida</button>
            </div>
        </form>
    </section>
    <footer>Il report distingue errori gravi e avvertimenti regionali.</footer>
</main>
</body>
</html>
