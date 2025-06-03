<?php

namespace App\Http\Controllers;

use App\Models\Mascota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class MascotaController extends Controller
{
    // Listar todas las mascotas
    public function index(Request $request)
{
    $user = $request->user();

    // Si es veterinario, devuelve todas las mascotas con datos del dueño
    if ($user->role === 'veterinario') {
        $mascotas = Mascota::with('usuario')->get(); // 👈 incluye datos del dueño
    } else {
        // Si es dueño, solo ve sus propias mascotas
        $mascotas = Mascota::with('usuario')
            ->where('user_id', $user->id)
            ->get();
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

}
