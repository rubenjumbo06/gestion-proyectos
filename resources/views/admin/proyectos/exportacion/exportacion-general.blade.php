@extends('layouts.app')

@section('title', 'Exportación General')

@section('content')
    <style>
        /* Estilos personalizados para las columnas */
        .table th.nombre-proyecto,
        .table td.nombre-proyecto {
            width: 30%; /* Columna de Nombre del Proyecto más ancha */
        }
        .table th.cliente-proyecto,
        .table td.cliente-proyecto {
            width: 30%; /* Columna de Cliente igual de ancha */
        }
        .table th.fecha-creacion,
        .table td.fecha-creacion {
            width: 15%; /* Columna de Fecha de Creación menos ancha */
        }
        .table th.acciones,
        .table td.acciones {
            width: 25%; /* Columna de Acciones con el resto del espacio */
        }
    </style>

    <section class="content-header">
        <h1>Exportación General <small>Exporta PDFs completos de proyectos</small></h1>
    </section>

    <section class="content">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Lista de Proyectos</h3>
            </div>
            <div class="box-body">
                <table class="table table-bordered text-center table-striped">
                    <thead>
                        <tr>
                            <th class="nombre-proyecto">Nombre del Proyecto</th>
                            <th class="cliente-proyecto">Cliente</th>
                            <th class="fecha-creacion">Fecha de Creación</th>
                            <th class="acciones">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($proyectos as $proyecto)
                            <tr>
                                <td class="nombre-proyecto">{{ $proyecto->nombre_proyecto }}</td>
                                <td class="cliente-proyecto">{{ $proyecto->cliente_proyecto }}</td>
                                <td class="fecha-creacion">{{ $proyecto->fecha_creacion ? $proyecto->fecha_creacion->format('d/m/Y') : 'N/A' }}</td>
                                <td class="acciones">
                                    <a href="{{ route('proyectos.exportPdf', $proyecto) }}" class="btn btn-success">
                                        <i class="fa fa-file-pdf"></i> Exportar PDF
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4">No hay proyectos disponibles.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                {{ $proyectos->links() }}
            </div>
        </div>
    </section>
@endsection