<!DOCTYPE html>
<html>
<head>
    <title>Exportación: {{ $proyecto->nombre_proyecto }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            position: relative;
            margin: 0;
            padding: 20px;
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
            margin-top: 10px; /* Reduced spacing between title and table */
            margin-bottom: 30px; /* Space after table */
            page-break-inside: avoid; /* Prevent table from splitting across pages */
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
            top: 15px;
            left: 10px;
            width: 100px;
        }
        .page-number {
            position: fixed;
            bottom: 10px;
            width: 100%;
            text-align: center;
            font-size: 10pt;
            color: #666;
        }
        .section {
            page-break-before: always; /* Each section starts on a new page */
            margin-bottom: 30px;
        }
        .intro-text {
            margin-bottom: 20px;
            font-size: 11pt; /* Slightly larger font size */
            text-align: justify; /* Justified text */
            line-height: 1.5;
        }
        .info-general p {
            margin: 5px 0;
            line-height: 1.5; /* Line spacing of 1.15 */
        }
        .spacer {
            margin-bottom: 30px; /* Space below Monto Inicial */
        }
    </style>
</head>
<body>
    <img src="{{ public_path('images/BARUC_LOGO.jpeg') }}" alt="Logo Baruc" class="logo">
    <div class="watermark">CONFIDENCIAL</div>
    <h1 style="text-align: center;">{{ $proyecto->nombre_proyecto }}</h1>

    <div class="section" style="page-break-before: auto;"> <!-- No page break for first section -->
        <div class="intro-text">
            <p>El presente documento ha sido elaborado con el propósito de brindar una visión integral y detallada del desarrollo del proyecto, desde su planificación inicial hasta su culminación. Su finalidad es servir como una herramienta de recopilación y análisis que permita reflejar, de manera clara y ordenada, cada uno de los aspectos fundamentales que hicieron posible la ejecución del mismo.

En este informe se presentan no solo las fechas establecidas que marcaron las distintas etapas del proyecto, sino también los elementos clave que intervinieron en su realización. Entre ellos, se detallan los trabajadores que formaron parte de la planilla y desempeñaron funciones específicas, los materiales adquiridos para garantizar el cumplimiento de las metas propuestas, así como los proveedores que contribuyeron de manera directa con la provisión de recursos necesarios.

La estructura de este documento busca ofrecer una visión global del costo total del proyecto, presentando de forma rápida pero concisa los rubros que lo componen y que, en conjunto, reflejan el esfuerzo humano, técnico y financiero involucrado. De este modo, se constituye en una referencia formal que facilita el análisis posterior, la evaluación de resultados y la toma de decisiones para proyectos futuros.

En suma, este documento no solo recoge los hitos y registros más importantes, sino que también constituye un respaldo documental que demuestra la transparencia, la planificación y el orden con los que se llevó a cabo el proyecto.</p>
        </div>

        <h2>Información General</h2>
        <div class="info-general">
            <p><strong>Cliente:</strong> {{ $proyecto->cliente_proyecto }}</p>
            <p><strong>Descripción:</strong> {{ $proyecto->descripcion_proyecto ?? 'N/A' }}</p>
            <p><strong>Fecha de Inicio:</strong> {{ $proyecto->fechapr->fecha_inicio ? $proyecto->fechapr->fecha_inicio->format('d/m/Y') : 'N/A' }}</p>
            <p><strong>Fecha Fin Aproximada:</strong> {{ $proyecto->fechapr->fecha_fin_aprox ? $proyecto->fechapr->fecha_fin_aprox->format('d/m/Y') : 'N/A' }}</p>
            <p><strong>Monto Inicial:</strong> S/ {{ number_format($proyecto->montopr->monto_inicial ?? 0, 2) }}</p>
            <p><strong>Cantidad de Trabajadores:</strong> {{ count($proyecto->planilla) }}</p>
            <p><strong>Sueldo Estimado para todos los Trabajadores:</strong> S/ {{ number_format($proyecto->planilla->sum('pago') ?? 0, 2) }}</p>
        </div>
        <div class="spacer"></div>
        <div class="page-number">Página 1</div>
    </div>

    <div class="section">
        <h2>Proveedores</h2>
        <table>
            <thead><tr><th>Proveedor</th><th>Total Monto (S/)</th></tr></thead>
            <tbody>
                @foreach ($proveedores as $prov)
                    <tr><td>{{ $prov['nombre'] }}</td><td>{{ number_format($prov['total_monto'], 2) }}</td></tr>
                @endforeach
            </tbody>
        </table>
        <div class="page-number">Página 2</div>
    </div>

    <div class="section">
        <h2>Materiales</h2>
        <table>
            <thead><tr><th>Descripción</th><th>Proveedor</th><th>Monto</th><th>Fecha</th></tr></thead>
            <tbody>
                @foreach ($proyecto->materiales as $mat)
                    <tr><td>{{ $mat->descripcion_mat }}</td><td>{{ $mat->proveedor->nombre_prov }}</td><td>{{ number_format($mat->monto_mat, 2) }}</td><td>{{ $mat->fecha_mat->format('d/m/Y') }}</td></tr>
                @endforeach
            </tbody>
        </table>
        <div class="page-number">Página 3</div>
    </div>

    <div class="section">
        <h2>Planilla</h2>
        <table>
            <thead><tr><th>Trabajador</th><th>DNI</th><th>Pago</th><th>Alimentación</th><th>Hospedaje</th><th>Pasajes</th><th>Estado</th></tr></thead>
            <tbody>
                @foreach ($proyecto->planilla as $pl)
                    <tr><td>{{ $pl->trabajador->nombre_trab }} {{ $pl->trabajador->apellido_trab }}</td><td>{{ $pl->trabajador->dni_trab }}</td><td>{{ number_format($pl->pago, 2) }}</td><td>{{ number_format($pl->alimentacion_trabajador, 2) }}</td><td>{{ number_format($pl->hospedaje_trabajador, 2) }}</td><td>{{ number_format($pl->pasajes_trabajador, 2) }}</td><td>{{ $pl->estado }}</td></tr>
                @endforeach
            </tbody>
        </table>
        <div class="page-number">Página 4</div>
    </div>

    <div class="section">
        <h2>Gastos Extra</h2>
        <table>
            <thead><tr><th>Alimentación</th><th>Hospedaje</th><th>Pasajes</th><th>Fecha</th></tr></thead>
            <tbody>
                @foreach ($proyecto->gastosExtra as $ge)
                    <tr><td>{{ number_format($ge->alimentacion_general, 2) }}</td><td>{{ number_format($ge->hospedaje, 2) }}</td><td>{{ number_format($ge->pasajes, 2) }}</td><td>{{ $ge->created_at->format('d/m/Y') }}</td></tr>
                @endforeach
            </tbody>
        </table>
        <div class="page-number">Página 5</div>
    </div>

    <div class="section">
        <h2>Egresos</h2>
        <table>
            <thead><tr><th>Categoría</th><th>Monto</th></tr></thead>
            <tbody>
                <tr><td>Materiales</td><td>{{ number_format($proyecto->egresos->materiales ?? 0, 2) }}</td></tr>
                <tr><td>Planilla</td><td>{{ number_format($proyecto->egresos->planilla ?? 0, 2) }}</td></tr>
                <tr><td>SCR</td><td>{{ number_format($proyecto->egresos->scr ?? 0, 2) }}</td></tr>
                <tr><td>Gastos Administrativos</td><td>{{ number_format($proyecto->egresos->gastos_administrativos ?? 0, 2) }}</td></tr>
                <tr><td>Gastos Extra</td><td>{{ number_format($proyecto->egresos->gastos_extra ?? 0, 2) }}</td></tr>
            </tbody>
        </table>
        <div class="page-number">Página 6</div>
    </div>
</body>
</html>