<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class MessageController extends Controller
{
    // Obtener los mensajes recibidos por el usuario autenticado
    public function inbox()
    {
        return Message::with('sender')
            ->where('receiver_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();
    }

    // Enviar un nuevo mensaje
    public function send(Request $request)
    {
        $validated = $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'body' => 'required|string|max:1000',
        ]);

        $message = Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $validated['receiver_id'],
            'body' => $validated['body'],
        ]);

        return response()->json(['message' => 'Mensaje enviado con éxito', 'data' => $message]);
    }

    // (Opcional) Marcar un mensaje como leído
    public function markAsRead($id)
    {
        $message = Message::where('id', $id)
                          ->where('receiver_id', Auth::id())
                          ->firstOrFail();

        $message->read = true;
        $message->save();

        return response()->json(['message' => 'Mensaje marcado como leído']);
    }

    public function marcarLeidosDeUsuario($fromId)
{
    $userId = Auth::id();

    Message::where('sender_id', $fromId)
        ->where('receiver_id', $userId)
        ->where('read', false)
        ->update(['read' => true]);

    return response()->json(['success' => true]);
}

public function usuariosAutorizados()
{
    $usuarioActual = Auth::user();

if ($usuarioActual->role === 'Cliente') {
    $usuarios = User::where('role', 'Terapeuta')->get();
} else {
    $usuarios = User::where('role', 'Cliente')->get();
}

    return response()->json($usuarios);
}


}
