<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Completa tu Contrato</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .container {
            background-color: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #007bff;
        }
        .header h1 {
            color: #007bff;
            margin: 0;
            font-size: 28px;
        }
        .content {
            margin-bottom: 30px;
        }
        .highlight-box {
            background-color: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .cta-button {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            font-size: 16px;
            text-align: center;
            margin: 20px 0;
        }
        .cta-button:hover {
            background-color: #0056b3;
            color: white;
        }
        .contract-details {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #666;
            font-size: 14px;
        }
        .important {
            color: #dc3545;
            font-weight: bold;
        }
        ul {
            padding-left: 20px;
        }
        li {
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>¡Completa tu Contrato!</h1>
            <p>Tu venta ha sido procesada exitosamente</p>
        </div>

        <div class="content">
            <p>Estimado/a <strong>{{ $customerName }}</strong>,</p>

            <p>¡Felicitaciones! Tu proceso de venta ha sido completado exitosamente. Ahora necesitamos que completes y firmes tu contrato digital para finalizar todo el proceso.</p>

            <div class="contract-details">
                <h3>Detalles del Contrato</h3>
                <p><strong>Tipo de Contrato:</strong> {{ $contractName }}</p>
                <p><strong>Cliente:</strong> {{ $customerName }}</p>
                <p><strong>Email:</strong> {{ $sale->email_cliente }}</p>
            </div>

            <div class="highlight-box">
                <h3>¿Qué necesitas hacer?</h3>
                <ul>
                    <li>Hacer clic en el botón de abajo para acceder a tu contrato</li>
                    <li>Completar los campos faltantes con tu información</li>
                    <li>Firmar digitalmente el contrato</li>
                    <li>Confirmar y enviar</li>
                </ul>
            </div>

            <div style="text-align: center;">
                <a href="{{ $contractUrl }}" class="cta-button">
                    🖋️ Completar y Firmar Contrato
                </a>
            </div>

            <div class="highlight-box">
                <p class="important">⏰ Importante:</p>
                <ul>
                    <li>Este enlace es único y personal, no lo compartas con nadie</li>
                    <li>El contrato estará disponible las 24 horas para tu conveniencia</li>
                    <li>Una vez firmado, recibirás una copia por email</li>
                    <li>Si tienes dudas, puedes contactarnos respondiendo a este email</li>
                </ul>
            </div>

            <p>Si no puedes hacer clic en el botón, copia y pega este enlace en tu navegador:</p>
            <p style="background-color: #f8f9fa; padding: 10px; border-radius: 4px; word-break: break-all; font-family: monospace;">
                {{ $contractUrl }}
            </p>

            <p>Gracias por confiar en nosotros. Si tienes alguna pregunta, no dudes en contactarnos.</p>

            <p>Saludos cordiales,<br>
            <strong>El Equipo de Ventas</strong></p>
        </div>

        <div class="footer">
            <p>Este es un email automático, por favor no respondas directamente a este mensaje.</p>
            <p>© {{ date('Y') }} - Todos los derechos reservados</p>
        </div>
    </div>
</body>
</html>