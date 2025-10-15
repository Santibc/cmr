<?php
// app/Http/Controllers/SalesController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Lead;
use App\Models\ContractTemplate;
use App\Mail\ContractSigningMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SalesController extends Controller
{
    public function form(Lead $lead)
    {
        $sale = new Sale(); // Para un nuevo formulario
        $contractTemplates = ContractTemplate::all();

        return view('sales.form', compact('lead', 'sale', 'contractTemplates'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_cliente' => 'required|string|max:255',
            'apellido_cliente' => 'required|string|max:255',
            'email_cliente' => 'required|email|max:255',
            'telefono_cliente' => 'required|string|max:50',
            'identificacion_personal' => 'nullable|string|max:100',
            'domicilio' => 'required|string|max:255',
            'metodo_pago' => 'required|string|max:100',
            'comprobante_pago' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'tipo_acuerdo' => 'required|string|max:100',
            'tipo_contrato' => 'required|in:low ticket,high ticket,beca',
            'comentarios' => 'nullable|string',
            'contract_template_id' => 'required|exists:contract_templates,id',
            'forma_de_pago' => 'required|string',
        ]);

        // Guardar comprobante directamente en public
        $file = $request->file('comprobante_pago');
        $filename = time() . '_comprobante_' . $file->getClientOriginalName();
        $file->move(public_path('comprobantes'), $filename);
        $path = 'comprobantes/' . $filename;
        $lead = lead::findOrFail($request->lead_id);

        // Generar token único para el contrato
        $contractToken = Str::random(64);

        // Preparar datos iniciales del contrato con mapeo correcto
        $today = now();
        $contractData = [
            'forma_de_pago' => $validated['forma_de_pago'],
            'nombre' => $validated['nombre_cliente'],
            'dni' => $validated['identificacion_personal'],
            'dia' => $today->day,
            'mes' => $this->getMonthName($today->month),
            'anio' => $today->year,
        ];

        $sale = Sale::create([
            'lead_id' => $lead->id,
            'user_id' => Auth::id(), // O $llamada->user_id si el closer asignado es el que cierra
            'nombre_cliente' => $validated['nombre_cliente'],
            'apellido_cliente' => $validated['apellido_cliente'],
            'email_cliente' => $validated['email_cliente'],
            'telefono_cliente' => $validated['telefono_cliente'],
            'identificacion_personal' => $validated['identificacion_personal'],
            'domicilio' => $validated['domicilio'],
            'metodo_pago' => $validated['metodo_pago'],
            'comprobante_pago_path' => $path,
            'tipo_acuerdo' => $validated['tipo_acuerdo'],
            'tipo_contrato' => $validated['tipo_contrato'],
            'comentarios' => $validated['comentarios'],
            'contract_template_id' => $validated['contract_template_id'],
            'contract_data' => $contractData,
            'contract_token' => $contractToken,
        ]);

        // Crear log de venta registrada
        \App\Models\Log::create([
            'id_tabla' => $lead->id,
            'tabla' => 'leads',
            'detalle' => 'Venta registrada exitosamente. Tipo: ' . $validated['tipo_contrato'] . ', Método de pago: ' . $validated['metodo_pago'],
            'archivo_soporte' => asset($path),
            'tipo_log' => 'venta',
            'valor_viejo' => null,
            'valor_nuevo' => 'venta_registrada',
            'id_usuario' => Auth::id(),
            'estado' => 1,
        ]);

        // Cargar la relación contractTemplate para el email
        $sale->load('contractTemplate');

        // Enviar email de contrato automáticamente
        try {
            Mail::to($sale->email_cliente)->send(new ContractSigningMail($sale));
        } catch (\Exception $e) {
            // Log el error pero no interrumpir el flujo
            \Log::error('Error enviando email de contrato: ' . $e->getMessage());
        }

        return redirect()->route('leads')->with('success', 'Venta registrada correctamente y email de contrato enviado.');
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