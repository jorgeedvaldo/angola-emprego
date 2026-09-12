<!DOCTYPE html>
<html lang="{{ app()->getLocale() === 'pt' ? 'pt-AO' : app()->getLocale() }}">
<body style="margin:0;padding:24px;background:#f3f5f8;font-family:Arial,sans-serif;color:#202833;">
    <div style="max-width:600px;margin:0 auto;background:#fff;padding:36px;border-radius:12px;">
        <h1 style="font-size:24px;margin:0 0 18px;">{{ __('site.emails.senha_titulo') }}</h1>
        <p>{{ __('site.emails.ola', ['nome' => $user->name]) }}</p>
        <p>{{ __('site.emails.senha_texto', ['conta' => $user->isCompany() ? __('site.emails.senha_conta_empresa') : __('site.emails.senha_conta_candidato')]) }}</p>
        <p style="margin:28px 0;">
            <a href="{{ $url }}" style="display:inline-block;padding:13px 22px;border-radius:8px;background:#2557a7;color:#fff;text-decoration:none;font-weight:bold;">{{ __('site.emails.senha_botao') }}</a>
        </p>
        <p style="color:#6b7280;font-size:13px;">{{ __('site.emails.senha_rodape') }}</p>
    </div>
</body>
</html>
