<!DOCTYPE html>
<html>
<head>
    <title>Lista de Departamentos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            position: relative;
        }
        .watermark {
            position: absolute;
            opacity: 0.1;
            font-size: 80px;
            color: #ccc;
            transform: rotate(-45deg);
            top: 50%;
            left: 50%;
            transform-origin: 0 0;
            z-index: -1;
        }
        table {
            width: 80%;
            border-collapse: collapse;
            margin-top: 50px;
            margin-left: auto;
            margin-right: auto;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
            font-size: 10pt;
        }
        th {
            background-color: #c00c0c; /* Color rojo para la cabecera */
            color: white;
        }
        .logo {
            position: absolute;
            top: 20px;
            left: 15px;
            width: 120px; /* Ajusta según tu logo */
        }
    </style>
</head>
<body>
    <!-- Logo -->
    <img src="{{ public_path('images/BARUC_LOGO.jpeg') }}" alt="Logo" class="logo">

    <!-- Marca de agua -->
    <div class="watermark">CONFIDENCIAL</div>

    <h1 style="text-align: center;">Lista de Departamentos</h1>
    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Descripción</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($departamentos as $departamento)
                <tr>
                    <td>{{ $departamento->nombre_dep }}</td>
                    <td>{{ $departamento->descripcion_dep ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>