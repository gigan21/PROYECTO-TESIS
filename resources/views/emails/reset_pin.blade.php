<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; background-color: #f8fafc; color: #334155; padding: 20px; }
        .card { background-color: #ffffff; padding: 30px; border-radius: 12px; max-width: 480px; margin: 0 auto; border: 1px solid #e2e8f0; }
        .pin { font-size: 32px; font-weight: bold; letter-spacing: 6px; color: #4f46e5; text-align: center; margin: 20px 0; background: #eeefef; padding: 12px; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Recuperación de Contraseña</h2>
        <p>Has solicitado restablecer tu contraseña para la plataforma gamificada de cinemática.</p>
        <p>Tu código PIN de verificación es:</p>
        <div class="pin">{{ $pin }}</div>
        <p>Este código vencerá en 15 minutos. Si no realizaste esta solicitud, puedes ignorar este mensaje.</p>
    </div>
</body>
</html>