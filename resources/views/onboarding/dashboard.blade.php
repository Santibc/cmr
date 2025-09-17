<x-app-layout>
    <x-slot name="header">
        Dashboard Onboarding
    </x-slot>
<div class="container-fluid">
    <!-- Header con filtros de fecha -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="bi bi-graph-up-arrow"></i> Dashboard Onboarding
                    </h1>
                    <p class="text-muted">Métricas y estadísticas del proceso de onboarding</p>
                </div>
                <div class="d-flex gap-2">
                    <input type="date" id="dateFrom" class="form-control" value="{{ $dateFrom }}">
                    <input type="date" id="dateTo" class="form-control" value="{{ $dateTo }}">
                    <button type="button" id="updateDashboard" class="btn btn-primary">
                        <i class="bi bi-arrow-clockwise"></i> Actualizar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjetas de estadísticas principales -->
    <div class="row mb-4" id="statsCards">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Leads con Ventas
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalLeadsWithSales">
                                {{ $stats['totalLeadsWithSales'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-people-fill fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Llamadas Realizadas
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="callsCompleted">
                                {{ $stats['callsCompleted'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-telephone-check-fill fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Tasa de Conversión
                            </div>
                            <div class="row no-gutters align-items-center">
                                <div class="col-auto">
                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800" id="conversionRate">
                                        {{ $stats['conversionRate'] }}%
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="progress progress-sm mr-2">
                                        <div class="progress-bar bg-info" role="progressbar"
                                             style="width: {{ $stats['conversionRate'] }}%"
                                             aria-valuenow="{{ $stats['conversionRate'] }}" aria-valuemin="0"
                                             aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-graph-up fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Total Llamadas
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalCalls">
                                {{ $stats['totalCalls'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-telephone-fill fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjetas secundarias -->
    <div class="row mb-4">
        <div class="col-md-3 mb-4">
            <div class="card border-left-secondary shadow h-100 py-2">
                <div class="card-body text-center">
                    <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                        Pendientes
                    </div>
                    <div class="h6 mb-0 font-weight-bold text-gray-800" id="callsPending">
                        {{ $stats['callsPending'] }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body text-center">
                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                        No Realizadas
                    </div>
                    <div class="h6 mb-0 font-weight-bold text-gray-800" id="callsNotCompleted">
                        {{ $stats['callsNotCompleted'] }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body text-center">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                        Reprogramadas
                    </div>
                    <div class="h6 mb-0 font-weight-bold text-gray-800" id="callsRescheduled">
                        {{ $stats['callsRescheduled'] }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card border-left-dark shadow h-100 py-2">
                <div class="card-body text-center">
                    <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">
                        Notas Totales
                    </div>
                    <div class="h6 mb-0 font-weight-bold text-gray-800" id="totalNotes">
                        {{ $stats['totalNotes'] }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="row mb-4">
        <!-- Gráfico de llamadas diarias -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-bar-chart-line"></i> Llamadas por Día
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="dailyCallsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfico de distribución de estados -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-pie-chart"></i> Estado de Llamadas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="statusDistributionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <!-- Rendimiento por usuario -->
        <div class="col-xl-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-person-badge"></i> Rendimiento por Usuario
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="userPerformanceChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Respuesta por franja horaria -->
        <div class="col-xl-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-clock"></i> Tasa de Respuesta por Hora
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="hourlyResponseChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Tablas de datos -->
    <div class="row">
        <!-- Leads recientes -->
        <div class="col-xl-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-table"></i> Leads Recientes con Actividad
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="recentLeadsTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Lead</th>
                                    <th>Email</th>
                                    <th>Fecha Venta</th>
                                    <th>Última Llamada</th>
                                    <th>Estado</th>
                                    <th>Llamadas</th>
                                    <th>Notas</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentLeads as $lead)
                                <tr>
                                    <td>{{ $lead['nombre'] }}</td>
                                    <td>{{ $lead['email'] }}</td>
                                    <td>{{ $lead['sale_date'] }}</td>
                                    <td>{{ $lead['last_call_date'] }}</td>
                                    <td>
                                        @if($lead['last_call_status'] === 'realizada')
                                            <span class="badge bg-success">Realizada</span>
                                        @elseif($lead['last_call_status'] === 'pendiente')
                                            <span class="badge bg-warning">Pendiente</span>
                                        @elseif($lead['last_call_status'] === 'no_realizada')
                                            <span class="badge bg-danger">No Realizada</span>
                                        @elseif($lead['last_call_status'] === 'reprogramada')
                                            <span class="badge bg-info">Reprogramada</span>
                                        @else
                                            <span class="badge bg-secondary">Sin llamadas</span>
                                        @endif
                                    </td>
                                    <td><span class="badge bg-primary">{{ $lead['calls_count'] }}</span></td>
                                    <td><span class="badge bg-info">{{ $lead['notes_count'] }}</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Leads más activos -->
        <div class="col-xl-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-trophy"></i> Leads Más Activos
                    </h6>
                </div>
                <div class="card-body">
                    @foreach($performanceMetrics['mostActiveLeads'] as $index => $lead)
                    <div class="d-flex align-items-center mb-3">
                        <div class="mr-3">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                 style="width: 30px; height: 30px;">
                                {{ $index + 1 }}
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="font-weight-bold">{{ $lead->nombre }}</div>
                            <small class="text-muted">{{ $lead->email }}</small>
                        </div>
                        <div class="text-right">
                            <div class="font-weight-bold text-primary">{{ $lead->activity_score }}</div>
                            <small class="text-muted">{{ $lead->calls_count }}C / {{ $lead->notes_count }}N</small>
                        </div>
                    </div>
                    @if(!$loop->last)
                    <hr class="my-2">
                    @endif
                    @endforeach
                </div>
            </div>

            <!-- Métrica adicional -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-speedometer2"></i> Tiempo Promedio
                    </h6>
                </div>
                <div class="card-body text-center">
                    <div class="text-muted mb-1">Venta a Primera Llamada</div>
                    <div class="h4 font-weight-bold text-primary">
                        {{ $performanceMetrics['avgTimeToFirstCall'] }} horas
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts para gráficos -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Datos iniciales
    let chartData = @json($chartData);
    let performanceMetrics = @json($performanceMetrics);
    
    // Configuración de colores
    const colors = {
        primary: '#4e73df',
        success: '#1cc88a',
        info: '#36b9cc',
        warning: '#f6c23e',
        danger: '#e74a3b',
        secondary: '#858796'
    };

    // Gráfico de llamadas diarias
    initDailyCallsChart();
    
    // Gráfico de distribución de estados
    initStatusDistributionChart();
    
    // Gráfico de rendimiento por usuario
    initUserPerformanceChart();
    
    // Gráfico de respuesta por hora
    initHourlyResponseChart();

    // Actualizar dashboard
    document.getElementById('updateDashboard').addEventListener('click', updateDashboard);

    function initDailyCallsChart() {
        const ctx = document.getElementById('dailyCallsChart').getContext('2d');
        
        const dates = chartData.dailyCalls.map(item => {
            const date = new Date(item.date);
            return date.toLocaleDateString('es-ES', { month: 'short', day: 'numeric' });
        });
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: dates,
                datasets: [{
                    label: 'Total Llamadas',
                    data: chartData.dailyCalls.map(item => item.total),
                    borderColor: colors.primary,
                    backgroundColor: colors.primary + '20',
                    tension: 0.3,
                    fill: true
                }, {
                    label: 'Completadas',
                    data: chartData.dailyCalls.map(item => item.completed),
                    borderColor: colors.success,
                    backgroundColor: colors.success + '20',
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    function initStatusDistributionChart() {
        const ctx = document.getElementById('statusDistributionChart').getContext('2d');
        
        const statusLabels = {
            'pendiente': 'Pendientes',
            'realizada': 'Realizadas',
            'no_realizada': 'No Realizadas',
            'reprogramada': 'Reprogramadas'
        };
        
        const statusColors = {
            'pendiente': colors.warning,
            'realizada': colors.success,
            'no_realizada': colors.danger,
            'reprogramada': colors.info
        };
        
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: chartData.statusDistribution.map(item => statusLabels[item.status] || item.status),
                datasets: [{
                    data: chartData.statusDistribution.map(item => item.count),
                    backgroundColor: chartData.statusDistribution.map(item => statusColors[item.status] || colors.secondary)
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    function initUserPerformanceChart() {
        const ctx = document.getElementById('userPerformanceChart').getContext('2d');
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartData.callsByUser.map(item => item.name),
                datasets: [{
                    label: 'Total Llamadas',
                    data: chartData.callsByUser.map(item => item.total_calls),
                    backgroundColor: colors.primary + '80',
                    borderColor: colors.primary,
                    borderWidth: 1
                }, {
                    label: 'Completadas',
                    data: chartData.callsByUser.map(item => item.completed_calls),
                    backgroundColor: colors.success + '80',
                    borderColor: colors.success,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    function initHourlyResponseChart() {
        const ctx = document.getElementById('hourlyResponseChart').getContext('2d');
        
        const hourlyData = performanceMetrics.hourlyResponse.map(item => ({
            hour: item.hour,
            rate: item.total > 0 ? (item.completed / item.total * 100) : 0
        }));
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: hourlyData.map(item => item.hour + ':00'),
                datasets: [{
                    label: 'Tasa de Respuesta (%)',
                    data: hourlyData.map(item => item.rate.toFixed(1)),
                    backgroundColor: colors.info + '80',
                    borderColor: colors.info,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                }
            }
        });
    }

    function updateDashboard() {
        const dateFrom = document.getElementById('dateFrom').value;
        const dateTo = document.getElementById('dateTo').value;
        
        if (!dateFrom || !dateTo) {
            Swal.fire({
                icon: 'warning',
                title: 'Fechas requeridas',
                text: 'Por favor selecciona ambas fechas.'
            });
            return;
        }

        // Mostrar loading
        Swal.fire({
            title: 'Actualizando dashboard...',
            allowEscapeKey: false,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Hacer petición AJAX
        $.get('{{ route("onboarding.dashboard") }}', {
            date_from: dateFrom,
            date_to: dateTo
        })
        .done(function(data) {
            // Actualizar estadísticas
            updateStats(data.stats);
            
            // Recrear gráficos
            chartData = data.chartData;
            performanceMetrics = data.performanceMetrics;
            
            // Destruir gráficos existentes y recrear
            Chart.getChart('dailyCallsChart')?.destroy();
            Chart.getChart('statusDistributionChart')?.destroy();
            Chart.getChart('userPerformanceChart')?.destroy();
            Chart.getChart('hourlyResponseChart')?.destroy();
            
            initDailyCallsChart();
            initStatusDistributionChart();
            initUserPerformanceChart();
            initHourlyResponseChart();
            
            Swal.close();
        })
        .fail(function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo actualizar el dashboard.'
            });
        });
    }

    function updateStats(stats) {
        document.getElementById('totalLeadsWithSales').textContent = stats.totalLeadsWithSales;
        document.getElementById('callsCompleted').textContent = stats.callsCompleted;
        document.getElementById('conversionRate').textContent = stats.conversionRate + '%';
        document.getElementById('totalCalls').textContent = stats.totalCalls;
        document.getElementById('callsPending').textContent = stats.callsPending;
        document.getElementById('callsNotCompleted').textContent = stats.callsNotCompleted;
        document.getElementById('callsRescheduled').textContent = stats.callsRescheduled;
        document.getElementById('totalNotes').textContent = stats.totalNotes;
        
        // Actualizar barra de progreso
        const progressBar = document.querySelector('#statsCards .progress-bar');
        if (progressBar) {
            progressBar.style.width = stats.conversionRate + '%';
            progressBar.setAttribute('aria-valuenow', stats.conversionRate);
        }
    }
});
</script>

<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}
.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}
.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}
.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}
.border-left-danger {
    border-left: 0.25rem solid #e74a3b !important;
}
.border-left-secondary {
    border-left: 0.25rem solid #858796 !important;
}
.border-left-dark {
    border-left: 0.25rem solid #5a5c69 !important;
}

.chart-area {
    position: relative;
    height: 300px;
}

.chart-pie {
    position: relative;
    height: 300px;
}

.progress-sm {
    height: 0.5rem;
}
</style>

</x-app-layout>