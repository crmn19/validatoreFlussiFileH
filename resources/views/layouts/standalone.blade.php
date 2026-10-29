<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SoftwareEngine | Validatore Flusso H</title>
    <link rel="icon" type="image/png" href="{{ route('flussi-h.brand-icon') }}">
    <link rel="shortcut icon" type="image/png" href="{{ route('flussi-h.brand-icon') }}">
    @stack('style')
    <style>
        body { margin: 0; min-height: 100vh; font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; background: #f4f5f5; }
        body::before { content: ''; display: block; height: 5px; background: #4C184E; }
    </style>
</head>
<body>
    @yield('content')
</body>
</html>
