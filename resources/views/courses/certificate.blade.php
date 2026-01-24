<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Certificado - {{ $course->title }}</title>
    <style>
        @page {
            margin: 0;
            size: A4 landscape;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: 'Times New Roman', serif;
            background-color: #fff;
            width: 100%;
            height: 100%;
            background-image: url("{{ public_path('assets/img/certificate_bg.png') }}");
            background-size: 100% 100%;
            background-repeat: no-repeat;
            position: relative;
            color: #333;
        }
        .container {
            text-align: center;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 80%;
        }
        .title {
            font-size: 50px;
            font-weight: bold;
            color: #d4af37; /* Gold color approx */
            text-transform: uppercase;
            margin-bottom: 20px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
        }
        .content {
            font-size: 24px;
            line-height: 1.6;
            margin-bottom: 30px;
            font-style: italic;
        }
        .name {
            font-weight: bold;
            font-size: 32px;
            margin: 0 5px;
            display: inline-block;
            border-bottom: 1px solid #333;
            min-width: 300px;
        }
        .course-name {
            font-weight: bold;
            font-size: 28px;
        }
        .date {
            font-weight: bold;
        }
        .footer {
            margin-top: 50px;
            font-size: 14px;
            color: #666;
        }
        .logo {
            margin-bottom: 30px;
        }
        /* Fallback for background if image load fails in DOMPDF (sometimes happens with local paths) */
        /* But we prefer the image provided */
    </style>
</head>
<body>
    <div class="container">
        <!-- Logo is part of background usually, but if independent: -->
        <!-- <img src="{{ public_path('assets/img/logo.svg') }}" alt="Angola Emprego" height="60" class="logo"> -->

        <!-- Spacing to align with the background design -->
        <div style="height: 150px;"></div> 

        <!-- If the background image already HAS the text "CERTIFICADO DE CONCLUSAO", we might not need this.
             Looking at the user image, it has "CERTIFICADO DE CONCLUSÃO". -->
        
        <!-- The user image seems to have the blue border, logo, and title "CERTIFICADO DE CONCLUSÃO" hardcoded. 
             So we just need the dynamic text. -->
        
        <div class="content" style="margin-top: 20px; font-family: 'Times New Roman', serif;">
            Este certificado é concedido a <br>
            <span class="name">{{ Auth::user()->name }}</span><br>
            pela conclusão bem-sucedida do curso/programa<br>
            <span class="course-name">{{ $course->title }}</span><br>
            em <span class="date">{{ now()->format('d/m/Y') }}</span>.
        </div>
        
        <div class="footer">
            angolaemprego.com
        </div>
    </div>
</body>
</html>
