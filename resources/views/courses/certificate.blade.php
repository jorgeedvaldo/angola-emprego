<!DOCTYPE html>
<html>
<head>
    <title>Certificado de Conclusão</title>
    <style>
        body { font-family: sans-serif; text-align: center; padding: 50px; border: 10px solid #ddd; }
        h1 { font-size: 48px; color: #333; margin-bottom: 20px; }
        p { font-size: 24px; color: #555; }
        .name { font-size: 36px; font-weight: bold; color: #000; margin: 20px 0; border-bottom: 2px solid #333; display: inline-block; padding-bottom: 10px;}
        .course { font-size: 32px; font-weight: bold; color: #2c3e50; margin: 20px 0; }
        .date { font-size: 18px; margin-top: 50px; color: #777; }
        .logo { max-width: 200px; margin-bottom: 30px; }
    </style>
</head>
<body>
    @if(env('APP_URL') && file_exists(public_path('assets/img/logo.png')))
        <img src="{{ public_path('assets/img/logo.png') }}" class="logo">
    @else
        <h1>Angola Emprego</h1>
    @endif
    
    <p>Certificamos que</p>
    
    <div class="name">{{ auth()->user()->name }}</div>
    
    <p>concluiu com êxito o curso de</p>
    
    <div class="course">{{ $course->title }}</div>
    
    <p>Data de Conclusão: {{ now()->format('d/m/Y') }}</p>
    
    <div class="date">
        Certificado gerado por Angola Emprego
    </div>
</body>
</html>
