<!DOCTYPE html>
<html>
<head>
    <title>Lista de Proveedores</title>
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
    <h1 style="text-align: center;">Lista de Proveedores</h1>
    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Descripción</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($proveedores as $proveedor)
                <tr>
                    <td>{{ $proveedor->nombre_prov }}</td>
                    <td>{{ $proveedor->descripcion_prov ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
