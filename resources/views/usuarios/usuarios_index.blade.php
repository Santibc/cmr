<x-app-layout>
    <x-slot name="header">
        {{ __('Usuarios') }}
    </x-slot>

    <div class="py-12" style="padding-top: 0;">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h4 class="text-2xl font-semibold mb-4">Usuarios</h4>

                    <div class="border border-gray-300 rounded-lg">
                        <div class="overflow-x-auto">

                        <table id="users-table" class="table-responsive w-full text-sm text-left text-gray-700">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                                <tr class="border-b border-gray-300">
                                    <th class="px-6 py-3" data-priority="1">Acciones</th>
                                    <th class="px-6 py-3">Nombre</th>
                                    <th class="px-6 py-3">Email</th>
                                    <th class="px-6 py-3">Avatar</th>
                                    <th class="px-6 py-3">UUID</th>
                                    <th class="px-6 py-3">Slug</th>
                                    <th class="px-6 py-3">Rol</th>
                                    <th class="px-6 py-3">Calendly URL</th>
                                    <th class="px-6 py-3">Calendly URI</th>
                                    <th class="px-6 py-3">Zona Horaria</th>
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
document.addEventListener('DOMContentLoaded', function () {
    const table = $('#users-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        scrollX: true,
        autoWidth: false,
        ajax: "{{ route('usuarios') }}",
        columns: [
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false,
                className: 'noVis'
            },
            { data: 'name', name: 'name' },
            { data: 'email', name: 'email' },
            {
                data: 'avatar_url',
                name: 'avatar_url',
                render: function (data) {
                    return data ? `<img src="${data}" class="w-8 h-8 rounded-full" alt="avatar">` : '';
                },
                orderable: false,
                searchable: false
            },
            { data: 'uuid', name: 'uuid' },
            { data: 'slug', name: 'slug' },
            { 
                data: 'roles', 
                name: 'roles',
                render: function (data) {
                    return data && data.length > 0 ? data[0].name : '';
                },
                orderable: false,
                searchable: false
            },
            { data: 'scheduling_url', name: 'scheduling_url' },
            { data: 'calendly_uri', name: 'calendly_uri' },
            { data: 'timezone', name: 'timezone' }
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
                text: 'Nuevo',
                className: 'btn btn-outline-primary',
                action: function () {
                    window.location.href = "{{ route('usuarios.form') }}";
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
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Todos"]]
    });

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
});
</script>
@endpush

</x-app-layout>
