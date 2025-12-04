<?php
namespace App\Http\Controllers;

use App\Models\ActividadProyecto;
use App\Models\Proyectos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use GuzzleHttp\Client;
use Illuminate\Validation\Rule;
class ActividadesController extends Controller
{
    use AuthorizesRequests; // <-- Trae authorize()

public function store(Request $request, $id = null)
{
   $proyectoId = $id ?? $request->proyecto_id;

    $request->validate([
        'nombre' => [
            'required',
            'string',
            'max:255',
            Rule::unique('actividades_proyecto', 'nombre')
                ->where('proyecto_id', $proyectoId)
        ],
        'descripcion' => 'required|string|max:1000',
        'fecha_actividad' => 'required|date',
        'imagen' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    ], [
        'nombre.unique' => 'Ya existe una actividad con este nombre en este proyecto. Por favor elige otro nombre.'
    ]);

    $imagen_url = null;

    if ($request->hasFile('imagen')) {
        $client = new \GuzzleHttp\Client();
        try {
            $file = $request->file('imagen');
            $response = $client->post('https://api.imgur.com/3/image', [
                'headers' => ['Authorization' => 'Bearer ' . env('IMGUR_ACCESS_TOKEN')],
                'multipart' => [
                    [
                        'name' => 'image',
                        'contents' => file_get_contents($file->getRealPath()),
                        'filename' => 'actividad_' . time() . '.' . $file->extension()
                    ],
                    ['name' => 'privacy', 'contents' => 'hidden'],
                    ['name' => 'title', 'contents' => 'Actividad'],
                    ['name' => 'description', 'contents' => 'Imagen subida para actividad del proyecto']
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            if (isset($data['data']['link'])) {
                $imagen_url = $data['data']['link'];
            }
        } catch (\Exception $e) {
            \Log::error('Error subiendo imagen a Imgur', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Error al subir la imagen: ' . $e->getMessage());
        }
    }

    ActividadProyecto::create([
        'proyecto_id' => $proyectoId,
        'nombre' => $request->nombre,
        'descripcion' => $request->descripcion,
        'fecha_actividad' => $request->fecha_actividad,
        'imagen_url' => $imagen_url,
    ]);

    return redirect()->back()->with('success', 'Actividad registrada correctamente.');
}

    
    public function index($id)
    {
        $proyecto = Proyectos::with(['montopr', 'fechapr'])->findOrFail($id);
        $actividades = ActividadProyecto::where('proyecto_id', $id)
            ->orderByDesc('fecha_actividad')
            ->get();

        return view('admin.proyectos.actividades', compact('proyecto', 'actividades'));
    }

    public function reporte(Request $request)
    {
        $proyectos = Proyectos::all();
        $query = ActividadProyecto::with('proyecto')->orderByDesc('fecha_actividad');

        if ($request->has('proyecto_id') && $request->proyecto_id) {
            $query->where('proyecto_id', $request->proyecto_id);
        }

        $actividades = $query->get();

        return view('admin.proyectos.reporte_actividades', compact('proyectos', 'actividades'));
    }

    public function update(Request $request, $proyectoId, $actividadId)
    {
        $actividad = ActividadProyecto::findOrFail($actividadId);
        //$this->authorize('update', $actividad);

        $request->validate([
        'nombre' => [
            'required',
            'string',
            'max:255',
            Rule::unique('actividades_proyecto', 'nombre')
                ->where('proyecto_id', $proyectoId)
                ->ignore($actividadId, 'id_actividad')
        ],
        'descripcion' => 'required|string|max:1000',
        'fecha_actividad' => 'required|date',
        'imagen' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    ], [
        'nombre.unique' => 'Ya existe otra actividad con este nombre en este proyecto. Elige uno diferente.'
    ]);

    $imagen_url = $actividad->imagen_url;

    if ($request->hasFile('imagen')) {
        $client = new \GuzzleHttp\Client();
        try {
            $file = $request->file('imagen');
            $response = $client->post('https://api.imgur.com/3/image', [
                'headers' => ['Authorization' => 'Bearer ' . env('IMGUR_ACCESS_TOKEN')],
                'multipart' => [
                    [
                        'name' => 'image',
                        'contents' => file_get_contents($file->getRealPath()),
                        'filename' => 'actividad_' . time() . '.' . $file->extension()
                    ],
                    ['name' => 'privacy', 'contents' => 'hidden'],
                    ['name' => 'title', 'contents' => 'Actividad'],
                    ['name' => 'description', 'contents' => 'Imagen subida para actividad del proyecto']
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            if (isset($data['data']['link'])) {
                $imagen_url = $data['data']['link'];
            }
        } catch (\Exception $e) {
            \Log::error('Error subiendo imagen a Imgur', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Error al subir la imagen: ' . $e->getMessage());
        }
    }

        $actividad->update([
        'nombre' => $request->nombre,
        'descripcion' => $request->descripcion,
        'fecha_actividad' => $request->fecha_actividad,
        'imagen_url' => $imagen_url,
    ]);

    return redirect()->back()->with('success', 'Actividad actualizada correctamente.');
    }

    public function destroy($proyectoId, $actividadId = null)
    {
        $actividadId = $actividadId ?? $proyectoId; // Para reporte_actividades
        $actividad = ActividadProyecto::findOrFail($actividadId);
        //$this->authorize('delete', $actividad);

        $actividad->delete();

        return redirect()->back()->with('success', 'Actividad eliminada.');
    }
}