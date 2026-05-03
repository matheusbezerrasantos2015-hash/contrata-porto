<?php
/** @var string $nome */
/** @var string $resetLink */
$baseUrl = "http://localhost/ContrataPorto";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperação de Senha — ContrataPorto</title>
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
                            <p>Oi, <strong><?php echo htmlspecialchars($nome); ?></strong>! Tudo bem? 🔐</p>

                            <p>Recebemos um pedido para redefinir a senha da sua conta no ContrataPorto. Se foi você, clique no botão abaixo — o link é válido por <strong>1 hora</strong>.</p>

                            <div style="text-align: center; margin: 30px 0;">
                                <a href="<?php echo htmlspecialchars($resetLink); ?>" style="background-color: #4f46e5; color: #ffffff; padding: 14px 28px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block; font-size: 16px;">Redefinir minha senha →</a>
                            </div>

                            <p>Se o botão não funcionar, copie e cole este link no navegador:</p>
                            <p style="background: #f3f4f6; border-left: 4px solid #d1d5db; padding: 12px 15px; border-radius: 4px; font-size: 13px; word-break: break-all; color: #4f46e5;">
                                <?php echo htmlspecialchars($resetLink); ?>
                            </p>

                            <p>Se você <strong>não</strong> solicitou isso, pode ignorar este e-mail com tranquilidade. Sua senha não vai mudar.</p>

                            <p style="margin-top: 40px; border-top: 1px solid #f3f4f6; padding-top: 20px; font-size: 14px; color: #6b7280;">
                                <strong>Ana Beatriz</strong> — Time de Suporte<br>
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
