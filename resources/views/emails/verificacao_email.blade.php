<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .container { border: 1px solid #e0e0e0; border-radius: 8px; padding: 40px; background-color: #ffffff; }
        .logo { font-size: 24px; font-weight: bold; color: #007bff; margin-bottom: 20px; }
        .title { font-size: 20px; font-weight: bold; margin-bottom: 20px; }
        .code-container { background-color: #f8f9fa; border: 2px dashed #007bff; border-radius: 8px; padding: 20px; text-align: center; margin: 30px 0; }
        .code { font-size: 36px; font-weight: bold; letter-spacing: 10px; color: #007bff; margin: 0; }
        .footer { font-size: 12px; color: #777; margin-top: 30px; text-align: center; }
        .expiry { color: #dc3545; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">ContrataPorto</div>
        <div class="title">Confirme seu e-mail</div>
        <p>Olá, <strong>{{ $nome }}</strong>!</p>
        <p>Obrigado por se cadastrar no ContrataPorto. Para ativar sua conta e começar a usar a plataforma, utilize o código de verificação abaixo:</p>
        
        <div class="code-container">
            <p class="code">{{ $code }}</p>
        </div>
        
        <p>Este código expira em <span class="expiry">15 minutos</span>.</p>
        <p>Se você não solicitou este cadastro, pode ignorar este e-mail com segurança.</p>
        
        <div class="footer">
            &copy; {{ date('Y') }} ContrataPorto. Todos os direitos reservados.
        </div>
    </div>
</body>
</html>
