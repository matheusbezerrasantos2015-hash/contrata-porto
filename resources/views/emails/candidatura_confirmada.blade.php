<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidatura Confirmada - ContrataPorto</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f7f6; }
        .container { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05); }
        .header { background-color: #0162A8; color: white; padding: 30px 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 600; }
        .content { padding: 40px 30px; }
        .content p { margin: 0 0 15px; }
        .highlight-box { background: #f8fafc; border-left: 4px solid #0162A8; padding: 15px; margin: 20px 0; border-radius: 4px; }
        .footer { background-color: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Candidatura Confirmada! 🚀</h1>
        </div>
        <div class="content">
            <p>Olá, <strong>{{ $nomeUsuario }}</strong>,</p>
            <p>Sua candidatura para a vaga foi enviada com sucesso para a empresa!</p>
            
            <div class="highlight-box">
                <p style="margin: 0;"><strong>Vaga:</strong> {{ $tituloVaga }}</p>
                <p style="margin: 5px 0 0 0;"><strong>Empresa:</strong> {{ $nomeEmpresa }}</p>
            </div>
            
            <p>A partir de agora, a empresa responsável analisará o seu perfil. Você será notificado por e-mail caso haja alguma atualização no status da sua candidatura.</p>
            
            <p>Boa sorte no processo seletivo!</p>
            
            <p style="margin-top: 30px;">Abraços,<br>Equipe ContrataPorto</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} ContrataPorto. Todos os direitos reservados.</p>
            <p>Este é um e-mail automático, por favor não responda.</p>
        </div>
    </div>
</body>
</html>
