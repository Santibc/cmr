# MÓDULO DE LEADS - DOCUMENTACIÓN COMPLETA

## Resumen del Sistema

El módulo de leads es el corazón del sistema CRM que gestiona el proceso completo desde la importación de clientes potenciales desde Calendly hasta el cierre de ventas. Los closers utilizan Calendly para programar citas con clientes, y estas se importan automáticamente al CRM donde continúan con el proceso de seguimiento.

## Arquitectura del Módulo

### Modelos Principales

#### 1. **Lead** (`app/Models/Lead.php`)
- **Campos principales**: `nombre`, `email`, `telefono`, `instagram_user`, `user_id`, `pipeline_status_id`
- **Relaciones**:
  - `user()` / `closer()`: Pertenece a un User (closer asignado)
  - `llamadas()`: Tiene muchas llamadas de Calendly
  - `pipelineStatus()`: Pertenece a un estado del pipeline
  - `sale()`: Tiene una venta (relación 1:1)
  - `logs()`: Historial de cambios de estado

#### 2. **PipelineStatus** (`app/Models/PipelineStatus.php`)
- **Campos**: `name` (nombre del estado)
- **Estados típicos**: Nuevo Lead, Contactado, Interesado, Cerrada/Venta hecha, etc.
- **Relaciones**: `leads()` - tiene muchos leads

#### 3. **Sale** (`app/Models/Sale.php`)
- **Información del cliente**: `nombre_cliente`, `apellido_cliente`, `email_cliente`, `telefono_cliente`
- **Datos de la venta**: `identificacion_personal`, `domicilio`, `metodo_pago`, `tipo_acuerdo`
- **Documentos**: `comprobante_pago_path` (archivo subido)
- **Relaciones**: 
  - `lead()`: Pertenece a un lead
  - `user()`: Pertenece al closer que cerró la venta
  - `llamada()`: Asociada a una llamada específica

#### 4. **Log** (`app/Models/Log.php`)
- **Campos**: `id_tabla`, `tabla`, `detalle`, `valor_viejo`, `valor_nuevo`, `id_usuario`
- **Propósito**: Registra todos los cambios de estado del pipeline con comentarios
- **Relaciones**: `usuario()` - quien hizo el cambio

### Controladores

#### **LeadsController** (`app/Http/Controllers/LeadsController.php`)

##### Métodos principales:

1. **`importar_leads($id_usuario = null)`** (línea 29)
   - Importa leads y llamadas desde Calendly
   - Utiliza `LeadSynchronizationService` y `CalendlyEventImporter`
   - Los admins pueden importar para todos los usuarios, otros solo para sí mismos

2. **`index(Request $request)`** (línea 54)
   - Vista principal con tabla DataTables
   - Filtra por rol (admin ve todos, closers solo sus leads)
   - Genera dinámicamente los botones de acción según el estado del lead

3. **`updatePipelineStatus(Request $request, $id)`** (línea 133)
   - Actualiza el estado del pipeline del lead
   - Crea registro en la tabla `logs` con el comentario
   - Endpoint AJAX llamado desde el frontend

4. **`logs($id)`** (línea 166)
   - Retorna el historial de cambios de estado del lead
   - Formato JSON para el modal del historial

#### **SalesController** (`app/Http/Controllers/SalesController.php`)

1. **`form(Lead $lead)`** (línea 12)
   - Muestra el formulario de registro de venta
   - Solo accesible cuando el lead está en estado "Cerrada/Venta hecha"

2. **`store(Request $request)`** (línea 19)
   - Procesa el registro de la venta
   - Valida todos los campos requeridos
   - Guarda el comprobante de pago en `storage/app/public/comprobantes`

### Vista Principal: `resources/views/leads/leads_index.blade.php`

#### Estructura de la Tabla
- **Columna Acciones**: 3 botones dinámicos
- **Columna Estado Pipeline**: Select dropdown para cambiar estados
- **Columnas de datos**: Nombre, Email, Teléfono, Instagram

#### Los 3 Botones de Acción (línea 68-100):

1. **🔷 Botón Teléfono** (línea 71)
   - Siempre visible
   - Lleva al módulo de llamadas importadas de Calendly
   - Ruta: `/llamadas?lead_id={id}`

2. **🔷 Botón Historial** (línea 72-75)
   - Siempre visible  
   - Abre modal con historial de cambios de estado
   - Clase CSS: `view-logs-btn`
   - JavaScript en líneas 122-150

3. **🔷 Botón Dinámico de Venta** (línea 77-97)
   - **Si ya tiene venta**: Botón "👁️ Ver Detalles" (modal con info)
   - **Si está en "Cerrada/Venta hecha" sin venta**: Botón "📄 Registrar Venta"
   - **Otros estados**: No se muestra

#### Funcionalidades JavaScript

##### **Cambio de Estado del Pipeline** (líneas 248-295)
1. Guarda el estado original al hacer focus
2. Al cambiar, abre modal pidiendo comentario obligatorio
3. Si cancela el modal, revierte al estado original
4. AJAX POST a `/leads/{id}/update-status`

##### **Modal de Historial** (líneas 122-150)
- GET request a `/leads/{id}/logs`
- Muestra: Estado anterior, Estado nuevo, Comentario, Usuario, Fecha
- Manejo de estados: Cargando, Sin registros, Error

##### **Modal de Detalles de Venta** (líneas 152-165)
- Carga datos desde atributos `data-*` del botón
- Muestra toda la información de la venta
- Botón para descargar comprobante de pago

### Integración con Calendly

#### **CalendlyEventImporter** (`app/Services/CalendlyEventImporter.php`)
- Obtiene eventos programados por usuario
- Maneja paginación de la API de Calendly
- Extrae datos del invitado (lead) de cada evento
- Utiliza parámetros de configuración desde BD

#### **LeadSynchronizationService** (`app/Services/LeadSynchronizationService.php`)
- Coordina la importación completa
- **Proceso de sincronización** (línea 28):
  1. Valida que el closer tenga `calendly_uri`
  2. Obtiene llamadas existentes (optimización)
  3. Obtiene eventos desde Calendly
  4. Por cada evento:
     - Verifica si ya existe la llamada
     - Obtiene datos del invitado
     - Crea o encuentra el lead por email
     - Enriquece datos del lead (Instagram, teléfono)
     - Crea registro de llamada
     - Guarda respuestas del formulario
  5. Actualiza `last_synced_at` del usuario

### Rutas Importantes

```php
// Vista principal
Route::get('/leads', [LeadsController::class, 'index'])->name('leads');

// Importación manual
Route::get('/importar_leads', [LeadsController::class, 'importar_leads']);

// Actualización de estado (AJAX)
Route::post('leads/{id}/update-status', [LeadsController::class, 'updatePipelineStatus']);

// Historial de cambios (AJAX)
Route::get('/leads/{id}/logs', [LeadsController::class, 'logs']);

// Formulario de venta
Route::get('/leads_form/{lead?}', [LeadsController::class, 'form'])->name('leads.form');

// Guardar venta
Route::post('/sales', [SalesController::class, 'store'])->name('sales.store');
```

### Flujo Completo del Proceso

1. **Importación**:
   - Closer programa citas en Calendly
   - Sistema importa automáticamente leads y llamadas
   - Se crea lead con estado inicial del pipeline

2. **Seguimiento**:
   - Closer ve leads en tabla principal
   - Puede consultar llamadas importadas (botón teléfono)
   - Cambia estados del pipeline con comentarios
   - Todo cambio queda registrado en logs

3. **Cierre de Venta**:
   - Al cambiar estado a "Cerrada/Venta hecha" aparece botón "Registrar Venta"
   - Se llena formulario completo con datos del cliente
   - Se sube comprobante de pago
   - Botón cambia a "Ver Detalles de Venta"

4. **Historial**:
   - Cualquier cambio de estado se registra
   - Modal muestra cronología completa
   - Incluye comentarios y usuario que hizo el cambio

### Características Técnicas

- **DataTables**: Tabla responsiva con exportación a Excel
- **AJAX**: Actualizaciones sin recargar página
- **Transacciones BD**: Integridad en importaciones
- **Validaciones**: Frontend y backend
- **Permisos**: Admins ven todos, closers solo sus leads
- **Optimizaciones**: Consultas eficientes, paginación en APIs
- **Logs**: Auditoría completa de cambios
- **Archivos**: Almacenamiento seguro de comprobantes

### Estados del Pipeline
Los estados se pueden personalizar desde la BD, típicamente:
- Nuevo Lead
- Contactado 
- Interesado
- Propuesta Enviada
- Negociación
- Cerrada/Venta hecha
- Perdida
- No Contactado

### Seguridad y Validaciones

- **CSRF Protection**: Todas las operaciones POST
- **Validación de archivos**: Comprobantes solo JPG, PNG, PDF (máx 2MB)
- **Autorización**: Middleware auth en todas las rutas
- **Validaciones**: Email, teléfonos, campos requeridos
- **Sanitización**: Escapado de datos en vistas
- **Rollback**: Transacciones con manejo de errores

Este módulo es fundamental para el CRM ya que maneja todo el ciclo de vida del lead desde el primer contacto hasta la venta cerrada, con trazabilidad completa y integración fluida con Calendly.