<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\FormField;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class FormController extends Controller
{
    public function __construct()
    {
        // PATRÓN: Middleware de permisos (solo admin y cms pueden gestionar formularios)
        $this->middleware(function ($request, $next) {
            $userRole = auth()->user()->getRoleNames()->first();
            if (!in_array($userRole, ['admin', 'cms'])) {
                abort(403, 'Acceso no autorizado.');
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of the forms with DataTables.
     */
    public function index(Request $request)
    {
        // PATRÓN: DataTables server-side processing
        if ($request->ajax()) {
            $query = Form::with('user')->withCount('submissions')->orderByDesc('id');

            return DataTables::of($query)
                ->addColumn('action', function ($form) {
                    $editBtn = '<a href="' . route('forms.edit', $form->id) . '" class="btn btn-sm btn-primary me-1" title="Editar">
                        <i class="bi bi-pencil"></i>
                    </a>';

                    $submissionsBtn = '<a href="' . route('forms.submissions.index', $form->id) . '" class="btn btn-sm btn-info me-1" title="Ver respuestas">
                        <i class="bi bi-list-ul"></i> <span class="badge bg-white text-info">' . $form->submissions_count . '</span>
                    </a>';

                    $logsBtn = '<button class="btn btn-sm btn-secondary view-logs-btn" data-form-id="' . $form->id . '" title="Historial">
                        <i class="bi bi-clock-history"></i>
                    </button>';

                    return $editBtn . $submissionsBtn . $logsBtn;
                })
                ->addColumn('status_badge', function ($form) {
                    $badgeClass = $form->status === 'active' ? 'bg-success' : ($form->status === 'draft' ? 'bg-warning' : 'bg-secondary');
                    $statusLabel = Form::getStatuses()[$form->status] ?? $form->status;
                    return '<span class="badge ' . $badgeClass . '">' . $statusLabel . '</span>';
                })
                ->addColumn('module_badge', function ($form) {
                    if (!$form->module) {
                        return '<span class="badge bg-light text-dark">Sin módulo</span>';
                    }
                    return '<span class="badge bg-primary">' . ucfirst($form->module) . '</span>';
                })
                ->rawColumns(['action', 'status_badge', 'module_badge'])
                ->make(true);
        }

        return view('forms.index');
    }

    /**
     * Show the form for creating a new form.
     */
    public function create()
    {
        $fieldTypes = FormField::getFieldTypes();
        $modules = ['traige' => 'Traige', 'leads' => 'Leads', 'sales' => 'Ventas'];

        return view('forms.builder', compact('fieldTypes', 'modules'));
    }

    /**
     * Store a newly created form in storage.
     */
    public function store(Request $request)
    {
        // Pre-procesar datos del request
        $requestData = $request->all();
        if (isset($requestData['fields'])) {
            foreach ($requestData['fields'] as $index => $field) {
                // Convertir opciones de JSON string a array/object
                if (isset($field['options']) && is_string($field['options'])) {
                    $decoded = json_decode($field['options'], true);
                    $requestData['fields'][$index]['options'] = $decoded;
                }

                // Convertir is_required a booleano
                $requestData['fields'][$index]['is_required'] = isset($field['is_required']) && $field['is_required'] == '1';
            }
        }

        $validated = validator($requestData, [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:draft,active,inactive',
            'module' => 'nullable|string|max:100',
            'fields' => 'required|array|min:1',
            'fields.*.label' => 'required|string|max:255',
            'fields.*.field_type' => 'required|string',
            'fields.*.is_required' => 'nullable',
            'fields.*.placeholder' => 'nullable|string',
            'fields.*.help_text' => 'nullable|string',
            'fields.*.options' => 'nullable',
            'fields.*.default_value' => 'nullable|string',
        ])->validate();

        DB::beginTransaction();
        try {
            // Crear formulario
            $form = Form::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'slug' => Str::slug($validated['name']) . '-' . time(),
                'status' => $validated['status'],
                'module' => $validated['module'] ?? null,
                'user_id' => Auth::id(),
            ]);

            // Crear campos
            foreach ($validated['fields'] as $index => $fieldData) {
                $form->fields()->create([
                    'label' => $fieldData['label'],
                    'field_type' => $fieldData['field_type'],
                    'field_name' => Str::slug($fieldData['label']),
                    'is_required' => $fieldData['is_required'] ?? false,
                    'placeholder' => $fieldData['placeholder'] ?? null,
                    'help_text' => $fieldData['help_text'] ?? null,
                    'options' => $fieldData['options'] ?? null,
                    'default_value' => $fieldData['default_value'] ?? null,
                    'order' => $index,
                ]);
            }

            // PATRÓN: Log automático
            Log::create([
                'id_tabla' => $form->id,
                'tabla' => 'forms',
                'tipo_log' => 'form_created',
                'detalle' => 'Formulario creado: ' . $form->name,
                'valor_nuevo' => $form->status,
                'id_usuario' => Auth::id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Formulario creado exitosamente.',
                'form' => $form->load('fields')
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear formulario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified form.
     */
    public function edit($id)
    {
        $form = Form::with('fields')->findOrFail($id);
        $fieldTypes = FormField::getFieldTypes();
        $modules = ['traige' => 'Traige', 'leads' => 'Leads', 'sales' => 'Ventas'];

        return view('forms.builder', compact('form', 'fieldTypes', 'modules'));
    }

    /**
     * Update the specified form in storage.
     */
    public function update(Request $request, $id)
    {
        // Pre-procesar datos del request
        $requestData = $request->all();
        if (isset($requestData['fields'])) {
            foreach ($requestData['fields'] as $index => $field) {
                // Convertir opciones de JSON string a array/object
                if (isset($field['options']) && is_string($field['options'])) {
                    $decoded = json_decode($field['options'], true);
                    $requestData['fields'][$index]['options'] = $decoded;
                }

                // Convertir is_required a booleano
                $requestData['fields'][$index]['is_required'] = isset($field['is_required']) && $field['is_required'] == '1';
            }
        }

        $validated = validator($requestData, [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:draft,active,inactive',
            'module' => 'nullable|string|max:100',
            'fields' => 'required|array|min:1',
            'fields.*.id' => 'nullable|exists:form_fields,id',
            'fields.*.label' => 'required|string|max:255',
            'fields.*.field_type' => 'required|string',
            'fields.*.is_required' => 'nullable',
            'fields.*.placeholder' => 'nullable|string',
            'fields.*.help_text' => 'nullable|string',
            'fields.*.options' => 'nullable',
            'fields.*.default_value' => 'nullable|string',
        ])->validate();

        $form = Form::findOrFail($id);
        $oldStatus = $form->status;

        DB::beginTransaction();
        try {
            // Actualizar formulario
            $form->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'slug' => Str::slug($validated['name']) . '-' . $form->id,
                'status' => $validated['status'],
                'module' => $validated['module'] ?? null,
            ]);

            // Recolectar IDs de campos existentes en el request
            $fieldIdsInRequest = collect($validated['fields'])
                ->pluck('id')
                ->filter()
                ->toArray();

            // Eliminar campos que ya no están en el request
            $form->fields()->whereNotIn('id', $fieldIdsInRequest)->delete();

            // Actualizar o crear campos
            foreach ($validated['fields'] as $index => $fieldData) {
                $fieldAttributes = [
                    'form_id' => $form->id,
                    'label' => $fieldData['label'],
                    'field_type' => $fieldData['field_type'],
                    'field_name' => Str::slug($fieldData['label']),
                    'is_required' => $fieldData['is_required'] ?? false,
                    'placeholder' => $fieldData['placeholder'] ?? null,
                    'help_text' => $fieldData['help_text'] ?? null,
                    'options' => $fieldData['options'] ?? null,
                    'default_value' => $fieldData['default_value'] ?? null,
                    'order' => $index,
                ];

                if (!empty($fieldData['id'])) {
                    // Actualizar campo existente
                    FormField::where('id', $fieldData['id'])->update($fieldAttributes);
                } else {
                    // Crear nuevo campo
                    FormField::create($fieldAttributes);
                }
            }

            // PATRÓN: Log automático
            Log::create([
                'id_tabla' => $form->id,
                'tabla' => 'forms',
                'tipo_log' => 'form_updated',
                'detalle' => 'Formulario actualizado: ' . $form->name,
                'valor_viejo' => $oldStatus,
                'valor_nuevo' => $form->status,
                'id_usuario' => Auth::id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Formulario actualizado exitosamente.',
                'form' => $form->fresh()->load('fields')
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar formulario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified form from storage (soft delete).
     */
    public function destroy($id)
    {
        $form = Form::findOrFail($id);

        try {
            // PATRÓN: Log antes de eliminar
            Log::create([
                'id_tabla' => $form->id,
                'tabla' => 'forms',
                'tipo_log' => 'form_deleted',
                'detalle' => 'Formulario eliminado: ' . $form->name,
                'valor_viejo' => $form->status,
                'valor_nuevo' => 'deleted',
                'id_usuario' => Auth::id(),
            ]);

            $form->delete(); // Soft delete

            return response()->json([
                'success' => true,
                'message' => 'Formulario eliminado exitosamente.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar formulario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get logs for a specific form.
     */
    public function logs($id)
    {
        $form = Form::with(['submissions'])->findOrFail($id);

        // PATRÓN: Logs unificados
        $allLogs = collect();

        // Logs del formulario
        $formLogs = Log::where('tabla', 'forms')
            ->where('id_tabla', $id)
            ->with('usuario')
            ->get();
        $allLogs = $allLogs->merge($formLogs);

        // Logs de envíos si existen
        $submissionIds = $form->submissions->pluck('id');
        if ($submissionIds->isNotEmpty()) {
            $submissionLogs = Log::where('tabla', 'form_submissions')
                ->whereIn('id_tabla', $submissionIds)
                ->with('usuario')
                ->get();
            $allLogs = $allLogs->merge($submissionLogs);
        }

        $allLogs = $allLogs->sortByDesc('created_at');

        return response()->json($allLogs->map(function ($log) {
            return [
                'estado_anterior' => $log->valor_viejo ?? 'N/A',
                'estado_nuevo' => $log->valor_nuevo ?? 'N/A',
                'comentario' => $log->detalle,
                'usuario' => $log->usuario->name ?? 'Desconocido',
                'fecha' => $log->created_at->format('Y-m-d H:i'),
                'tipo' => $this->getLogType($log->tabla, $log->tipo_log),
            ];
        })->values());
    }

    /**
     * Get log type badge label (following project pattern).
     */
    private function getLogType($tabla, $tipoLog = null)
    {
        switch ($tabla) {
            case 'forms':
                if ($tipoLog === 'form_created') return 'Creación';
                elseif ($tipoLog === 'form_updated') return 'Actualización';
                elseif ($tipoLog === 'form_deleted') return 'Eliminación';
                else return 'Formulario';
            case 'form_submissions':
                return 'Respuesta';
            default:
                return 'General';
        }
    }
}
