<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\ContractTemplate;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ContractApprovalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Construir la consulta base una sola vez
            $query = Sale::with(['lead', 'contractTemplate', 'user'])
                ->whereNotNull('contract_template_id')
                ->where('contract_approved', false)
                ->whereNotNull('contract_data')
                ->orderByDesc('id');

            return DataTables::of($query)
                ->addColumn('action', function ($sale) {
                    $buttons = '<div class="d-flex justify-content-start gap-1">';
                    $buttons .= '<button class="btn btn-sm btn-primary edit-contract-btn"
                                    data-sale-id="' . $sale->id . '"
                                    title="Editar y Aprobar">
                                <i class="bi bi-pencil-check"></i> Revisar
                            </button>';
                    $buttons .= '</div>';
                    return $buttons;
                })
                ->addColumn('lead_name', function ($sale) {
                    return $sale->lead ? $sale->lead->nombre : 'N/A';
                })
                ->addColumn('client_name', function ($sale) {
                    return $sale->nombre_cliente . ' ' . $sale->apellido_cliente;
                })
                ->addColumn('contract_name', function ($sale) {
                    return $sale->contractTemplate ? $sale->contractTemplate->name : 'N/A';
                })
                ->addColumn('closer_name', function ($sale) {
                    return $sale->user ? $sale->user->name : 'N/A';
                })
                ->addColumn('created_at_formatted', function ($sale) {
                    return $sale->created_at->format('d/m/Y H:i');
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('contracts.approval.index');
    }

    public function edit($saleId)
    {
        $sale = Sale::with(['contractTemplate', 'lead'])->findOrFail($saleId);

        if ($sale->contract_approved) {
            return redirect()->route('contracts.approval.index')
                ->with('error', 'Este contrato ya ha sido aprobado.');
        }

        return view('contracts.approval.edit', compact('sale'));
    }

    public function update(Request $request, $saleId)
    {
        $sale = Sale::with('contractTemplate')->findOrFail($saleId);

        if ($sale->contract_approved) {
            return back()->with('error', 'Este contrato ya ha sido aprobado.');
        }

        // Validar todos los campos dinámicos
        $rules = [];
        foreach ($sale->contractTemplate->dynamic_fields as $field) {
            $rules[$field] = 'required|string';
        }

        $validated = $request->validate($rules);

        // Preparar datos para actualizar
        $updateData = ['contract_data' => $validated];

        // Agregar fecha de firma si se proporcionó
        if ($request->filled('contract_signed_date')) {
            $updateData['contract_signed_date'] = $request->contract_signed_date;
        }

        // Actualizar datos del contrato
        $sale->update($updateData);

        return back()->with('success', 'Contrato actualizado exitosamente.');
    }

    public function approve($saleId)
    {
        $sale = Sale::with(['lead', 'contractTemplate'])->findOrFail($saleId);

        if ($sale->contract_approved) {
            return back()->with('error', 'Este contrato ya ha sido aprobado.');
        }

        $sale->update(['contract_approved' => true]);

        // Crear log de contrato aprobado
        \App\Models\Log::create([
            'id_tabla' => $sale->lead->id,
            'tabla' => 'leads',
            'detalle' => 'Contrato aprobado por CSM. Contrato: ' . ($sale->contractTemplate->name ?? 'N/A'),
            'archivo_soporte' => route('contracts.download', $sale->id),
            'tipo_log' => 'contrato',
            'valor_viejo' => 'pendiente_aprobacion',
            'valor_nuevo' => 'aprobado',
            'id_usuario' => \Illuminate\Support\Facades\Auth::id(),
            'estado' => 1,
        ]);

        return redirect()->route('contracts.approval.index')
            ->with('success', 'Contrato aprobado exitosamente.');
    }

    public function previewAjax(Request $request, $saleId)
    {
        $sale = Sale::with('contractTemplate')->findOrFail($saleId);

        if (!$sale->contractTemplate) {
            return response('Contrato no encontrado', 404);
        }

        // Combinar datos existentes con datos del formulario
        $formData = $request->except(['_token', '_method']);
        $contractData = array_merge($sale->contract_data ?? [], $formData);

        // Crear objeto temporal
        $tempSale = clone $sale;
        $tempSale->contract_data = $contractData;

        return $this->generateContractHtml($tempSale, $sale->contractTemplate);
    }

    private function generateContractHtml($sale, $contractTemplate)
    {
        $html = $contractTemplate->html_content;
        $contractData = $sale->contract_data ?? [];

        // Asegurar que siempre tenga la fecha actual en la vista previa
        $today = now();
        $contractData['dia'] = $contractData['dia'] ?? $today->day;
        $contractData['mes'] = $contractData['mes'] ?? $this->getMonthName($today->month);
        $contractData['anio'] = $contractData['anio'] ?? $today->year;

        // Usar solo los datos del contrato tal como están
        foreach ($contractData as $key => $value) {
            if ($key === 'imagen_firma' && $value) {
                // Para imágenes de firma, insertar como img tag
                $html = str_replace('{' . $key . '}', '<img src="' . $value . '" class="signature-image">', $html);
            } else {
                $html = str_replace('{' . $key . '}', $value ?? '', $html);
            }
        }

        return $html;
    }

    private function getMonthName($monthNumber)
    {
        $months = [
            1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
            5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
            9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
        ];

        return $months[$monthNumber] ?? 'enero';
    }
}
