<?php

namespace App\Http\Controllers;

use App\Models\Mascota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\AsignacionPaseador;


class MascotaController extends Controller
{
    // Listar todas las mascotas
public function index(Request $request)
{
    $user = $request->user();

    if ($user->role === 'veterinario') {
        // Veterinario ve todas las mascotas
        $mascotas = Mascota::with(['usuario', 'asignaciones'])->get();
    } elseif ($user->role === 'dueno') {
        // Dueño ve solo sus mascotas
        $mascotas = Mascota::with(['usuario', 'asignaciones'])
            ->where('user_id', $user->id)
            ->get();
    } elseif ($user->role === 'paseador') {
        // Paseador ve mascotas asignadas a él (relación en tabla intermedia)
        $mascotas = $user->mascotasAsignadas()->with(['usuario', 'asignaciones'])->get();
    } else {
        return response()->json(['error' => 'Rol no autorizado.'], 403);
    }

    return response()->json($mascotas);
}




    // Registrar una nueva mascota (usando método estándar store)
    // 🐶 Registrar nueva mascota (veterinario elige el dueño)
    public function store(Request $request)
    {

        // ✅ Verificar que solo los veterinarios puedan registrar mascotas
    if ($request->user()->role !== 'veterinario') {
        return response()->json(['error' => 'No tienes permiso para registrar mascotas.'], 403);
    }
        // ✅ Validar todos los campos necesarios
        $request->validate([
            'user_id' => 'required|exists:users,id', // 👈 Se permite que el veterinario elija al dueño
            'nombre' => 'required|string',
            'especie' => 'required|string',
            'raza' => 'nullable|string',
            'edad' => 'nullable|integer',
            'sexo' => 'nullable|string',
            'fecha_nacimiento' => 'nullable|date',
            'microchip' => 'nullable|string',
            'color' => 'nullable|string',
            'esterilizado' => 'nullable|string',
            'descripcion' => 'nullable|string|max:1000',
            'foto' => 'nullable|image|max:2048', // 📷 Validar si hay imagen
        ]);

        // 📷 Subir la foto si se proporciona
    $fotoPath = null;
    if ($request->hasFile('foto')) {
        // Guarda la imagen en la carpeta storage/app/public/mascotas
        $fotoPath = $request->file('foto')->store('mascotas', 'public');
        // Solo guarda el nombre del archivo en la base de datos
        $fotoPath = basename($fotoPath);
    }
    // 🐾 Crear la mascota con los datos del formulario
    $mascota = Mascota::create([
        'user_id' => $request->user_id,
        'nombre' => $request->nombre,
        'especie' => $request->especie,
        'raza' => $request->raza,
        'edad' => $request->edad,
        'sexo' => $request->sexo,
        'fecha_nacimiento' => $request->fecha_nacimiento,
        'microchip' => $request->microchip,
        'color' => $request->color,
        'esterilizado' => $request->esterilizado,
        'descripcion' => $request->descripcion,
        'foto' => $fotoPath, // 📷 Guardamos la ruta de la foto
    ]);

        return response()->json([
            'mensaje' => 'Mascota registrada exitosamente',
            'mascota' => $mascota
        ]);
    }

    public function update(Request $request, $id)
{
    $mascota = Mascota::findOrFail($id);
    $usuario = $request->user();

    // 🛡 Permitir solo si es veterinario o dueño de la mascota
    if ($usuario->id !== $mascota->user_id && $usuario->role !== 'veterinario') {
        return response()->json(['mensaje' => 'No autorizado'], 403);
    }

    // 📥 Validación general para la foto (para ambos)
    $request->validate([
        'foto' => 'nullable|image|max:2048',
    ]);

    // ✅ Si hay imagen nueva, se sube y se reemplaza la anterior
    if ($request->hasFile('foto')) {
        if ($mascota->foto) {
            Storage::disk('public')->delete('mascotas/' . $mascota->foto);
        }

        $fotoPath = $request->file('foto')->store('mascotas', 'public');
        $mascota->foto = basename($fotoPath);
    }

    // ✅ Solo el veterinario puede actualizar otros campos
    if ($usuario->role === 'veterinario') {
        $request->validate([
            'nombre' => 'nullable|string',
            'especie' => 'nullable|string',
            'raza' => 'nullable|string',
            'edad' => 'nullable|integer',
            'sexo' => 'nullable|string',
            'fecha_nacimiento' => 'nullable|date',
            'microchip' => 'nullable|string',
            'color' => 'nullable|string',
            'esterilizado' => 'nullable|string',
        ]);

        $mascota->fill($request->except(['foto']));
    }

    $mascota->save();

    return response()->json([
        'mensaje' => 'Mascota actualizada con éxito',
        'mascota' => $mascota
    ]);
}


    // Eliminar mascota
   public function destroy(Request $request, $id)
{
    // ✅ Asegurarse de que SOLO el veterinario pueda eliminar
    if ($request->user()->role !== 'veterinario') {
        return response()->json(['error' => 'Solo los veterinarios pueden eliminar mascotas.'], 403);
    }

    // ✅ Buscar la mascota (sin importar el user_id)
    $mascota = Mascota::findOrFail($id);

    // ✅ Eliminar la mascota
    $mascota->delete();

    return response()->json(['mensaje' => 'Mascota eliminada con éxito']);
}

    // ✅ Método para buscar mascotas por nombre, dueño o correo
public function buscar(Request $request)
{
    $query = $request->input('query'); // 🔍 Término ingresado por el usuario (mascota o dueño)

    $mascotas = Mascota::with('usuario') // Carga también el dueño (relación usuario)
        ->where('nombre', 'like', "%{$query}%") // Busca por nombre de la mascota
        ->orWhereHas('usuario', function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")    // Busca por nombre del dueño
              ->orWhere('email', 'like', "%{$query}%"); // Busca por correo del dueño
        })
        ->get();

    return response()->json($mascotas); // 📤 Devuelve las mascotas encontradas
}



public function buscarAvanzado(Request $request)
{
    $query = $request->input('query');

    $mascotas = Mascota::with('usuario')
        ->where('nombre', 'like', "%$query%")
        ->orWhereHas('usuario', function ($q) use ($query) {
            $q->where('name', 'like', "%$query%")
              ->orWhere('email', 'like', "%$query%");
        })
        ->get();

    return response()->json($mascotas);
}


public function show($id)
{
    $mascota = Mascota::with('usuario')->findOrFail($id);
    return response()->json($mascota);
}

public function desasignarPaseador($mascotaId, $paseadorId)
{
    $mascota = Mascota::findOrFail($mascotaId);
    $mascota->paseadores()->detach($paseadorId); // 👈 Solo uno

    return response()->json(['mensaje' => 'Paseador desasignado correctamente']);
}



public function asignarPaseador(Request $request, $mascotaId)
{
    $request->validate([
        'paseador_id' => 'required|exists:users,id',
        'desde' => 'required|date',
        'hasta' => 'required|date|after_or_equal:desde'
    ]);

    $mascota = Mascota::findOrFail($mascotaId);

    $mascota->paseadores()->syncWithoutDetaching([
        $request->paseador_id => [
            'desde' => $request->desde,
            'hasta' => $request->hasta
        ]
    ]);

    return response()->json(['mensaje' => 'Paseador asignado correctamente']);
}

public function mascotasAsignadasAlPaseador()
{
    $user = Auth::user();

    if ($user->role !== 'paseador') {
        return response()->json(['error' => 'No autorizado'], 403);
    }

    $hoy = Carbon::today();

    $asignaciones = AsignacionPaseador::with('mascota')
        ->where('paseador_id', $user->id)
        ->whereDate('desde', '<=', $hoy)
        ->whereDate('hasta', '>=', $hoy)
        ->get();

    $mascotas = $asignaciones->pluck('mascota')->filter(); // Elimina nulls por seguridad

    return response()->json($mascotas);
}

}
