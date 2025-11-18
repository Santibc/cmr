<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class FormSubmissionController extends Controller
{
    public function __construct()
    {
        // PATRÓN: Middleware de permisos
        $this->middleware(function ($request, $next) {
            $userRole = auth()->user()->getRoleNames()->first();
            if (!in_array($userRole, ['admin', 'cms', 'closer', 'traige'])) {
                abort(403, 'Acceso no autorizado.');
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of submissions for a specific form.
     */
    public function index(Request $request, $formId)
    {
        $form = Form::with('fields')->findOrFail($formId);

        // PATRÓN: DataTables server-side processing
        if ($request->ajax()) {
            $query = FormSubmission::where('form_id', $formId)
                ->with(['user', 'lead'])
                ->orderByDesc('submitted_at');

            return DataTables::of($query)
                ->addColumn('submitted_by', function ($submission) {
                    if ($submission->user) {
                        return $submission->user->name;
                    }
                    return 'N/A';
                })
                ->addColumn('related_lead', function ($submission) {
                    if ($submission->lead) {
                        return $submission->lead->nombre;
                    }
                    return 'N/A';
                })
                ->addColumn('status_badge', function ($submission) {
                    $badgeClass = $submission->status === 'approved' ? 'bg-success' :
                                  ($submission->status === 'pending' ? 'bg-warning' : 'bg-danger');
                    $statusLabel = FormSubmission::getStatuses()[$submission->status] ?? $submission->status;
                    return '<span class="badge ' . $badgeClass . '">' . $statusLabel . '</span>';
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
                ->rawColumns(['action', 'status_badge'])
                ->make(true);
        }

        return view('forms.submissions.index', compact('form'));
    }

    /**
     * Get submission details.
     */
    public function show($submissionId)
    {
        $submission = FormSubmission::with(['form.fields', 'user', 'lead'])->findOrFail($submissionId);

        $formattedData = [];
        foreach ($submission->form->fields as $field) {
            $fieldName = $field->field_name;
            $value = $submission->getFieldValue($fieldName, 'N/A');

            // Format array values (checkbox)
            if (is_array($value)) {
                $value = implode(', ', $value);
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
                'related_lead' => $submission->lead ? $submission->lead->nombre : 'N/A',
                'submitted_at' => $submission->submitted_at ? $submission->submitted_at->format('Y-m-d H:i:s') : 'N/A',
                'status' => FormSubmission::getStatuses()[$submission->status] ?? $submission->status,
                'data' => $formattedData
            ]
        ]);
    }

    /**
     * Store a new form submission (generic).
     */
    public function store(Request $request, $formSlug)
    {
        $form = Form::where('slug', $formSlug)->with('fields')->firstOrFail();

        // Validar que el formulario esté activo
        if (!$form->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Este formulario no está disponible actualmente.'
            ], 403);
        }

        // Validación dinámica basada en campos del formulario
        $rules = [];
        foreach ($form->fields as $field) {
            $fieldRules = [];

            if ($field->is_required) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            // Agregar validaciones según tipo de campo
            switch ($field->field_type) {
                case 'email':
                    $fieldRules[] = 'email';
                    break;
                case 'number':
                case 'scale':
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
            // Crear submission
            $submission = FormSubmission::create([
                'form_id' => $form->id,
                'lead_id' => $request->input('lead_id'),
                'user_id' => Auth::id(),
                'submission_data' => $validated,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status' => FormSubmission::STATUS_PENDING,
                'submitted_at' => now(),
            ]);

            // PATRÓN: Log automático
            Log::create([
                'id_tabla' => $submission->id,
                'tabla' => 'form_submissions',
                'tipo_log' => 'submission_created',
                'detalle' => 'Nueva respuesta enviada para formulario: ' . $form->name,
                'valor_nuevo' => 'pending',
                'id_usuario' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Formulario enviado exitosamente.',
                'submission' => $submission
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar formulario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Render Traige Daily form fields.
     */
    public function renderTraigeDaily()
    {
        $form = Form::where('module', 'traige')
            ->where('status', 'active')
            ->with('fields')
            ->first();

        if (!$form) {
            return response()->json([
                'success' => false,
                'message' => 'Formulario Traige Daily no encontrado.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'form' => [
                'id' => $form->id,
                'name' => $form->name,
                'description' => $form->description,
                'fields' => $form->fields->map(function ($field) {
                    return [
                        'id' => $field->id,
                        'label' => $field->label,
                        'field_type' => $field->field_type,
                        'field_name' => $field->field_name,
                        'placeholder' => $field->placeholder,
                        'is_required' => $field->is_required,
                        'options' => $field->options,
                        'help_text' => $field->help_text,
                    ];
                })
            ]
        ]);
    }

    /**
     * Store Traige Daily submission.
     */
    public function storeTraigeDaily(Request $request)
    {
        // Buscar el formulario Traige Daily
        $form = Form::where('module', 'traige')
            ->where('status', 'active')
            ->with('fields')
            ->first();

        if (!$form) {
            return response()->json([
                'success' => false,
                'message' => 'Formulario Traige Daily no encontrado.'
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
                'lead_id' => $request->input('lead_id'),
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
                'tipo_log' => 'traige_daily_submitted',
                'detalle' => 'Formulario Traige Daily completado',
                'valor_nuevo' => 'approved',
                'id_usuario' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Traige Daily enviado exitosamente.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Render Closer Daily form fields.
     */
    public function renderCloserDaily()
    {
        $form = Form::where('module', 'leads')
            ->where('status', 'active')
            ->with('fields')
            ->first();

        if (!$form) {
            return response()->json([
                'success' => false,
                'message' => 'Formulario Closer Daily no encontrado.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'form' => [
                'id' => $form->id,
                'name' => $form->name,
                'description' => $form->description,
                'fields' => $form->fields->map(function ($field) {
                    return [
                        'id' => $field->id,
                        'label' => $field->label,
                        'field_type' => $field->field_type,
                        'field_name' => $field->field_name,
                        'placeholder' => $field->placeholder,
                        'is_required' => $field->is_required,
                        'options' => $field->options,
                        'help_text' => $field->help_text,
                    ];
                })
            ]
        ]);
    }

    /**
     * Store Closer Daily submission.
     */
    public function storeCloserDaily(Request $request)
    {
        // Buscar el formulario Closer Daily
        $form = Form::where('module', 'leads')
            ->where('status', 'active')
            ->with('fields')
            ->first();

        if (!$form) {
            return response()->json([
                'success' => false,
                'message' => 'Formulario Closer Daily no encontrado.'
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
                'tipo_log' => 'closer_daily_submitted',
                'detalle' => 'Formulario Closer Daily completado',
                'valor_nuevo' => 'approved',
                'id_usuario' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Closer Daily enviado exitosamente.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export submissions to CSV.
     */
    public function export($formId)
    {
        $form = Form::with(['fields', 'submissions'])->findOrFail($formId);

        $filename = 'respuestas_' . \Str::slug($form->name) . '_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($form) {
            $file = fopen('php://output', 'w');

            // Headers del CSV
            $csvHeaders = ['ID', 'Enviado por', 'Lead relacionado', 'Fecha de envío', 'Estado'];
            foreach ($form->fields as $field) {
                $csvHeaders[] = $field->label;
            }
            fputcsv($file, $csvHeaders);

            // Datos
            foreach ($form->submissions as $submission) {
                $row = [
                    $submission->id,
                    $submission->user ? $submission->user->name : 'N/A',
                    $submission->lead ? $submission->lead->nombre : 'N/A',
                    $submission->submitted_at ? $submission->submitted_at->format('Y-m-d H:i') : 'N/A',
                    FormSubmission::getStatuses()[$submission->status] ?? $submission->status
                ];

                foreach ($form->fields as $field) {
                    $value = $submission->getFieldValue($field->field_name, '');
                    if (is_array($value)) {
                        $value = implode(', ', $value);
                    }
                    $row[] = $value;
                }

                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
