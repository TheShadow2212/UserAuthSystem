<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {!! NoCaptcha::renderJs('login') !!}
    <title>Iniciar Sesión</title>
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
        h1 {
            color: #4682b4;
        }
        label {
            display: block;
            margin: 10px 0 5px;
            text-align: left;
        }
        input {
            width: calc(100% - 16px);
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
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
        .success-message {
            color: green;
            font-size: 14px;
            margin-top: 10px;
        }
    </style>
    <!-- <script>
        function validateLoginForm(event) {
            var email = document.getElementById('email').value;
            var password = document.getElementById('password').value;
            var errorMessage = "";

            if (email === "") {
                errorMessage += "El correo electrónico es obligatorio.\n";
            }
            if (password === "") {
                errorMessage += "La contraseña es obligatoria.\n";
            }
            if (errorMessage) {
                event.preventDefault();
                alert(errorMessage); 
            }
        }
    </script> -->
</head>
<body>
    <div class="container">
        <h1>Iniciar sesión</h1>
        <form action="{{ route('login') }}" method="POST" onsubmit="validateLoginForm(event)">
            @csrf
            <label for="email">Correo Electrónico:</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" >

            <label for="password">Contraseña:</label>
            <input type="password" name="password" id="password" >
            
            <br>
                {!! NoCaptcha::display(['data-callback' => 'onSubmit']) !!}
            <br>

            <button type="submit">Iniciar sesión</button>

            @if ($errors->any())
                <div class="error-message">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('success'))
                <div class="success-message">
                    <p>{{ session('success') }}</p>
                </div>
            @endif
        </form>
        <p>¿No tienes cuenta? <a href="{{ route('register') }}" style="color: #4682b4;">Registrarse</a></p>
    </div>
    <script>
        function onSubmit(event) {
            event.preventDefault();
            grecaptcha.execute();
        }
    </script>
    </body>
</html>
