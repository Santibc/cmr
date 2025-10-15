<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ContractTemplate;

class NewContractTemplatesSeeder extends Seeder
{
    private $originalHtml;

    public function run()
    {
        // Obtener el template original
        $originalTemplate = ContractTemplate::find(1);

        if (!$originalTemplate) {
            $this->command->error('El template original (ID=1) no existe. Por favor crear primero el template base.');
            return;
        }

        $this->originalHtml = $originalTemplate->html_content;
        $dynamicFields = json_encode(['dia', 'mes', 'anio', 'nombre', 'dni', 'forma_de_pago', 'imagen_firma', 'aclaracion']);

        // Template 2 Cuotas: 180 días, cuatro instancias
        ContractTemplate::create([
            'name' => '2 Cuotas',
            'html_content' => $this->create2CuotasTemplate(),
            'dynamic_fields' => $dynamicFields,
        ]);

        // Template 3 Cuotas: 180 días, cuatro instancias
        ContractTemplate::create([
            'name' => '3 Cuotas',
            'html_content' => $this->create3CuotasTemplate(),
            'dynamic_fields' => $dynamicFields,
        ]);

        // Template Beca: 180 días, SIN artículo 4 (Garantía)
        ContractTemplate::create([
            'name' => 'Beca',
            'html_content' => $this->createBecaTemplate(),
            'dynamic_fields' => $dynamicFields,
        ]);

        // Template Low Ticket: $500 USD, SIN artículo 4 (Garantía)
        ContractTemplate::create([
            'name' => 'Low Ticket',
            'html_content' => $this->createLowTicketTemplate(),
            'dynamic_fields' => $dynamicFields,
        ]);

        // Template Full Pago: igual al original con garantía
        ContractTemplate::create([
            'name' => 'Full Pago',
            'html_content' => $this->createFullPagoTemplate(),
            'dynamic_fields' => $dynamicFields,
        ]);

        $this->command->info('5 templates creados exitosamente!');
    }

    private function create2CuotasTemplate()
    {
        $html = $this->originalHtml;

        // Cambiar 90 días a 180 días
        $html = str_replace(
            'será de 90 días',
            'será de 180 días',
            $html
        );

        // Cambiar cinco instancias a cuatro instancias
        $html = str_replace(
            'aprobar las cinco instancias',
            'aprobar las cuatro instancias',
            $html
        );

        return $html;
    }

    private function create3CuotasTemplate()
    {
        $html = $this->originalHtml;

        // Cambiar 90 días a 180 días
        $html = str_replace(
            'será de 90 días',
            'será de 180 días',
            $html
        );

        // Cambiar cinco instancias a cuatro instancias
        $html = str_replace(
            'aprobar las cinco instancias',
            'aprobar las cuatro instancias',
            $html
        );

        return $html;
    }

    private function createBecaTemplate()
    {
        $html = $this->originalHtml;

        // Cambiar 90 días a 180 días
        $html = str_replace(
            'será de 90 días',
            'será de 180 días',
            $html
        );

        // Cambiar cinco instancias a cuatro instancias
        $html = str_replace(
            'aprobar las cinco instancias',
            'aprobar las cuatro instancias',
            $html
        );

        // ELIMINAR todo el Artículo 4 (Garantía)
        $html = preg_replace(
            '/<div class="article-title">\s*<strong>Artículo 4\. Garantía<\/strong>\s*<\/div>.*?(?=<div class="article-title">|<div class="verification-section">)/s',
            '',
            $html
        );

        // Renumerar artículos después del 3
        $html = str_replace('Artículo 5. Honorarios', 'Artículo 4. Honorarios', $html);
        $html = str_replace('Artículo 6. Forma de pago', 'Artículo 5. Forma de pago', $html);
        $html = str_replace('artículo 6.1', 'artículo 5.1', $html);
        $html = str_replace('Artículo 6.1', 'Artículo 5.1', $html);
        $html = str_replace('Artículo 7. Condiciones', 'Artículo 6. Condiciones', $html);
        $html = str_replace('Artículo 8. Exención', 'Artículo 7. Exención', $html);
        $html = str_replace('Artículo 9. Rescisión', 'Artículo 8. Rescisión', $html);
        $html = str_replace('Artículo 10. Ley aplicable', 'Artículo 9. Ley aplicable', $html);
        $html = str_replace('Artículo 11. Divisibilidad', 'Artículo 10. Divisibilidad', $html);

        return $html;
    }

    private function createLowTicketTemplate()
    {
        $html = $this->originalHtml;

        // Cambiar 90 días a 180 días
        $html = str_replace(
            'será de 90 días',
            'será de 180 días',
            $html
        );

        // Cambiar $2,000 a $500
        $html = str_replace(
            '$2.000 (USD)',
            '$500 (USD)',
            $html
        );

        $html = str_replace(
            '$2.000 (USD',
            '$500 (USD',
            $html
        );

        // ELIMINAR todo el Artículo 4 (Garantía)
        $html = preg_replace(
            '/<div class="article-title">\s*<strong>Artículo 4\. Garantía<\/strong>\s*<\/div>.*?(?=<div class="article-title">|<div class="verification-section">)/s',
            '',
            $html
        );

        // Renumerar artículos después del 3
        $html = str_replace('Artículo 5. Honorarios', 'Artículo 4. Honorarios', $html);
        $html = str_replace('Artículo 6. Forma de pago', 'Artículo 5. Forma de pago', $html);
        $html = str_replace('artículo 6.1', 'artículo 5.1', $html);
        $html = str_replace('Artículo 6.1', 'Artículo 5.1', $html);
        $html = str_replace('Artículo 7. Condiciones', 'Artículo 6. Condiciones', $html);
        $html = str_replace('Artículo 8. Exención', 'Artículo 7. Exención', $html);
        $html = str_replace('Artículo 9. Rescisión', 'Artículo 8. Rescisión', $html);
        $html = str_replace('Artículo 10. Ley aplicable', 'Artículo 9. Ley aplicable', $html);
        $html = str_replace('Artículo 11. Divisibilidad', 'Artículo 10. Divisibilidad', $html);

        return $html;
    }

    private function createFullPagoTemplate()
    {
        // Full Pago es exactamente igual al original
        return $this->originalHtml;
    }
}
