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
use App\Models\Sale;
use App\Mail\ContractSigningMail;
use Illuminate\Support\Facades\Mail;
use Yajra\DataTables\Facades\DataTables;

class LeadsController extends Controller
{
    protected $apiFactory;

    public function __construct(ApiClientFactoryInterface $apiFactory)
    {
        $this->apiFactory = $apiFactory;
    }

    /**
     * Inicia el proceso de importación de leads y llamadas 
     * para el usuario autenticado.
     */
    public function importar_leads($id_usuario = null)
    {
        set_time_limit(1000); // ← importante

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
     * Muestra una lista de los leads que pertenecen al closer autenticado.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Construir la consulta base una sola vez
            $query = Lead::with('pipelineStatus', 'user', 'sale.contractTemplate')->orderByDesc('id');

            // Aplicar filtro por rol al query existente
            if (auth()->user()->getRoleNames()->first() !== 'admin') {
                $query->where('user_id', Auth::id());
            }
            
            $pipelineStatuses = PipelineStatus::all();

            return DataTables::of($query)
                ->addColumn('action', function ($lead) {
                    $llamadasUrl = route('llamadas', ['lead_id' => $lead->id]);
                    $buttons = '<div class="d-flex justify-content-start  gap-1">';
                    $buttons .= '<a href="' . $llamadasUrl . '" class="btn btn-outline-secondary btn-sm" title="Ver llamadas"><i class="bi bi-telephone"></i></a>';
                    $buttons .= '<button type="button" class="btn btn-outline-info btn-sm view-logs-btn" 
                        data-lead-id="' . $lead->id . '" title="Ver Historial de Cambios">
                        <i class="bi bi-clock-history"></i>
                    </button>';     
                    
                    if ($lead->sale) {
                        // Ya tiene una venta registrada: botón para ver modal
                        $buttons .= '<button type="button" class="btn btn-outline-primary btn-sm view-sale-btn"
                            data-lead-id="' . $lead->id . '"
                            data-nombre="' . e($lead->sale->nombre_cliente) . '"
                            data-apellido="' . e($lead->sale->apellido_cliente) . '"
                            data-email="' . e($lead->sale->email_cliente) . '"
                            data-telefono="' . e($lead->sale->telefono_cliente) . '"
                            data-identificacion="' . e($lead->sale->identificacion_personal) . '"
                            data-domicilio="' . e($lead->sale->domicilio) . '"
                            data-metodo_pago="' . e($lead->sale->metodo_pago) . '"
                            data-tipo_acuerdo="' . e($lead->sale->tipo_acuerdo) . '"
                            data-tipo_contrato="' . e($lead->sale->tipo_contrato) . '"
                            data-comentarios="' . e($lead->sale->comentarios) . '"
                            data-comprobante="' . asset($lead->sale->comprobante_pago_path) . '"
                            data-contrato="' . e($lead->sale->contractTemplate->name ?? 'N/A') . '"
                            data-contrato_estado="' . ($lead->sale->contract_approved ? 'Aprobado' : 'Pendiente') . '"
                            data-forma_pago="' . e($lead->sale->contract_data['forma_de_pago'] ?? 'N/A') . '"
                            data-fecha_firma="' . ($lead->sale->contract_signed_date ? $lead->sale->contract_signed_date->format('d/m/Y H:i') : 'N/A') . '"
                            title="Ver Detalles de la Venta">
                            <i class="bi bi-eye"></i>
                        </button>';

                        // Botones de contrato si existe
                        if ($lead->sale->contractTemplate) {
                            if ($lead->sale->contract_approved) {
                                // Contrato aprobado: botón para descargar
                                $buttons .= '<button type="button" class="btn btn-outline-success btn-sm download-contract-btn"
                                    data-sale-id="' . $lead->sale->id . '"
                                    title="Descargar Contrato Aprobado">
                                    <i class="bi bi-download"></i>
                                </button>';
                            } else {
                                // Contrato no aprobado: botón para reenviar email
                                $buttons .= '<button type="button" class="btn btn-outline-warning btn-sm resend-contract-btn"
                                    data-sale-id="' . $lead->sale->id . '"
                                    title="Reenviar Email de Contrato">
                                    <i class="bi bi-envelope"></i>
                                </button>';
                            }
                        }
                    } elseif ($lead->pipelineStatus && $lead->pipelineStatus->name == 'Cerrada/Venta hecha') {
                        // Si no tiene venta pero está en estado de cierre: botón para registrar
                        $buttons .= '<a href="' . route('sales.form', $lead->id) . '" class="btn btn-outline-success btn-sm" title="Registrar Venta"><i class="bi bi-file-earmark-text"></i></a>';
                    }
                    
                    $buttons .= '</div>';
                    return $buttons;
                })
                ->addColumn('pipeline_status', function ($lead) use ($pipelineStatuses) {
                    $options = '';
                    foreach ($pipelineStatuses as $status) {
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

        if (!$request->ajax()) {
            if (auth()->user()->hasRole('admin')) {
                $usuarios = User::where('id', '!=', 1)->get();
                foreach ($usuarios as $usuario) {
                    $this->importar_leads($usuario->id);
                }
            } else {
                $this->importar_leads(Auth::id());
            }
        }
        
        return view('leads.leads_index');
    }

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
            'detalle' => $request->comentario ?? 'Cambio de estado sin comentario.',
            'valor_viejo' => $oldStatus,
            'valor_nuevo' => $newStatus,
            'id_usuario' => Auth::id(),
        ]);

        return response()->json(['success' => true, 'message' => 'Estado del lead actualizado.']);
    }

    public function infoJson($id)
    {
        $lead = Lead::findOrFail($id);
        return response()->json($lead);
    }

    public function logs($id)
    {
        $lead = Lead::with(['sale', 'onboardingCalls'])->findOrFail($id);

        // Obtener todos los logs relacionados con este lead
        $allLogs = collect();

        // 1. Logs del lead (cambios de pipeline)
        $leadLogs = Log::where('tabla', 'leads')
            ->where('id_tabla', $id)
            ->with('usuario')
            ->get();
        $allLogs = $allLogs->merge($leadLogs);

        // 2. Logs de llamadas de onboarding
        $callIds = $lead->onboardingCalls->pluck('id');
        if ($callIds->isNotEmpty()) {
            $callLogs = Log::where('tabla', 'onboarding_calls')
                ->whereIn('id_tabla', $callIds)
                ->with('usuario')
                ->get();
            $allLogs = $allLogs->merge($callLogs);
        }

        // 3. Logs de upsell si tiene venta
        if ($lead->sale) {
            $saleLogs = Log::where('tabla', 'sales')
                ->where('id_tabla', $lead->sale->id)
                ->where('tipo_log', 'upsell')
                ->with('usuario')
                ->get();
            $allLogs = $allLogs->merge($saleLogs);
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
                if ($tipoLog === 'venta') return 'Venta';
                if ($tipoLog === 'contrato') return 'Contrato';
                return 'Pipeline';
            case 'onboarding_calls':
                return 'Onboarding';
            case 'sales':
                return $tipoLog === 'upsell' ? 'Upsell' : 'Venta';
            default:
                return 'General';
        }
    }

    public function downloadContract($saleId)
    {
        $sale = Sale::with('contractTemplate')->findOrFail($saleId);

        if (!$sale->contract_approved) {
            return back()->with('error', 'El contrato no ha sido aprobado aún.');
        }

        // Generar HTML del contrato
        $contractHtml = $this->generateContractHtml($sale, $sale->contractTemplate);

        // Crear PDF usando DomPDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($contractHtml);

        return $pdf->download('contrato_' . $sale->id . '.pdf');
    }

    public function resendContractEmail($saleId)
    {
        $sale = Sale::with(['contractTemplate', 'lead'])->findOrFail($saleId);

        if (!$sale->contractTemplate) {
            return response()->json(['success' => false, 'message' => 'No hay contrato asociado.']);
        }

        try {
            Mail::to($sale->email_cliente)->send(new ContractSigningMail($sale));

            return response()->json([
                'success' => true,
                'message' => 'Email de contrato reenviado exitosamente.',
                'contract_url' => route('contract.sign', $sale->contract_token)
            ]);
        } catch (\Exception $e) {
            \Log::error('Error reenviando email de contrato: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al enviar el email. Inténtalo de nuevo.'
            ]);
        }
    }

    private function generateContractHtml($sale, $contractTemplate)
    {
        $html = $contractTemplate->html_content;
        $contractData = $sale->contract_data ?? [];

        // Usar solo los datos del contrato tal como están
        foreach ($contractData as $key => $value) {
            if ($key === 'imagen_firma' && $value) {
                // Para PDF, convertir URL a ruta absoluta del archivo
                $imagePath = $value;
                if (strpos($value, 'http') === 0) {
                    // Es una URL completa, extraer solo la parte del archivo
                    $imagePath = str_replace(asset(''), '', $value);
                }

                // Construir ruta absoluta para DomPDF
                $absolutePath = public_path($imagePath);

                if (file_exists($absolutePath)) {
                    $html = str_replace('{' . $key . '}', '<img src="' . $absolutePath . '" class="signature-image">', $html);
                } else {
                    // Si no existe el archivo, usar la URL original
                    $html = str_replace('{' . $key . '}', '<img src="' . $value . '" class="signature-image">', $html);
                }
            } else {
                $html = str_replace('{' . $key . '}', $value ?? '', $html);
            }
        }

        return $html;
    }
}