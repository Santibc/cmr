<x-app-layout>
    <x-slot name="header">
        {{ __('Formulario Fulfillment Daily') }}
    </x-slot>

    <div class="py-12" style="padding-top: 0;">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Encabezado -->
                    <div class="mb-4">
                        <h2 class="text-2xl font-semibold mb-2"><i class="bi bi-clipboard-check me-2"></i>{{ $form->name }}</h2>
                        @if($form->description)
                            <p class="text-muted mb-0">{{ $form->description }}</p>
                        @endif
                    </div>

    <div class="row justify-content-center">
        <!-- Formulario -->
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form id="fulfillmentForm">
                        @csrf

                        @foreach($form->fields as $field)
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    {{ $field->label }}
                                    @if($field->is_required)
                                        <span class="text-danger">*</span>
                                    @endif
                                </label>

                                @if($field->help_text)
                                    <small class="form-text text-muted d-block mb-1">{{ $field->help_text }}</small>
                                @endif

                                @switch($field->field_type)
                                    @case('text')
                                    @case('email')
                                        <input
                                            type="{{ $field->field_type }}"
                                            name="{{ $field->field_name }}"
                                            class="form-control"
                                            placeholder="{{ $field->placeholder }}"
                                            {{ $field->is_required ? 'required' : '' }}
                                        >
                                        @break

                                    @case('number')
                                        <input
                                            type="number"
                                            name="{{ $field->field_name }}"
                                            class="form-control"
                                            placeholder="{{ $field->placeholder }}"
                                            {{ $field->is_required ? 'required' : '' }}
                                        >
                                        @break

                                    @case('date')
                                        <input
                                            type="date"
                                            name="{{ $field->field_name }}"
                                            class="form-control"
                                            {{ $field->is_required ? 'required' : '' }}
                                            value="{{ date('Y-m-d') }}"
                                        >
                                        @break

                                    @case('textarea')
                                        <textarea
                                            name="{{ $field->field_name }}"
                                            class="form-control"
                                            rows="4"
                                            placeholder="{{ $field->placeholder }}"
                                            {{ $field->is_required ? 'required' : '' }}
                                        ></textarea>
                                        @break

                                    @case('select')
                                        <select
                                            name="{{ $field->field_name }}"
                                            class="form-select"
                                            {{ $field->is_required ? 'required' : '' }}
                                        >
                                            <option value="">Seleccione una opción</option>
                                            @if($field->options)
                                                @foreach($field->options as $option)
                                                    <option value="{{ $option }}">{{ $option }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        @break

                                    @case('radio')
                                        @if($field->options)
                                            <div class="d-flex flex-column gap-2">
                                                @foreach($field->options as $option)
                                                    <div class="form-check">
                                                        <input
                                                            class="form-check-input"
                                                            type="radio"
                                                            name="{{ $field->field_name }}"
                                                            id="{{ $field->field_name }}_{{ $loop->index }}"
                                                            value="{{ $option }}"
                                                            {{ $field->is_required ? 'required' : '' }}
                                                        >
                                                        <label class="form-check-label" for="{{ $field->field_name }}_{{ $loop->index }}">
                                                            {{ $option }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                        @break

                                    @case('checkbox')
                                        @if($field->options)
                                            <div class="d-flex flex-column gap-2">
                                                @foreach($field->options as $option)
                                                    <div class="form-check">
                                                        <input
                                                            class="form-check-input"
                                                            type="checkbox"
                                                            name="{{ $field->field_name }}[]"
                                                            id="{{ $field->field_name }}_{{ $loop->index }}"
                                                            value="{{ $option }}"
                                                        >
                                                        <label class="form-check-label" for="{{ $field->field_name }}_{{ $loop->index }}">
                                                            {{ $option }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                        @break

                                    @case('scale')
                                        @php
                                            $min = $field->options['min'] ?? 1;
                                            $max = $field->options['max'] ?? 10;
                                        @endphp
                                        <div class="d-flex gap-2 align-items-center flex-wrap">
                                            @for($i = $min; $i <= $max; $i++)
                                                <div class="form-check">
                                                    <input
                                                        class="form-check-input"
                                                        type="radio"
                                                        name="{{ $field->field_name }}"
                                                        id="{{ $field->field_name }}_{{ $i }}"
                                                        value="{{ $i }}"
                                                        {{ $field->is_required ? 'required' : '' }}
                                                    >
                                                    <label class="form-check-label" for="{{ $field->field_name }}_{{ $i }}">
                                                        {{ $i }}
                                                    </label>
                                                </div>
                                            @endfor
                                        </div>
                                        @break

                                    @case('rating')
                                        @php
                                            $maxRating = $field->options['max'] ?? 5;
                                        @endphp
                                        <div class="d-flex gap-2">
                                            @for($i = 1; $i <= $maxRating; $i++)
                                                <div class="form-check">
                                                    <input
                                                        class="form-check-input"
                                                        type="radio"
                                                        name="{{ $field->field_name }}"
                                                        id="{{ $field->field_name }}_star_{{ $i }}"
                                                        value="{{ $i }}"
                                                        {{ $field->is_required ? 'required' : '' }}
                                                    >
                                                    <label class="form-check-label" for="{{ $field->field_name }}_star_{{ $i }}">
                                                        ⭐ {{ $i }}
                                                    </label>
                                                </div>
                                            @endfor
                                        </div>
                                        @break
                                @endswitch
                            </div>
                        @endforeach

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-send me-2"></i>Enviar Formulario
                            </button>
                            <button type="reset" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-clockwise me-2"></i>Limpiar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Enviar formulario
    $('#fulfillmentForm').on('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        $.ajax({
            url: '{{ route("fulfillment.form.store") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                });

                // Limpiar formulario
                $('#fulfillmentForm')[0].reset();

                // Establecer fecha de hoy nuevamente
                $('input[type="date"]').val('{{ date("Y-m-d") }}');
            },
            error: function(xhr) {
                let errorMessage = 'Error al enviar el formulario';

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    errorMessage = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    html: errorMessage
                });
            }
        });
    });
});
</script>
@endpush

</x-app-layout>