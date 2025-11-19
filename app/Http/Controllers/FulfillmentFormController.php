<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class FulfillmentFormController extends Controller
{
    public function __construct()
    {
        // PATRÓN: Middleware de permisos - Solo admin y cms
        $this->middleware(function ($request, $next) {
            $userRole = auth()->user()->getRoleNames()->first();
            if (!in_array($userRole, ['admin', 'cms'])) {
                abort(403, 'Acceso no autorizado.');
            }
            return $next($request);
        });
    }

    /**
     * Mostrar la vista del formulario Fulfillment Daily
     */
    public function index()
    {
        // Obtener el formulario Fulfillment Daily
        $form = Form::where('module', 'fulfillment')
            ->where('status', 'active')
            ->with('fields')
            ->first();

        if (!$form) {
            abort(404, 'Formulario Fulfillment Daily no encontrado.');
        }

        return view('fulfillment.form', compact('form'));
    }

    /**
     * Obtener todas las respuestas del formulario (para DataTables)
     */
    public function getSubmissions(Request $request)
    {
        $form = Form::where('module', 'fulfillment')
            ->where('status', 'active')
            ->first();

        if (!$form) {
            return response()->json(['error' => 'Formulario no encontrado'], 404);
        }

        if ($request->ajax()) {
            $query = FormSubmission::where('form_id', $form->id)
                ->with(['user'])
                ->orderByDesc('submitted_at');

            return DataTables::of($query)
                ->addColumn('fecha', function ($submission) {
                    return $submission->getFieldValue('fecha', 'N/A');
                })
                ->addColumn('responsable', function ($submission) {
                    return $submission->getFieldValue('nombre-responsable', 'N/A');
                })
                ->addColumn('clientes_onbordeados', function ($submission) {
                    return $submission->getFieldValue('clientes-onbordeados', '0');
                })
                ->addColumn('alumnos_dificultades', function ($submission) {
                    return $submission->getFieldValue('alumnos-con-dificultades', '0');
                })
                ->addColumn('satisfaccion', function ($submission) {
                    $rating = $submission->getFieldValue('satisfaccion-alumnos', 0);
                    $stars = '';
                    for ($i = 1; $i <= 5; $i++) {
                        $stars .= $i <= $rating ? '⭐' : '☆';
                    }
                    return $stars;
                })
                ->addColumn('submitted_by', function ($submission) {
                    return $submission->user ? $submission->user->name : 'N/A';
                })
                ->addColumn('submitted_date', function ($submission) {
                    return $submission->submitted_at ? $submission->submitted_at->format('Y-m-d H:i') : 'N/A';
                })
                ->addColumn('action', function ($submission) {
                    $viewBtn = '<button class="btn btn-sm btn-info me-1 view-submission-btn"
                                data-submission-id="' . $submission->id . '" title="Ver detalles">
                        <i class="bi bi-eye"></i>
                    </button>';

                    return $viewBtn;
                })
                ->rawColumns(['action', 'satisfaccion'])
                ->make(true);
        }
    }

    /**
     * Guardar nueva respuesta del formulario
     */
    public function store(Request $request)
    {
        // Buscar el formulario Fulfillment Daily
        $form = Form::where('module', 'fulfillment')
            ->where('status', 'active')
            ->with('fields')
            ->first();

        if (!$form) {
            return response()->json([
                'success' => false,
                'message' => 'Formulario Fulfillment Daily no encontrado.'
            ], 404);
        }

        // Validación dinámica
        $rules = [];
        foreach ($form->fields as $field) {
            $fieldRules = [];

            if ($field->is_required) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            switch ($field->field_type) {
                case 'email':
                    $fieldRules[] = 'email';
                    break;
                case 'number':
                case 'scale':
                case 'rating':
                    $fieldRules[] = 'numeric';
                    break;
                case 'date':
                    $fieldRules[] = 'date';
                    break;
            }

            $rules[$field->field_name] = implode('|', $fieldRules);
        }

        $validated = $request->validate($rules);

        try {
            $submission = FormSubmission::create([
                'form_id' => $form->id,
                'lead_id' => null, // No está relacionado con un lead específico
                'user_id' => Auth::id(),
                'submission_data' => $validated,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status' => FormSubmission::STATUS_APPROVED, // Auto-aprobado
                'submitted_at' => now(),
            ]);

            Log::create([
                'id_tabla' => $submission->id,
                'tabla' => 'form_submissions',
                'tipo_log' => 'fulfillment_daily_submitted',
                'detalle' => 'Formulario Fulfillment Daily completado',
                'valor_nuevo' => 'approved',
                'id_usuario' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Formulario enviado exitosamente.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener detalles de una respuesta específica
     */
    public function show($submissionId)
    {
        $submission = FormSubmission::with(['form.fields', 'user'])->findOrFail($submissionId);

        $formattedData = [];
        foreach ($submission->form->fields as $field) {
            $fieldName = $field->field_name;
            $value = $submission->getFieldValue($fieldName, 'N/A');

            // Format array values (checkbox)
            if (is_array($value)) {
                $value = implode(', ', $value);
            }

            // Format rating values
            if ($field->field_type === 'rating' && is_numeric($value)) {
                $stars = '';
                for ($i = 1; $i <= 5; $i++) {
                    $stars .= $i <= $value ? '⭐' : '☆';
                }
                $value = $stars . ' (' . $value . '/5)';
            }

            $formattedData[] = [
                'label' => $field->label,
                'value' => $value,
                'type' => $field->field_type
            ];
        }

        return response()->json([
            'success' => true,
            'submission' => [
                'id' => $submission->id,
                'form_name' => $submission->form->name,
                'submitted_by' => $submission->user ? $submission->user->name : 'N/A',
                'submitted_at' => $submission->submitted_at ? $submission->submitted_at->format('Y-m-d H:i:s') : 'N/A',
                'status' => FormSubmission::getStatuses()[$submission->status] ?? $submission->status,
                'data' => $formattedData
            ]
        ]);
    }
}
