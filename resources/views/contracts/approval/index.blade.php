<x-app-layout>
    <x-slot name="header">
        {{ __('Aprobación de Contratos') }}
    </x-slot>

    <div class="py-12" style="padding-top: 0;">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h4 class="text-2xl font-semibold mb-4">Contratos Pendientes de Aprobación</h4>

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="border border-gray-300 rounded-lg">
                        <div class="overflow-x-auto">
                            <table id="contracts-table" class="table-responsive w-full text-sm text-left text-gray-700">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                                    <tr class="border-b border-gray-300">
                                        <th class="px-6 py-3" data-priority="1">Acciones</th>
                                        <th class="px-6 py-3">Lead</th>
                                        <th class="px-6 py-3">Cliente</th>
                                        <th class="px-6 py-3">Contrato</th>
                                        <th class="px-6 py-3">Closer</th>
                                        <th class="px-6 py-3">Fecha</th>
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


@push('scripts')

<script>
    $(document).ready(function () {
        const table = $('#contracts-table').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            scrollX: true,
            autoWidth: false,
            ajax: "{{ route('contracts.approval.index') }}",
            columns: [
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    className: 'noVis'
                },
                { data: 'lead_name', name: 'lead_name' },
                { data: 'client_name', name: 'client_name' },
                { data: 'contract_name', name: 'contract_name' },
                { data: 'closer_name', name: 'closer_name' },
                { data: 'created_at_formatted', name: 'created_at' }
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
                    extend: 'excelHtml5',
                    text: 'Excel',
                    className: 'btn btn-outline-success'
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
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Todos"]]
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

        // Manejar click en botón de editar contrato
        $(document).on('click', '.edit-contract-btn', function() {
            const saleId = $(this).data('sale-id');
            window.location.href = '{{ route("contracts.approval.edit", ":id") }}'.replace(':id', saleId);
        });
    });
</script>
@endpush
</x-app-layout>