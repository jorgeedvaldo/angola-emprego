<!DOCTYPE html>
<html lang="pt-AO">
<body style="margin:0;padding:24px;background:#f3f5f8;font-family:Arial,sans-serif;color:#202833;">
    <div style="max-width:600px;margin:0 auto;background:#fff;padding:36px;border-radius:12px;">
        <h1 style="font-size:24px;margin:0 0 18px;">Confirme o email da sua empresa</h1>
        <p>Olá, {{ $user->name }}.</p>
        <p>Confirme este endereço de email para continuar o cadastro da empresa no Angola Emprego.</p>
        <p style="margin:28px 0;">
            <a href="{{ $url }}" style="display:inline-block;padding:13px 22px;border-radius:8px;background:#2557a7;color:#fff;text-decoration:none;font-weight:bold;">Confirmar email</a>
        </p>
        <p style="color:#6b7280;font-size:13px;">Este link expira em 60 minutos. Se não criou esta conta, ignore esta mensagem.</p>
    </div>
</body>
</html>
