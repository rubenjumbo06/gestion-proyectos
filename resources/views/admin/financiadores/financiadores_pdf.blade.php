<!DOCTYPE html>
<html>
<head>
    <title>Lista de Financiadores</title>
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
            margin-left: auto;
            margin-right: auto;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
            font-size: 9pt;
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
    <h1 style="text-align: center;">Lista de Financiadores</h1>
    <table>
        <thead>
            <tr>
                <th>Tipo ID</th>
                <th>N° Identificación</th>
                <th>Nombre</th>
                <th>Descripción</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($proveedores as $proveedor)
                <tr>
                    <td>{{ $proveedor->tipo_identificacion ?? '-' }}</td>
                    <td>{{ $proveedor->identificacion ?? '-' }}</td>
                    <td>{{ $proveedor->nombre_prov }}</td>
                    <td>{{ $proveedor->descripcion_prov ?? 'Sin descripción' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center; font-style: italic;">No hay financiadores registrados</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>