<?php

namespace App\Console\Commands;

use App\Models\ContractTemplate;
use Illuminate\Console\Command;

class ProcessContractTemplate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'contract:process-template';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process contract template HTML and extract dynamic fields';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Procesando plantilla de contrato...');

        $templatePath = resource_path('views/contracts/contrato_definitivo.html');

        if (!file_exists($templatePath)) {
            $this->error('No se encontró la plantilla en: ' . $templatePath);
            return Command::FAILURE;
        }

        $htmlContent = file_get_contents($templatePath);

        // Detectar campos dinámicos (texto entre llaves {}) pero excluir CSS
        preg_match_all('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', $htmlContent, $matches);
        $dynamicFields = array_unique($matches[1]);

        $this->info('Campos dinámicos encontrados: ' . implode(', ', $dynamicFields));

        // Verificar si la plantilla ya existe
        $existingTemplate = ContractTemplate::where('name', 'Acuerdo de Formación y Anexo')->first();

        if ($existingTemplate) {
            $existingTemplate->update([
                'html_content' => $htmlContent,
                'dynamic_fields' => $dynamicFields
            ]);
            $this->info('Plantilla actualizada exitosamente.');
        } else {
            ContractTemplate::create([
                'name' => 'Acuerdo de Formación y Anexo',
                'html_content' => $htmlContent,
                'dynamic_fields' => $dynamicFields
            ]);
            $this->info('Plantilla creada exitosamente.');
        }

        $this->info('Proceso completado. Campos detectados: ' . count($dynamicFields));

        return Command::SUCCESS;
    }
}
