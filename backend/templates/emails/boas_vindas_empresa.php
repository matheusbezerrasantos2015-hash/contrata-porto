<?php
/** @var string $nome_fantasia */
$baseUrl = "http://localhost/ContrataPorto";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sua empresa no ContrataPorto</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f8f9fa; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 20px 0;">
        <tr>
            <td align="center">
                <table border="0" cellpadding="0" cellspacing="0" width="560" style="background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                    <!-- Header -->
                    <tr>
                        <td align="center" style="padding: 40px 0 20px 0;">
                            <img src="<?php echo $baseUrl; ?>/frontend/assets/textlogo.png" alt="ContrataPorto" width="180" style="display: block;">
                        </td>
                    </tr>
                    <!-- Content -->
                    <tr>
                        <td style="padding: 20px 40px; color: #374151; font-size: 16px; line-height: 1.6;">
                            <p>Olá, <strong><?php echo htmlspecialchars($nome_fantasia); ?></strong>! Bem-vindos ao ContrataPorto 🙌</p>
                            
                            <p>Estamos muito felizes em ter a <?php echo htmlspecialchars($nome_fantasia); ?> na nossa plataforma. A gente sabe que contratar a pessoa certa faz toda a diferença — e é exatamente isso que a gente quer ajudar vocês a fazer.</p>
                            
                            <p>Seu painel está pronto. Você já pode publicar sua primeira vaga agora mesmo e começar a receber candidaturas de pessoas da região.</p>
                            
                            <div style="text-align: center; margin: 30px 0;">
                                <a href="<?php echo $baseUrl; ?>/frontend/pages/settings-empresa.html" style="background-color: #4f46e5; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block;">Publicar primeira vaga →</a>
                            </div>
                            
                            <p>Uma dica do nosso time: vagas com descrição detalhada recebem até 3x mais candidaturas. Vale caprichar! 😉</p>
                            
                            <p>Qualquer dúvida, pode responder este email. A gente lê tudo. 💙</p>
                            
                            <p style="margin-top: 40px; border-top: 1px solid #f3f4f6; padding-top: 20px; font-size: 14px; color: #6b7280;">
                                Carlos — Time de Empresas<br>
                                <strong>ContrataPorto</strong> • Porto Ferreira - SP
                            </p>
                        </td>
                    </tr>
                </table>
                <!-- Footer -->
                <table border="0" cellpadding="0" cellspacing="0" width="560">
                    <tr>
                        <td align="center" style="padding: 20px; color: #9ca3af; font-size: 12px;">
                            © <?php echo date('Y'); ?> ContrataPorto • Porto Ferreira - SP
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
