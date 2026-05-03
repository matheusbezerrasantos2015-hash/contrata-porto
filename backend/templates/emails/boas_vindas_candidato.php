<?php
/** @var string $nome */
$baseUrl = "http://localhost/ContrataPorto";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bem-vindo ao ContrataPorto</title>
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
                            <p>Oi, <strong><?php echo htmlspecialchars($nome); ?></strong>! Que bom ter você aqui 😊</p>
                            
                            <p>A gente criou o ContrataPorto com um objetivo simples: conectar as pessoas certas às melhores oportunidades de Porto Ferreira e região. E agora você faz parte disso.</p>
                            
                            <p>Seu perfil já está ativo e você pode começar a explorar as vagas agora mesmo. Uma dica: complete seu perfil com LinkedIn e currículo — isso aumenta muito suas chances de ser notado pelas empresas.</p>
                            
                            <div style="text-align: center; margin: 30px 0;">
                                <a href="<?php echo $baseUrl; ?>/frontend/pages/jobs.html" style="background-color: #4f46e5; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block;">Explorar vagas agora →</a>
                            </div>
                            
                            <p>Se tiver qualquer dúvida, é só responder este email. A gente lê tudo. 💙</p>
                            
                            <p style="margin-top: 40px; border-top: 1px solid #f3f4f6; padding-top: 20px; font-size: 14px; color: #6b7280;">
                                Boa sorte nas candidaturas!<br>
                                <strong>Ana Beatriz</strong> — Time de Candidatos<br>
                                ContrataPorto • Porto Ferreira - SP
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
