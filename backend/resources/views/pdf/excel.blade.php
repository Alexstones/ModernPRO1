<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <title>{{ $title ?? 'Documento' }}</title>
  <style>
    @page { size: A4; margin: 12mm; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 11pt; color: #111827; }
    h1 { font-size: 16pt; margin: 0 0 10mm 0; }
    table { border-collapse: collapse; width: 100%; }
    td, th { border: .2mm solid #ddd; padding: 6px 8px; }
    th { background: #f3f4f6; }
  </style>
</head>
<body>
  <h1>{{ $title ?? 'Documento' }}</h1>
  {!! $table !!}
</body>
</html>
