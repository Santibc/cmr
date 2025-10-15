# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel 9 CRM system built with PHP 8+, featuring a complete sales pipeline from lead import through Calendly, lead qualification via Traige module, sales closure, contract signing, and onboarding. The system includes advanced features like:

- **Traige Module**: Lead qualification stage before passing to closers with dedicated pipeline statuses and call management
- **Contract Management**: Dynamic contract templates with client signing workflow
- **Upsell Workflows**: Low ticket to high ticket conversion process
- **Comprehensive Activity Tracking**: Complete audit trail across all stages with differentiated log types
- **Role-Based Access**: Separate modules for Traige users, Closers, CSM, and Admins

## Technology Stack

- **Backend**: Laravel 9, PHP 8+
- **Frontend**: Blade templates, Alpine.js, Tailwind CSS, Bootstrap 5
- **Database**: MySQL (Laravel migrations)
- **Package Management**: Composer (PHP), npm (Node.js)
- **Authorization**: Laravel Sanctum, Spatie Laravel Permission
- **UI Components**: Livewire 2.x
- **Data Tables**: Yajra DataTables, DataTables.net with export functionality
- **PDF Generation**: DomPDF (Barryvdh)
- **Build Tools**: Vite, Laravel Mix
- **Integrations**: Calendly API
- **Email**: Laravel Mail with custom Mailables

## Development Commands

### PHP/Laravel Commands
```bash
# Install PHP dependencies
composer install

# Run database migrations
php artisan migrate

# Seed the database
php artisan db:seed

# Start development server
php artisan serve

# Clear application cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Run tests
php artisan test

# Code formatting (Laravel Pint)
./vendor/bin/pint
```

### Frontend Commands
```bash
# Install JavaScript dependencies
npm install

# Development build with hot reload
npm run dev

# Production build
npm run build
```

## System Flow - Complete Business Process

### 1. User Import (Usuarios Module)
**Route**: `/usuarios`
**Controller**: `UsuariosController`
**Access**: Admin only

#### Process:
1. When accessing the usuarios module, the system automatically calls `importar_usuarios()`
2. Uses `UserSynchronizationService` with `CalendlyUserImporter`
3. Fetches all users from the Calendly organization using the admin's organization ID (stored in `parametros` table)
4. Creates/updates users in the system with Calendly integration fields:
   - `calendly_uri`: User's Calendly URI
   - `calendly_access_token`: Access token for API calls
   - `scheduling_url`: User's scheduling URL
   - Additional fields: uuid, locale, time_notation, timezone, slug
5. Assigns roles using Spatie Laravel Permission

**Key Points**:
- Users are imported from Calendly organization
- Each user represents a "closer" (sales person) in the system
- Admin manages all users except the system admin (id != 1)

### 2. Lead Import and Traige (Qualification Module)
**Route**: `/traige`
**Controller**: `TraigeController`
**Access**: Admin and Traige role

#### Process:
1. **Automatic Import on Page Load**:
   - When accessing `/traige`, the system triggers `importar_leads()`
   - Imports ALL leads from ALL users (like admin)
   - All newly imported leads go to Traige module FIRST

2. **Import Process** (`LeadSynchronizationService`):
   - Uses `CalendlyEventImporter` to fetch scheduled events from Calendly API
   - For each event (scheduled meeting):
     - Fetches event details and invitee data
     - Creates/updates Lead record:
       - nombre: Lead's name
       - email: Lead's email
       - telefono: Lead's phone
       - instagram_user: From form responses
     - Creates Llamada (call) record associated with the event
     - Associates lead with the closer (user_id)
   - Handles pagination for large datasets
   - Uses `last_synced_at` to only fetch new events

3. **Traige Display**:
   - Admin and Traige users see ALL leads
   - Leads shown BEFORE being passed to closers (passed_to_closer = false)
   - DataTables for sortable, filterable display

#### Traige Pipeline Status Management:
- Each lead has a `pipeline_status_id` linking to `PipelineStatus` model
- **Traige-Specific Statuses**:
  - Llamadas agendadas
  - Asistencias
  - Canceladas
  - Calificadas
  - Tasa de asistencia
  - Tasa de calificación
- States can be changed via dropdown in the leads table
- **CRITICAL**: All status changes in Traige create Log entries with `tipo_log = 'traige'`
- Status changes require mandatory comment via modal

#### Traige Call Management:
Similar to onboarding module but for initial qualification:

##### A. Call Scheduling
- **Button**: "Programar Llamada de Traige"
- **Modal Form**:
  - scheduled_date: Date and time for call
  - call_link: URL for call (Zoom, Meet, etc.)
  - notes: Additional notes
  - parent_call_id: Link to parent call if rescheduling

- **Process** (TraigeCallsController::store):
  - Creates TraigeCall record with status 'pendiente'
  - Sends email to lead with call details (`TraigeCallScheduled`)
  - Sets email_sent = true and email_sent_at timestamp
  - Creates log entry in traige_calls table

##### B. Call Status Management
- **Status Options**:
  - pendiente: Scheduled, awaiting completion
  - realizada: Successfully completed
  - no_realizada: Not completed/missed
  - reprogramada: Rescheduled

- **Update Process** (PUT `/traige/calls/{callId}/status`):
  - Changes call status
  - Adds comments explaining status change
  - Creates log with old/new status

##### C. Call Rescheduling
- **Process** (PUT `/traige/calls/{callId}/reschedule`):
  - Marks current call as 'reprogramada'
  - Creates new call with new date/time
  - Links to parent call via parent_call_id
  - Maintains call history chain

##### D. Passing Lead to Closer
- **Button**: "Pasar a Closer" (only available in Traige module)
- **Action** (POST `/traige/{id}/pass-to-closer`):
  - Sets passed_to_closer = true
  - Sets passed_to_closer_at = now()
  - Sets passed_by_user_id = current user ID
  - Creates log entry with tipo_log = 'traige'
  - **CRITICAL**: Lead now appears in Closer's module

##### E. Change History
- **Button**: Clock icon - "Ver Historial de Cambios"
- Shows complete lead history:
  - Pipeline status changes (tipo: "Traige")
  - Traige call activities (tipo: "Llamadas")
  - Who made each change and when
  - All comments and details

### 3. Closers Module (Lead Management - Post-Traige)
**Route**: `/leads`
**Controller**: `LeadsController`
**Access**: Admin and Closers (role-based)

#### Process:
1. **Lead Display**:
   - **CRITICAL**: Only shows leads where `passed_to_closer = true`
   - Admin sees ALL passed leads from all closers
   - Closers see only their assigned leads (filtered by user_id)
   - DataTables for sortable, filterable display

2. **Pipeline Statuses**:
   - **IMPORTANT**: Excludes Traige statuses (Llamadas agendadas, Asistencias, Canceladas, Calificadas, Tasa de asistencia, Tasa de calificación)
   - Shows only closer-specific pipeline statuses
   - Original statuses maintained from before Traige implementation

#### Pipeline Status Management:
- Each lead has a `pipeline_status_id` linking to `PipelineStatus` model
- States can be changed via dropdown in the leads table
- When status changes to **"Cerrada/Venta hecha"**, a button appears to register the sale
- **CRITICAL**: All status changes create Log entries with `tipo_log = null` (to distinguish from Traige)

#### Change History:
- **Button**: Clock icon - "Ver Historial de Cambios"
- Shows complete lead lifecycle:
  - Traige activities (tipo: "Traige" and "Llamadas")
  - Closer pipeline changes (tipo: "Pipeline")
  - Onboarding activities (tipo: "Onboarding")
  - Sales and contracts (tipo: "Venta", "Contrato")
  - Upsell activities (tipo: "Upsell")

### 4. Sales Closure (Sales Module)
**Route**: `/sales/form/{lead}`
**Controller**: `SalesController`
**Access**: Authenticated users

#### Process When Closing a Sale:
1. **Trigger**: Lead status changed to "Cerrada/Venta hecha" shows "Registrar Venta" button
2. **Form Fields** (sales/form.blade.php):
   - **Client Information**:
     - nombre_cliente, apellido_cliente
     - email_cliente, telefono_cliente
     - identificacion_personal (DNI/ID)
     - domicilio (address)

   - **Payment Information**:
     - metodo_pago: Payment method
     - comprobante_pago: Payment proof upload (jpg, jpeg, png, pdf)
     - tipo_acuerdo: Agreement type

   - **Critical Fields**:
     - **tipo_contrato**: "low ticket" or "high ticket" (determines upsell eligibility)
     - **contract_template_id**: Contract template from database (ContractTemplate model)
     - **forma_de_pago**: Payment terms text (inserted into contract HTML)

   - comentarios: Additional comments

3. **Contract Template System**:
   - Stored in `contract_templates` table
   - Contains:
     - `name`: Template name
     - `html_content`: Full HTML contract with placeholders like {nombre}, {dni}, {forma_de_pago}
     - `dynamic_fields`: JSON array of field names that need to be filled
   - Currently only one template exists in the system

4. **Sale Creation Process**:
   - Saves payment proof to `public/comprobantes/`
   - Generates unique `contract_token` for secure contract signing link
   - Creates `contract_data` JSON with initial values:
     - forma_de_pago: From form
     - nombre, dni: Client data
     - dia, mes, anio: Current date in Spanish format
   - Creates Sale record with:
     - All client information
     - Payment details
     - Contract reference (contract_template_id)
     - contract_approved: false (pending CSM approval)
     - contract_signed_date: null (not signed yet)

5. **Email Sending**:
   - Automatically sends `ContractSigningMail` to client's email
   - Email contains link to sign contract: `/contract/sign/{token}`
   - Uses Laravel Mail with custom Mailable

6. **Log Creation**:
   - Creates log entry in `logs` table:
     - tabla: 'leads'
     - tipo_log: 'venta'
     - Links payment proof as archivo_soporte
     - Captures sale details in detalle field

### 5. Contract Signing (Public Route)
**Route**: `/contract/sign/{token}`
**Controller**: `ContractSigningController`
**Access**: Public (no authentication required)

#### Process:
1. **Display Contract Form**:
   - Lead receives email with unique token link
   - Shows contract template with pre-filled data (forma_de_pago, dates)
   - Displays fields from `dynamic_fields` that need client input (excluding: dia, mes, anio, forma_de_pago)
   - Includes signature pad for digital signature

2. **Client Fills Contract**:
   - Enters missing information required by template
   - Signs using canvas signature pad (captures as base64 image)
   - Can preview contract before submitting

3. **Contract Submission**:
   - Validates all required dynamic fields
   - Saves signature image to `public/signatures/`
   - Merges client data with existing contract_data
   - Sets `contract_signed_date` to current timestamp
   - **Does NOT approve contract** - awaits CSM review

4. **Preview Route** (`/contract/preview/{token}`):
   - Shows filled contract HTML
   - Replaces all placeholders with actual data
   - Displays signature image if provided

### 6. Contract Approval (Onboarding - Aprobación de Contratos)
**Route**: `/onboarding/contracts/approval`
**Controller**: `ContractApprovalController`
**Access**: Admin and CSM roles only

#### Purpose:
Review and approve contracts after client signature, ensuring data accuracy before moving to onboarding.

#### Process:
1. **Pending Contracts List**:
   - Shows all sales with:
     - contract_template_id IS NOT NULL
     - contract_approved = false
     - contract_data IS NOT NULL
   - DataTables display with client info, contract name, closer name

2. **Contract Review** (`/onboarding/contracts/approval/{saleId}/edit`):
   - CSM can view full contract preview
   - Can edit any dynamic field if data is incorrect
   - Live preview updates as fields change (AJAX)
   - Shows contract signed date if available

3. **Contract Modification** (PUT `/onboarding/contracts/approval/{saleId}`):
   - Updates contract_data with corrected information
   - Can update contract_signed_date if needed
   - Does not approve automatically - separate action required

4. **Contract Approval** (POST `/onboarding/contracts/approval/{saleId}/approve`):
   - Sets contract_approved = true
   - Creates log entry:
     - tabla: 'leads'
     - tipo_log: 'contrato'
     - valor_viejo: 'pendiente_aprobacion'
     - valor_nuevo: 'aprobado'
   - Lead now eligible for onboarding process

### 7. Onboarding - Lead Management
**Route**: `/onboarding/leads`
**Controller**: `OnboardingLeadsController`
**Access**: Admin and CSM roles only

#### Purpose:
Manage leads after sale closure and contract approval through onboarding calls.

#### Display Criteria:
Shows only leads that have:
- A sale record (hasOne Sale)
- Contract template assigned (contract_template_id NOT NULL)
- Contract approved (contract_approved = true)

#### Features:

##### A. Call Scheduling
- **Button**: "Programar Llamada de Onboarding"
- **Modal Form**:
  - scheduled_date: Date and time for call
  - call_link: URL for call (Zoom, Meet, etc.)
  - notes: Additional notes
  - parent_call_id: Link to parent call if rescheduling

- **Process** (OnboardingCallsController::store):
  - Creates OnboardingCall record with status 'pendiente'
  - Sends email to lead with call details (`OnboardingCallScheduled`)
  - Sets email_sent = true and email_sent_at timestamp
  - Creates log entry in onboarding_calls table

##### B. Call Status Management
- **Status Options**:
  - pendiente: Scheduled, awaiting completion
  - realizada: Successfully completed
  - no_realizada: Not completed/missed
  - reprogramada: Rescheduled

- **Update Process** (PUT `/onboarding/calls/{callId}/status`):
  - Changes call status
  - Adds comments explaining status change
  - Creates log with old/new status

##### C. Call Rescheduling
- **Process** (PUT `/onboarding/calls/{callId}/reschedule`):
  - Marks current call as 'reprogramada'
  - Creates new call with new date/time
  - Links to parent call via parent_call_id
  - Maintains call history chain

##### D. Lead Notes Management
- **Route**: `/lead-notes`
- **Controller**: `LeadNotesController`
- **Features**:
  - Add notes to leads (POST /lead-notes)
  - View all notes for lead (GET /lead-notes/{leadId})
  - Delete notes (DELETE /lead-notes/{noteId})
- **Model**: LeadNote with lead_id, user_id, note content

##### E. Upsell Workflow (Low Ticket Only)
- **Visibility**: Button only appears if:
  - tipo_contrato = 'low ticket'
  - upsell IS NULL (not yet in upsell process)

- **Action** (POST `/upsell/{sale}/pendiente`):
  - Sets upsell = 'pendiente'
  - Records upsell_comentarios
  - Sets upsell_fecha_pendiente = now()
  - Sets upsell_user_pendiente = current user ID
  - Creates log with tipo_log = 'upsell'
  - Lead moves to Upsell module

##### F. Additional Actions
- **View Sale Details**: Shows modal with all sale information
- **Download Contract**: Generates PDF of approved contract
- **View Calls**: Shows all onboarding calls for lead
- **View Logs**: Complete history of all changes (pipeline, sales, onboarding, upsell)

### 8. Upsell Module
**Route**: `/upsell`
**Controller**: `UpsellController`
**Access**: Admin only

#### Purpose:
Convert low ticket sales to high ticket through upsell approval process.

#### Display Criteria:
Shows all sales where:
- upsell IS NOT NULL (either 'pendiente' or 'aprobado')

#### Features:

##### A. Pending Upsells List
- Shows sales with upsell = 'pendiente'
- Displays:
  - Lead information
  - Sale details
  - Who marked as pendiente (upsell_user_pendiente)
  - When marked as pendiente (upsell_fecha_pendiente)
  - Comments from onboarding (upsell_comentarios)

##### B. Upsell Approval Process
**Button**: "Aprobar Upsell - Pasar a High Ticket"

**Form Requirements**:
- comprobante_upsell: Payment proof upload (jpg, jpeg, png, pdf)
- comentarios_aprobacion: Approval comments (optional)

**Process** (POST `/upsell/{sale}/approve`):
1. Validates upsell status is 'pendiente'
2. Saves upsell proof to `public/upsell_comprobantes/`
3. Updates Sale record:
   - upsell = 'aprobado'
   - **tipo_contrato = 'high ticket'** (CRITICAL CHANGE)
   - upsell_comprobante_path = file path
   - Appends approval comments to upsell_comentarios
   - upsell_fecha_aprobado = now()
   - upsell_user_aprobado = current user ID

4. Creates log entry:
   - tabla: 'sales'
   - tipo_log: 'upsell'
   - valor_viejo: 'pendiente'
   - valor_nuevo: 'aprobado'
   - archivo_soporte: upsell proof path

5. Lead remains in onboarding module but now as high ticket

##### C. Approved Upsells
- Filter to view completed upsells
- Shows conversion history
- Displays who approved (upsell_user_aprobado)
- Shows approval date (upsell_fecha_aprobado)

##### D. Complete Traceability
- **Upsell Logs**: View complete history via "Ver Historial de Upsell" button
- Includes logs from:
  - Lead pipeline changes
  - Onboarding calls
  - Upsell status changes
- Shows all users involved and timestamps

### 9. Historial de Cambios (Change History)
**Available In**: ALL modules (Leads, Onboarding, Upsell)
**Button**: Clock icon - "Ver Historial de Cambios"
**Model**: Log

#### Purpose:
Complete audit trail of every action taken on a lead throughout its lifecycle.

#### Log Types:
1. **Pipeline Logs** (tabla: 'leads'):
   - tipo_log: null or 'venta'
   - Tracks status changes in lead pipeline
   - Records who changed status and when

2. **Venta Logs** (tabla: 'leads', tipo_log: 'venta'):
   - Sale registration
   - Payment proof attached
   - Sale details captured

3. **Contrato Logs** (tabla: 'leads', tipo_log: 'contrato'):
   - Contract approval by CSM
   - Links to contract download

4. **Onboarding Logs** (tabla: 'onboarding_calls'):
   - Call scheduling
   - Status changes
   - Rescheduling
   - Comments and notes

5. **Upsell Logs** (tabla: 'sales', tipo_log: 'upsell'):
   - Upsell initiation
   - Approval process
   - Contract type change
   - Payment proofs

#### Log Structure:
- **id_tabla**: ID of related record
- **tabla**: Table name (leads, sales, onboarding_calls)
- **tipo_log**: Specific log type (venta, contrato, upsell)
- **detalle**: Descriptive text of action
- **valor_viejo**: Previous value/status
- **valor_nuevo**: New value/status
- **id_usuario**: User who performed action
- **archivo_soporte**: URL to supporting file (payment proof, contract, etc.)
- **created_at**: Timestamp of action

### 10. Dashboard System

#### A. Main Dashboard (HomeController)
**Route**: `/dashboard`
**Access**: All authenticated users

**For Admin Users**:
- **Lead Metrics**:
  - Total leads this month vs previous month
  - Growth percentage
  - Conversion rate (leads with sales)
  - Pipeline distribution chart
  - Top performing closers

- **Onboarding Metrics**:
  - Total calls this month
  - Calls by status breakdown
  - Overdue calls count
  - Average time to first call

- **Upsell Metrics**:
  - Approved upsells this month
  - Low ticket to high ticket conversion rate
  - Pending upsell approvals
  - Upsell status distribution

- **General Metrics**:
  - Total sales this month
  - Approved vs pending contracts
  - Contract type distribution
  - Recent activity (last 7 days)

- **Charts**:
  - Daily leads trend (last 30 days)
  - Daily sales trend
  - Daily calls activity
  - Weekly performance comparison

**For Non-Admin Users**:
- Basic dashboard view
- Limited metrics relevant to their role

#### B. Onboarding Dashboard (OnboardingDashboardController)
**Route**: `/onboarding/dashboard`
**Access**: Admin and CSM only

**Features**:
- Date range filter (customizable)
- Statistics:
  - Total leads with sales
  - Leads with scheduled calls
  - Call completion rate
  - Total notes

- Charts:
  - Daily call activity
  - Status distribution pie chart
  - Calls by user (who scheduled)

- Recent leads list with:
  - Last call status
  - Last note date
  - Activity counts

- Performance metrics:
  - Average time from sale to first call
  - Response rate by time of day
  - Most active leads

## Core Models and Relationships

### User
- **Relationships**:
  - hasMany: Lead (as closer)
  - hasMany: Sale (as closer who closed)
  - hasMany: OnboardingCall (as scheduler)
  - hasMany: TraigeCall (as scheduler)
- **Key Fields**:
  - Calendly integration: calendly_uri, calendly_access_token, scheduling_url
  - Organization data: uuid, locale, timezone, slug
  - last_synced_at: Last Calendly sync timestamp
- **Roles**: admin, cms, closer, traige (via Spatie Laravel Permission)

### Lead
- **Relationships**:
  - belongsTo: User (closer/owner)
  - belongsTo: PipelineStatus
  - hasMany: Llamada (Calendly calls)
  - hasOne: Sale
  - hasMany: OnboardingCall
  - hasMany: TraigeCall
  - hasMany: LeadNote
  - hasMany: Log
  - belongsTo: User as passedByUser
- **Key Fields**:
  - nombre, email, telefono, instagram_user
  - pipeline_status_id
  - **Traige Fields**:
    - passed_to_closer: Boolean (default false)
    - passed_to_closer_at: Timestamp when passed
    - passed_by_user_id: User who passed to closer
- **Flow**: Imported from Calendly → Traige (qualification) → Passed to Closer → Pipeline → Sale → Onboarding

### Sale
- **Relationships**:
  - belongsTo: Lead (unique - one sale per lead)
  - belongsTo: User (closer who closed)
  - belongsTo: ContractTemplate
  - belongsTo: User as upsellUserPendiente
  - belongsTo: User as upsellUserAprobado
- **Key Fields**:
  - **Client Info**: nombre_cliente, apellido_cliente, email_cliente, telefono_cliente, identificacion_personal, domicilio
  - **Payment**: metodo_pago, comprobante_pago_path, tipo_acuerdo
  - **Contract**: contract_template_id, contract_approved, contract_data (JSON), contract_token, contract_signed_date
  - **Type**: tipo_contrato ('low ticket' or 'high ticket')
  - **Upsell**: upsell, upsell_comprobante_path, upsell_comentarios, upsell_fecha_pendiente, upsell_fecha_aprobado, upsell_user_pendiente, upsell_user_aprobado

### ContractTemplate
- **Relationships**:
  - hasMany: Sale
- **Key Fields**:
  - name: Template display name
  - html_content: Full HTML with placeholders {field_name}
  - dynamic_fields: JSON array ['nombre', 'dni', 'forma_de_pago', 'imagen_firma', 'dia', 'mes', 'anio']
- **Usage**: Single template system (currently only one template exists)

### OnboardingCall
- **Relationships**:
  - belongsTo: Lead
  - belongsTo: User (who scheduled)
  - belongsTo: OnboardingCall as parentCall
  - hasMany: OnboardingCall as childCalls (rescheduled calls)
  - hasMany: Log
- **Key Fields**:
  - scheduled_date, call_link, notes
  - status: 'pendiente' | 'realizada' | 'no_realizada' | 'reprogramada'
  - comments: Status change explanations
  - parent_call_id: Links rescheduled calls
  - email_sent, email_sent_at
- **Flow**: Scheduled → Pending → (Realizada|No Realizada|Reprogramada)

### TraigeCall
- **Relationships**:
  - belongsTo: Lead
  - belongsTo: User (who scheduled - traige user)
  - belongsTo: TraigeCall as parentCall
  - hasMany: TraigeCall as childCalls (rescheduled calls)
  - hasMany: Log
- **Key Fields**:
  - scheduled_date, call_link, notes
  - status: 'pendiente' | 'realizada' | 'no_realizada' | 'reprogramada'
  - comments: Status change explanations
  - parent_call_id: Links rescheduled calls
  - email_sent, email_sent_at
- **Flow**: Scheduled → Pending → (Realizada|No Realizada|Reprogramada)
- **Purpose**: Qualification calls before lead is passed to closers
- **Note**: Identical structure to OnboardingCall but used in pre-closer stage

### LeadNote
- **Relationships**:
  - belongsTo: Lead
  - belongsTo: User (author)
- **Purpose**: Free-form notes for lead tracking in onboarding

### Log
- **Relationships**:
  - belongsTo: User (who performed action)
  - Polymorphic tracking via id_tabla + tabla fields
- **Key Fields**:
  - id_tabla: ID of record being tracked
  - tabla: 'leads' | 'sales' | 'onboarding_calls' | 'traige_calls'
  - tipo_log: 'traige' | 'venta' | 'contrato' | 'upsell' | null
  - detalle: Action description
  - valor_viejo, valor_nuevo: Before/after values
  - archivo_soporte: URL to related file
- **Usage**: Universal audit trail across all modules
- **Important**: tipo_log = 'traige' distinguishes traige module actions from closer pipeline actions (tipo_log = null)

### PipelineStatus
- **Relationships**:
  - hasMany: Lead
- **Purpose**: Define available lead statuses
- **Status Types**:
  - **Traige Statuses**: Llamadas agendadas, Asistencias, Canceladas, Calificadas, Tasa de asistencia, Tasa de calificación
  - **Closer Statuses**: Original pipeline statuses (excluding traige statuses)
- **Critical Status**: "Cerrada/Venta hecha" triggers sale form
- **Important**: Traige statuses are filtered out in LeadsController to keep modules separate

### Llamada (Calendly Call)
- **Relationships**:
  - belongsTo: Lead
  - belongsTo: User (assigned closer)
- **Purpose**: Stores imported Calendly scheduled events
- **Note**: Different from OnboardingCall and TraigeCall (which are manually scheduled post-import)

## Key Controllers

### UsuariosController
- `index()`: Lists all users (except admin), auto-imports on load
- `importar_usuarios()`: Syncs users from Calendly organization
- `form()`, `guardar()`: User CRUD with role assignment

### TraigeController
- `index()`: Lists ALL leads with passed_to_closer = false, auto-imports on load
- `importar_leads()`: Syncs leads and calls from Calendly events (same as admin)
- `updatePipelineStatus()`: Changes lead status, creates log with tipo_log = 'traige'
- `passToCloser()`: Marks lead as passed, sets timestamps, creates log
- `logs()`: Returns complete lead history (traige pipeline + traige calls)
- `getLogType()`: Determines log type badge (Traige, Llamadas Traige, etc.)
- **Critical**: All pipeline status changes create logs with tipo_log = 'traige'

### TraigeCallsController
- `store()`: Creates traige call, sends email to lead
- `updateStatus()`: Changes call status, creates log in traige_calls table
- `reschedule()`: Marks as reprogrammed, links to new call
- `getLeadCalls()`: Returns all traige calls for a lead
- `getLogs()`: Returns call history

### LeadsController
- `index()`: Lists leads where passed_to_closer = true (filtered by role), auto-imports on load
- `importar_leads()`: Syncs leads and calls from Calendly events
- `updatePipelineStatus()`: Changes lead status, creates log with tipo_log = null
- `logs()`: Returns complete lead history (traige + pipeline + onboarding + upsell)
- `infoJson()`: Returns lead data for modals
- `downloadContract()`: Generates PDF of approved contract
- `resendContractEmail()`: Resends signing email to client
- `getLogType()`: Determines log type badge (handles Traige, Llamadas Traige, Pipeline, etc.)
- **Critical**: Filters out traige pipeline statuses, only loads leads passed from traige

### SalesController
- `form()`: Shows sale registration form with contract templates
- `store()`: Creates sale, generates contract token, sends email, creates log

### ContractSigningController (Public)
- `show()`: Displays contract form to client
- `update()`: Processes client signature and data
- `preview()`: Shows filled contract
- `previewAjax()`: AJAX preview for live updates

### ContractApprovalController
- `index()`: Lists pending contracts for CSM review
- `edit()`: Contract review form with live preview
- `update()`: Updates contract data
- `approve()`: Approves contract, creates log, enables onboarding
- `previewAjax()`: AJAX preview during editing

### OnboardingLeadsController
- `index()`: Lists leads with approved contracts
- `logs()`: Returns complete lead history
- `downloadContract()`: PDF generation

### OnboardingCallsController
- `store()`: Creates call, sends email to lead
- `updateStatus()`: Changes call status, creates log
- `reschedule()`: Marks as reprogrammed, links to new call
- `getLeadCalls()`: Returns all calls for a lead
- `getLogs()`: Returns call history

### LeadNotesController
- `store()`: Creates note for lead
- `getLeadNotes()`: Returns all notes for lead
- `destroy()`: Deletes note

### UpsellController
- `index()`: Lists all upsells (pending and approved)
- `markPendiente()`: Initiates upsell process from onboarding
- `approve()`: Approves upsell, changes to high ticket, creates log
- `getLogs()`: Returns complete upsell history

### OnboardingDashboardController
- `index()`: Onboarding metrics dashboard
- Methods: `getMainStats()`, `getChartData()`, `getRecentLeads()`, `getPerformanceMetrics()`

### HomeController
- `index()`: Main dashboard for admin with comprehensive metrics
- Methods: `getLeadsMetrics()`, `getOnboardingMetrics()`, `getUpsellMetrics()`, `getGeneralMetrics()`, `getChartData()`

## Service Layer

### CalendlyUserImporter
- Implements `UserImporterInterface`
- Uses organization parameters from `parametros` table
- `getUsersData()`: Fetches organization_memberships from Calendly API
- Returns collection of users for synchronization

### CalendlyEventImporter
- `getAllScheduledEventsForUser()`: Fetches scheduled events with pagination
- Uses `last_synced_at` to only fetch new events
- `getInviteeData()`: Gets lead information and form responses for event
- Handles Calendly API pagination automatically

### UserSynchronizationService
- Orchestrates user import process
- Creates/updates users with Calendly data
- Returns report: imported, skipped, errors

### LeadSynchronizationService
- Orchestrates lead and call import from Calendly
- For each event:
  - Creates/updates Lead
  - Creates Llamada record
  - Associates with closer
- Returns report: imported, skipped, errors

### UserCreationService
- Handles user creation workflows
- `create()`: Creates new user
- `update()`: Updates existing user

## Mail System

### ContractSigningMail
- Sent to: Lead's email when sale is registered
- Subject: "Completa tu contrato - {contract_name}"
- View: `emails.contract-signing`
- Data: sale, contractUrl, customerName, contractName
- Action: Directs lead to sign contract via unique token URL

### TraigeCallScheduled
- Sent to: Lead's email when traige call is scheduled
- Subject: "Llamada de Traige Programada - {app_name}"
- View: `emails.traige-call-scheduled`
- Data: call details, scheduled_date, call_link
- Purpose: Notifies lead of upcoming qualification call with join link

### OnboardingCallScheduled
- Sent to: Lead's email when onboarding call is scheduled
- Subject: "Llamada de Onboarding Programada - {app_name}"
- View: `emails.onboarding-call-scheduled`
- Data: call details, scheduled_date, call_link
- Purpose: Notifies lead of upcoming call with join link

## Frontend Architecture

### Layouts
- `app.blade.php`: Authenticated users layout with navigation
- `guest.blade.php`: Public layout for contract signing

### Key Views

#### Usuarios Module
- `usuarios/usuarios_index.blade.php`: User list with DataTables
- `usuarios/usuarios_form.blade.php`: User create/edit form

#### Traige Module
- `traige/index.blade.php`:
  - DataTables with traige pipeline status dropdown
  - Action buttons: Schedule Call, View Calls, View Logs, Pass to Closer
  - Modals:
    - Comment modal for status changes (mandatory)
    - Schedule traige call modal
    - Call traceability modal (matches onboarding design)
    - Update call status modal
    - Call logs modal
    - Lead change history modal
  - JavaScript features:
    - SweetAlert2 for confirmations
    - AJAX for status updates and call management
    - getTipoBadge() function for log type display (Traige, Llamadas)
  - **Critical**: Only shows leads with passed_to_closer = false

#### Leads Module (Closers)
- `leads/leads_index.blade.php`:
  - DataTables with closer pipeline status dropdown (excludes traige statuses)
  - Action buttons: View Calls, View Logs, Register Sale, View Sale Details
  - Modal for viewing sale information
  - Modal for viewing complete history (including traige activities)
  - JavaScript features:
    - getTipoBadge() function handles Traige, Llamadas Traige, Pipeline, etc.
  - **Critical**: Only shows leads with passed_to_closer = true

#### Sales Module
- `sales/form.blade.php`:
  - Complete sale registration form
  - Contract template selection
  - Payment proof upload
  - tipo_contrato selection (low/high ticket)
  - forma_de_pago textarea for contract insertion

#### Contracts Module
- `contracts/sign.blade.php`:
  - Public form for client to fill contract
  - Signature pad
  - Dynamic fields from template
  - Live preview

- `contracts/preview.blade.php`:
  - Shows completed contract HTML
  - Used after signing

- `contracts/approval/index.blade.php`:
  - CSM view for pending contracts
  - DataTables with review actions

- `contracts/approval/edit.blade.php`:
  - Contract editing interface
  - Live preview panel
  - Approve button

#### Onboarding Module
- `onboarding/leads_index.blade.php`:
  - Leads with approved contracts
  - Schedule call button
  - View calls, notes, sale details
  - Upsell button for low ticket
  - Download contract, view logs

- `onboarding/dashboard.blade.php`:
  - Metrics and charts
  - Date range filter
  - Performance statistics

#### Upsell Module
- `upsell/index.blade.php`:
  - Pending and approved upsells
  - Approve upsell modal
  - View logs
  - Status badges

#### Email Templates
- `emails/contract-signing.blade.php`: Contract signing invitation
- `emails/traige-call-scheduled.blade.php`: Traige qualification call notification
- `emails/onboarding-call-scheduled.blade.php`: Onboarding call notification

### JavaScript Features
- **DataTables**: All list views with search, sort, filter
- **Alpine.js**: Interactive components and modals
- **AJAX**:
  - Real-time contract preview
  - Status updates
  - Log fetching
  - Call management
- **Signature Pad**: Canvas-based signature capture in contract signing

## Database Notes

### Key Tables

#### users
- Standard Laravel users table
- Additional Calendly fields: calendly_uri, calendly_access_token, scheduling_url, uuid, locale, timezone, slug, last_synced_at
- Spatie roles attached via role_user pivot

#### leads
- Core prospect tracking
- Fields: nombre, email, telefono, instagram_user, user_id, pipeline_status_id
- **Traige fields**: passed_to_closer (boolean), passed_to_closer_at (timestamp), passed_by_user_id (foreign key)
- One-to-one with sales, one-to-many with onboarding_calls, traige_calls and notes
- **Important**: Only appears in closers module when passed_to_closer = true

#### sales
- Complete sale information
- Client data, payment data, contract data
- Upsell tracking fields
- JSON contract_data field stores dynamic contract values

#### contract_templates
- name, html_content (with {placeholder} syntax)
- dynamic_fields JSON array
- Single template system (currently one record)

#### traige_calls
- Scheduled qualification calls in traige module
- Identical structure to onboarding_calls
- Rescheduling via parent_call_id
- Email tracking: email_sent, email_sent_at
- Status: pendiente, realizada, no_realizada, reprogramada

#### onboarding_calls
- Scheduled calls after sale
- Rescheduling via parent_call_id
- Email tracking: email_sent, email_sent_at

#### lead_notes
- Simple notes system: lead_id, user_id, note, created_at

#### logs
- Universal audit trail
- Polymorphic: id_tabla + tabla + tipo_log
- File support: archivo_soporte URL
- **Critical**: tipo_log = 'traige' for traige module actions, null for closer pipeline actions
- tabla can be: 'leads', 'sales', 'onboarding_calls', 'traige_calls'

#### pipeline_statuses
- Predefined lead statuses
- **Traige statuses**: Llamadas agendadas, Asistencias, Canceladas, Calificadas, Tasa de asistencia, Tasa de calificación
- **Closer statuses**: All other statuses (filtered to exclude traige statuses in LeadsController)
- Critical: "Cerrada/Venta hecha" triggers sale form

#### llamadas
- Calendly imported calls
- Different from onboarding_calls

#### parametros
- System configuration
- Key parameters:
  - url_organizacion: Calendly API base URL
  - token_organizacion: Calendly API token
  - organizacion: Calendly organization ID

## Authentication & Authorization

### Roles (Spatie Laravel Permission)
- **admin**: Full system access, dashboard metrics, user management, lead management, traige access, upsell approval
- **traige**: Access to traige module, lead qualification, call scheduling, passing leads to closers
- **cms**: Onboarding access, contract approval, call scheduling, lead notes
- **closer**: Limited to own leads (passed from traige), sales registration, pipeline management

### Route Protection
- All authenticated routes use `auth` middleware
- Role-specific checks in controller constructors:
  - `TraigeController`: admin or traige
  - `TraigeCallsController`: admin or traige
  - `OnboardingLeadsController`: admin or cms
  - `OnboardingCallsController`: admin or cms
  - `OnboardingDashboardController`: admin or cms
  - `ContractApprovalController`: admin or cms
  - `UpsellController`: admin only
  - `LeadsController`: admin or closer (only sees passed_to_closer = true)

### Public Routes
- Contract signing routes (no authentication): `/contract/sign/{token}`, `/contract/preview/{token}`
- Token-based security via unique contract_token

## File Storage

### Directory Structure
- `public/comprobantes/`: Payment proofs from sales
- `public/signatures/`: Client signatures from contracts
- `public/upsell_comprobantes/`: Upsell payment proofs
- All files stored directly in public for easy access

### File Naming
- Comprobantes: `{timestamp}_comprobante_{original_name}`
- Signatures: `{sale_id}_{timestamp}.{ext}`
- Upsell: `{timestamp}_upsell_{original_name}`

## Important Business Rules

1. **Lead Import and Traige**: ALL leads are imported to Traige module FIRST (passed_to_closer = false by default)
2. **Traige to Closers**: Leads MUST be passed via "Pasar a Closer" button before appearing in closers module
3. **Pipeline Separation**:
   - Traige has 6 specific statuses (Llamadas agendadas, Asistencias, etc.)
   - Closers module filters out traige statuses completely
   - Two separate pipelines for different stages
4. **Log Differentiation**:
   - Traige logs have tipo_log = 'traige'
   - Closer logs have tipo_log = null
   - This allows proper display in history across modules
5. **Lead to Sale**: Lead must reach "Cerrada/Venta hecha" pipeline status before sale registration
6. **Sale to Onboarding**: Sale must have contract_approved = true to appear in onboarding
7. **Contract Flow**:
   - Sale created → Email sent → Client signs → CSM reviews → CSM approves → Onboarding begins
8. **Upsell Eligibility**: Only "low ticket" sales without existing upsell can be sent to upsell
9. **Upsell Approval**: Admin-only action that permanently changes tipo_contrato to "high ticket"
10. **Contract Template**: Single template system with dynamic field replacement
11. **Calendly Sync**:
    - Users imported on /usuarios access
    - Leads imported on /traige access (admin and traige roles import ALL)
    - Leads imported on /leads access (closers import only their own)
    - Uses last_synced_at to avoid duplicates
12. **Log Everything**: Every status change, creation, update across all modules creates log entry
13. **Email Automation**:
    - Traige call email: Automatic on traige call scheduling
    - Contract signing email: Automatic on sale creation
    - Onboarding call email: Automatic on onboarding call scheduling
14. **Role-Based Data**:
    - Traige users see ALL leads not yet passed (passed_to_closer = false)
    - Closers see only leads passed to them (passed_to_closer = true and user_id matches)
    - Admin sees everything in all modules
    - CSM sees approved contracts in onboarding

## Data Flow Summary

```
Calendly Events
    ↓
[USUARIOS MODULE] → Import Organization Users
    ↓
[TRAIGE MODULE] → Import Events as Leads + Llamadas
    - ALL leads imported here FIRST
    - passed_to_closer = false
    ↓
Traige Pipeline Status Changes (tipo_log = 'traige')
    - Llamadas agendadas
    - Asistencias
    - Canceladas
    - Calificadas
    - Tasa de asistencia
    - Tasa de calificación
    ↓
Traige Call Management
    - Schedule qualification calls
    - Send traige call emails
    - Update call status
    - Reschedule if needed
    - Track all activities in logs
    ↓
[PASS TO CLOSER] → "Pasar a Closer" button clicked
    - Sets passed_to_closer = true
    - Sets passed_to_closer_at timestamp
    - Sets passed_by_user_id
    - Creates log entry (tipo_log = 'traige')
    ↓
[CLOSERS MODULE] → Lead now appears for closers
    - Only shows passed_to_closer = true
    - Excludes traige pipeline statuses
    - Closers see only their assigned leads
    ↓
Closer Pipeline Status Changes (tipo_log = null)
    ↓
Status: "Cerrada/Venta hecha" → Show "Registrar Venta" Button
    ↓
[SALES MODULE] → Register Sale
    - Client info
    - Payment proof
    - Contract template selection
    - Payment terms (forma_de_pago)
    - tipo_contrato (low/high ticket)
    ↓
Generate contract_token → Send email to client
    ↓
[CONTRACT SIGNING - PUBLIC] → Client fills and signs
    - Complete missing fields
    - Digital signature
    - Set contract_signed_date
    ↓
[CONTRACT APPROVAL - CSM] → Review and approve
    - Edit if incorrect
    - Approve → contract_approved = true
    - Create approval log
    ↓
[ONBOARDING MODULE] → Lead appears in onboarding
    - Schedule calls
    - Send call emails
    - Manage call status
    - Add notes
    - View complete history (includes traige activities)
    ↓
If low ticket → [UPSELL OPTION]
    - Mark as pendiente
    - Move to upsell module
    ↓
[UPSELL MODULE - ADMIN ONLY]
    - Upload upsell payment proof
    - Approve → Change to high ticket
    - Create upsell log
    ↓
Continue onboarding as high ticket
```

## Common Workflows

### Workflow 0: Traige Qualification Process
1. Admin/Traige user accesses /traige module
2. System auto-imports ALL leads from Calendly events
3. Leads appear with passed_to_closer = false
4. Traige user reviews lead and schedules qualification call
5. System sends email to lead with call details and link
6. Traige user changes pipeline status as qualification progresses:
   - Llamadas agendadas → Asistencias → Calificadas, OR
   - Llamadas agendadas → Canceladas, OR
   - Tasa de asistencia / Tasa de calificación for metrics
7. If call not completed, traige user can reschedule (creates new call linked to parent)
8. All status changes require mandatory comment via modal
9. All changes create logs with tipo_log = 'traige'
10. When lead is qualified, traige user clicks "Pasar a Closer"
11. Lead is marked: passed_to_closer = true, timestamps set
12. Lead now disappears from traige module
13. Lead now appears in closers module for further sales process

### Workflow 1: Complete Lead to Sale (with Traige)
1. Admin imports users from Calendly
2. Admin/Traige user accesses traige module → auto-imports ALL events
3. Lead appears in traige table with pipeline status (passed_to_closer = false)
4. Traige user schedules qualification calls
5. Traige user changes status through traige pipeline stages
6. Traige user clicks "Pasar a Closer" button
7. Lead is marked as passed (passed_to_closer = true)
8. Lead NOW appears in closers module
9. Closer changes status through closer pipeline stages
10. Status reaches "Cerrada/Venta hecha"
11. Closer clicks "Registrar Venta" button
12. Fills sale form with client info, payment proof, contract selection
13. Submits → Sale created, email sent to client
14. Client receives email, clicks link to sign
15. Client fills missing fields and signs
16. CSM reviews contract in approval module
17. CSM edits if needed, then approves
18. Lead appears in onboarding module

### Workflow 2: Onboarding Process
1. Lead appears in onboarding after contract approval
2. CSM clicks "Programar Llamada"
3. Fills date, time, call link, notes
4. Email sent to lead automatically
5. After call, CSM updates status to "realizada" or "no_realizada"
6. If "no_realizada", can reschedule (creates new call, links parent)
7. CSM adds notes as needed
8. If low ticket, CSM clicks "Pasar a Upsell"
9. Lead moves to upsell module

### Workflow 3: Upsell to High Ticket
1. Lead marked as upsell from onboarding
2. Appears in upsell module as "pendiente"
3. Admin uploads upsell payment proof
4. Admin adds approval comments
5. Clicks "Aprobar Upsell"
6. Sale tipo_contrato changes from "low ticket" to "high ticket"
7. Upsell log created
8. Lead remains in onboarding but now as high ticket
9. Complete history viewable via logs

### Workflow 4: Complete History Tracking
1. Any module → Click "Ver Historial de Cambios" (clock icon)
2. Modal shows combined logs:
   - Pipeline changes (from leads module)
   - Sale registration
   - Contract approval
   - Onboarding calls (scheduling, status changes, rescheduling)
   - Upsell process (if applicable)
3. Each log shows:
   - Type (Pipeline/Venta/Contrato/Onboarding/Upsell)
   - Old/new values
   - Comments/details
   - User who performed action
   - Timestamp
   - Supporting files (payment proofs, contracts)

## Testing Considerations

When testing or developing:
1. Ensure `parametros` table has Calendly credentials
2. Test with actual Calendly organization for realistic data
3. Verify email configuration for contract and call emails
4. Test signature pad in various browsers
5. Verify PDF generation with signature images
6. Test role-based access thoroughly
7. Verify log creation at each step
8. Test upsell workflow from low to high ticket
9. Verify DataTables filtering and sorting
10. Test file uploads (comprobantes, signatures, upsell)
