<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Login</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            background: url('/images/bar.jpg') no-repeat center center;
            background-size: cover; /* cambia de 'cover' a 'contain' para evitar que se vea tan enorme */
            background-repeat: no-repeat;
            backdrop-filter: blur(5px);
            background-color: #f0f0f0; /* color de fondo detrás de la imagen si no ocupa todo */
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

        .logo {
            max-width: 120px;
            margin: 0 auto;
            margin-top: -10px;
            margin-bottom: 20px;
        }

        @media (max-width: 640px) {
            body {
                background-size: cover; /* más amigable para móviles */
            }

            .logo {
                max-width: 90px;
            }
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen px-4">
    <div class="login-container p-8 rounded-lg w-full max-w-md">
        <h2 class="text-3xl font-bold mb-2 text-center text-red-600">Acceso Login</h2>
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

        <form action="{{ route('login') }}" method="POST" class="space-y-6">
            @csrf
            <div class="mb-4">
                <label for="login" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="text" name="login" id="login" class="input-field mt-1 block w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none transition duration-200" placeholder="tuemail@example.com" required>
            </div>
            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" name="password" id="password" class="input-field mt-1 block w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none transition duration-200" placeholder="••••••••" required>
            </div>
            <button type="submit" class="w-full bg-red-600 text-white py-3 px-4 rounded-lg hover:bg-red-700 transition duration-200 transform hover:scale-105">
                Ingresar
            </button>
        </form>

        <a href="{{ route('google.login') }}" class="block mt-4 bg-blue-600 text-white text-center py-3 px-4 rounded-lg hover:bg-blue-700 transition duration-200 transform hover:scale-105">
            Login with Google
        </a>

        <a href="{{ route('register.form') }}" class="block mt-4 text-center text-blue-600 hover:underline">
            ¿No tienes cuenta? Regístrate aquí
        </a>

        @if (session('status'))
            <div class="mt-4 bg-green-100 text-green-700 p-3 rounded text-center">
                {{ session('status') }}
            </div>
        @endif
    </div>

    <script>
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
        document.addEventListener('DOMContentLoaded', function() {
            if (window.performance && window.performance.navigation.type === 2) {
                window.location.href = '{{ route('login') }}';
            }
        });
    </script>
</body>
</html>