# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel 9 CRM system built with PHP 8+, featuring lead management, call tracking, and sales pipeline functionality. The system integrates with Calendly for lead import and includes user management with role-based permissions.

## Technology Stack

- **Backend**: Laravel 9, PHP 8+
- **Frontend**: Blade templates, Alpine.js, Tailwind CSS, Bootstrap 5
- **Database**: MySQL (Laravel migrations)
- **Package Management**: Composer (PHP), npm (Node.js)
- **Authorization**: Laravel Sanctum, Spatie Laravel Permission
- **UI Components**: Livewire 2.x
- **Data Tables**: Yajra DataTables, DataTables.net with export functionality
- **Build Tools**: Vite, Laravel Mix
- **Integrations**: Calendly API

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

## Project Architecture

### Core Models and Relationships
- **User**: System users with Calendly integration fields (`calendly_user_uri`, `calendly_access_token`)
- **Lead**: Customer prospects with pipeline status tracking
- **Llamada**: Call records associated with leads
- **Sale**: Completed sales records
- **PipelineStatus**: Lead progression tracking
- **Log**: Activity logging system
- **Parametros**: System configuration parameters

### Key Controllers
- **HomeController**: Dashboard functionality
- **LeadsController**: Lead management and pipeline operations
- **LlamadasController**: Call tracking and responses
- **SalesController**: Sales record management
- **UsuariosController**: User management and Calendly import

### Service Layer
- **CalendlyEventImporter**: Imports events from Calendly API
- **CalendlyUserImporter**: Synchronizes users from Calendly
- **LeadSynchronizationService**: Lead data synchronization
- **UserSynchronizationService**: User data synchronization
- **UserCreationService**: User creation workflows

### Directory Structure
- `app/Models/`: Eloquent models
- `app/Http/Controllers/`: Request handling logic
- `app/Services/`: Business logic and external integrations
- `resources/views/`: Blade templates organized by feature
- `routes/web.php`: Application routes
- `database/migrations/`: Database schema definitions

## Key Features

### Lead Management
- Lead pipeline with status tracking
- Call logging and response management
- Activity logs for lead interactions
- Lead assignment to closers (users)

### Calendly Integration
- User synchronization from Calendly organization
- Event import with lead creation
- Token-based API authentication

### User Management
- Role-based permissions using Spatie package
- User creation and management interface
- Integration with Calendly user data

### Sales Pipeline
- Pipeline status management
- Sales record creation and tracking
- Lead progression through sales stages

## Database Notes

The application uses Laravel migrations with a focus on CRM functionality. Key tables include users with Calendly fields, leads with pipeline status, call tracking, and comprehensive logging.

## Authentication & Authorization

- Laravel Breeze for authentication scaffolding
- Spatie Laravel Permission for role-based access
- Middleware protection on all authenticated routes
- Laravel Sanctum for API token management

## Frontend Architecture

- Blade components in `resources/views/components/`
- Layouts: `app.blade.php` (authenticated), `guest.blade.php` (public)
- Alpine.js for interactive components
- Tailwind CSS with custom configuration
- DataTables for data presentation with export capabilities