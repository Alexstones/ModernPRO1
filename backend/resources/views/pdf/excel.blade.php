<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { 
            font-family: Arial, Helvetica, sans-serif; 
            font-size: 12px; 
        }
        table { border-collapse: collapse; width: 100%; }
        td, th { border:1px solid #000; padding:6px; }
        h1 { font-size:16px; margin-bottom:10px; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    {!! $table !!}
</body>
</html>