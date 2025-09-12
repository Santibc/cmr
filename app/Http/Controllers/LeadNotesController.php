<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\LeadNote;
use Illuminate\Support\Facades\Auth;

class LeadNotesController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $userRole = auth()->user()->getRoleNames()->first();
            if (!in_array($userRole, ['admin', 'cms'])) {
                abort(403, 'Acceso no autorizado. Solo usuarios con rol Admin o CMS pueden gestionar notas de leads.');
            }
            return $next($request);
        });
    }

    /**
     * Guarda una nueva nota para un lead
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'note' => 'required|string|min:5|max:2000',
        ], [
            'note.required' => 'La nota es obligatoria.',
            'note.min' => 'La nota debe tener al menos 5 caracteres.',
            'note.max' => 'La nota no puede exceder los 2000 caracteres.',
        ]);

        $lead = Lead::findOrFail($validated['lead_id']);

        $note = LeadNote::create([
            'lead_id' => $validated['lead_id'],
            'user_id' => Auth::id(),
            'note' => $validated['note'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Nota agregada correctamente.',
            'note' => [
                'id' => $note->id,
                'note' => $note->note,
                'user_name' => Auth::user()->name,
                'created_at' => $note->created_at->format('d/m/Y H:i'),
            ]
        ]);
    }

    /**
     * Obtiene todas las notas de un lead
     */
    public function getLeadNotes($leadId)
    {
        $lead = Lead::with(['notes.user'])->findOrFail($leadId);

        return response()->json([
            'lead_name' => $lead->nombre,
            'lead_email' => $lead->email,
            'notes' => $lead->notes->map(function ($note) {
                return [
                    'id' => $note->id,
                    'note' => $note->note,
                    'user_name' => $note->user->name,
                    'created_at' => $note->created_at->format('d/m/Y H:i'),
                    'created_at_diff' => $note->created_at->diffForHumans(),
                ];
            })
        ]);
    }

    /**
     * Elimina una nota (opcional - solo el autor o admin)
     */
    public function destroy(Request $request, $noteId)
    {
        $note = LeadNote::findOrFail($noteId);
        
        // Solo el autor o admin pueden eliminar
        if ($note->user_id !== Auth::id() && !auth()->user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para eliminar esta nota.'
            ], 403);
        }

        $note->delete();

        return response()->json([
            'success' => true,
            'message' => 'Nota eliminada correctamente.'
        ]);
    }
}
