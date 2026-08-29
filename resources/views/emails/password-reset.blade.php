<!DOCTYPE html>
<html lang="pt-AO">
<body style="margin:0;padding:24px;background:#f3f5f8;font-family:Arial,sans-serif;color:#202833;">
    <div style="max-width:600px;margin:0 auto;background:#fff;padding:36px;border-radius:12px;">
        <h1 style="font-size:24px;margin:0 0 18px;">Redefinir a sua senha</h1>
        <p>Olá, {{ $user->name }}.</p>
        <p>Recebemos um pedido para redefinir a senha da sua conta.</p>
        <p style="margin:28px 0;">
            <a href="{{ $url }}" style="display:inline-block;padding:13px 22px;border-radius:8px;background:#2557a7;color:#fff;text-decoration:none;font-weight:bold;">Criar nova senha</a>
        </p>
        <p style="color:#6b7280;font-size:13px;">Este link expira em 60 minutos. Se não fez este pedido, pode ignorar esta mensagem.</p>
    </div>
</body>
</html>
