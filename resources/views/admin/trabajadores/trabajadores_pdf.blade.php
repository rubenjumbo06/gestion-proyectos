<!DOCTYPE html>
<html>
<head>
    <title>Lista de Trabajadores</title>
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
            width: 100%;
            border-collapse: collapse;
            margin-top: 50px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
            font-size: 10pt;
        }
        th {
            background-color: #c00c0c;
            color: white;
        }
        .logo {
            position: absolute;
            top: 20px;
            left: 15px;
            width: 120px;
        }
    </style>
</head>
<body>
    <img src="{{ public_path('images/BARUC_LOGO.jpeg') }}" alt="Logo" class="logo">
    <div class="watermark">CONFIDENCIAL</div>
    <h1 style="text-align: center;">Lista de Trabajadores</h1>
    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Apellidos</th>
                <th>DNI</th>
                <th>Correo</th>
                <th>Teléfono</th>
                <th>Sexo</th>
                <th>Fecha de Nacimiento</th>
                <th>Departamento</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($trabajadores as $trabajador)
                <tr>
                    <td>{{ $trabajador->nombre_trab }}</td>
                    <td>{{ $trabajador->apellido_trab }}</td>
                    <td>{{ $trabajador->dni_trab }}</td>
                    <td>{{ $trabajador->correo_trab }}</td>
                    <td>{{ $trabajador->num_telef }}</td>
                    <td>{{ $trabajador->sexo_trab }}</td>
                    <td>{{ $trabajador->fecha_nac ? $trabajador->fecha_nac->format('d/m/Y') : '' }}</td>
                    <td>{{ $trabajador->departamento ? $trabajador->departamento->nombre_dep : 'Sin departamento' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>