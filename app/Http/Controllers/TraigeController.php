<?php

namespace App\Http\Controllers;

use App\Services\LeadSynchronizationService;
use App\Services\CalendlyEventImporter;
use App\Services\Contracts\ApiClientFactoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Log;
use App\Models\PipelineStatus;
use App\Models\Lead;
use Yajra\DataTables\Facades\DataTables;

class TraigeController extends Controller
{
    protected $apiFactory;

    public function __construct(ApiClientFactoryInterface $apiFactory)
    {
        $this->apiFactory = $apiFactory;

        $this->middleware(function ($request, $next) {
            $userRole = auth()->user()->getRoleNames()->first();
            if (!in_array($userRole, ['admin', 'traige'])) {
                abort(403, 'Acceso no autorizado. Solo usuarios con rol Admin o Traige pueden acceder a este módulo.');
            }
            return $next($request);
        });
    }

    /**
     * Inicia el proceso de importación de leads y llamadas
     * para todos los usuarios (admin/traige importa todos).
     */
    public function importar_leads($id_usuario = null)
    {
        set_time_limit(1000);

        $user_to_sync = $id_usuario ? User::find($id_usuario) : Auth::user();

        if (!$user_to_sync) {
            return response()->json(['message' => 'No autorizado.'], 401);
        }

        $eventImporter = new CalendlyEventImporter($this->apiFactory);
        $syncService = new LeadSynchronizationService($eventImporter);
        $report = $syncService->synchronizeLeadsAndCalls($user_to_sync);

        return response()->json([
            'message' => 'Proceso de importación de leads y llamadas completado.',
            'imported_calls' => $report['imported'],
            'skipped_calls' => $report['skipped'],
            'errors' => $report['errors'],
        ]);
    }

    /**
     * Muestra una lista de TODOS los leads (sin filtro de passed_to_closer)
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Obtener solo los IDs de pipeline statuses para traige
            $traigeStatusNames = [
                'Llamadas agendadas',
                'Asistencias',
                'Canceladas',
                'Calificadas',
                'Tasa de asistencia',
                'Tasa de calificación'
            ];

            $traigeStatuses = PipelineStatus::whereIn('name', $traigeStatusNames)->get();

            $query = Lead::with('pipelineStatus', 'user', 'traigeCalls')
                ->orderByDesc('id');

            return DataTables::of($query)
                ->addColumn('action', function ($lead) {
                    $buttons = '<div class="d-flex justify-content-start gap-1">';

                    // Botón de programar llamada
                    $buttons .= '<button type="button" class="btn btn-outline-primary btn-sm schedule-call-btn"
                        data-lead-id="' . $lead->id . '"
                        data-lead-name="' . e($lead->nombre) . '"
                        data-lead-email="' . e($lead->email) . '"
                        title="Programar Llamada">
                        <i class="bi bi-calendar-plus"></i>
                    </button>';

                    // Botón de ver llamadas
                    $buttons .= '<button type="button" class="btn btn-outline-secondary btn-sm view-traige-calls-btn"
                        data-lead-id="' . $lead->id . '" title="Trazabilidad de Llamadas">
                        <i class="bi bi-telephone"></i>
                    </button>';

                    // Botón de llenar Triage Daily
                    $buttons .= '<button type="button" class="btn btn-outline-warning btn-sm fill-traige-daily-btn"
                        data-lead-id="' . $lead->id . '"
                        data-lead-name="' . e($lead->nombre) . '"
                        title="Llenar Triage Daily">
                        <i class="bi bi-file-text"></i>
                    </button>';

                    // Botón de historial de cambios
                    $buttons .= '<button type="button" class="btn btn-outline-info btn-sm view-logs-btn"
                        data-lead-id="' . $lead->id . '" title="Ver Historial de Cambios">
                        <i class="bi bi-clock-history"></i>
                    </button>';

                    // Botón "Pasar a Closer" si aún no ha sido pasado
                    if (!$lead->passed_to_closer) {
                        $buttons .= '<button type="button" class="btn btn-outline-success btn-sm pass-to-closer-btn"
                            data-lead-id="' . $lead->id . '" title="Pasar a Closer">
                            <i class="bi bi-arrow-right-circle"></i> Pasar a Closer
                        </button>';
                    } else {
                        $buttons .= '<span class="badge bg-success">Pasado a Closer</span>';
                    }

                    $buttons .= '</div>';
                    return $buttons;
                })
                ->addColumn('pipeline_status', function ($lead) use ($traigeStatuses) {
                    $options = '';
                    foreach ($traigeStatuses as $status) {
                        $selected = $lead->pipeline_status_id == $status->id ? 'selected' : '';
                        $options .= '<option value="' . $status->id . '" ' . $selected . '>' . $status->name . '</option>';
                    }
                    return '<select class="form-select form-select-sm pipeline-status-select" data-lead-id="' . $lead->id . '">' . $options . '</select>';
                })
                ->filterColumn('pipeline_status', function($query, $keyword) {
                    $query->whereHas('pipelineStatus', function($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->rawColumns(['action', 'pipeline_status'])
                ->make(true);
        }

        // Importar todos los leads al cargar la página
        if (!$request->ajax()) {
            $usuarios = User::where('id', '!=', 1)->get();
            foreach ($usuarios as $usuario) {
                $this->importar_leads($usuario->id);
            }
        }

        return view('traige.index');
    }

    /**
     * Actualiza el estado del pipeline de un lead
     */
    public function updatePipelineStatus(Request $request, $id)
    {
        $request->validate([
            'status_id' => 'required|exists:pipeline_statuses,id',
            'comentario' => 'nullable|string',
        ]);

        $lead = Lead::findOrFail($id);
        $oldStatus = $lead->pipelineStatus->name;

        $lead->pipeline_status_id = $request->status_id;
        $lead->save();

        $newStatus = $lead->fresh()->pipelineStatus->name;

        Log::create([
            'id_tabla' => $lead->id,
            'tabla' => 'leads',
            'tipo_log' => 'traige',
            'detalle' => $request->comentario ?? 'Cambio de estado en Traige sin comentario.',
            'valor_viejo' => $oldStatus,
            'valor_nuevo' => $newStatus,
            'id_usuario' => Auth::id(),
        ]);

        return response()->json(['success' => true, 'message' => 'Estado del lead actualizado.']);
    }

    /**
     * Pasa un lead al módulo de closers
     */
    public function passToCloser($id)
    {
        $lead = Lead::findOrFail($id);

        if ($lead->passed_to_closer) {
            return response()->json([
                'success' => false,
                'message' => 'Este lead ya fue pasado a closer anteriormente.'
            ]);
        }

        $lead->update([
            'passed_to_closer' => true,
            'passed_to_closer_at' => now(),
            'passed_by_user_id' => Auth::id(),
        ]);

        // Crear log del paso a closer
        Log::create([
            'id_tabla' => $lead->id,
            'tabla' => 'leads',
            'tipo_log' => 'traige',
            'detalle' => 'Lead pasado al módulo de closers',
            'valor_viejo' => 'En Traige',
            'valor_nuevo' => 'Pasado a Closer',
            'id_usuario' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lead pasado a closer exitosamente.'
        ]);
    }

    /**
     * Almacena un nuevo lead creado manualmente desde traige
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'telefono' => 'nullable|string|max:50',
            'instagram_user' => 'nullable|string|max:255',
        ]);

        try {
            $lead = Lead::create([
                'nombre' => $request->nombre,
                'email' => $request->email,
                'telefono' => $request->telefono,
                'instagram_user' => $request->instagram_user,
                'user_id' => null,
                'pipeline_status_id' => 1,
                'passed_to_closer' => false,
                'passed_to_closer_at' => null,
                'passed_by_user_id' => null,
            ]);

            // Crear log de creación manual
            Log::create([
                'id_tabla' => $lead->id,
                'tabla' => 'leads',
                'tipo_log' => 'traige',
                'detalle' => 'Lead creado manualmente desde el módulo de traige',
                'valor_viejo' => null,
                'valor_nuevo' => 'Lead creado',
                'id_usuario' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Lead registrado exitosamente.',
                'lead' => $lead
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar el lead: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene los logs de un lead (pipeline + traige calls)
     */
    public function logs($id)
    {
        $lead = Lead::with(['traigeCalls'])->findOrFail($id);

        $allLogs = collect();

        // 1. Logs del lead (cambios de pipeline en traige)
        $leadLogs = Log::where('tabla', 'leads')
            ->where('id_tabla', $id)
            ->with('usuario')
            ->get();
        $allLogs = $allLogs->merge($leadLogs);

        // 2. Logs de llamadas de traige
        $callIds = $lead->traigeCalls->pluck('id');
        if ($callIds->isNotEmpty()) {
            $callLogs = Log::where('tabla', 'traige_calls')
                ->whereIn('id_tabla', $callIds)
                ->with('usuario')
                ->get();
            $allLogs = $allLogs->merge($callLogs);
        }

        // Ordenar todos los logs por fecha descendente
        $allLogs = $allLogs->sortByDesc('created_at');

        return response()->json($allLogs->map(function ($log) {
            return [
                'estado_anterior' => $log->valor_viejo ?? 'N/A',
                'estado_nuevo' => $log->valor_nuevo ?? 'N/A',
                'comentario' => $log->detalle,
                'usuario' => $log->usuario->name ?? 'Desconocido',
                'fecha' => $log->created_at->format('Y-m-d H:i'),
                'tipo' => $this->getLogType($log->tabla, $log->tipo_log ?? null),
                'archivo_soporte' => $log->archivo_soporte,
            ];
        })->values());
    }

    private function getLogType($tabla, $tipoLog = null)
    {
        switch ($tabla) {
            case 'leads':
                // Si tiene tipo_log = 'traige', mostrar Traige, sino Pipeline
                if ($tipoLog === 'traige') {
                    return 'Traige';
                } elseif ($tipoLog === 'venta') {
                    return 'Venta';
                } elseif ($tipoLog === 'contrato') {
                    return 'Contrato';
                } else {
                    return 'Pipeline';
                }
            case 'traige_calls':
                return 'Llamadas Traige';
            case 'onboarding_calls':
                return 'Onboarding';
            case 'sales':
                return $tipoLog === 'upsell' ? 'Upsell' : 'Venta';
            default:
                return 'General';
        }
    }
}
