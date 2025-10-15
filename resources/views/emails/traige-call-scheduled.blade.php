<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Llamada de Triage Programada</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
            border: 1px solid #dee2e6;
        }
        .content {
            background-color: white;
            padding: 30px;
            border: 1px solid #dee2e6;
            border-top: none;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-radius: 0 0 8px 8px;
            border: 1px solid #dee2e6;
            border-top: none;
            font-size: 12px;
            color: #6c757d;
        }
        .call-details {
            background-color: #e9ecef;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .call-details h3 {
            margin-top: 0;
            color: #495057;
        }
        .detail-item {
            margin: 10px 0;
        }
        .detail-label {
            font-weight: bold;
            color: #495057;
        }
        .call-button {
            display: inline-block;
            background-color: #28a745;
            color: white !important;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
        }
        .call-button:hover {
            background-color: #218838;
            color: white !important;
            text-decoration: none;
        }
        .call-button:visited {
            color: white !important;
        }
        .call-button:active {
            color: white !important;
        }
        .logo {
            max-width: 200px;
            height: auto;
        }
        .important-note {
            background-color: #cce5ff;
            border: 1px solid #b8daff;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>¡Llamada de Triage Programada!</h1>
        <p>ELITE CLOSER SOCIETY</p>
    </div>

    <div class="content">
        <p>Estimado/a <strong>{{ $lead->nombre }}</strong>,</p>

        <p>¡Hola! Hemos programado una llamada para conocerte mejor y entender tus necesidades.</p>

        <div class="call-details">
            <h3>📅 Detalles de la Llamada</h3>

            <div class="detail-item">
                <span class="detail-label">Fecha y Hora:</span>
                {{ $call->scheduled_date->setTimezone('America/Argentina/Buenos_Aires')->format('l, d \d\e F \d\e Y \a \l\a\s H:i') }}
            </div>

            <div class="detail-item">
                <span class="detail-label">Zona Horaria:</span>
                Argentina (ART)
            </div>

            @if($call->notes)
            <div class="detail-item">
                <span class="detail-label">Notas:</span>
                {{ $call->notes }}
            </div>
            @endif
        </div>

        <div style="text-align: center;">
            <a href="{{ $call->call_link }}" class="call-button">
                🔗 Unirse a la Llamada
            </a>
        </div>

        <div class="important-note">
            <strong>📝 Importante:</strong>
            <ul>
                <li>Guarda este email para acceder fácilmente al link de la llamada</li>
                <li>Asegúrate de tener una conexión estable a internet</li>
                <li>Ten a mano cualquier pregunta que quieras hacer</li>
                <li>Si no puedes asistir, por favor contáctanos lo antes posible</li>
            </ul>
        </div>

        <p>Durante esta llamada:</p>
        <ul>
            <li>✅ Conoceremos tus objetivos y expectativas</li>
            <li>✅ Resolveremos cualquier duda inicial que tengas</li>
            <li>✅ Te explicaremos los próximos pasos</li>
        </ul>

        <p>Si tienes alguna pregunta antes de la llamada, no dudes en contactarnos.</p>

        <p>¡Esperamos con ansias conocerte!</p>

        <p>Saludos cordiales,<br>
        <strong>El equipo de ELITE CLOSER SOCIETY</strong></p>
    </div>

    <div class="footer">
        <p>Este es un email automático, por favor no responder directamente.</p>
        <p>© {{ date('Y') }} ELITE CLOSER SOCIETY. Todos los derechos reservados.</p>
    </div>
</body>
</html>
