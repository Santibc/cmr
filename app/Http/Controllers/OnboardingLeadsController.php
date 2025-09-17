<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\OnboardingCall;
use App\Models\Sale;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class OnboardingLeadsController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $userRole = auth()->user()->getRoleNames()->first();
            if (!in_array($userRole, ['admin', 'cms'])) {
                abort(403, 'Acceso no autorizado. Solo usuarios con rol Admin o CMS pueden acceder a este módulo.');
            }
            return $next($request);
        });
    }

    /**
     * Muestra todos los leads que tienen una venta cerrada (para onboarding)
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Solo leads que tienen una venta registrada con contrato aprobado
            $query = Lead::with(['sale.contractTemplate', 'onboardingCalls' => function($query) {
                $query->latest();
            }])
            ->whereHas('sale', function($query) {
                $query->whereNotNull('contract_template_id')
                      ->where('contract_approved', true);
            })
            ->orderByDesc('id');

            return DataTables::of($query)
                ->addColumn('action', function ($lead) {
                    $buttons = '<div class="d-flex justify-content-start gap-1">';
                    
                    // Botón para programar llamada
                    $buttons .= '<button type="button" class="btn btn-outline-success btn-sm schedule-call-btn" 
                        data-lead-id="' . $lead->id . '" 
                        data-lead-name="' . e($lead->nombre) . '"
                        data-lead-email="' . e($lead->email) . '"
                        title="Programar Llamada de Onboarding">
                        <i class="bi bi-calendar-plus"></i>
                    </button>';
                    
                    // Botón para ver información de venta
                    if ($lead->sale) {
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
                            data-comentarios="' . e($lead->sale->comentarios) . '"
                            data-comprobante="' . asset($lead->sale->comprobante_pago_path) . '"
                            title="Ver Detalles de la Venta">
                            <i class="bi bi-eye"></i>
                        </button>';
                    }
                    
                    // Botón para ver trazabilidad de llamadas
                    $buttons .= '<button type="button" class="btn btn-outline-info btn-sm view-calls-btn" 
                        data-lead-id="' . $lead->id . '" 
                        title="Ver Llamadas de Onboarding">
                        <i class="bi bi-telephone"></i>
                    </button>';
                    
                    // Botón para gestionar notas del lead
                    $buttons .= '<button type="button" class="btn btn-outline-warning btn-sm manage-notes-btn"
                        data-lead-id="' . $lead->id . '"
                        data-lead-name="' . e($lead->nombre) . '"
                        data-lead-email="' . e($lead->email) . '"
                        title="Gestionar Notas del Lead">
                        <i class="bi bi-sticky"></i>
                    </button>';

                    // Botón para descargar contrato (solo si existe y está aprobado)
                    if ($lead->sale && $lead->sale->contract_approved && $lead->sale->contractTemplate) {
                        $buttons .= '<button type="button" class="btn btn-outline-success btn-sm download-contract-btn"
                            data-sale-id="' . $lead->sale->id . '"
                            title="Descargar Contrato Aprobado">
                            <i class="bi bi-download"></i>
                        </button>';
                    }

                    // Botón para ver historial de cambios
                    $buttons .= '<button type="button" class="btn btn-outline-secondary btn-sm view-logs-btn"
                        data-lead-id="' . $lead->id . '"
                        title="Ver Historial de Cambios">
                        <i class="bi bi-clock-history"></i>
                    </button>';

                    $buttons .= '</div>';
                    return $buttons;
                })
                ->addColumn('onboarding_status', function ($lead) {
                    $lastCall = $lead->onboardingCalls->first();
                    if (!$lastCall) {
                        return '<span class="badge bg-secondary">Sin llamadas</span>';
                    }
                    
                    $statusLabels = [
                        'pendiente' => '<span class="badge bg-warning">Pendiente</span>',
                        'realizada' => '<span class="badge bg-success">Realizada</span>',
                        'no_realizada' => '<span class="badge bg-danger">No Realizada</span>',
                        'reprogramada' => '<span class="badge bg-info">Reprogramada</span>',
                    ];
                    
                    return $statusLabels[$lastCall->status] ?? '<span class="badge bg-secondary">Desconocido</span>';
                })
                ->addColumn('next_call', function ($lead) {
                    $nextCall = $lead->onboardingCalls()
                        ->whereIn('status', ['pendiente'])
                        ->orderBy('scheduled_date', 'asc')
                        ->first();
                    
                    if ($nextCall) {
                        return $nextCall->scheduled_date->format('d/m/Y H:i');
                    }
                    
                    return '-';
                })
                ->rawColumns(['action', 'onboarding_status'])
                ->make(true);
        }

        return view('onboarding.leads_index');
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

    public function logs($id)
    {
        $lead = Lead::with(['logs.usuario'])->findOrFail($id);

        return response()->json($lead->logs->map(function ($log) {
            return [
                'estado_anterior' => $log->valor_viejo,
                'estado_nuevo' => $log->valor_nuevo,
                'comentario' => $log->detalle,
                'usuario' => $log->usuario->name ?? 'Desconocido',
                'fecha' => $log->created_at->format('Y-m-d H:i'),
            ];
        }));
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
