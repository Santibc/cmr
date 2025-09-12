<x-app-layout>
    <x-slot name="header">
        {{ $user->exists ? 'Editar Usuario' : 'Nuevo Usuario' }}
    </x-slot>

    <div class="container py-4">
        <div class="card shadow">

            <div class="card-body">
                <form method="POST" action="{{ route('usuarios.guardar') }}">
                    @csrf
                    <input type="hidden" name="id" value="{{ old('id', $user->id) }}">

                    <div class="row">
                        {{-- UUID --}}
                        <div class="col-12 col-md-6 mb-3">
                            <label class="form-label">UUID</label>
                            <input name="uuid" type="text"
                                   class="form-control"
                                   value="{{ old('uuid', $user->uuid) }}"
                                   {{ $user->uuid ? 'disabled' : '' }}>
                            @error('uuid')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Nombre --}}
                        <div class="col-12 col-md-6 mb-3">
                            <label class="form-label">
                                Nombre <span class="text-danger">*</span>
                            </label>
                            <input name="name" type="text"
                                   class="form-control"
                                   value="{{ old('name', $user->name) }}">
                            @error('name')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div class="col-12 col-md-6 mb-3">
                            <label class="form-label">
                                Email <span class="text-danger">*</span>
                            </label>
                            <input name="email" type="email"
                                   class="form-control"
                                   value="{{ old('email', $user->email) }}"
                                   {{ $user->exists ? 'readonly' : '' }}>
                            @error('email')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Contraseña --}}
                        <div class="col-12 col-md-6 mb-3">
                            <label class="form-label">
                                Contraseña 
                                @if(!$user->exists)
                                    <span class="text-danger">*</span>
                                @else
                                    <small class="text-muted">(opcional)</small>
                                @endif
                            </label>
                            <input name="password" type="password"
                                   class="form-control">
                            @error('password')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Rol --}}
                        <div class="col-12 col-md-6 mb-3">
                            <label class="form-label">
                                Rol <span class="text-danger">*</span>
                            </label>
                            <select name="role" class="form-control">
                                <option value="">Selecciona un rol</option>
                                @foreach(\Spatie\Permission\Models\Role::all() as $role)
                                    <option value="{{ $role->name }}" 
                                        {{ old('role', $user->roles->first()?->name) == $role->name ? 'selected' : '' }}>
                                        {{ ucfirst($role->name) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    {{-- Campos adicionales --}}
                    <div class="row">
                        @foreach([
                            'locale' => 'Idioma',
                            'time_notation' => 'Notación de Hora',
                            'timezone' => 'Zona Horaria',
                            'slug' => 'Slug',
                            'scheduling_url' => 'URL de Agendamiento',
                            'calendly_uri' => 'Calendly URI'
                        ] as $field => $label)
                            <div class="col-12 col-md-6 mb-3">
                                <label class="form-label">{{ $label }}</label>
                                <input name="{{ $field }}" type="text"
                                       class="form-control"
                                       value="{{ old($field, $user->$field) }}">
                                @error($field)
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        @endforeach
                    </div>

                    {{-- Botones --}}
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <button type="submit" class="btn btn-primary">
                            Guardar
                        </button>
                        <a href="{{ route('usuarios') }}" class="btn btn-outline-secondary">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
