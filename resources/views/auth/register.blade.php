<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
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
    </style>
    <!-- <script>
        function validateRegisterForm(event) {
            var name = document.getElementById('name').value;
            var email = document.getElementById('email').value;
            var password = document.getElementById('password').value;
            var confirmPassword = document.getElementById('password_confirmation').value;
            var errorMessage = "";

            if (name === "") {
                errorMessage += "El nombre es obligatorio.\n";
            }
            if (email === "") {
                errorMessage += "El correo electrónico es obligatorio.\n";
            }
            if (password === "") {
                errorMessage += "La contraseña es obligatoria.\n";
            }
            if (password !== confirmPassword) {
                errorMessage += "Las contraseñas no coinciden.\n";
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
        <h1>Registrarse</h1>
        <form action="{{ route('register') }}" method="POST" onsubmit="validateRegisterForm(event)">
            @csrf
            <label for="name">Nombre:</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" >

            <label for="email">Correo Electrónico:</label>
            <input type="email" name="email" id="email" >

            <label for="password">Contraseña:</label>
            <input type="password" name="password" id="password" >

            <label for="password_confirmation">Confirmar Contraseña:</label>
            <input type="password" name="password_confirmation" id="password_confirmation" >

            <button type="submit">Registrar</button>

            @if ($errors->any())
                <div class="error-message">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </form>
        <p>¿Ya tienes una cuenta? <a href="{{ route('login') }}" style="color: #4682b4;">Iniciar sesión</a></p>
    </div>
</body>
</html>
