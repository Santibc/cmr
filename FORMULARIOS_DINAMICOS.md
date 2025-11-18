# Módulo de Formularios Dinámicos

## Descripción General

El módulo de **Formularios Dinámicos** permite crear, gestionar y capturar respuestas de formularios personalizados en el CRM. El sistema está diseñado para ser flexible y extensible, permitiendo crear formularios con diversos tipos de campos y asignarlos a módulos específicos del sistema.

## Características Principales

### 1. Gestión de Formularios
- ✅ Crear formularios con nombre, descripción y estado
- ✅ Asignar formularios a módulos específicos (traige, leads, sales)
- ✅ Editar formularios existentes (agregar/eliminar/modificar campos)
- ✅ Eliminar formularios (soft delete)
- ✅ Ver historial completo de cambios (logs)

### 2. Constructor de Campos
- ✅ **10 tipos de campos soportados:**
  1. **text** - Texto corto
  2. **textarea** - Texto largo
  3. **email** - Email con validación
  4. **number** - Número
  5. **date** - Selector de fecha
  6. **select** - Lista desplegable
  7. **radio** - Selección única (radio buttons)
  8. **checkbox** - Selección múltiple
  9. **scale** - Escala numérica (1-10 con radio buttons)
  10. **rating** - Calificación por estrellas (1-5)

### 3. Gestión de Respuestas
- ✅ Captura de respuestas con validación dinámica
- ✅ Almacenamiento en JSON para flexibilidad
- ✅ Visualización de respuestas en DataTables
- ✅ Exportación a CSV
- ✅ Estados de respuesta (pendiente, aprobado, rechazado)

### 4. Integración
- ✅ Endpoints específicos para Traige Daily y Closer Daily
- ✅ Vinculación con leads y usuarios
- ✅ Sistema de logs integrado con el patrón del proyecto

---

## Estructura de la Base de Datos

### Tabla: `forms`
```sql
id                  BIGINT PRIMARY KEY
name                VARCHAR(255)        - Nombre del formulario
description         TEXT                - Descripción del formulario
slug                VARCHAR(255) UNIQUE - Slug para URLs
status              ENUM('draft', 'active', 'inactive') - Estado
module              VARCHAR(255)        - Módulo asociado (traige, leads, sales)
trigger_event       VARCHAR(255)        - Evento que dispara el formulario
settings            JSON                - Configuraciones adicionales
user_id             BIGINT FK           - Usuario creador
created_at          TIMESTAMP
updated_at          TIMESTAMP
deleted_at          TIMESTAMP           - Soft delete
```

### Tabla: `form_fields`
```sql
id                  BIGINT PRIMARY KEY
form_id             BIGINT FK           - Referencia a forms
label               VARCHAR(255)        - Etiqueta del campo
field_type          VARCHAR(255)        - Tipo de campo (text, select, etc.)
field_name          VARCHAR(255)        - Nombre slug del campo
placeholder         VARCHAR(255)        - Placeholder
default_value       VARCHAR(255)        - Valor por defecto
options             JSON                - Opciones para select/radio/checkbox
validations         JSON                - Reglas de validación
order               INTEGER             - Orden de visualización
is_required         BOOLEAN             - Si es obligatorio
help_text           TEXT                - Texto de ayuda
created_at          TIMESTAMP
updated_at          TIMESTAMP
```

### Tabla: `form_submissions`
```sql
id                  BIGINT PRIMARY KEY
form_id             BIGINT FK           - Referencia a forms
lead_id             BIGINT FK           - Lead relacionado (nullable)
user_id             BIGINT FK           - Usuario que envió (nullable)
submission_data     JSON                - Todas las respuestas
ip_address          VARCHAR(255)        - IP del usuario
user_agent          TEXT                - User agent
status              ENUM('pending', 'approved', 'rejected')
submitted_at        TIMESTAMP           - Fecha de envío
created_at          TIMESTAMP
updated_at          TIMESTAMP
```

---

## Modelos Eloquent

### Form.php
**Ubicación:** `app/Models/Form.php`

**Relaciones:**
- `fields()` → hasMany FormField
- `submissions()` → hasMany FormSubmission
- `user()` → belongsTo User
- `logs()` → hasMany Log (polimórfico manual)

**Métodos Helper:**
- `isActive()` - Verifica si está activo
- `isDraft()` - Verifica si está en borrador

**Constantes:**
```php
const STATUS_DRAFT = 'draft';
const STATUS_ACTIVE = 'active';
const STATUS_INACTIVE = 'inactive';
```

### FormField.php
**Ubicación:** `app/Models/FormField.php`

**Relaciones:**
- `form()` → belongsTo Form

**Métodos Helper:**
- `hasOptions()` - Verifica si el campo requiere opciones
- `isScale()` - Verifica si es escala numérica
- `isRating()` - Verifica si es calificación por estrellas

**Tipos de Campo:**
```php
const TYPE_TEXT = 'text';
const TYPE_TEXTAREA = 'textarea';
const TYPE_EMAIL = 'email';
const TYPE_NUMBER = 'number';
const TYPE_DATE = 'date';
const TYPE_SELECT = 'select';
const TYPE_RADIO = 'radio';
const TYPE_CHECKBOX = 'checkbox';
const TYPE_SCALE = 'scale';
const TYPE_RATING = 'rating';
```

### FormSubmission.php
**Ubicación:** `app/Models/FormSubmission.php`

**Relaciones:**
- `form()` → belongsTo Form
- `lead()` → belongsTo Lead
- `user()` → belongsTo User
- `logs()` → hasMany Log (polimórfico manual)

**Métodos Helper:**
- `getFieldValue($fieldName, $default)` - Obtiene valor de un campo
- `isPending()` - Verifica si está pendiente
- `isApproved()` - Verifica si está aprobado

---

## Controladores

### FormController.php
**Ubicación:** `app/Http/Controllers/FormController.php`

**Permisos:** Admin y CMS

**Métodos:**
- `index()` - Lista de formularios (DataTables)
- `create()` - Vista para crear formulario
- `store()` - Crear nuevo formulario
- `edit($id)` - Vista para editar formulario
- `update($id)` - Actualizar formulario
- `destroy($id)` - Eliminar formulario (soft delete)
- `logs($id)` - Obtener historial de cambios

### FormSubmissionController.php
**Ubicación:** `app/Http/Controllers/FormSubmissionController.php`

**Permisos:** Admin, CMS, Closer, Traige

**Métodos:**
- `index($formId)` - Lista de respuestas (DataTables)
- `show($submissionId)` - Ver detalles de una respuesta
- `store($formSlug)` - Guardar respuesta genérica
- `storeTraigeDaily()` - Guardar Traige Daily
- `storeCloserDaily()` - Guardar Closer Daily
- `export($formId)` - Exportar respuestas a CSV

---

## Rutas

### Rutas de Gestión (Autenticadas)
```
GET    /forms                          - Lista de formularios
GET    /forms/create                   - Crear formulario
POST   /forms                          - Guardar formulario
GET    /forms/{id}/edit                - Editar formulario
PUT    /forms/{id}                     - Actualizar formulario
DELETE /forms/{id}                     - Eliminar formulario
GET    /forms/{id}/logs                - Ver logs del formulario
```

### Rutas de Respuestas
```
GET    /forms/{formId}/submissions            - Lista de respuestas
GET    /forms/submissions/{submissionId}/show - Ver detalles de respuesta
GET    /forms/{formId}/export                 - Exportar a CSV
POST   /forms/traige-daily/submit             - Enviar Traige Daily
POST   /forms/closer-daily/submit             - Enviar Closer Daily
POST   /forms/{formSlug}/submit               - Enviar formulario genérico
```

---

## Vistas Blade

### forms/index.blade.php
**Ubicación:** `resources/views/forms/index.blade.php`

**Funcionalidad:**
- Lista de formularios con DataTables
- Botones de acción (editar, ver respuestas, logs, eliminar)
- Modal de historial de cambios
- SweetAlert2 para confirmaciones

### forms/builder.blade.php
**Ubicación:** `resources/views/forms/builder.blade.php`

**Funcionalidad:**
- Constructor de formularios
- Agregar/editar/eliminar campos dinámicamente
- Selección de tipo de campo
- Configuración de opciones para select/radio/checkbox
- Validación y guardado con AJAX

### forms/submissions/index.blade.php
**Ubicación:** `resources/views/forms/submissions/index.blade.php`

**Funcionalidad:**
- Lista de respuestas con DataTables
- Modal para ver detalles completos
- Botón de exportar a CSV
- Filtros por estado y fecha

---

## Formularios Pre-cargados

### 1. TRIAGE DAILY
**Slug:** `triage-daily`
**Módulo:** `traige`
**Campos:** 10

| Campo | Tipo | Obligatorio |
|-------|------|-------------|
| Fecha | date | Sí |
| Nombre del prospecto | text | Sí |
| Fuente de captación | radio | Sí |
| Calificación inicial | scale (1-10) | Sí |
| Interés del prospecto | radio | No |
| Presupuesto disponible | text | No |
| Problema principal identificado | textarea | No |
| Urgencia percibida | radio | No |
| Avanza a llamada con closer | radio | No |
| Observaciones/Notas adicionales | textarea | No |

**Endpoint:** `POST /forms/traige-daily/submit`

### 2. CLOSER DAILY
**Slug:** `closer-daily`
**Módulo:** `leads`
**Campos:** 13

| Campo | Tipo | Obligatorio |
|-------|------|-------------|
| Fecha | date | No |
| Nombre del closer | text | No |
| Donde nos conoció | radio | No |
| Llamadas totales | scale (1-10) | No |
| Llamadas conectadas | scale (1-10) | No |
| Presentaciones realizadas | scale (1-10) | No |
| Ventas cerradas | scale (0-10) | No |
| Cash collected | text | No |
| Revenue | text | No |
| Fuente | text | No |
| Seguimientos | text | No |
| Observaciones | textarea | No |
| Calificación de desempeño | rating (1-5) | No |

**Endpoint:** `POST /forms/closer-daily/submit`

---

## Seeder

### FormSeeder.php
**Ubicación:** `database/seeders/FormSeeder.php`

**Comando de ejecución:**
```bash
php artisan db:seed --class=FormSeeder
```

**Resultado:**
- Crea formulario "TRIAGE DAILY" con 10 campos
- Crea formulario "CLOSER DAILY" con 13 campos
- Ambos formularios están en estado "active"

---

## Sistema de Logs

El módulo está completamente integrado con el sistema de logs del proyecto:

### Tipos de Log
- `form_created` - Formulario creado
- `form_updated` - Formulario actualizado
- `form_deleted` - Formulario eliminado
- `submission_created` - Respuesta enviada
- `traige_daily_submitted` - Traige Daily completado
- `closer_daily_submitted` - Closer Daily completado

### Estructura de Log
```php
Log::create([
    'id_tabla' => $form->id,
    'tabla' => 'forms',
    'tipo_log' => 'form_created',
    'detalle' => 'Formulario creado: ' . $form->name,
    'valor_nuevo' => $form->status,
    'id_usuario' => Auth::id(),
]);
```

---

## Validación Dinámica

El sistema genera reglas de validación automáticamente basándose en los campos del formulario:

```php
$rules = [];
foreach ($form->fields as $field) {
    $fieldRules = [];

    if ($field->is_required) {
        $fieldRules[] = 'required';
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
```

---

## Exportación CSV

Las respuestas pueden exportarse a CSV con el siguiente formato:

| ID | Enviado por | Lead relacionado | Fecha de envío | Estado | Campo 1 | Campo 2 | ... |
|----|-------------|------------------|----------------|--------|---------|---------|-----|

**Endpoint:** `GET /forms/{formId}/export`

**Resultado:** Descarga archivo CSV con nombre `respuestas_{slug-formulario}_{fecha}.csv`

---

## Integración Futura

### Módulo Traige
Para integrar el formulario Traige Daily en el módulo de Traige:

```html
<!-- Botón en resources/views/traige/index.blade.php -->
<button class="btn btn-success fill-traige-daily-btn" data-lead-id="{{$lead->id}}">
    <i class="bi bi-file-text"></i> Llenar Traige Daily
</button>

<!-- Modal dinámico que carga campos del formulario via AJAX -->
<script>
$(document).on('click', '.fill-traige-daily-btn', function() {
    const leadId = $(this).data('lead-id');
    // Cargar formulario y mostrar en modal
    // Enviar a /forms/traige-daily/submit
});
</script>
```

### Módulo Leads (Closers)
Para integrar el formulario Closer Daily:

```html
<!-- Botón en resources/views/leads/leads_index.blade.php -->
<button class="btn btn-primary fill-closer-daily-btn">
    <i class="bi bi-clipboard-check"></i> Llenar Closer Daily
</button>

<!-- Modal dinámico -->
<script>
$(document).on('click', '.fill-closer-daily-btn', function() {
    // Cargar formulario y mostrar en modal
    // Enviar a /forms/closer-daily/submit
});
</script>
```

---

## Arquitectura y Patrones

### Patrón Strategy
Los formularios se asignan a módulos específicos mediante el campo `module`, permitiendo:
- Formularios reutilizables
- Lógica específica por módulo
- Validación contextual

### Patrón Repository (Implícito)
Los modelos Eloquent actúan como repositorios, encapsulando:
- Acceso a datos
- Relaciones
- Lógica de negocio básica

### Validación Dinámica
Las reglas de validación se generan en tiempo de ejecución basándose en la configuración de campos:
- Elimina código duplicado
- Permite cambios sin modificar código
- Mantiene consistencia

### Sistema de Logs Polimórfico Manual
Siguiendo el patrón del proyecto:
- `tabla` + `id_tabla` para identificar registro
- `tipo_log` para diferenciar acciones
- Integración con el resto del sistema

---

## Comandos Útiles

### Ejecutar Migraciones
```bash
php artisan migrate
```

### Ejecutar Seeder
```bash
php artisan db:seed --class=FormSeeder
```

### Revertir Migraciones
```bash
php artisan migrate:rollback --step=3
```

### Limpiar Caché
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

---

## Permisos por Rol

| Acción | Admin | CMS | Closer | Traige |
|--------|-------|-----|--------|--------|
| Ver formularios | ✅ | ✅ | ❌ | ❌ |
| Crear formularios | ✅ | ✅ | ❌ | ❌ |
| Editar formularios | ✅ | ✅ | ❌ | ❌ |
| Eliminar formularios | ✅ | ✅ | ❌ | ❌ |
| Ver respuestas | ✅ | ✅ | ✅ | ✅ |
| Enviar Traige Daily | ✅ | ✅ | ❌ | ✅ |
| Enviar Closer Daily | ✅ | ✅ | ✅ | ❌ |
| Exportar CSV | ✅ | ✅ | ✅ | ✅ |

---

## Notas de Desarrollo

### Fechas Importantes
- **Creación del módulo:** 14 de noviembre de 2025
- **Migraciones ejecutadas:** 14 de noviembre de 2025
- **Seeder ejecutado:** 14 de noviembre de 2025

### Versión
- **Laravel:** 9.x
- **PHP:** 8.0+
- **MySQL:** 5.7+

### Dependencias
- **Yajra DataTables:** Para tablas server-side
- **SweetAlert2:** Para alertas
- **Bootstrap 5:** Para UI
- **Bootstrap Icons:** Para iconos

---

## Soporte y Mantenimiento

### Para agregar un nuevo tipo de campo:
1. Agregar constante en `FormField.php`
2. Agregar en array `getFieldTypes()`
3. Actualizar validación en `FormSubmissionController.php`
4. Actualizar vista `builder.blade.php` si requiere configuración especial

### Para agregar un nuevo formulario:
1. Opción 1: Crear via interfaz web en `/forms/create`
2. Opción 2: Agregar en `FormSeeder.php` y ejecutar seeder

---

## Conclusión

El módulo de Formularios Dinámicos proporciona una solución flexible y escalable para la captura de datos en el CRM. Su arquitectura permite:

✅ Crear formularios sin modificar código
✅ Capturar respuestas de manera estructurada
✅ Exportar datos fácilmente
✅ Mantener trazabilidad completa
✅ Integrar con módulos existentes

El sistema está listo para ser extendido según las necesidades futuras del negocio.
