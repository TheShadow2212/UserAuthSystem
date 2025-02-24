<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {!! NoCaptcha::renderJs('verify') !!}
    <title>Verificación</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f8ff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
            text-align: center;
        }
        h2 {
            color: #4682b4;
        }
        p {
            font-size: 14px;
            color: #333;
        }
        input {
            width: calc(100% - 16px);
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
            text-align: center;
        }
        button {
            background-color: #4682b4;
            color: white;
            padding: 10px;
            border: none;
            width: 100%;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #5a9bd6;
        }
        .error-message {
            color: red;
            font-size: 14px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Verificación</h2>
        <p>Ingrese un código de verificación enviado a <strong>{{ session('email') }}</strong></p>
        <form action="{{ route('verify') }}" method="POST" onsubmit="validateVerificationForm(event)">
            @csrf
            <input type="hidden" name="email" value="{{ session('email') }}">
            <input type="text" name="code" id="code" placeholder="Ingrese el código">
            <br>
                {!! NoCaptcha::display(['data-callback' => 'onSubmit']) !!}
            <br>
            <button type="submit">Verificar</button>
        </form>
        @if($errors->any())
            <div>
                <p class="error-message">{{ $errors->first() }}</p>
            </div>
        @endif
        <script>
        function onSubmit(event) {
            event.preventDefault();
            grecaptcha.execute();
        }
    </script>
    </body>
</html>