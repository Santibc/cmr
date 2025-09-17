<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Firma de Contrato - {{ $contractTemplate->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .signature-pad {
            border: 1px solid #ccc;
            background-color: #f9f9f9;
            cursor: crosshair;
        }
        .contract-preview {
            border: 1px solid #ddd;
            background-color: white;
            height: 600px;
            overflow-y: auto;
            padding: 20px;
        }
        .preview-content {
            transform-origin: top left;
            transform: scale(0.8);
            width: 125%;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12 mb-3">
                <h2 class="text-center">Completar Contrato: {{ $contractTemplate->name }}</h2>
                <p class="text-center text-muted">Complete los campos faltantes para finalizar su contrato</p>
            </div>
        </div>

        <div class="row">
            <!-- Panel Izquierdo: Formulario -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Información Requerida</h5>
                    </div>
                    <div class="card-body">
                        <form id="contractForm" method="POST" action="{{ route('contract.update', $sale->contract_token) }}">
                            @csrf
                            @method('PUT')

                            @foreach($missingFields as $field)
                                @if($field === 'imagen_firma')
                                    <div class="mb-3">
                                        <label class="form-label">Firma Digital <span class="text-danger">*</span></label>
                                        <div>
                                            <canvas id="signaturePad" class="signature-pad" width="400" height="200"></canvas>
                                            <input type="hidden" name="imagen_firma" id="signatureData">
                                        </div>
                                        <div class="mt-2">
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearSignature()">
                                                Limpiar Firma
                                            </button>
                                        </div>
                                        @error($field)
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @else
                                    <div class="mb-3">
                                        <label class="form-label">{{ ucfirst(str_replace('_', ' ', $field)) }} <span class="text-danger">*</span></label>
                                        @if(strlen($field) > 15 || in_array($field, ['comentarios', 'observaciones', 'descripcion']))
                                            <textarea
                                                name="{{ $field }}"
                                                class="form-control"
                                                rows="3"
                                                required
                                                oninput="updatePreview()"
                                            >{{ old($field, $sale->contract_data[$field] ?? '') }}</textarea>
                                        @else
                                            <input
                                                type="text"
                                                name="{{ $field }}"
                                                class="form-control"
                                                value="{{ old($field, $sale->contract_data[$field] ?? '') }}"
                                                required
                                                oninput="updatePreview()"
                                            >
                                        @endif
                                        @error($field)
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @endif
                            @endforeach

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Completar Contrato</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Panel Derecho: Vista Previa -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Vista Previa del Contrato</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="contract-preview">
                            <div class="preview-content" id="contractPreview">
                                <!-- La vista previa se cargará aquí -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Configuración del canvas para firma
        const canvas = document.getElementById('signaturePad');
        const ctx = canvas.getContext('2d');
        let isDrawing = false;

        // Eventos del canvas
        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDrawing);
        canvas.addEventListener('mouseout', stopDrawing);

        // Eventos táctiles para dispositivos móviles
        canvas.addEventListener('touchstart', handleTouch);
        canvas.addEventListener('touchmove', handleTouch);
        canvas.addEventListener('touchend', stopDrawing);

        function startDrawing(e) {
            isDrawing = true;
            draw(e);
        }

        function draw(e) {
            if (!isDrawing) return;

            const rect = canvas.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            ctx.lineWidth = 2;
            ctx.lineCap = 'round';
            ctx.strokeStyle = '#000';

            ctx.lineTo(x, y);
            ctx.stroke();
            ctx.beginPath();
            ctx.moveTo(x, y);

            // Actualizar campo oculto con datos de la firma
            updateSignatureData();
        }

        function stopDrawing() {
            if (isDrawing) {
                isDrawing = false;
                ctx.beginPath();
            }
        }

        function handleTouch(e) {
            e.preventDefault();
            const touch = e.touches[0];
            const mouseEvent = new MouseEvent(e.type === 'touchstart' ? 'mousedown' :
                                            e.type === 'touchmove' ? 'mousemove' : 'mouseup', {
                clientX: touch.clientX,
                clientY: touch.clientY
            });
            canvas.dispatchEvent(mouseEvent);
        }

        function clearSignature() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            document.getElementById('signatureData').value = '';

            // Limpiar timeout si existe
            if (signatureUpdateTimeout) {
                clearTimeout(signatureUpdateTimeout);
            }

            updatePreview();
        }

        let signatureUpdateTimeout;

        function updateSignatureData() {
            const dataURL = canvas.toDataURL();
            document.getElementById('signatureData').value = dataURL;

            // Limpiar timeout anterior si existe
            if (signatureUpdateTimeout) {
                clearTimeout(signatureUpdateTimeout);
            }

            // Actualizar vista previa después de 1 segundo de delay
            signatureUpdateTimeout = setTimeout(() => {
                updatePreview();
            }, 1000);
        }

        // Función para actualizar vista previa
        function updatePreview() {
            const formData = new FormData(document.getElementById('contractForm'));
            const params = new URLSearchParams();

            for (const [key, value] of formData) {
                if (key !== '_method') { // Excluir el campo _method del FormData
                    params.append(key, value);
                }
            }

            fetch('{{ route("contract.preview.ajax", $sale->contract_token) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: params
            })
            .then(response => response.text())
            .then(html => {
                document.getElementById('contractPreview').innerHTML = html;
            })
            .catch(error => {
                console.error('Error updating preview:', error);
            });
        }

        // Cargar vista previa inicial
        document.addEventListener('DOMContentLoaded', function() {
            updatePreview();
        });
    </script>
</body>
</html>