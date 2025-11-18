<x-app-layout>
    <x-slot name="header">
        {{ __('Respuestas del Formulario') }}
    </x-slot>

    <div class="py-12" style="padding-top: 0;">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h4 class="text-2xl font-semibold">{{ $form->name }}</h4>
                            <p class="text-muted">{{ $form->description }}</p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('forms.submissions.export', $form->id) }}" class="btn btn-success">
                                <i class="bi bi-file-earmark-spreadsheet"></i> Exportar CSV
                            </a>
                            <a href="{{ route('forms.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Volver
                            </a>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="submissions-table" class="table table-striped table-hover table-bordered" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Acciones</th>
                                    <th>ID</th>
                                    <th>Enviado por</th>
                                    <th>Lead relacionado</th>
                                    <th>Estado</th>
                                    <th>Fecha de envío</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para ver detalles de la respuesta -->
    <div class="modal fade" id="submissionDetailsModal" tabindex="-1" aria-labelledby="submissionDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="submissionDetailsModalLabel">Detalles de la Respuesta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="submissionDetailsContent">
                        <p class="text-center">Cargando...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">
    @endpush

    @push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            // DataTables
            const table = $('#submissions-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                scrollX: true,
                autoWidth: false,
                ajax: "{{ route('forms.submissions.index', $form->id) }}",
                columns: [
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                    { data: 'id', name: 'id' },
                    { data: 'submitted_by', name: 'user.name' },
                    { data: 'related_lead', name: 'lead.nombre' },
                    { data: 'status_badge', name: 'status', orderable: false },
                    { data: 'submitted_date', name: 'submitted_at' }
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                },
                order: [[5, 'desc']]
            });

            // Ver detalles de la respuesta
            $(document).on('click', '.view-submission-btn', function() {
                const submissionId = $(this).data('submission-id');

                $('#submissionDetailsModal').modal('show');
                $('#submissionDetailsContent').html('<p class="text-center">Cargando...</p>');

                $.get(`/forms/submissions/${submissionId}/show`, function(response) {
                    if (response.success) {
                        const submission = response.submission;

                        let html = `
                            <div class="mb-3">
                                <p><strong>Formulario:</strong> ${submission.form_name}</p>
                                <p><strong>Enviado por:</strong> ${submission.submitted_by}</p>
                                <p><strong>Lead relacionado:</strong> ${submission.related_lead}</p>
                                <p><strong>Fecha:</strong> ${submission.submitted_at}</p>
                                <p><strong>Estado:</strong> <span class="badge bg-info">${submission.status}</span></p>
                            </div>
                            <hr>
                            <h6 class="mb-3">Respuestas:</h6>
                            <table class="table table-bordered table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 40%;">Campo</th>
                                        <th>Respuesta</th>
                                    </tr>
                                </thead>
                                <tbody>
                        `;

                        submission.data.forEach(field => {
                            html += `
                                <tr>
                                    <td><strong>${field.label}</strong></td>
                                    <td>${field.value}</td>
                                </tr>
                            `;
                        });

                        html += `
                                </tbody>
                            </table>
                        `;

                        $('#submissionDetailsContent').html(html);
                    }
                }).fail(function() {
                    $('#submissionDetailsContent').html('<p class="text-danger text-center">Error al cargar los detalles.</p>');
                });
            });
        });
    </script>
    @endpush
</x-app-layout>
