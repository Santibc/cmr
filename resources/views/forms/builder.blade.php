<x-app-layout>
    <x-slot name="header">
        {{ isset($form) ? __('Editar Formulario') : __('Crear Formulario') }}
    </x-slot>

    <div class="py-12" style="padding-top: 0;">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="text-2xl font-semibold">
                            {{ isset($form) ? 'Editar Formulario: ' . $form->name : 'Crear Nuevo Formulario' }}
                        </h4>
                        <a href="{{ route('forms.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Volver
                        </a>
                    </div>

                    <form id="formBuilder" method="POST">
                        @csrf
                        @if(isset($form))
                            @method('PUT')
                        @endif

                        <!-- Información básica del formulario -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">Información del Formulario</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label">Nombre del Formulario <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name"
                                               value="{{ old('name', $form->name ?? '') }}" required>
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label for="status" class="form-label">Estado <span class="text-danger">*</span></label>
                                        <select class="form-select" id="status" name="status" required>
                                            @foreach(\App\Models\Form::getStatuses() as $value => $label)
                                                <option value="{{ $value }}"
                                                    {{ old('status', $form->status ?? 'draft') == $value ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label for="module" class="form-label">Módulo</label>
                                        <select class="form-select" id="module" name="module">
                                            <option value="">Sin módulo</option>
                                            @foreach($modules as $value => $label)
                                                <option value="{{ $value }}"
                                                    {{ old('module', $form->module ?? '') == $value ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-12 mb-3">
                                        <label for="description" class="form-label">Descripción</label>
                                        <textarea class="form-control" id="description" name="description" rows="2">{{ old('description', $form->description ?? '') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Constructor de campos -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">Campos del Formulario</h5>
                            </div>
                            <div class="card-body">
                                <div id="fieldsContainer">
                                    @if(isset($form) && $form->fields->count() > 0)
                                        @foreach($form->fields as $index => $field)
                                            <div class="field-item border rounded p-3 mb-3" data-index="{{ $index }}">
                                                <input type="hidden" name="fields[{{ $index }}][id]" value="{{ $field->id }}">

                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <h6 class="mb-0">Campo #{{ $index + 1 }}</h6>
                                                    <button type="button" class="btn btn-sm btn-danger remove-field-btn">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-4 mb-2">
                                                        <label class="form-label">Etiqueta <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control" name="fields[{{ $index }}][label]"
                                                               value="{{ $field->label }}" required>
                                                    </div>

                                                    <div class="col-md-3 mb-2">
                                                        <label class="form-label">Tipo de Campo <span class="text-danger">*</span></label>
                                                        <select class="form-select field-type-select" name="fields[{{ $index }}][field_type]" required>
                                                            @foreach($fieldTypes as $type => $typeLabel)
                                                                <option value="{{ $type }}" {{ $field->field_type == $type ? 'selected' : '' }}>
                                                                    {{ $typeLabel }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <div class="col-md-3 mb-2">
                                                        <label class="form-label">Placeholder</label>
                                                        <input type="text" class="form-control" name="fields[{{ $index }}][placeholder]"
                                                               value="{{ $field->placeholder }}">
                                                    </div>

                                                    <div class="col-md-2 mb-2">
                                                        <label class="form-label">Obligatorio</label>
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox"
                                                                   name="fields[{{ $index }}][is_required]" value="1"
                                                                   {{ $field->is_required ? 'checked' : '' }}>
                                                        </div>
                                                    </div>

                                                    <!-- Opciones para select/radio/checkbox -->
                                                    <div class="col-12 mb-2 options-container"
                                                         style="display: {{ in_array($field->field_type, ['select', 'radio', 'checkbox']) ? 'block' : 'none' }}">
                                                        <label class="form-label">Opciones (una por línea)</label>
                                                        <textarea class="form-control options-textarea" name="fields[{{ $index }}][options]" rows="3">{{ is_array($field->options) && !isset($field->options['min']) ? implode("\n", $field->options) : '' }}</textarea>
                                                    </div>

                                                    <!-- Opciones para scale/rating -->
                                                    <div class="col-12 mb-2 scale-rating-container row" style="display: {{ in_array($field->field_type, ['scale', 'rating']) ? 'flex' : 'none' }}">
                                                        <div class="col-md-6">
                                                            <label class="form-label">Valor mínimo</label>
                                                            <input type="number" class="form-control scale-min" name="fields[{{ $index }}][scale_min]" value="{{ is_array($field->options) && isset($field->options['min']) ? $field->options['min'] : 1 }}">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Valor máximo</label>
                                                            <input type="number" class="form-control scale-max" name="fields[{{ $index }}][scale_max]" value="{{ is_array($field->options) && isset($field->options['max']) ? $field->options['max'] : 5 }}">
                                                        </div>
                                                    </div>

                                                    <div class="col-12 mb-2">
                                                        <label class="form-label">Texto de ayuda</label>
                                                        <input type="text" class="form-control" name="fields[{{ $index }}][help_text]"
                                                               value="{{ $field->help_text }}">
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <p class="text-muted text-center" id="emptyMessage">No hay campos. Haz clic en "Agregar Campo" para comenzar.</p>
                                    @endif
                                </div>

                                <!-- Botón de agregar campo al final -->
                                <div class="mt-3 text-center">
                                    <button type="button" class="btn btn-primary" id="addFieldBtn">
                                        <i class="bi bi-plus-circle"></i> Agregar Campo
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('forms.index') }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary" id="saveFormBtn">
                                <i class="bi bi-save"></i> {{ isset($form) ? 'Actualizar Formulario' : 'Crear Formulario' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    <script>
        let fieldIndex = {{ isset($form) ? $form->fields->count() : 0 }};

        $(document).ready(function() {
            // Inicializar visibilidad de contenedores de opciones al cargar
            $('.field-type-select').each(function() {
                const fieldType = $(this).val();
                const fieldItem = $(this).closest('.field-item');
                const optionsContainer = fieldItem.find('.options-container');
                const scaleRatingContainer = fieldItem.find('.scale-rating-container');

                if (['select', 'radio', 'checkbox'].includes(fieldType)) {
                    optionsContainer.show();
                    scaleRatingContainer.hide();
                } else if (['scale', 'rating'].includes(fieldType)) {
                    optionsContainer.hide();
                    scaleRatingContainer.css('display', 'flex');
                } else {
                    optionsContainer.hide();
                    scaleRatingContainer.hide();
                }
            });

            // Agregar nuevo campo
            $('#addFieldBtn').on('click', function() {
                const fieldHtml = `
                    <div class="field-item border rounded p-3 mb-3" data-index="${fieldIndex}">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">Campo #${fieldIndex + 1}</h6>
                            <button type="button" class="btn btn-sm btn-danger remove-field-btn">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Etiqueta <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="fields[${fieldIndex}][label]" required>
                            </div>

                            <div class="col-md-3 mb-2">
                                <label class="form-label">Tipo de Campo <span class="text-danger">*</span></label>
                                <select class="form-select field-type-select" name="fields[${fieldIndex}][field_type]" required>
                                    @foreach($fieldTypes as $type => $typeLabel)
                                        <option value="{{ $type }}">{{ $typeLabel }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3 mb-2">
                                <label class="form-label">Placeholder</label>
                                <input type="text" class="form-control" name="fields[${fieldIndex}][placeholder]">
                            </div>

                            <div class="col-md-2 mb-2">
                                <label class="form-label">Obligatorio</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="fields[${fieldIndex}][is_required]" value="1">
                                </div>
                            </div>

                            <!-- Opciones para select/radio/checkbox -->
                            <div class="col-12 mb-2 options-container" style="display: none;">
                                <label class="form-label">Opciones (una por línea)</label>
                                <textarea class="form-control options-textarea" name="fields[${fieldIndex}][options]" rows="3"></textarea>
                            </div>

                            <!-- Opciones para scale/rating -->
                            <div class="col-12 mb-2 scale-rating-container row" style="display: none;">
                                <div class="col-md-6">
                                    <label class="form-label">Valor mínimo</label>
                                    <input type="number" class="form-control scale-min" name="fields[${fieldIndex}][scale_min]" value="1">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Valor máximo</label>
                                    <input type="number" class="form-control scale-max" name="fields[${fieldIndex}][scale_max]" value="5">
                                </div>
                            </div>

                            <div class="col-12 mb-2">
                                <label class="form-label">Texto de ayuda</label>
                                <input type="text" class="form-control" name="fields[${fieldIndex}][help_text]">
                            </div>
                        </div>
                    </div>
                `;

                $('#fieldsContainer').append(fieldHtml);
                $('#emptyMessage').hide(); // Ocultar mensaje de "No hay campos"
                fieldIndex++;
            });

            // Eliminar campo
            $(document).on('click', '.remove-field-btn', function() {
                if (confirm('¿Estás seguro de eliminar este campo?')) {
                    $(this).closest('.field-item').remove();

                    // Mostrar mensaje si no hay campos
                    if ($('.field-item').length === 0) {
                        $('#emptyMessage').show();
                    }
                }
            });

            // Mostrar/ocultar opciones según tipo de campo
            $(document).on('change', '.field-type-select', function() {
                const fieldType = $(this).val();
                const fieldItem = $(this).closest('.field-item');
                const optionsContainer = fieldItem.find('.options-container');
                const scaleRatingContainer = fieldItem.find('.scale-rating-container');

                // Ocultar ambos contenedores primero
                optionsContainer.hide();
                scaleRatingContainer.hide();

                // Mostrar el contenedor apropiado
                if (['select', 'radio', 'checkbox'].includes(fieldType)) {
                    optionsContainer.show();
                } else if (['scale', 'rating'].includes(fieldType)) {
                    scaleRatingContainer.css('display', 'flex');
                }
            });

            // Submit del formulario
            $('#formBuilder').on('submit', function(e) {
                e.preventDefault();

                // Validar que haya al menos un campo
                if ($('.field-item').length === 0) {
                    Swal.fire('Error', 'Debes agregar al menos un campo al formulario', 'error');
                    return;
                }

                // Validar que los campos select/radio/checkbox tengan opciones
                let hasErrors = false;
                $('.field-item').each(function() {
                    const fieldType = $(this).find('.field-type-select').val();
                    const label = $(this).find('input[name*="[label]"]').val();

                    if (['select', 'radio', 'checkbox'].includes(fieldType)) {
                        const optionsTextarea = $(this).find('.options-textarea').val();
                        if (!optionsTextarea || optionsTextarea.trim() === '') {
                            Swal.fire('Error', `El campo "${label}" de tipo "${fieldType}" debe tener al menos una opción`, 'error');
                            hasErrors = true;
                            return false; // break
                        }
                    }
                });

                if (hasErrors) return;

                const formData = new FormData(this);
                const url = @json(isset($form) ? route('forms.update', $form->id) : route('forms.store'));

                // Procesar campos dinámicamente
                $('.field-item').each(function() {
                    const fieldItem = $(this);
                    const fieldIndex = fieldItem.data('index'); // Usar data-index en lugar del índice del iterador
                    const fieldType = fieldItem.find('.field-type-select').val();
                    const optionsFieldName = `fields[${fieldIndex}][options]`;

                    // Eliminar el campo options del formData para reconstruirlo correctamente
                    formData.delete(optionsFieldName);
                    formData.delete(`fields[${fieldIndex}][scale_min]`);
                    formData.delete(`fields[${fieldIndex}][scale_max]`);

                    if (['select', 'radio', 'checkbox'].includes(fieldType)) {
                        // Para select/radio/checkbox: array de strings
                        const optionsTextarea = fieldItem.find('.options-textarea').val();
                        if (optionsTextarea && optionsTextarea.trim() !== '') {
                            const options = optionsTextarea.split('\n').filter(opt => opt.trim() !== '');
                            formData.append(optionsFieldName, JSON.stringify(options));
                        } else {
                            // Si no hay opciones, enviar array vacío
                            formData.append(optionsFieldName, JSON.stringify([]));
                        }
                    } else if (['scale', 'rating'].includes(fieldType)) {
                        // Para scale/rating: objeto con min y max
                        const min = parseInt(fieldItem.find('.scale-min').val()) || 1;
                        const max = parseInt(fieldItem.find('.scale-max').val()) || 5;
                        formData.append(optionsFieldName, JSON.stringify({ min: min, max: max }));
                    } else {
                        // Para otros tipos de campo, enviar null
                        formData.append(optionsFieldName, 'null');
                    }
                });

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Éxito',
                                text: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                window.location.href = '{{ route("forms.index") }}';
                            });
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = 'Error al guardar el formulario';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        Swal.fire('Error', errorMsg, 'error');
                    }
                });
            });
        });
    </script>
    @endpush
</x-app-layout>
