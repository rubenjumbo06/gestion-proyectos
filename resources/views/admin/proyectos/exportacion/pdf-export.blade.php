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
            margin: 6px 0;
            line-height: 1.6;
            font-size: 11.5pt;
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
        page-break-inside: avoid;
        }

        /* imagen como inline-block para compatibilidad con los motores PDF */
        .activity-image {
        display: inline-block; 
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

        .fecha-exportacion {
            text-align: center;
            font-size: 12pt;
            color: #555;
            margin: 10px 0 30px 0;
        }

        .total-gastado {
            color: #c00c0c !important;
            font-weight: bold;
            font-size: 13pt;
        }

        .footer {
            position: fixed;
            bottom: 40px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 11pt;
            color: #666;
        }

        .footer .pagenum:before {
            content: "Página " counter(page);
        }
    </style>
</head>
<body>
    <img src="{{ public_path('images/BARUC_LOGO.jpeg') }}" alt="Logo Baruc" class="logo">
    <div class="watermark">CONFIDENCIAL</div>
    <h1>{{ $proyecto->nombre_proyecto }}</h1>
    <div class="fecha-exportacion">
        Generado el {{ now()->format('d/m/Y') }} a las {{ now()->format('H:i') }} hrs
    </div>

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
            <p><strong>Cantidad de Trabajadores:</strong> {{ $proyecto->planilla->count() }}</p>

            <!-- SCTR -->
            <p><strong>SCTR:</strong> 
                @if($proyecto->egresos && $proyecto->egresos->scr > 0)
                    S/ {{ number_format($proyecto->egresos->scr, 2) }}
                @else
                    <em style="color: #888;">No se implementó este gasto en el proyecto</em>
                @endif
            </p>

            <!-- Gastos Administrativos -->
            <p><strong>Gastos Administrativos:</strong> 
                @if($proyecto->egresos && $proyecto->egresos->gastos_administrativos > 0)
                    S/ {{ number_format($proyecto->egresos->gastos_administrativos, 2) }}
                @else
                    <em style="color: #888;">No se implementó este gasto en el proyecto</em>
                @endif
            </p>
        </div>
        <div class="spacer"></div>
    </div>
    
    @php
    // Cálculos reales (siempre correctos)
    $materiales = $proyecto->materiales->sum('monto_mat');
    $planilla_sueldo = $proyecto->planilla->sum('pago');
    $planilla_alimentacion = $proyecto->planilla->sum('alimentacion_trabajador');
    $planilla_hospedaje = $proyecto->planilla->sum('hospedaje_trabajador');
    $planilla_pasajes = $proyecto->planilla->sum('pasajes_trabajador');
    $planilla_total = $planilla_sueldo + $planilla_alimentacion + $planilla_hospedaje + $planilla_pasajes;
    $servicios = $proyecto->servicios->sum('monto');
    $gastos_extra = $proyecto->gastosExtra->sum(fn($g) => $g->alimentacion_general + $g->hospedaje + $g->pasajes);
    $scr = $proyecto->egresos->scr ?? 0;
    $gastos_admin = $proyecto->egresos->gastos_administrativos ?? 0;

    $total_egresos = $materiales + $planilla_total + $servicios + $gastos_extra + $scr + $gastos_admin;
    $monto_inicial = $proyecto->montopr->monto_inicial ?? 0;
    $utilidad = max(0, $monto_inicial - $total_egresos);
@endphp

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
            <thead><tr><th>Trabajador</th><th>DNI</th><th>Sueldo</th><th>Alimentación</th><th>Hospedaje</th><th>Pasajes</th><th>Total</th><th>Estado</th></tr></thead>
            <tbody>
                @foreach ($proyecto->planilla as $pl)
                    @php $total_trab = $pl->pago + $pl->alimentacion_trabajador + $pl->hospedaje_trabajador + $pl->pasajes_trabajador @endphp
                    <tr>
                        <td>{{ $pl->trabajador->nombre_trab }} {{ $pl->trabajador->apellido_trab }}</td>
                        <td>{{ $pl->trabajador->dni_trab }}</td>
                        <td>{{ number_format($pl->pago, 2) }}</td>
                        <td>{{ number_format($pl->alimentacion_trabajador, 2) }}</td>
                        <td>{{ number_format($pl->hospedaje_trabajador, 2) }}</td>
                        <td>{{ number_format($pl->pasajes_trabajador, 2) }}</td>
                        <td><strong>{{ number_format($total_trab, 2) }}</strong></td>
                        <td>{{ $pl->estado }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="6"><strong>Total Personal</strong></td>
                    <td><strong>S/ {{ number_format($planilla_total, 2) }}</strong></td>
                    <td></td>
                </tr>
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

<!-- EGRESOS -->
<div class="section">
    <h2>Egresos</h2>
    <table>
        <thead><tr><th>Categoría</th><th>Monto</th></tr></thead>
        <tbody>
            <tr><td>Materiales</td><td>S/ {{ number_format($materiales, 2) }}</td></tr>
            <tr><td>Planilla</td><td>S/ {{ number_format($planilla_total, 2) }}</td></tr>
            <tr><td>Servicios</td><td>S/ {{ number_format($servicios, 2) }}</td></tr>
            <tr><td>SCTR</td><td>S/ {{ number_format($scr, 2) }}</td></tr>
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
            <p>- Planilla: S/ {{ number_format($planilla_total, 2) }}</p>
            <p>- Servicios: S/ {{ number_format($servicios, 2) }}</p>
            <p>- SCTR: 
                @if($scr > 0) S/ {{ number_format($scr, 2) }} @else <em style="color:#888;">No se implementó</em> @endif
            </p>
            <p>- Gastos Administrativos: 
                @if($gastos_admin > 0) S/ {{ number_format($gastos_admin, 2) }} @else <em style="color:#888;">No se implementó</em> @endif
            </p>
            <p>- Gastos Extra: S/ {{ number_format($gastos_extra, 2) }}</p>
            <p class="total-gastado"><strong>Total Gastado:</strong> S/ {{ number_format($total_egresos, 2) }}</p>
            <p><strong>Utilidad / Monto Restante:</strong> <span style="color: {{ $utilidad > 0 ? 'green' : 'red' }}; font-weight:bold;">
                S/ {{ number_format($utilidad, 2) }}
            </span></p>
        </div>
    </div>
    <!-- PAGINACIÓN QUE SÍ FUNCIONA -->
    <div class="footer">
        <span class="pagenum"></span>
    </div>
</body>
</html>