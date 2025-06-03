<!DOCTYPE html>
<html>
<head>
    <title>Verifica tu correo</title>
</head>
<body>
    <h1>Verifica tu correo electrónico</h1>
    <p>Te hemos enviado un correo para que confirmes tu dirección de email.</p>
    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit">Reenviar correo de verificación</button>
    </form>
</body>
</html>
