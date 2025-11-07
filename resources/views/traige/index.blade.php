<x-app-layout>
    <x-slot name="header">
        {{ __('Módulo de Triage') }}
    </x-slot>

    <div class="py-12" style="padding-top: 0;">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h4 class="text-2xl font-semibold mb-4">Gestión de Leads - Triage</h4>

                    <div class="border border-gray-300 rounded-lg">
                        <div class="overflow-x-auto">
                            <table id="traige-table" class="table-responsive w-full text-sm text-left text-gray-700">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                                    <tr class="border-b border-gray-300">
                                        <th class="px-6 py-3" data-priority="1">Acciones</th>
                                        <th class="px-6 py-3">Estado Pipeline</th>
                                        <th class="px-6 py-3">Nombre</th>
                                        <th class="px-6 py-3">Email</th>
                                        <th class="px-6 py-3">Teléfono</th>
                                        <th class="px-6 py-3">Instagram</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para comentarios en cambio de estado -->
    <div class="modal fade" id="comentarioModal" tabindex="-1" aria-labelledby="comentarioModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="comentarioModalLabel">Añadir Comentario al Cambio de Estado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="comentarioForm">
                        <input type="hidden" id="leadId" name="lead_id">
                        <input type="hidden" id="statusId" name="status_id">
                        <div class="mb-3">
                            <label for="comentarioText" class="form-label">Comentario</label>
                            <textarea class="form-control" id="comentarioText" name="comentario" rows="3"
                                      placeholder="Agregue un comentario sobre este cambio de estado..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="guardarComentario">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para programar llamada -->
    <div class="modal fade" id="scheduleCallModal" tabindex="-1" aria-labelledby="scheduleCallModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="scheduleCallModalLabel">Programar Llamada de Triage</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="scheduleCallForm">
                        <input type="hidden" id="callLeadId" name="lead_id">
                        <input type="hidden" id="parentCallId" name="parent_call_id">

                        <div class="mb-3">
                            <label for="leadInfo" class="form-label">Lead</label>
                            <input type="text" id="leadInfo" class="form-control" readonly>
                        </div>

                        <div class="mb-3">
                            <label for="scheduledDate" class="form-label">Fecha y Hora <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" id="scheduledDate" name="scheduled_date" required>
                        </div>

                        <div class="mb-3">
                            <label for="callLink" class="form-label">Link de la Llamada <span class="text-danger">*</span></label>
                            <input type="url" class="form-control" id="callLink" name="call_link" required
                                   placeholder="https://meet.google.com/abc-defg-hij">
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notas</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"
                                      placeholder="Notas adicionales para la llamada..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="saveScheduleCall">Programar Llamada</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para trazabilidad de llamadas -->
    <div class="modal fade" id="callsTraceModal" tabindex="-1" aria-labelledby="callsTraceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Trazabilidad de Llamadas de Triage</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div id="callsTraceContent">
                        <div class="text-center">Cargando...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para actualizar estado de llamada -->
    <div class="modal fade" id="updateCallStatusModal" tabindex="-1" aria-labelledby="updateCallStatusModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateCallStatusModalLabel">Actualizar Estado de Llamada</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="updateCallStatusForm">
                        <input type="hidden" id="callId" name="call_id">

                        <div class="mb-3">
                            <label for="callStatus" class="form-label">Estado <span class="text-danger">*</span></label>
                            <select class="form-select" id="callStatus" name="status" required>
                                <option value="pendiente">Pendiente</option>
                                <option value="realizada">Realizada</option>
                                <option value="no_realizada">No Realizada</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="callComments" class="form-label">Comentarios</label>
                            <textarea class="form-control" id="callComments" name="comments" rows="3"
                                      placeholder="Comentarios sobre el estado de la llamada..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="saveCallStatus">Actualizar Estado</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para historial de cambios del lead -->
    <div class="modal fade" id="logsModal" tabindex="-1" aria-labelledby="logsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Historial de Cambios de Estado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
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
            </div>
        </div>
    </div>

    <!-- Modal para historial de una llamada específica -->
    <div class="modal fade" id="callLogsModal" tabindex="-1" aria-labelledby="callLogsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Historial de Cambios de Estado de la Llamada</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Estado Anterior</th>
                                <th>Estado Nuevo</th>
                                <th>Comentario</th>
                                <th>Usuario</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody id="callLogsTableBody">
                            <tr><td colspan="5" class="text-center">Cargando...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para crear nuevo lead -->
    <div class="modal fade" id="newLeadModal" tabindex="-1" aria-labelledby="newLeadModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newLeadModalLabel">Registrar Nuevo Lead</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="newLeadForm">
                        <div class="mb-3">
                            <label for="newLeadNombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="newLeadNombre" name="nombre" required>
                        </div>

                        <div class="mb-3">
                            <label for="newLeadEmail" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="newLeadEmail" name="email" required>
                        </div>

                        <div class="mb-3">
                            <label for="newLeadTelefono" class="form-label">Teléfono</label>
                            <input type="text" class="form-control" id="newLeadTelefono" name="telefono">
                        </div>

                        <div class="mb-3">
                            <label for="newLeadInstagram" class="form-label">Instagram</label>
                            <input type="text" class="form-control" id="newLeadInstagram" name="instagram_user" placeholder="@usuario">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="saveNewLead">Guardar Lead</button>
                </div>
            </div>
        </div>
    </div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const table = $('#traige-table').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            scrollX: true,
            autoWidth: false,
            ajax: "{{ route('traige.index') }}",
            columns: [
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    className: 'noVis'
                },
                {
                    data: 'pipeline_status',
                    name: 'pipeline_status',
                    orderable: false,
                    searchable: true
                },
                { data: 'nombre', name: 'nombre' },
                { data: 'email', name: 'email' },
                { data: 'telefono', name: 'telefono' },
                { data: 'instagram_user', name: 'instagram_user' }
            ],

            dom: "<'flex flex-wrap justify-between items-center mb-4'<'relative'B>f>" +
                 "t" +
                 "<'flex justify-between items-center px-2 my-2'i<'pagination-wrapper'p>>",

            buttons: [
                {
                    extend: 'pageLength',
                    className: 'btn btn-outline-dark',
                    text: 'Filas '
                },
                {
                    extend: 'colvis',
                    text: 'Columnas',
                    columns: ':not(.noVis)',
                    className: 'btn btn-outline-dark'
                },
                {
                    extend: 'excelHtml5',
                    text: 'Excel',
                    className: 'btn btn-outline-success'
                },
                {
                    text: '<i class="bi bi-plus-circle"></i> Nuevo',
                    className: 'btn btn-outline-primary',
                    action: function (e, dt, node, config) {
                        $('#newLeadModal').modal('show');
                    }
                }
            ],
            language: {
                url: '{{ asset("js/datatables/es-ES.json") }}',
                buttons: {
                    pageLength: {
                        _: "Mostrar %d filas",
                        '-1': "Mostrar todos"
                    }
                }
            },
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Todos"]],
            initComplete: function() {
                console.log('DataTable cargado completamente');
            }
        });

        // Configuración de botones
        table.on('buttons-action', function () {
            setTimeout(() => {
                $('.dt-button-collection')
                    .addClass('bg-white border border-gray-300 rounded shadow-md mt-2 p-2')
                    .css({
                        position: 'absolute',
                        'z-index': 999,
                        top: 'calc(100% + 0.5rem)',
                        left: '0',
                        right: 'auto'
                    });

                $('.dt-button-collection button')
                    .removeClass()
                    .addClass('block w-full text-left text-sm text-gray-800 px-4 py-2 rounded hover:bg-gray-100 cursor-pointer transition-colors duration-150');
            }, 50);
        });

        // ==================== MANEJO DE CAMBIO DE ESTADO PIPELINE ====================
        let originalStatusId;

        $('#traige-table').on('focus', '.pipeline-status-select', function () {
            originalStatusId = $(this).val();
        });

        $('#traige-table').on('change', '.pipeline-status-select', function () {
            const leadId = $(this).data('lead-id');
            const statusId = $(this).val();
            const selectElement = $(this);

            console.log('Cambio de estado detectado:', leadId, statusId);

            $('#comentarioModal #leadId').val(leadId);
            $('#comentarioModal #statusId').val(statusId);
            $('#comentarioModal').modal('show');

            $('#comentarioModal').off('hidden.bs.modal').on('hidden.bs.modal', function () {
                if (!$('#comentarioForm').data('submitted')) {
                    selectElement.val(originalStatusId);
                }
                $('#comentarioForm').data('submitted', false);
                $('#comentarioText').val('');
            });
        });

        $('#guardarComentario').on('click', function() {
            const leadId = $('#comentarioModal #leadId').val();
            const statusId = $('#comentarioModal #statusId').val();
            const comentario = $('#comentarioModal #comentarioText').val();

            $.ajax({
                url: `/traige/${leadId}/update-status`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    status_id: statusId,
                    comentario: comentario
                },
                success: function(response) {
                    $('#comentarioModal').modal('hide');
                    $('#comentarioForm').data('submitted', true);
                    $('#traige-table').DataTable().ajax.reload(null, false);
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: 'Estado actualizado correctamente.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al actualizar el estado.'
                    });
                }
            });
        });

        // ==================== PROGRAMAR LLAMADA ====================
        let currentLeadForCall = null;

        $(document).on('click', '.schedule-call-btn', function() {
            const leadId = $(this).data('lead-id');
            const leadName = $(this).data('lead-name');
            const leadEmail = $(this).data('lead-email');

            currentLeadForCall = leadId;

            // Primero resetear el form
            $('#scheduleCallForm')[0].reset();

            // Luego establecer los valores
            $('#callLeadId').val(leadId);
            $('#parentCallId').val('');
            $('#leadInfo').val(`${leadName} (${leadEmail})`);
            $('#scheduledDate').val('');
            $('#callLink').val('');
            $('#notes').val('');

            $('#scheduleCallModal').modal('show');
        });

        $('#saveScheduleCall').on('click', function() {
            const form = $('#scheduleCallForm')[0];

            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            const formData = {
                _token: '{{ csrf_token() }}',
                lead_id: $('#callLeadId').val(),
                scheduled_date: $('#scheduledDate').val(),
                call_link: $('#callLink').val(),
                notes: $('#notes').val(),
                parent_call_id: $('#parentCallId').val() || null
            };

            $.ajax({
                url: '{{ route("traige.calls.store") }}',
                method: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: 'Llamada programada exitosamente y email enviado al lead.',
                            timer: 3000,
                            showConfirmButton: false
                        });
                        $('#scheduleCallModal').modal('hide');
                        $('#scheduleCallForm')[0].reset();
                        $('#traige-table').DataTable().ajax.reload(null, false);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al programar la llamada.'
                    });
                    console.error(xhr.responseText);
                }
            });
        });

        // ==================== TRAZABILIDAD DE LLAMADAS ====================
        $(document).on('click', '.view-traige-calls-btn', function() {
            const leadId = $(this).data('lead-id');

            $('#callsTraceModal').modal('show');
            $('#callsTraceContent').html('<div class="text-center">Cargando llamadas...</div>');

            $.get(`/traige/calls/lead/${leadId}`, function(data) {
                if (data.length === 0) {
                    $('#callsTraceContent').html(`
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> No hay llamadas programadas para este lead.
                        </div>
                    `);
                    return;
                }

                let content = '<div class="table-responsive"><table class="table table-bordered">';
                content += '<thead class="table-light">';
                content += '<tr><th>Fecha Programada</th><th>Estado</th><th>Link</th><th>Notas</th><th>Comentarios</th><th>Usuario Triage</th><th>Email Enviado</th><th>Acciones</th></tr>';
                content += '</thead><tbody>';

                data.forEach(call => {
                    const statusBadge = getStatusBadge(call.status);
                    const emailBadge = call.email_sent ? '<span class="badge bg-success">Sí</span>' : '<span class="badge bg-danger">No</span>';

                    content += `<tr>
                        <td>${call.scheduled_date}</td>
                        <td>${statusBadge}</td>
                        <td><a href="${call.call_link}" target="_blank" class="btn btn-sm btn-outline-primary">Unirse</a></td>
                        <td>${call.notes || '-'}</td>
                        <td>${call.comments || '-'}</td>
                        <td>${call.user_name}</td>
                        <td>${emailBadge}</td>
                        <td>
                            <div class="btn-group-vertical gap-1">
                                <button class="btn btn-sm btn-outline-info update-call-status-btn"
                                    data-call-id="${call.id}"
                                    data-current-status="${call.status}">
                                    Actualizar Estado
                                </button>
                                <button class="btn btn-sm btn-outline-secondary view-call-logs-btn"
                                    data-call-id="${call.id}">
                                    Ver Historial
                                </button>
                            </div>
                            ${call.status === 'pendiente' || call.status === 'no_realizada' ?
                                `<button class="btn btn-sm btn-outline-warning reschedule-call-btn mt-2"
                                    data-call-id="${call.id}"
                                    data-lead-id="${leadId}">
                                    <i class="bi bi-arrow-clockwise"></i> Reprogramar
                                </button>` : ''}
                        </td>
                    </tr>`;
                });

                content += '</tbody></table></div>';
                $('#callsTraceContent').html(content);
            }).fail(() => {
                $('#callsTraceContent').html('<div class="text-danger text-center">Error al cargar las llamadas.</div>');
            });
        });

        // ==================== ACTUALIZAR ESTADO DE LLAMADA ====================
        $(document).on('click', '.update-call-status-btn', function() {
            const callId = $(this).data('call-id');

            $('#callId').val(callId);
            $('#updateCallStatusForm')[0].reset();
            $('#callId').val(callId);
            $('#updateCallStatusModal').modal('show');
        });

        $('#saveCallStatus').on('click', function() {
            const callId = $('#callId').val();
            const status = $('#callStatus').val();
            const comments = $('#callComments').val();

            $.ajax({
                url: `/traige/calls/${callId}/status`,
                method: 'PUT',
                data: {
                    _token: '{{ csrf_token() }}',
                    status: status,
                    comments: comments
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: 'Estado de la llamada actualizado correctamente.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        $('#updateCallStatusModal').modal('hide');
                        // Recargar trazabilidad si está abierta
                        if ($('#callsTraceModal').hasClass('show')) {
                            $('.view-traige-calls-btn.last-clicked').click();
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al actualizar el estado de la llamada.'
                    });
                }
            });
        });

        // ==================== VER HISTORIAL DE UNA LLAMADA ====================
        $(document).on('click', '.view-call-logs-btn', function() {
            const callId = $(this).data('call-id');
            $('#callLogsTableBody').html('<tr><td colspan="5" class="text-center">Cargando...</td></tr>');
            $('#callLogsModal').modal('show');

            $.get(`/traige/calls/${callId}/logs`, function(data) {
                if (data.length === 0) {
                    $('#callLogsTableBody').html('<tr><td colspan="5" class="text-center">Sin registros de cambios</td></tr>');
                    return;
                }

                let rows = '';
                data.forEach(log => {
                    rows += `
                        <tr>
                            <td>${log.valor_viejo || 'N/A'}</td>
                            <td>${log.valor_nuevo}</td>
                            <td>${log.detalle}</td>
                            <td>${log.usuario}</td>
                            <td>${log.fecha}</td>
                        </tr>
                    `;
                });

                $('#callLogsTableBody').html(rows);
            }).fail(() => {
                $('#callLogsTableBody').html('<tr><td colspan="5" class="text-danger text-center">Error al cargar los logs.</td></tr>');
            });
        });

        // ==================== REPROGRAMAR LLAMADA ====================
        $(document).on('click', '.reschedule-call-btn', function() {
            const oldCallId = $(this).data('call-id');
            const leadId = $(this).data('lead-id');

            // Marcar la llamada anterior como reprogramada
            $.ajax({
                url: `/traige/calls/${oldCallId}/reschedule`,
                method: 'PUT',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        // Ahora abrir el modal para crear la nueva llamada
                        $('#parentCallId').val(oldCallId);
                        $(`.schedule-call-btn[data-lead-id="${leadId}"]`).click();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error al marcar la llamada como reprogramada.'
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al reprogramar la llamada.'
                    });
                }
            });
        });

        // ==================== PASAR A CLOSER ====================
        $(document).on('click', '.pass-to-closer-btn', function() {
            const leadId = $(this).data('lead-id');

            Swal.fire({
                title: '¿Está seguro?',
                text: '¿Desea pasar este lead al módulo de closers? Esta acción no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, pasar a closer',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/traige/${leadId}/pass-to-closer`,
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Éxito!',
                                    text: 'Lead pasado exitosamente al módulo de closers.',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                                $('#traige-table').DataTable().ajax.reload(null, false);
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message
                                });
                            }
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Error al pasar el lead a closer.'
                            });
                        }
                    });
                }
            });
        });

        // ==================== VER HISTORIAL DE CAMBIOS ====================
        $(document).on('click', '.view-logs-btn', function () {
            const leadId = $(this).data('lead-id');
            $('#logsModal').modal('show');
            $('#logsTableBody').html('<tr><td colspan="6" class="text-center">Cargando...</td></tr>');

            $.get(`/traige/${leadId}/logs`, function (data) {
                if (data.length === 0) {
                    $('#logsTableBody').html('<tr><td colspan="6" class="text-center">Sin registros</td></tr>');
                    return;
                }

                let rows = '';
                data.forEach(log => {
                    const tipoBadge = getTipoBadge(log.tipo);
                    rows += `
                        <tr>
                            <td>${tipoBadge}</td>
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

        // ==================== FUNCIONES AUXILIARES ====================
        function getTipoBadge(tipo) {
            switch(tipo) {
                case 'Traige':
                    return '<span class="badge bg-primary">Triage</span>';
                case 'Pipeline':
                    return '<span class="badge bg-info">Pipeline</span>';
                case 'Llamadas Traige':
                    return '<span class="badge bg-success">Llamadas</span>';
                case 'Onboarding':
                    return '<span class="badge bg-warning">Onboarding</span>';
                case 'Upsell':
                    return '<span class="badge bg-danger">Upsell</span>';
                case 'Venta':
                    return '<span class="badge bg-dark">Venta</span>';
                case 'Contrato':
                    return '<span class="badge bg-secondary">Contrato</span>';
                default:
                    return '<span class="badge bg-secondary">General</span>';
            }
        }

        function getStatusBadge(status) {
            switch(status) {
                case 'pendiente':
                    return '<span class="badge bg-warning">Pendiente</span>';
                case 'realizada':
                    return '<span class="badge bg-success">Realizada</span>';
                case 'no_realizada':
                    return '<span class="badge bg-danger">No Realizada</span>';
                case 'reprogramada':
                    return '<span class="badge bg-secondary">Reprogramada</span>';
                default:
                    return '<span class="badge bg-secondary">' + status + '</span>';
            }
        }

        // Guardar referencia al último botón de llamadas clickeado
        $(document).on('click', '.view-traige-calls-btn', function() {
            $('.view-traige-calls-btn').removeClass('last-clicked');
            $(this).addClass('last-clicked');
        });

        // ==================== GUARDAR NUEVO LEAD ====================
        $('#saveNewLead').on('click', function() {
            const form = $('#newLeadForm')[0];

            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            const formData = {
                _token: '{{ csrf_token() }}',
                nombre: $('#newLeadNombre').val(),
                email: $('#newLeadEmail').val(),
                telefono: $('#newLeadTelefono').val(),
                instagram_user: $('#newLeadInstagram').val()
            };

            $.ajax({
                url: '{{ route("traige.store") }}',
                method: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: 'Lead registrado exitosamente.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        $('#newLeadModal').modal('hide');
                        $('#newLeadForm')[0].reset();
                        $('#traige-table').DataTable().ajax.reload(null, false);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message
                        });
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Error al registrar el lead.';
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        errorMessage = Object.values(xhr.responseJSON.errors).flat().join('\n');
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMessage
                    });
                    console.error(xhr.responseText);
                }
            });
        });
    });
</script>
@endpush

</x-app-layout>
