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
            margin-top: 10px;
            margin-bottom: 30px;
            page-break-inside: avoid;
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
            top: 30px;
            left: 20px;
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
            page-break-before: always;
            margin-bottom: 30px;
        }
        .intro-text {
            margin-bottom: 20px;
            font-size: 11pt;
            text-align: justify;
            line-height: 1.5;
        }
        .info-general p {
            margin: 5px 0;
            line-height: 1.5;
        }
        .spacer {
            margin-bottom: 30px;
        }
        h1 {
            margin-top: 60px;
            text-align: center;
            color: #c00c0c;
            font-weight: bold;
        }
        h2 {
            color: #c00c0c;
            border-bottom: 2px solid #c00c0c;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }

        /* ACTIVIDADES: ESTILO BONITO */
        .activity-card {
            background: #f9f9f9;
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            page-break-inside: avoid;
        }
        .activity-card h3 {
            margin: 0 0 12px 0;
            color: #c00c0c;
            font-size: 14pt;
            text-align: center;
        }
        /* contenedor que garantiza el centrado en HTML y en motores de PDF */
        .activity-image-wrapper {
        text-align: center;        /* centra contenido inline/inline-block */
        width: 100%;
        margin: 0 auto;
        /* evita que el contenedor fuerce saltos de página raros */
        page-break-inside: avoid;
        }

        /* imagen como inline-block para compatibilidad con los motores PDF */
        .activity-image {
        display: inline-block;     /* <- importante para PDF */
        margin: 0 auto;
        max-width: 220px;
        max-height: 220px;
        border-radius: 10px;
        border: 2px solid #ddd;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        vertical-align: middle;
        }
        .activity-card p {
            margin: 8px 0;
            text-align: justify;
            font-size: 11pt;
            line-height: 1.6;
        }
        .activity-date {
            text-align: right;
            font-size: 10pt;
            color: #666;
            font-style: italic;
            margin-top: 10px;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sections = document.querySelectorAll('.section');
            let pageNum = 1;
            sections.forEach((section) => {
                const pageNumDiv = document.createElement('div');
                pageNumDiv.className = 'page-number';
                pageNumDiv.textContent = `Página ${pageNum}`;
                section.appendChild(pageNumDiv);
                pageNum++;
            });
        });
    </script>
</head>
<body>
    <img src="{{ public_path('images/BARUC_LOGO.jpeg') }}" alt="Logo Baruc" class="logo">
    <div class="watermark">CONFIDENCIAL</div>
    <h1>{{ $proyecto->nombre_proyecto }}</h1>

    <div class="section" style="page-break-before: auto;">
        <div class="intro-text">
            <p>Este documento ofrece un resumen claro y conciso del proyecto, detallando su planificación, ejecución y costos. Incluye información sobre trabajadores, materiales, proveedores y egresos, sirviendo como herramienta para análisis y toma de decisiones futuras.</p>
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
    </div>

    <div class="section">
        <h2>Financiadores</h2>
        <table>
            <thead><tr><th>Financiador</th><th>Total Monto (S/)</th></tr></thead>
            <tbody>
                @foreach ($proveedores as $prov)
                    <tr><td>{{ $prov['nombre'] }}</td><td>{{ number_format($prov['total_monto'], 2) }}</td></tr>
                @endforeach
                <tr><td><strong>Total</strong></td><td><strong>S/ {{ number_format($proveedores->sum('total_monto'), 2) }}</strong></td></tr>
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Materiales</h2>
        <table>
            <thead><tr><th>Descripción</th><th>Financiador</th><th>Monto</th><th>Fecha</th></tr></thead>
            <tbody>
                @foreach ($proyecto->materiales as $mat)
                    <tr><td>{{ $mat->descripcion_mat }}</td><td>{{ $mat->proveedor->nombre_prov }}</td><td>{{ number_format($mat->monto_mat, 2) }}</td><td>{{ $mat->fecha_mat->format('d/m/Y') }}</td></tr>
                @endforeach
                <tr><td colspan="2"><strong>Total</strong></td><td><strong>S/ {{ number_format($proyecto->materiales->sum('monto_mat'), 2) }}</strong></td><td></td></tr>
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Personal</h2>
        <table>
            <thead><tr><th>Trabajador</th><th>DNI</th><th>Pago</th><th>Alimentación</th><th>Hospedaje</th><th>Pasajes</th><th>Estado</th></tr></thead>
            <tbody>
                @foreach ($proyecto->planilla as $pl)
                    <tr><td>{{ $pl->trabajador->nombre_trab }} {{ $pl->trabajador->apellido_trab }}</td><td>{{ $pl->trabajador->dni_trab }}</td><td>{{ number_format($pl->pago, 2) }}</td><td>{{ number_format($pl->alimentacion_trabajador, 2) }}</td><td>{{ number_format($pl->hospedaje_trabajador, 2) }}</td><td>{{ number_format($pl->pasajes_trabajador, 2) }}</td><td>{{ $pl->estado }}</td></tr>
                @endforeach
                <tr><td colspan="2"><strong>Total</strong></td><td><strong>S/ {{ number_format($proyecto->planilla->sum('pago'), 2) }}</strong></td><td><strong>S/ {{ number_format($proyecto->planilla->sum('alimentacion_trabajador'), 2) }}</strong></td><td><strong>S/ {{ number_format($proyecto->planilla->sum('hospedaje_trabajador'), 2) }}</strong></td><td><strong>S/ {{ number_format($proyecto->planilla->sum('pasajes_trabajador'), 2) }}</strong></td><td></td></tr>
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Gastos Extra</h2>
        <table>
            <thead>
                <tr>
                    <th>Alimentación</th>
                    <th>Hospedaje</th>
                    <th>Pasajes</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($proyecto->gastosExtra as $ge)
                    <tr>
                        <td>S/ {{ number_format($ge->alimentacion_general, 2) }}</td>
                        <td>S/ {{ number_format($ge->hospedaje, 2) }}</td>
                        <td>S/ {{ number_format($ge->pasajes, 2) }}</td>
                        <td>{{ $ge->created_at->format('d/m/Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="font-style: italic; color: #666;">No hay gastos extra registrados</td>
                    </tr>
                @endforelse
                <tr>
                    <td><strong>Total</strong></td>
                    <td><strong>S/ {{ number_format($proyecto->gastosExtra->sum('alimentacion_general'), 2) }}</strong></td>
                    <td><strong>S/ {{ number_format($proyecto->gastosExtra->sum('hospedaje'), 2) }}</strong></td>
                    <td><strong>S/ {{ number_format($proyecto->gastosExtra->sum('pasajes'), 2) }}</strong></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Servicios</h2>
        <table>
            <thead>
                <tr>
                    <th>Descripción</th>
                    <th>Monto</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($proyecto->servicios as $serv)
                    <tr>
                        <td>{{ $serv->descripcion_serv }}</td>
                        <td>S/ {{ number_format($serv->monto, 2) }}</td>
                        <td>{{ $serv->created_at->format('d/m/Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" style="font-style: italic; color: #666;">No hay servicios registrados</td>
                    </tr>
                @endforelse
                <tr>
                    <td><strong>Total</strong></td>
                    <td><strong>S/ {{ number_format($proyecto->servicios->sum('monto'), 2) }}</strong></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>

    @php
    // Cálculos reales (siempre correctos)
    $materiales = $proyecto->materiales->sum('monto_mat');
    $planilla = $proyecto->planilla->sum('pago');
    $servicios = $proyecto->servicios->sum('monto');
    $gastos_extra = $proyecto->gastosExtra->sum(fn($g) => $g->alimentacion_general + $g->hospedaje + $g->pasajes);
    $scr = $proyecto->egresos->scr ?? 0;
    $gastos_admin = $proyecto->egresos->gastos_administrativos ?? 0;

    $total_egresos = $materiales + $planilla + $servicios + $gastos_extra + $scr + $gastos_admin;
    $monto_inicial = $proyecto->montopr->monto_inicial ?? 0;
    $utilidad = max(0, $monto_inicial - $total_egresos);
@endphp

<!-- EGRESOS -->
<div class="section">
    <h2>Egresos</h2>
    <table>
        <thead><tr><th>Categoría</th><th>Monto</th></tr></thead>
        <tbody>
            <tr><td>Materiales</td><td>S/ {{ number_format($materiales, 2) }}</td></tr>
            <tr><td>Planilla</td><td>S/ {{ number_format($planilla, 2) }}</td></tr>
            <tr><td>Servicios</td><td>S/ {{ number_format($servicios, 2) }}</td></tr>
            <tr><td>SCR</td><td>S/ {{ number_format($scr, 2) }}</td></tr>
            <tr><td>Gastos Administrativos</td><td>S/ {{ number_format($gastos_admin, 2) }}</td></tr>
            <tr><td>Gastos Extra</td><td>S/ {{ number_format($gastos_extra, 2) }}</td></tr>
            <tr><td><strong>Total</strong></td><td><strong>S/ {{ number_format($total_egresos, 2) }}</strong></td></tr>
        </tbody>
    </table>
</div>

    <div class="section">
        <h2>Actividades</h2>
        <div>
            @forelse ($proyecto->actividades as $actividad)
                <div class="activity-card">
                    <h3>{{ $actividad->nombre ?? 'Sin título' }}</h3>
                    
                   @if($actividad->imagen_url)
                        <div class="activity-image-wrapper">
                            <img src="{{ $actividad->imagen_url }}" 
                                alt="Imagen de {{ $actividad->nombre }}" 
                                class="activity-image">
                        </div>
                    @endif

                    <p>{{ $actividad->descripcion ?? 'Sin descripción' }}</p>
                    <p class="activity-date">
                        {{ $actividad->fecha_actividad ? \Carbon\Carbon::parse($actividad->fecha_actividad)->format('d/m/Y') : 'Sin fecha' }}
                    </p>
                </div>
            @empty
                <p style="text-align: center; color: #666; font-style: italic;">No hay actividades registradas para este proyecto.</p>
            @endforelse
        </div>
    </div>

    <!-- RESUMEN DE UTILIDAD -->
<div class="section">
    <h2>Resumen de Utilidad</h2>
    <div class="info-general">
        <p><strong>Monto Inicial:</strong> S/ {{ number_format($monto_inicial, 2) }}</p>
        <p><strong>Total Gastado:</strong></p>
        <p>- Materiales: S/ {{ number_format($materiales, 2) }}</p>
        <p>- Planilla: S/ {{ number_format($planilla, 2) }}</p>
        <p>- Servicios: S/ {{ number_format($servicios, 2) }}</p>
        <p>- SCR: S/ {{ number_format($scr, 2) }}</p>
        <p>- Gastos Administrativos: S/ {{ number_format($gastos_admin, 2) }}</p>
        <p>- Gastos Extra: S/ {{ number_format($gastos_extra, 2) }}</p>
        <p><strong>Total Gastado:</strong> S/ {{ number_format($total_egresos, 2) }}</p>
        <p><strong>Monto Restante:</strong> S/ {{ number_format($utilidad, 2) }}</p>
    </div>
</div>
</body>
</html>