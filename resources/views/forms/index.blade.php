<x-app-layout>
    <x-slot name="header">
        {{ __('Gestión de Formularios Dinámicos') }}
    </x-slot>

    <div class="py-12" style="padding-top: 0;">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="text-2xl font-semibold">Gestión de Formularios</h4>
                        <a href="{{ route('forms.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Crear Formulario
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table id="forms-table" class="table table-striped table-hover table-bordered" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Acciones</th>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th>Estado</th>
                                    <th>Módulo</th>
                                    <th>Respuestas</th>
                                    <th>Creado por</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para logs -->
    <div class="modal fade" id="logsModal" tabindex="-1" aria-labelledby="logsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logsModalLabel">Historial de Cambios</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Tipo</th>
                                <th>Estado anterior</th>
                                <th>Estado nuevo</th>
                                <th>Comentario</th>
                                <th>Usuario</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody id="logsTableBody">
                            <tr><td colspan="6" class="text-center">Cargando...</td></tr>
                        </tbody>
                    </table>
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
    <style>
        /* Fix para alineación de columnas en DataTables */
        #forms-table {
            width: 100% !important;
        }
        #forms-table thead th,
        #forms-table tbody td {
            white-space: nowrap;
        }
        .table-responsive {
            overflow-x: auto;
        }
    </style>
    @endpush

    @push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            // PATRÓN: DataTables server-side
            const table = $('#forms-table').DataTable({
                processing: true,
                serverSide: true,
                scrollX: true,
                ajax: "{{ route('forms.index') }}",
                columns: [
                    { data: 'action', name: 'action', orderable: false, searchable: false, width: '100px' },
                    { data: 'name', name: 'name' },
                    { data: 'description', name: 'description' },
                    { data: 'status_badge', name: 'status', orderable: false, width: '80px' },
                    { data: 'module_badge', name: 'module', orderable: false, width: '100px' },
                    { data: 'submissions_count', name: 'submissions_count', width: '80px' },
                    { data: 'user.name', name: 'user.name' },
                    { data: 'created_at', name: 'created_at', width: '150px' }
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                },
                drawCallback: function() {
                    // Ajustar columnas después de dibujar
                    table.columns.adjust();
                }
            });

            // PATRÓN: Ver logs
            $(document).on('click', '.view-logs-btn', function () {
                const formId = $(this).data('form-id');
                $('#logsModal').modal('show');
                $('#logsTableBody').html('<tr><td colspan="6" class="text-center">Cargando...</td></tr>');

                $.get(`/forms/${formId}/logs`, function (data) {
                    if (data.length === 0) {
                        $('#logsTableBody').html('<tr><td colspan="6" class="text-center">Sin registros</td></tr>');
                        return;
                    }

                    let rows = '';
                    data.forEach(log => {
                        rows += `
                            <tr>
                                <td><span class="badge bg-primary">${log.tipo}</span></td>
                                <td>${log.estado_anterior}</td>
                                <td>${log.estado_nuevo}</td>
                                <td>${log.comentario}</td>
                                <td>${log.usuario}</td>
                                <td>${log.fecha}</td>
                            </tr>
                        `;
                    });

                    $('#logsTableBody').html(rows);
                }).fail(() => {
                    $('#logsTableBody').html('<tr><td colspan="6" class="text-danger text-center">Error al cargar los logs.</td></tr>');
                });
            });
        });
    </script>
    @endpush
</x-app-layout>
