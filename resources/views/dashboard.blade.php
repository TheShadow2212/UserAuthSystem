<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
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
        p {
            color: #333;
        }
        .nav-item {
            list-style-type: none;
            padding: 0;
            margin: 0;
            margin-top: 20px;
        }
        .btn {
            background-color: #4682b4;
            color: white;
            padding: 10px;
            border: none;
            width: 100%;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #5a9bd6;
        }
        .success-message {
            color: green;
            font-size: 14px;
            margin-top: 10px;
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
        <h1>Bienvenido, {{ $user->name }}!</h1>
        <p>Correo Electrónico: {{ $user->email }}</p>

        <ul class="nav-item">
            <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="btn">Cerrar Sesión</button>

                @if (session('success'))
                    <div class="success-message">
                        <p class="success-message">{{ session('success') }}</p>
                    </div>
                @endif
            </form>
        </ul>
    </div>
</body>
</html>
