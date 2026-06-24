<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atualização sobre sua candidatura</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f8f9fa; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 20px 0;">
        <tr>
            <td align="center">
                <table border="0" cellpadding="0" cellspacing="0" width="560" style="background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                    <!-- Header -->
                    <tr>
                        <td align="center" style="padding: 40px 0 20px 0;">
                            <img src="{{ $baseUrl }}/frontend/assets/textlogo.png" alt="ContrataPorto" width="180" style="display: block;">
                        </td>
                    </tr>
                    <!-- Content -->
                    <tr>
                        <td style="padding: 20px 40px; color: #374151; font-size: 16px; line-height: 1.6;">
                            <p>Oi, <strong>{{ $nome }}</strong>, tudo bem?</p>
                            
                            <p>A gente sabe que não é a notícia que você esperava, mas a <strong>{{ $nomeEmpresa }}</strong> decidiu seguir com outro perfil para a vaga de <strong>{{ $vaga }}</strong> desta vez.</p>
                            
                            <p>Não desanima! Cada processo seletivo é uma experiência que te aproxima da vaga certa. O ContrataPorto tem várias outras oportunidades esperando por você.</p>
                            
                            <div style="text-align: center; margin: 30px 0;">
                                <a href="{{ $baseUrl }}/vagas" style="background-color: #4f46e5; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block;">Ver outras vagas →</a>
                            </div>
                            
                            <p>Continue explorando — sua vaga ideal pode estar logo ali. 🙂</p>
                            
                            <p style="margin-top: 40px; border-top: 1px solid #f3f4f6; padding-top: 20px; font-size: 14px; color: #6b7280;">
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
                            © {{ date('Y') }} ContrataPorto • Porto Ferreira - SP
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
