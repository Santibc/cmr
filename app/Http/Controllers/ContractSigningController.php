<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\ContractTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ContractSigningController extends Controller
{
    public function show($token)
    {
        $sale = Sale::where('contract_token', $token)->firstOrFail();
        $contractTemplate = $sale->contractTemplate;

        if (!$contractTemplate) {
            abort(404, 'Contrato no encontrado');
        }

        // Verificar si el contrato ya fue firmado
        if ($sale->contract_signed_date) {
            return redirect()->route('contract.preview', $token)->with('info', 'Este contrato ya ha sido firmado.');
        }

        // Obtener campos que necesitan input (excluyendo solo fecha y forma_de_pago)
        $excludedFields = ['dia', 'mes', 'anio', 'forma_de_pago'];
        $missingFields = array_filter($contractTemplate->dynamic_fields, function($field) use ($excludedFields) {
            return !in_array($field, $excludedFields);
        });

        return view('contracts.sign', compact('sale', 'contractTemplate', 'missingFields'));
    }

    public function update(Request $request, $token)
    {
        $sale = Sale::where('contract_token', $token)->firstOrFail();
        $contractTemplate = $sale->contractTemplate;

        // Verificar si el contrato ya fue firmado
        if ($sale->contract_signed_date) {
            return redirect()->route('contract.preview', $token)->with('error', 'Este contrato ya ha sido firmado anteriormente.');
        }

        // Validar campos dinámicos
        $rules = [];
        $excludedFields = ['dia', 'mes', 'anio', 'forma_de_pago'];
        foreach ($contractTemplate->dynamic_fields as $field) {
            if (!in_array($field, $excludedFields)) { // Excluir solo fecha y forma_de_pago
                $rules[$field] = 'required|string';
            }
        }

        // Manejo especial para imagen de firma
        if (in_array('imagen_firma', $contractTemplate->dynamic_fields)) {
            $rules['imagen_firma'] = 'required|string'; // Base64 de la firma
        }

        $validated = $request->validate($rules);

        // Combinar con datos existentes
        $contractData = array_merge($sale->contract_data ?? [], $validated);

        // Guardar imagen de firma si existe
        if (isset($validated['imagen_firma'])) {
            $signatureData = $validated['imagen_firma'];

            if (preg_match('/^data:image\/(\w+);base64,/', $signatureData, $type)) {
                $data = substr($signatureData, strpos($signatureData, ',') + 1);
                $type = strtolower($type[1]);

                if (!in_array($type, ['jpg', 'jpeg', 'png'])) {
                    return back()->withErrors(['imagen_firma' => 'Formato de imagen no válido']);
                }

                $data = base64_decode($data);
                $fileName = 'signatures/' . $sale->id . '_' . time() . '.' . $type;

                // Crear directorio si no existe
                $signatureDir = public_path('signatures');
                if (!file_exists($signatureDir)) {
                    mkdir($signatureDir, 0755, true);
                }

                // Guardar directamente en public/signatures
                file_put_contents(public_path($fileName), $data);
                $contractData['imagen_firma'] = asset($fileName);
            }
        }

        $sale->update([
            'contract_data' => $contractData,
            'contract_signed_date' => now()
        ]);

        return redirect()->route('contract.preview', $token)->with('success', 'Contrato completado exitosamente');
    }

    public function preview($token)
    {
        $sale = Sale::where('contract_token', $token)->firstOrFail();
        $contractTemplate = $sale->contractTemplate;

        if (!$contractTemplate) {
            abort(404, 'Contrato no encontrado');
        }

        // Generar HTML del contrato con datos llenados
        $contractHtml = $this->generateContractHtml($sale, $contractTemplate);

        return view('contracts.preview', compact('sale', 'contractTemplate', 'contractHtml'));
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

    public function previewAjax(Request $request, $token)
    {
        $sale = Sale::where('contract_token', $token)->firstOrFail();
        $contractTemplate = $sale->contractTemplate;

        if (!$contractTemplate) {
            return response('Contrato no encontrado', 404);
        }

        // Combinar datos existentes con datos del formulario
        $formData = $request->except(['_token', '_method']);
        $contractData = array_merge($sale->contract_data ?? [], $formData);

        // Crear un objeto temporal para generar la vista previa
        $tempSale = clone $sale;
        $tempSale->contract_data = $contractData;

        return $this->generateContractHtml($tempSale, $contractTemplate);
    }
}
