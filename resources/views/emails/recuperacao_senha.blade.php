<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 8px; }
        .btn { display: inline-block; padding: 12px 24px; background-color: #4f46e5; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: bold; margin: 20px 0; }
        .footer { font-size: 12px; color: #888; margin-top: 30px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Oi, {{ $nome }}!</h2>
        <p>Recebemos um pedido para redefinir sua senha no <strong>ContrataPorto</strong>.</p>
        <p>Para criar uma nova senha, clique no botão abaixo:</p>
        
        <a href="{{ $resetLink }}" class="btn">Criar nova senha</a>
        
        <p>Este link é válido por <strong>1 hora</strong>.</p>
        <p>Se você não pediu isso, pode ignorar este e-mail tranquilamente. Sua senha atual não será alterada.</p>
        
        <div class="footer">
            <p>Atenciosamente,<br>Equipe ContrataPorto</p>
        </div>
    </div>
</body>
</html>
