<?php

namespace Database\Seeders;

use App\Models\Form;
use App\Models\FormField;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Obtener el primer usuario admin
        $admin = User::first();

        if (!$admin) {
            $this->command->error('No hay usuarios en el sistema. Ejecuta primero DatabaseSeeder.');
            return;
        }

        // ==========================================
        // FORMULARIO 1: TRIAGE DAILY
        // ==========================================
        $triageDaily = Form::firstOrCreate(
            ['slug' => 'triage-daily'],
            [
                'name' => 'TRIAGE DAILY',
                'description' => 'Rellenar formulario siempre al finalizar la jornada laboral. Esto es muy importante para llevar al día las métricas y escalar en el negocio.',
                'status' => 'active',
                'module' => 'traige',
                'user_id' => $admin->id,
            ]
        );

        // Campos del formulario Traige Daily
        $triageFields = [
            [
                'label' => 'Fecha',
                'field_type' => 'date',
                'field_name' => 'fecha',
                'placeholder' => 'dd/mm/aaaa',
                'is_required' => true,
                'order' => 0,
            ],
            [
                'label' => 'Nombre del prospecto',
                'field_type' => 'text',
                'field_name' => 'nombre-del-prospecto',
                'placeholder' => 'Ingrese el nombre',
                'is_required' => true,
                'order' => 1,
            ],
            [
                'label' => 'Fuente de captación',
                'field_type' => 'radio',
                'field_name' => 'fuente-de-captacion',
                'is_required' => true,
                'options' => ['ADS', 'INSTAGRAM', 'YOUTUBE', 'OTRO'],
                'order' => 2,
            ],
            [
                'label' => 'CALIFICACION INICIAL',
                'field_type' => 'scale',
                'field_name' => 'calificacion-inicial',
                'is_required' => true,
                'options' => ['min' => 1, 'max' => 10],
                'order' => 3,
            ],
            [
                'label' => 'INTERES DEL PROSPECTO',
                'field_type' => 'radio',
                'field_name' => 'interes-del-prospecto',
                'is_required' => false,
                'options' => ['BAJA', 'MEDIA', 'ALTA'],
                'order' => 4,
            ],
            [
                'label' => 'PRESUPUESTO DISPONIBLE',
                'field_type' => 'text',
                'field_name' => 'presupuesto-disponible',
                'placeholder' => 'Ingrese el presupuesto',
                'is_required' => false,
                'order' => 5,
            ],
            [
                'label' => 'PROBLEMA PRINCIPAL IDENTIFICADO',
                'field_type' => 'textarea',
                'field_name' => 'problema-principal-identificado',
                'placeholder' => 'Describa el problema',
                'is_required' => false,
                'order' => 6,
            ],
            [
                'label' => 'URGENCIA PERCIBIDA',
                'field_type' => 'radio',
                'field_name' => 'urgencia-percibida',
                'is_required' => false,
                'options' => ['BAJA', 'MEDIA', 'ALTA'],
                'order' => 7,
            ],
            [
                'label' => 'AVANZA A LLAMADA CON CLOSER',
                'field_type' => 'radio',
                'field_name' => 'avanza-a-llamada-con-closer',
                'is_required' => false,
                'options' => ['SI', 'NO'],
                'order' => 8,
            ],
            [
                'label' => 'OBSERVACIONES/ NOTAS ADICIONES',
                'field_type' => 'textarea',
                'field_name' => 'observaciones-notas-adiciones',
                'placeholder' => 'Añade observaciones',
                'is_required' => false,
                'order' => 9,
            ],
        ];

        // Solo crear campos si el formulario no tenía campos previamente
        if ($triageDaily->fields()->count() === 0) {
            foreach ($triageFields as $fieldData) {
                FormField::create(array_merge($fieldData, ['form_id' => $triageDaily->id]));
            }
            $this->command->info('✅ Formulario TRIAGE DAILY creado con ' . count($triageFields) . ' campos');
        } else {
            $this->command->info('ℹ️  Formulario TRIAGE DAILY ya existe con ' . $triageDaily->fields()->count() . ' campos');
        }

        // ==========================================
        // FORMULARIO 2: CLOSER DAILY
        // ==========================================
        $closerDaily = Form::firstOrCreate(
            ['slug' => 'closer-daily'],
            [
                'name' => 'CLOSER DAILY',
                'description' => 'Completar todos los días post finalización de jornada laboral',
                'status' => 'active',
                'module' => 'leads',
                'user_id' => $admin->id,
            ]
        );

        // Campos del formulario Closer Daily
        $closerFields = [
            [
                'label' => 'FECHA',
                'field_type' => 'date',
                'field_name' => 'fecha',
                'placeholder' => 'Día, mes, año',
                'is_required' => false,
                'order' => 0,
            ],
            [
                'label' => 'NOMBRE DEL CLOSER',
                'field_type' => 'text',
                'field_name' => 'nombre-del-closer',
                'placeholder' => 'Texto de respuesta breve',
                'is_required' => false,
                'order' => 1,
            ],
            [
                'label' => 'DONDE NOS CONOCIO',
                'field_type' => 'radio',
                'field_name' => 'donde-nos-conocio',
                'is_required' => false,
                'options' => ['YT'],
                'order' => 2,
            ],
            [
                'label' => 'LLAMADAS TOTALES',
                'field_type' => 'scale',
                'field_name' => 'llamadas-totales',
                'is_required' => false,
                'options' => ['min' => 1, 'max' => 10],
                'order' => 3,
            ],
            [
                'label' => 'LLAMADAS CONECTADAS',
                'field_type' => 'scale',
                'field_name' => 'llamadas-conectadas',
                'is_required' => false,
                'options' => ['min' => 1, 'max' => 10],
                'order' => 4,
            ],
            [
                'label' => 'PRESENTACIONES REALIZADAS',
                'field_type' => 'scale',
                'field_name' => 'presentaciones-realizadas',
                'is_required' => false,
                'options' => ['min' => 1, 'max' => 10],
                'order' => 5,
            ],
            [
                'label' => 'VENTAS CERRADAS',
                'field_type' => 'scale',
                'field_name' => 'ventas-cerradas',
                'is_required' => false,
                'options' => ['min' => 0, 'max' => 10],
                'order' => 6,
            ],
            [
                'label' => 'CASH COLLECTED',
                'field_type' => 'text',
                'field_name' => 'cash-collected',
                'placeholder' => 'Texto de respuesta breve',
                'is_required' => false,
                'order' => 7,
            ],
            [
                'label' => 'REVENUE',
                'field_type' => 'text',
                'field_name' => 'revenue',
                'placeholder' => 'Texto de respuesta breve',
                'is_required' => false,
                'order' => 8,
            ],
            [
                'label' => 'FUENTE',
                'field_type' => 'text',
                'field_name' => 'fuente',
                'placeholder' => 'Texto de respuesta breve',
                'is_required' => false,
                'order' => 9,
            ],
            [
                'label' => 'SEGUIMIENTOS',
                'field_type' => 'text',
                'field_name' => 'seguimientos',
                'placeholder' => 'Texto de respuesta breve',
                'is_required' => false,
                'order' => 10,
            ],
            [
                'label' => 'OBSERVACIONES',
                'field_type' => 'textarea',
                'field_name' => 'observaciones',
                'placeholder' => 'Texto de respuesta largo',
                'is_required' => false,
                'order' => 11,
            ],
            [
                'label' => 'CALIFICACION DE DESEMPEÑO',
                'field_type' => 'rating',
                'field_name' => 'calificacion-de-desempeno',
                'is_required' => false,
                'options' => ['min' => 1, 'max' => 5],
                'order' => 12,
            ],
        ];

        // Solo crear campos si el formulario no tenía campos previamente
        if ($closerDaily->fields()->count() === 0) {
            foreach ($closerFields as $fieldData) {
                FormField::create(array_merge($fieldData, ['form_id' => $closerDaily->id]));
            }
            $this->command->info('✅ Formulario CLOSER DAILY creado con ' . count($closerFields) . ' campos');
        } else {
            $this->command->info('ℹ️  Formulario CLOSER DAILY ya existe con ' . $closerDaily->fields()->count() . ' campos');
        }

        // ==========================================
        // FORMULARIO 3: ELITE CLOSER SOCIETY ONBOARDING
        // ==========================================
        $eliteOnboarding = Form::firstOrCreate(
            ['slug' => 'elite-closer-society-onboarding'],
            [
                'name' => 'Elite Closer Society Onboarding',
                'description' => 'Formulario de inscripción para Elite Closer Society',
                'status' => 'active',
                'module' => null,
                'user_id' => $admin->id,
            ]
        );

        // Campos del formulario Elite Closer Society Onboarding
        $eliteFields = [
            [
                'label' => 'Dirección de correo electrónico',
                'field_type' => 'email',
                'field_name' => 'email-address',
                'placeholder' => 'Tu dirección de correo electrónico',
                'is_required' => true,
                'order' => 0,
            ],
            [
                'label' => 'Nombre y Apellido',
                'field_type' => 'text',
                'field_name' => 'full-name',
                'placeholder' => 'Tu respuesta',
                'is_required' => true,
                'order' => 1,
            ],
            [
                'label' => 'Edad',
                'field_type' => 'number',
                'field_name' => 'age',
                'placeholder' => 'Tu respuesta',
                'is_required' => true,
                'order' => 2,
            ],
            [
                'label' => 'País de nacimiento',
                'field_type' => 'text',
                'field_name' => 'country-of-birth',
                'placeholder' => 'Tu respuesta',
                'is_required' => true,
                'order' => 3,
            ],
            [
                'label' => 'País de residencia actual',
                'field_type' => 'text',
                'field_name' => 'current-country-of-residence',
                'placeholder' => 'Tu respuesta',
                'is_required' => true,
                'order' => 4,
            ],
            [
                'label' => '¿Hace cuánto conoces a Fede?',
                'field_type' => 'radio',
                'field_name' => 'how-long-have-you-known-fede',
                'is_required' => true,
                'options' => ['1 - 3 meses', '6 meses - 1 año', 'Más de 1 año'],
                'order' => 5,
            ],
            [
                'label' => '¿Cómo conociste a Fede?',
                'field_type' => 'radio',
                'field_name' => 'how-did-you-meet-fede',
                'is_required' => true,
                'options' => ['Anuncios', 'You Tube', 'Contenido de Instagram', 'Referidos'],
                'order' => 6,
            ],
            [
                'label' => 'Número de teléfono',
                'field_type' => 'text',
                'field_name' => 'phone-number',
                'placeholder' => 'Tu respuesta',
                'is_required' => true,
                'help_text' => 'Indicar prefijo de país, ej: Argentina +54',
                'order' => 7,
            ],
            [
                'label' => '¿Trabajas actualmente?',
                'field_type' => 'radio',
                'field_name' => 'current-employment-status',
                'is_required' => true,
                'options' => ['No', 'Sí, en relación de dependencia', 'Sí, independiente', 'Estudiante'],
                'order' => 8,
            ],
            [
                'label' => 'Ingresos mensuales en USD',
                'field_type' => 'radio',
                'field_name' => 'monthly-income-in-usd',
                'is_required' => true,
                'options' => ['100 - 300 USD', '500 - 700 USD', '1000 - 1500 USD', '+1500 USD'],
                'order' => 9,
            ],
            [
                'label' => '¿Tienes experiencia en algún negocio digital?',
                'field_type' => 'textarea',
                'field_name' => 'digital-business-experience',
                'placeholder' => 'Tu respuesta',
                'is_required' => false,
                'order' => 10,
            ],
            [
                'label' => '¿Por qué quieres entrar a Elite Closer Society?',
                'field_type' => 'textarea',
                'field_name' => 'why-join-elite-closer-society',
                'placeholder' => 'Tu respuesta',
                'is_required' => true,
                'order' => 11,
            ],
            [
                'label' => '¿Con qué programa vas a entrar?',
                'field_type' => 'radio',
                'field_name' => 'program-selection',
                'is_required' => true,
                'options' => ['New Closer (2.000 USD)', 'Basic Closer (500 USD)'],
                'order' => 12,
            ],
            [
                'label' => '¿Contemplaste entrar en otra formación?',
                'field_type' => 'radio',
                'field_name' => 'considered-other-training-programs',
                'is_required' => true,
                'options' => ['Sí', 'No'],
                'order' => 13,
            ],
            [
                'label' => '¿Qué esperas de la formación?',
                'field_type' => 'textarea',
                'field_name' => 'training-expectations',
                'placeholder' => 'Tu respuesta',
                'is_required' => true,
                'order' => 14,
            ],
            [
                'label' => '¿Qué dificultades se te presentan al aprender por tu cuenta?',
                'field_type' => 'textarea',
                'field_name' => 'self-study-challenges',
                'placeholder' => 'Tu respuesta',
                'is_required' => true,
                'order' => 15,
            ],
            [
                'label' => '¿Cuál es tu objetivo con la formación?',
                'field_type' => 'textarea',
                'field_name' => 'training-objectives',
                'placeholder' => 'Tu respuesta',
                'is_required' => true,
                'order' => 16,
            ],
        ];

        // Solo crear campos si el formulario no tenía campos previamente
        if ($eliteOnboarding->fields()->count() === 0) {
            foreach ($eliteFields as $fieldData) {
                FormField::create(array_merge($fieldData, ['form_id' => $eliteOnboarding->id]));
            }
            $this->command->info('✅ Formulario ELITE CLOSER SOCIETY ONBOARDING creado con ' . count($eliteFields) . ' campos');
        } else {
            $this->command->info('ℹ️  Formulario ELITE CLOSER SOCIETY ONBOARDING ya existe con ' . $eliteOnboarding->fields()->count() . ' campos');
        }

        // ==========================================
        // FORMULARIO 4: FORMULARIO VENTA CERRADA
        // ==========================================
        $ventaCerrada = Form::firstOrCreate(
            ['slug' => 'formulario-venta-cerrada'],
            [
                'name' => 'Formulario Venta cerrada',
                'description' => 'Registro de ventas cerradas con información detallada',
                'status' => 'active',
                'module' => null,
                'user_id' => $admin->id,
            ]
        );

        // Campos del formulario Venta cerrada
        $ventaCerradaFields = [
            [
                'label' => 'Fecha',
                'field_type' => 'date',
                'field_name' => 'fecha',
                'placeholder' => 'DD/MM/AAAA',
                'is_required' => true,
                'order' => 0,
            ],
            [
                'label' => 'NOMBRE DEL PROSPECTO',
                'field_type' => 'text',
                'field_name' => 'nombre-del-prospecto',
                'placeholder' => 'Tu respuesta',
                'is_required' => true,
                'order' => 1,
            ],
            [
                'label' => 'TICKET CERRADO',
                'field_type' => 'radio',
                'field_name' => 'ticket-cerrado',
                'is_required' => true,
                'options' => ['HT', 'BECA', 'LT', 'UPSELL'],
                'order' => 2,
            ],
            [
                'label' => 'METODOLOGÍA',
                'field_type' => 'radio',
                'field_name' => 'metodologia',
                'is_required' => true,
                'options' => ['FULL', 'RESERVA', 'CUOTAS', 'COMPLETÓ RESERVA'],
                'order' => 3,
            ],
            [
                'label' => 'CASH COLLECTED',
                'field_type' => 'text',
                'field_name' => 'cash-collected',
                'placeholder' => 'Tu respuesta',
                'is_required' => true,
                'order' => 4,
            ],
            [
                'label' => 'REVENUE',
                'field_type' => 'text',
                'field_name' => 'revenue',
                'placeholder' => 'Tu respuesta',
                'is_required' => true,
                'order' => 5,
            ],
            [
                'label' => 'CUOTAS PENDIENTES (SI APLICA)',
                'field_type' => 'radio',
                'field_name' => 'cuotas-pendientes',
                'is_required' => false,
                'options' => ['1', '2', '3', '4', '5'],
                'order' => 6,
            ],
            [
                'label' => 'RESTANTE A PAGAR EN CUOTAS',
                'field_type' => 'text',
                'field_name' => 'restante-a-pagar-en-cuotas',
                'placeholder' => 'Tu respuesta',
                'is_required' => false,
                'order' => 7,
            ],
            [
                'label' => 'METODO DE PAGO',
                'field_type' => 'select',
                'field_name' => 'metodo-de-pago',
                'is_required' => true,
                'options' => ['D LOCAL GO', 'WHOP', 'TRNASFERENCIA USD', 'TRANSFERENCIA PESOS', 'CRIPTO', 'EFECTIVO', 'WIRE', 'PAYPAL'],
                'order' => 8,
            ],
            [
                'label' => 'Fuente',
                'field_type' => 'radio',
                'field_name' => 'fuente',
                'is_required' => true,
                'options' => ['Instagram', 'Anuncios', 'YouTube', 'Referidos', 'Otros'],
                'order' => 9,
            ],
            [
                'label' => 'OBSERVACIONES',
                'field_type' => 'textarea',
                'field_name' => 'observaciones',
                'placeholder' => 'Tu respuesta',
                'is_required' => false,
                'order' => 10,
            ],
        ];

        // Solo crear campos si el formulario no tenía campos previamente
        if ($ventaCerrada->fields()->count() === 0) {
            foreach ($ventaCerradaFields as $fieldData) {
                FormField::create(array_merge($fieldData, ['form_id' => $ventaCerrada->id]));
            }
            $this->command->info('✅ Formulario VENTA CERRADA creado con ' . count($ventaCerradaFields) . ' campos');
        } else {
            $this->command->info('ℹ️  Formulario VENTA CERRADA ya existe con ' . $ventaCerrada->fields()->count() . ' campos');
        }

        // ============================================================
        // FORMULARIO 5: FULFILLMENT DAILY
        // ============================================================
        $this->command->info('');
        $this->command->info('📋 Creando formulario: FULFILLMENT DAILY...');

        $fulfillmentDaily = Form::firstOrCreate(
            ['slug' => 'fulfillment-daily'],
            [
                'name' => 'FULFILLMENT DAILY',
                'description' => 'Formulario diario de seguimiento de fulfillment',
                'status' => 'active',
                'module' => 'fulfillment',
                'user_id' => $admin->id,
            ]
        );

        // Campos del formulario Fulfillment Daily
        $fulfillmentFields = [
            [
                'label' => 'FECHA',
                'field_type' => 'date',
                'field_name' => 'fecha',
                'placeholder' => '',
                'is_required' => true,
                'order' => 0,
            ],
            [
                'label' => 'NOMBRE DEL RESPONSABLE',
                'field_type' => 'text',
                'field_name' => 'nombre-responsable',
                'placeholder' => 'Tu respuesta',
                'is_required' => true,
                'order' => 1,
            ],
            [
                'label' => 'CANTIDAD DE CLIENTES ONBORDEADOS',
                'field_type' => 'scale',
                'field_name' => 'clientes-onbordeados',
                'is_required' => true,
                'options' => ['min' => 0, 'max' => 10],
                'order' => 2,
            ],
            [
                'label' => 'CANTIDAD DE ALUMNOS CON DIFICULTADES DETECTADAS',
                'field_type' => 'scale',
                'field_name' => 'alumnos-con-dificultades',
                'is_required' => true,
                'options' => ['min' => 0, 'max' => 5],
                'order' => 3,
            ],
            [
                'label' => 'CASOS CRITICOS A ESCALAR',
                'field_type' => 'textarea',
                'field_name' => 'casos-criticos',
                'placeholder' => 'Tu respuesta',
                'is_required' => true,
                'order' => 4,
            ],
            [
                'label' => 'SATIFACCION DE ALUMNOS',
                'field_type' => 'rating',
                'field_name' => 'satisfaccion-alumnos',
                'is_required' => true,
                'options' => ['max' => 5],
                'order' => 5,
            ],
            [
                'label' => 'FEEDBACK RECIBIDO DE ALUMNOS',
                'field_type' => 'textarea',
                'field_name' => 'feedback-alumnos',
                'placeholder' => 'Tu respuesta',
                'is_required' => true,
                'order' => 6,
            ],
            [
                'label' => 'OBSERVACIONES DEL DIA',
                'field_type' => 'textarea',
                'field_name' => 'observaciones-dia',
                'placeholder' => 'Tu respuesta',
                'is_required' => true,
                'order' => 7,
            ],
        ];

        // Solo crear campos si el formulario no tenía campos previamente
        if ($fulfillmentDaily->fields()->count() === 0) {
            foreach ($fulfillmentFields as $fieldData) {
                FormField::create(array_merge($fieldData, ['form_id' => $fulfillmentDaily->id]));
            }
            $this->command->info('✅ Formulario FULFILLMENT DAILY creado con ' . count($fulfillmentFields) . ' campos');
        } else {
            $this->command->info('ℹ️  Formulario FULFILLMENT DAILY ya existe con ' . $fulfillmentDaily->fields()->count() . ' campos');
        }

        $this->command->info('');
        $this->command->info('🎉 Seeder de formularios completado exitosamente!');
        $this->command->info('   - TRIAGE DAILY (traige module): ' . count($triageFields) . ' campos');
        $this->command->info('   - CLOSER DAILY (leads module): ' . count($closerFields) . ' campos');
        $this->command->info('   - ELITE CLOSER SOCIETY ONBOARDING (sin módulo): ' . count($eliteFields) . ' campos');
        $this->command->info('   - FORMULARIO VENTA CERRADA (sin módulo): ' . count($ventaCerradaFields) . ' campos');
        $this->command->info('   - FULFILLMENT DAILY (fulfillment module): ' . count($fulfillmentFields) . ' campos');
    }
}
