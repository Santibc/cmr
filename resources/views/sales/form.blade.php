<x-app-layout>
    <x-slot name="header">
        {{ __('Formulario de Cierre de Venta') }}
    </x-slot>

    <div class="container py-4">
        <div class="card shadow">
            <div class="card-body">
                <form method="POST" action="{{ route('sales.store') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="lead_id" value="{{ $lead->id }}">

                    <div class="row">
                        <!-- Columna Izquierda -->
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Nombre del Cliente <span class="text-danger">*</span></label>
                                <input name="nombre_cliente" type="text" class="form-control" value="{{ old('nombre_cliente', $lead->nombre) }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Apellido del Cliente <span class="text-danger">*</span></label>
                                <input name="apellido_cliente" type="text" class="form-control" value="{{ old('apellido_cliente') }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Correo Electrónico <span class="text-danger">*</span></label>
                                <input name="email_cliente" type="email" class="form-control" value="{{ old('email_cliente', $lead->email) }}" required>
                            </div>
                             <div class="mb-3">
                                <label class="form-label">Número de Teléfono <span class="text-danger">*</span></label>
                                <input name="telefono_cliente" type="text" class="form-control" value="{{ old('telefono_cliente', $lead->telefono) }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Identificación Personal</label>
                                <input name="identificacion_personal" type="text" class="form-control" value="{{ old('identificacion_personal') }}">
                            </div>
                        </div>

                        <!-- Columna Derecha -->
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Domicilio <span class="text-danger">*</span></label>
                                <input name="domicilio" type="text" class="form-control" value="{{ old('domicilio') }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Método de Pago <span class="text-danger">*</span></label>
                                <input name="metodo_pago" type="text" class="form-control" value="{{ old('metodo_pago') }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Comprobante de Pago <span class="text-danger">*</span></label>
                                <input name="comprobante_pago" type="file" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tipo de Acuerdo Contractual <span class="text-danger">*</span></label>
                                <select name="tipo_acuerdo" class="form-select" required>
                                    <option value="">Seleccione un acuerdo</option>
                                    <option value="Pago completo" {{ old('tipo_acuerdo') == 'Pago completo' ? 'selected' : '' }}>Pago completo</option>
                                    <option value="Pago en 2 cuotas" {{ old('tipo_acuerdo') == 'Pago en 2 cuotas' ? 'selected' : '' }}>Pago en 2 cuotas</option>
                                    <option value="Pago en 3 cuotas" {{ old('tipo_acuerdo') == 'Pago en 3 cuotas' ? 'selected' : '' }}>Pago en 3 cuotas</option>
                                    <option value="Beca" {{ old('tipo_acuerdo') == 'Beca' ? 'selected' : '' }}>Beca</option>
                                    <option value="Low ticket" {{ old('tipo_acuerdo') == 'Low ticket' ? 'selected' : '' }}>Low ticket</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tipo de Contrato <span class="text-danger">*</span></label>
                                <select name="tipo_contrato" class="form-select" required>
                                    <option value="">Seleccione el tipo de contrato</option>
                                    <option value="low ticket" {{ old('tipo_contrato') == 'low ticket' ? 'selected' : '' }}>Low Ticket</option>
                                    <option value="high ticket" {{ old('tipo_contrato') == 'high ticket' ? 'selected' : '' }}>High Ticket</option>
                                    <option value="beca" {{ old('tipo_contrato') == 'beca' ? 'selected' : '' }}>Beca</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Comentarios/Aclaraciones</label>
                                <textarea name="comentarios" class="form-control" rows="3">{{ old('comentarios') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Sección de Contrato -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5 class="text-primary mb-3">Información del Contrato</h5>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Contrato <span class="text-danger">*</span></label>
                                <select name="contract_template_id" id="contract_template_id" class="form-select" required>
                                    <option value="">Seleccione un contrato</option>
                                    @foreach($contractTemplates as $template)
                                        <option value="{{ $template->id }}" {{ old('contract_template_id') == $template->id ? 'selected' : '' }}>
                                            {{ $template->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3" id="forma_pago_container" style="display: none;">
                                <label class="form-label">Forma de Pago (para el contrato) <span class="text-danger">*</span></label>
                                <textarea name="forma_de_pago" class="form-control" rows="3" placeholder="Describe la forma de pago acordada...">{{ old('forma_de_pago') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <button type="submit" class="btn btn-primary">Registrar Venta</button>
                        <a href="{{ route('leads') }}" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const contractSelect = document.getElementById('contract_template_id');
        const formaPagoContainer = document.getElementById('forma_pago_container');

        contractSelect.addEventListener('change', function() {
            if (this.value) {
                formaPagoContainer.style.display = 'block';
                formaPagoContainer.querySelector('textarea').required = true;
            } else {
                formaPagoContainer.style.display = 'none';
                formaPagoContainer.querySelector('textarea').required = false;
            }
        });

        // Verificar estado inicial
        if (contractSelect.value) {
            formaPagoContainer.style.display = 'block';
            formaPagoContainer.querySelector('textarea').required = true;
        }
    });
</script>
@endpush
</x-app-layout>
