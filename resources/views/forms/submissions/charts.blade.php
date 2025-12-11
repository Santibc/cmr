<x-app-layout>
    <x-slot name="header">
        {{ __('Graficas de Respuestas') }}
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
                            <a href="{{ route('forms.submissions.index', $form->id) }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Volver a Respuestas
                            </a>
                        </div>
                    </div>

                    <!-- FILTROS -->
                    <div class="card mb-4">
                        <div class="card-header bg-light py-2">
                            <h6 class="mb-0"><i class="bi bi-funnel"></i> Filtros</h6>
                        </div>
                        <div class="card-body py-3">
                            <div class="row g-2">
                                <div class="col-md-3">
                                    <label for="filterDateFrom" class="form-label small mb-1">Fecha desde</label>
                                    <input type="date" class="form-control form-control-sm" id="filterDateFrom" value="{{ $filters['date_from'] ?? '' }}">
                                </div>
                                <div class="col-md-3">
                                    <label for="filterDateTo" class="form-label small mb-1">Fecha hasta</label>
                                    <input type="date" class="form-control form-control-sm" id="filterDateTo" value="{{ $filters['date_to'] ?? '' }}">
                                </div>
                                <div class="col-md-2">
                                    <label for="filterStatus" class="form-label small mb-1">Estado</label>
                                    <select class="form-select form-select-sm" id="filterStatus">
                                        <option value="">Todos</option>
                                        @foreach(App\Models\FormSubmission::getStatuses() as $key => $label)
                                            <option value="{{ $key }}" {{ ($filters['status'] ?? '') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="filterUser" class="form-label small mb-1">Enviado por</label>
                                    <select class="form-select form-select-sm" id="filterUser">
                                        <option value="">Todos</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ ($filters['user_id'] ?? '') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 d-flex align-items-end gap-1">
                                    <button type="button" class="btn btn-primary btn-sm" id="applyFilters">
                                        <i class="bi bi-search"></i> Filtrar
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="clearFilters">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Resumen -->
                    <div class="card mb-4 bg-primary text-white">
                        <div class="card-body py-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0"><i class="bi bi-bar-chart-fill me-2"></i>Total de Respuestas</h6>
                                </div>
                                <div class="h4 mb-0" id="totalResponses">{{ $totalSubmissions }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Contenedor de Graficas -->
                    <div id="chartsContainer">
                        @if(count($chartsData) > 0)
                            <div class="row" id="chartsGrid">
                                @foreach($chartsData as $index => $chart)
                                    <div class="col-xl-3 col-lg-4 col-md-6 mb-3">
                                        <div class="card h-100">
                                            <div class="card-header bg-light py-2">
                                                <h6 class="mb-0 small text-truncate" title="{{ $chart['label'] }}">
                                                    {{ $chart['label'] }}
                                                </h6>
                                                <small class="text-muted" style="font-size: 0.7rem;">
                                                    {{ $chart['responded_count'] }}/{{ $chart['total_responses'] }} resp.
                                                </small>
                                            </div>
                                            <div class="card-body p-2">
                                                <canvas id="chart-{{ $index }}" style="max-height: 180px;"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-info text-center">
                                <i class="bi bi-info-circle me-2"></i>
                                No hay datos suficientes para generar graficas con los filtros actuales.
                            </div>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Paleta de colores consistente
        const chartColors = [
            '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b',
            '#858796', '#5a5c69', '#6f42c1', '#fd7e14', '#20c997',
            '#6610f2', '#e83e8c', '#17a2b8', '#28a745', '#dc3545'
        ];

        // Almacenar instancias de graficas para destruirlas al actualizar
        let chartInstances = [];

        // Inicializar graficas con datos del servidor
        let chartsData = @json($chartsData);

        function initializeCharts() {
            // Destruir graficas existentes
            chartInstances.forEach(chart => chart.destroy());
            chartInstances = [];

            chartsData.forEach((chartData, index) => {
                const canvas = document.getElementById(`chart-${index}`);
                if (!canvas) return;

                const ctx = canvas.getContext('2d');
                let config;

                // Configurar segun tipo de grafica
                if (chartData.chart_type === 'doughnut') {
                    config = {
                        type: 'doughnut',
                        data: {
                            labels: chartData.labels,
                            datasets: [{
                                data: chartData.data,
                                backgroundColor: chartColors.slice(0, chartData.labels.length),
                                borderWidth: 1,
                                borderColor: '#ffffff'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        padding: 8,
                                        usePointStyle: true,
                                        font: { size: 9 },
                                        boxWidth: 8
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                            const percentage = ((context.raw / total) * 100).toFixed(1);
                                            return `${context.label}: ${context.raw} (${percentage}%)`;
                                        }
                                    }
                                }
                            }
                        }
                    };
                } else if (chartData.chart_type === 'bar_horizontal') {
                    config = {
                        type: 'bar',
                        data: {
                            labels: chartData.labels.map(l => l.length > 20 ? l.substring(0, 20) + '...' : l),
                            datasets: [{
                                label: 'Respuestas',
                                data: chartData.data,
                                backgroundColor: chartColors[0],
                                borderColor: chartColors[0],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: { display: false }
                            },
                            scales: {
                                x: {
                                    beginAtZero: true,
                                    ticks: { stepSize: 1, font: { size: 9 } }
                                },
                                y: {
                                    ticks: { font: { size: 8 } }
                                }
                            }
                        }
                    };
                } else {
                    // bar (vertical)
                    config = {
                        type: 'bar',
                        data: {
                            labels: chartData.labels.map(l => String(l).length > 15 ? String(l).substring(0, 15) + '...' : l),
                            datasets: [{
                                label: 'Respuestas',
                                data: chartData.data,
                                backgroundColor: chartColors.slice(0, chartData.labels.length),
                                borderColor: chartColors.slice(0, chartData.labels.length),
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: { display: false }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: { stepSize: 1, font: { size: 9 } }
                                },
                                x: {
                                    ticks: { font: { size: 8 } }
                                }
                            }
                        }
                    };
                }

                chartInstances.push(new Chart(ctx, config));
            });
        }

        // Build query string from current filters
        function getFilterQueryString() {
            const params = new URLSearchParams();
            const dateFrom = document.getElementById('filterDateFrom').value;
            const dateTo = document.getElementById('filterDateTo').value;
            const status = document.getElementById('filterStatus').value;
            const userId = document.getElementById('filterUser').value;

            if (dateFrom) params.append('date_from', dateFrom);
            if (dateTo) params.append('date_to', dateTo);
            if (status) params.append('status', status);
            if (userId) params.append('user_id', userId);

            return params.toString();
        }

        // Cargar graficas via AJAX
        function loadCharts() {
            const queryString = getFilterQueryString();
            const url = "{{ route('forms.submissions.charts', $form->id) }}" + (queryString ? '?' + queryString : '');

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    chartsData = data.chartsData;
                    document.getElementById('totalResponses').textContent = data.totalSubmissions;

                    // Regenerar HTML de graficas
                    if (data.chartsData.length > 0) {
                        let html = '';
                        data.chartsData.forEach((chart, index) => {
                            html += `
                                <div class="col-xl-3 col-lg-4 col-md-6 mb-3">
                                    <div class="card h-100">
                                        <div class="card-header bg-light py-2">
                                            <h6 class="mb-0 small text-truncate" title="${chart.label}">
                                                ${chart.label}
                                            </h6>
                                            <small class="text-muted" style="font-size: 0.7rem;">
                                                ${chart.responded_count}/${chart.total_responses} resp.
                                            </small>
                                        </div>
                                        <div class="card-body p-2">
                                            <canvas id="chart-${index}" style="max-height: 180px;"></canvas>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                        document.getElementById('chartsContainer').innerHTML = `<div class="row" id="chartsGrid">${html}</div>`;
                    } else {
                        document.getElementById('chartsContainer').innerHTML = `
                            <div class="alert alert-info text-center">
                                <i class="bi bi-info-circle me-2"></i>
                                No hay datos suficientes para generar graficas con los filtros actuales.
                            </div>
                        `;
                    }

                    // Reinicializar graficas
                    setTimeout(() => initializeCharts(), 100);
                }
            })
            .catch(error => {
                console.error('Error cargando graficas:', error);
            });
        }

        // Event listeners
        document.getElementById('applyFilters').addEventListener('click', loadCharts);

        document.getElementById('clearFilters').addEventListener('click', function() {
            document.getElementById('filterDateFrom').value = '';
            document.getElementById('filterDateTo').value = '';
            document.getElementById('filterStatus').value = '';
            document.getElementById('filterUser').value = '';
            loadCharts();
        });

        // Inicializar graficas al cargar la pagina
        document.addEventListener('DOMContentLoaded', function() {
            initializeCharts();
        });
    </script>
    @endpush
</x-app-layout>
