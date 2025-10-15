<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TraigeCall;
use App\Models\Lead;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\TraigeCallScheduled;

class TraigeCallsController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $userRole = auth()->user()->getRoleNames()->first();
            if (!in_array($userRole, ['admin', 'traige'])) {
                abort(403, 'Acceso no autorizado. Solo usuarios con rol Admin o Traige pueden acceder a este módulo.');
            }
            return $next($request);
        });
    }

    /**
     * Programa una nueva llamada de traige
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'scheduled_date' => 'required|date_format:Y-m-d\TH:i|after:' . now()->subMinutes(1)->format('Y-m-d H:i'),
            'call_link' => 'required|url',
            'notes' => 'nullable|string|max:1000',
            'parent_call_id' => 'nullable|exists:traige_calls,id',
        ], [
            'scheduled_date.after' => 'La fecha y hora debe ser posterior al momento actual.',
            'scheduled_date.date_format' => 'El formato de fecha y hora no es válido.',
            'call_link.url' => 'El enlace de la llamada debe ser una URL válida.',
        ]);

        $lead = Lead::findOrFail($validated['lead_id']);

        $call = TraigeCall::create([
            'lead_id' => $validated['lead_id'],
            'user_id' => Auth::id(),
            'parent_call_id' => $validated['parent_call_id'],
            'scheduled_date' => $validated['scheduled_date'],
            'call_link' => $validated['call_link'],
            'notes' => $validated['notes'],
            'status' => TraigeCall::STATUS_PENDIENTE,
        ]);

        // Crear log
        Log::create([
            'id_tabla' => $call->id,
            'tabla' => 'traige_calls',
            'detalle' => 'Nueva llamada de traige programada para ' . $call->scheduled_date->format('d/m/Y H:i'),
            'valor_viejo' => null,
            'valor_nuevo' => 'programada',
            'id_usuario' => Auth::id(),
        ]);

        // Enviar email al lead
        try {
            Mail::to($lead->email)->send(new TraigeCallScheduled($call, $lead));
            $call->update([
                'email_sent' => true,
                'email_sent_at' => now(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Error enviando email de traige: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Llamada programada correctamente y email enviado.',
            'call_id' => $call->id
        ]);
    }

    /**
     * Actualiza el estado de una llamada
     */
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pendiente,realizada,no_realizada',
            'comments' => 'nullable|string|max:1000',
        ]);

        $call = TraigeCall::findOrFail($id);
        $oldStatus = $call->status;

        $call->update([
            'status' => $validated['status'],
            'comments' => $validated['comments'],
        ]);

        // Crear log del cambio
        Log::create([
            'id_tabla' => $call->id,
            'tabla' => 'traige_calls',
            'detalle' => $validated['comments'] ?? 'Cambio de estado sin comentario.',
            'valor_viejo' => $oldStatus,
            'valor_nuevo' => $validated['status'],
            'id_usuario' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado correctamente.'
        ]);
    }

    /**
     * Obtiene las llamadas de un lead específico
     */
    public function getLeadCalls($leadId)
    {
        $calls = TraigeCall::with(['user', 'parentCall'])
            ->where('lead_id', $leadId)
            ->orderByDesc('scheduled_date')
            ->get();

        return response()->json($calls->map(function ($call) {
            return [
                'id' => $call->id,
                'scheduled_date' => $call->scheduled_date->format('d/m/Y H:i'),
                'call_link' => $call->call_link,
                'notes' => $call->notes,
                'status' => $call->status,
                'status_label' => TraigeCall::getStatuses()[$call->status] ?? 'Desconocido',
                'comments' => $call->comments,
                'user_name' => $call->user->name,
                'created_at' => $call->created_at->format('d/m/Y H:i'),
                'parent_call_id' => $call->parent_call_id,
                'has_children' => $call->childCalls()->count() > 0,
                'email_sent' => $call->email_sent,
            ];
        }));
    }

    /**
     * Obtiene los logs de una llamada específica
     */
    public function getLogs($callId)
    {
        $call = TraigeCall::with(['logs.usuario'])->findOrFail($callId);

        return response()->json($call->logs->map(function ($log) {
            return [
                'detalle' => $log->detalle,
                'valor_viejo' => $log->valor_viejo,
                'valor_nuevo' => $log->valor_nuevo,
                'usuario' => $log->usuario->name ?? 'Desconocido',
                'fecha' => $log->created_at->format('Y-m-d H:i'),
            ];
        }));
    }

    /**
     * Marca una llamada como reprogramada (solo para reprogramación)
     */
    public function reschedule(Request $request, $id)
    {
        $call = TraigeCall::findOrFail($id);
        $oldStatus = $call->status;

        $call->update([
            'status' => TraigeCall::STATUS_REPROGRAMADA,
            'comments' => 'Llamada reprogramada automáticamente',
        ]);

        // Crear log del cambio
        Log::create([
            'id_tabla' => $call->id,
            'tabla' => 'traige_calls',
            'detalle' => 'Llamada reprogramada automáticamente',
            'valor_viejo' => $oldStatus,
            'valor_nuevo' => TraigeCall::STATUS_REPROGRAMADA,
            'id_usuario' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Llamada marcada como reprogramada.'
        ]);
    }
}
