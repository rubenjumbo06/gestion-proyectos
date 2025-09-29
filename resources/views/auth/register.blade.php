<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        /* Copia el estilo de login.blade.php aquí para consistencia */
        body {
            background: url('/images/bar.jpg') no-repeat center center;
            background-size: cover;
            background-repeat: no-repeat;
            backdrop-filter: blur(5px);
            background-color: #f0f0f0;
        }
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        .input-field:focus {
            border-color: #ff4500;
            box-shadow: 0 0 5px rgba(255, 69, 0, 0.5);
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen px-4">
    <div class="login-container p-8 rounded-lg w-full max-w-md">
        <h2 class="text-3xl font-bold mb-2 text-center text-red-600">Registro de Usuario</h2>
        @if (session('error'))
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4 text-center">
                {{ session('error') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4 text-center">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('register') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700">Nombre Completo</label>
                <input type="text" name="name" id="name" class="input-field mt-1 block w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none transition duration-200" placeholder="Tu nombre completo" required>
            </div>
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" id="email" class="input-field mt-1 block w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none transition duration-200" placeholder="tuemail@example.com" required>
            </div>
            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
                <input type="password" name="password" id="password" class="input-field mt-1 block w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none transition duration-200" placeholder="••••••••" required>
            </div>
            <div class="mb-4">
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar Contraseña</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="input-field mt-1 block w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none transition duration-200" placeholder="••••••••" required>
            </div>
            <button type="submit" class="w-full bg-red-600 text-white py-3 px-4 rounded-lg hover:bg-red-700 transition duration-200 transform hover:scale-105">
                Registrarse
            </button>
        </form>

        <a href="{{ route('login') }}" class="block mt-4 text-center text-blue-600 hover:underline">
            ¿Ya tienes cuenta? Inicia sesión
        </a>
    </div>
</body>
</html>