<x-app-layout>
    <x-slot name="header">
        {{ __('Dashboard Administrativo') }}
    </x-slot>

    @if (!Auth::user()->hasRole('admin'))
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <p>Bienvenido, Closer.</p>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="container-fluid py-4">
            <!-- Métricas Principales -->
            <div class="row mb-4">
                <div class="col-12">
                    <h2 class="h4 text-primary mb-3">📊 Métricas del Mes - {{ now()->format('F Y') }}</h2>
                </div>

                <!-- Card Leads -->
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="card-subtitle text-muted mb-1">Leads Nuevos</h6>
                                    <h3 class="card-title mb-1">{{ number_format($metrics['leads']['total_this_month']) }}</h3>
                                    <small class="text-muted">vs {{ number_format($metrics['leads']['total_previous_month']) }} mes anterior</small>
                                </div>
                                <div class="text-end">
                                    @if($metrics['leads']['growth_percentage'] !== null)
                                        <span class="badge {{ $metrics['leads']['growth_percentage'] >= 0 ? 'bg-success' : 'bg-danger' }}">
                                            {{ $metrics['leads']['growth_percentage'] >= 0 ? '+' : '' }}{{ number_format($metrics['leads']['growth_percentage'], 1) }}%
                                        </span>
                                    @else
                                        <span class="badge bg-info">Nuevo</span>
                                    @endif
                                    <br><small class="text-primary">{{ number_format($metrics['leads']['conversion_rate'], 1) }}% conversión</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card Ventas -->
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="card-subtitle text-muted mb-1">Ventas Cerradas</h6>
                                    <h3 class="card-title mb-1">{{ number_format($metrics['general']['total_sales_this_month']) }}</h3>
                                    <small class="text-muted">vs {{ number_format($metrics['general']['total_sales_previous_month']) }} mes anterior</small>
                                </div>
                                <div class="text-end">
                                    @if($metrics['general']['sales_growth_percentage'] !== null)
                                        <span class="badge {{ $metrics['general']['sales_growth_percentage'] >= 0 ? 'bg-success' : 'bg-danger' }}">
                                            {{ $metrics['general']['sales_growth_percentage'] >= 0 ? '+' : '' }}{{ number_format($metrics['general']['sales_growth_percentage'], 1) }}%
                                        </span>
                                    @else
                                        <span class="badge bg-info">Nuevo</span>
                                    @endif
                                    <br><small class="text-warning">{{ $metrics['general']['pending_contracts'] }} contratos pendientes</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card Onboarding -->
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="card-subtitle text-muted mb-1">Llamadas Onboarding</h6>
                                    <h3 class="card-title mb-1">{{ number_format($metrics['onboarding']['total_calls_this_month']) }}</h3>
                                    <small class="text-muted">{{ number_format($metrics['onboarding']['avg_time_to_first_call'], 1) }} días promedio</small>
                                </div>
                                <div class="text-end">
                                    @if($metrics['onboarding']['growth_percentage'] !== null)
                                        <span class="badge {{ $metrics['onboarding']['growth_percentage'] >= 0 ? 'bg-success' : 'bg-danger' }}">
                                            {{ $metrics['onboarding']['growth_percentage'] >= 0 ? '+' : '' }}{{ number_format($metrics['onboarding']['growth_percentage'], 1) }}%
                                        </span>
                                    @else
                                        <span class="badge bg-info">Nuevo</span>
                                    @endif
                                    @if($metrics['onboarding']['overdue_calls'] > 0)
                                        <br><small class="text-danger">{{ $metrics['onboarding']['overdue_calls'] }} vencidas</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card Upsells -->
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="card-subtitle text-muted mb-1">Upsells Aprobados</h6>
                                    <h3 class="card-title mb-1">{{ number_format($metrics['upsell']['total_this_month']) }}</h3>
                                    <small class="text-muted">{{ number_format($metrics['upsell']['conversion_rate'], 1) }}% conversión LT→HT</small>
                                </div>
                                <div class="text-end">
                                    @if($metrics['upsell']['growth_percentage'] !== null)
                                        <span class="badge {{ $metrics['upsell']['growth_percentage'] >= 0 ? 'bg-success' : 'bg-danger' }}">
                                            {{ $metrics['upsell']['growth_percentage'] >= 0 ? '+' : '' }}{{ number_format($metrics['upsell']['growth_percentage'], 1) }}%
                                        </span>
                                    @else
                                        <span class="badge bg-info">Nuevo</span>
                                    @endif
                                    @if($metrics['upsell']['pending_approvals'] > 0)
                                        <br><small class="text-warning">{{ $metrics['upsell']['pending_approvals'] }} pendientes</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alertas y Acciones Requeridas -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light border-0">
                            <h5 class="card-title mb-0">🚨 Acciones Requeridas</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @if($metrics['onboarding']['overdue_calls'] > 0)
                                <div class="col-md-3 mb-2">
                                    <div class="alert alert-danger mb-0 py-2">
                                        <strong>{{ $metrics['onboarding']['overdue_calls'] }}</strong> llamadas vencidas
                                        <br><a href="/onboarding/leads" class="small">Ver en Onboarding →</a>
                                    </div>
                                </div>
                                @endif

                                @if($metrics['upsell']['pending_approvals'] > 0)
                                <div class="col-md-3 mb-2">
                                    <div class="alert alert-warning mb-0 py-2">
                                        <strong>{{ $metrics['upsell']['pending_approvals'] }}</strong> upsells pendientes
                                        <br><a href="/upsell" class="small">Aprobar Upsells →</a>
                                    </div>
                                </div>
                                @endif

                                @if($metrics['general']['pending_contracts'] > 0)
                                <div class="col-md-3 mb-2">
                                    <div class="alert alert-info mb-0 py-2">
                                        <strong>{{ $metrics['general']['pending_contracts'] }}</strong> contratos pendientes
                                        <br><a href="/contracts/approval" class="small">Aprobar Contratos →</a>
                                    </div>
                                </div>
                                @endif

                                <div class="col-md-3 mb-2">
                                    <div class="alert alert-success mb-0 py-2">
                                        <strong>{{ $metrics['general']['recent_activity']['new_leads'] }}</strong> leads últimos 7 días
                                        <br><small>{{ $metrics['general']['recent_activity']['new_sales'] }} ventas, {{ $metrics['general']['recent_activity']['completed_calls'] }} llamadas</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Distribuciones y Análisis en Gráficos -->
            <div class="row mb-4">
                <!-- Pipeline Distribution Chart -->
                <div class="col-lg-4 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-light border-0 py-2">
                            <h6 class="card-title mb-0 small">📈 Distribución Pipeline</h6>
                        </div>
                        <div class="card-body py-2">
                            <div class="chart-small">
                                <canvas id="pipelineStatusChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contract Types Chart -->
                <div class="col-lg-4 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-light border-0 py-2">
                            <h6 class="card-title mb-0 small">📋 Tipos de Contrato</h6>
                        </div>
                        <div class="card-body py-2">
                            <div class="chart-small">
                                <canvas id="contractTypesChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Onboarding Status Chart -->
                <div class="col-lg-4 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-light border-0 py-2">
                            <h6 class="card-title mb-0 small">📞 Estado Llamadas</h6>
                        </div>
                        <div class="card-body py-2">
                            <div class="chart-small">
                                <canvas id="onboardingStatusChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Segunda fila de gráficos pequeños -->
            <div class="row mb-4">
                <!-- Top Performers Chart -->
                <div class="col-lg-6 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-light border-0 py-2">
                            <h6 class="card-title mb-0 small">🏆 Top Closers del Mes</h6>
                        </div>
                        <div class="card-body py-2">
                            <div class="chart-small">
                                <canvas id="topPerformersChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upsell Status Chart -->
                <div class="col-lg-6 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-light border-0 py-2">
                            <h6 class="card-title mb-0 small">💰 Estado Upsells</h6>
                        </div>
                        <div class="card-body py-2">
                            <div class="chart-small">
                                <canvas id="upsellStatusChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráficos y Análisis Visual -->
            <div class="row mb-4">
                <div class="col-12">
                    <h2 class="h4 text-primary mb-3">📊 Análisis Visual y Tendencias</h2>
                </div>

                <!-- Gráfico de tendencias diarias -->
                <div class="col-xl-8 col-lg-7 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light border-0">
                            <h6 class="card-title mb-0">📈 Tendencias Diarias - Últimos 30 Días</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-area">
                                <canvas id="dailyTrendsChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gráfico de distribución pipeline -->
                <div class="col-xl-4 col-lg-5 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light border-0">
                            <h6 class="card-title mb-0">🎯 Distribución Pipeline</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-pie">
                                <canvas id="pipelineDistributionChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Segunda fila de gráficos -->
            <div class="row mb-4">
                <!-- Rendimiento semanal -->
                <div class="col-xl-6 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light border-0">
                            <h6 class="card-title mb-0">⚡ Rendimiento Semanal</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="weeklyPerformanceChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Top closers performance -->
                <div class="col-xl-6 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light border-0">
                            <h6 class="card-title mb-0">🏆 Performance Top Closers</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="topClosersChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scripts para hacer el dashboard más interactivo -->
        @push('scripts')
        <!-- Chart.js CDN -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            // Auto-refresh cada 5 minutos para mantener datos actualizados
            setInterval(function() {
                if (document.visibilityState === 'visible') {
                    location.reload();
                }
            }, 300000); // 5 minutos

            // Agregar tooltips a las métricas
            document.addEventListener('DOMContentLoaded', function () {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                })

                // Inicializar gráficos
                initCharts();
            });

            function initCharts() {
                // Datos del servidor
                const chartData = @json($metrics['charts'] ?? []);

                // Configuración de colores
                const colors = {
                    primary: '#4e73df',
                    success: '#1cc88a',
                    info: '#36b9cc',
                    warning: '#f6c23e',
                    danger: '#e74a3b',
                    secondary: '#858796'
                };

                // Inicializar cada gráfico
                initDailyTrendsChart(chartData, colors);
                initPipelineDistributionChart(chartData, colors);
                initWeeklyPerformanceChart(chartData, colors);
                initTopClosersChart(chartData, colors);

                // Nuevos gráficos para métricas específicas
                initPipelineStatusChart(colors);
                initTopPerformersChart(colors);
                initContractTypesChart(colors);
                initOnboardingStatusChart(colors);
                initUpsellStatusChart(colors);
            }

            function initDailyTrendsChart(chartData, colors) {
                const ctx = document.getElementById('dailyTrendsChart');
                if (!ctx) return;

                const dates = chartData.leads_daily?.map(item => {
                    const date = new Date(item.date);
                    return date.toLocaleDateString('es-ES', { month: 'short', day: 'numeric' });
                }) || [];

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: dates,
                        datasets: [{
                            label: 'Leads',
                            data: chartData.leads_daily?.map(item => item.count) || [],
                            borderColor: colors.primary,
                            backgroundColor: colors.primary + '20',
                            tension: 0.3,
                            fill: true
                        }, {
                            label: 'Ventas',
                            data: chartData.sales_daily?.map(item => item.count) || [],
                            borderColor: colors.success,
                            backgroundColor: colors.success + '20',
                            tension: 0.3
                        }, {
                            label: 'Llamadas',
                            data: chartData.calls_daily?.map(item => item.total) || [],
                            borderColor: colors.info,
                            backgroundColor: colors.info + '20',
                            tension: 0.3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }

            function initPipelineDistributionChart(chartData, colors) {
                const ctx = document.getElementById('pipelineDistributionChart');
                if (!ctx) return;

                const pipelineData = @json($metrics['leads']['pipeline_distribution'] ?? []);

                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: pipelineData.map(item => item.name),
                        datasets: [{
                            data: pipelineData.map(item => item.count),
                            backgroundColor: [
                                colors.primary,
                                colors.success,
                                colors.info,
                                colors.warning,
                                colors.danger,
                                colors.secondary
                            ]
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

            function initWeeklyPerformanceChart(chartData, colors) {
                const ctx = document.getElementById('weeklyPerformanceChart');
                if (!ctx) return;

                const weeklyData = chartData.weekly_performance || [];

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: weeklyData.map(item => item.week),
                        datasets: [{
                            label: 'Leads',
                            data: weeklyData.map(item => item.leads),
                            backgroundColor: colors.primary + '80',
                            borderColor: colors.primary,
                            borderWidth: 1
                        }, {
                            label: 'Ventas',
                            data: weeklyData.map(item => item.sales),
                            backgroundColor: colors.success + '80',
                            borderColor: colors.success,
                            borderWidth: 1
                        }, {
                            label: 'Llamadas',
                            data: weeklyData.map(item => item.calls),
                            backgroundColor: colors.info + '80',
                            borderColor: colors.info,
                            borderWidth: 1
                        }, {
                            label: 'Upsells',
                            data: weeklyData.map(item => item.upsells),
                            backgroundColor: colors.warning + '80',
                            borderColor: colors.warning,
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }

            function initTopClosersChart(chartData, colors) {
                const ctx = document.getElementById('topClosersChart');
                if (!ctx) return;

                const topClosers = @json($metrics['leads']['top_closers'] ?? []);

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: topClosers.map(item => item.name),
                        datasets: [{
                            label: 'Ventas',
                            data: topClosers.map(item => item.sales_count),
                            backgroundColor: [
                                colors.primary + '80',
                                colors.success + '80',
                                colors.info + '80',
                                colors.warning + '80',
                                colors.danger + '80'
                            ],
                            borderColor: [
                                colors.primary,
                                colors.success,
                                colors.info,
                                colors.warning,
                                colors.danger
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        indexAxis: 'y',
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }

            // Nuevas funciones para métricas específicas
            function initPipelineStatusChart(colors) {
                const ctx = document.getElementById('pipelineStatusChart');
                if (!ctx) return;

                const pipelineData = @json($metrics['leads']['pipeline_distribution'] ?? []);

                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: pipelineData.map(item => item.name),
                        datasets: [{
                            data: pipelineData.map(item => item.count),
                            backgroundColor: [
                                colors.primary,
                                colors.success,
                                colors.info,
                                colors.warning,
                                colors.danger,
                                colors.secondary
                            ]
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

            function initTopPerformersChart(colors) {
                const ctx = document.getElementById('topPerformersChart');
                if (!ctx) return;

                const topClosers = @json($metrics['leads']['top_closers'] ?? []);

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: topClosers.map(item => item.name),
                        datasets: [{
                            label: 'Ventas',
                            data: topClosers.map(item => item.sales_count),
                            backgroundColor: [
                                '#FFD700', // Oro para el primero
                                '#C0C0C0', // Plata para el segundo
                                '#CD7F32', // Bronce para el tercero
                                colors.primary + '80',
                                colors.info + '80'
                            ],
                            borderColor: [
                                '#FFD700',
                                '#C0C0C0',
                                '#CD7F32',
                                colors.primary,
                                colors.info
                            ],
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }

            function initContractTypesChart(colors) {
                const ctx = document.getElementById('contractTypesChart');
                if (!ctx) return;

                const contractTypes = @json($metrics['general']['contract_types'] ?? []);

                // Mapear labels correctamente para los 3 tipos
                const labels = contractTypes.map(item => {
                    if (item.tipo_contrato === 'high ticket') return 'High Ticket';
                    if (item.tipo_contrato === 'low ticket') return 'Low Ticket';
                    if (item.tipo_contrato === 'beca') return 'Beca';
                    return item.tipo_contrato;
                });

                // Mapear colores según tipo
                const backgroundColors = contractTypes.map(item => {
                    if (item.tipo_contrato === 'high ticket') return colors.success; // Verde
                    if (item.tipo_contrato === 'low ticket') return colors.warning; // Amarillo
                    if (item.tipo_contrato === 'beca') return colors.info; // Azul
                    return colors.secondary;
                });

                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: contractTypes.map(item => item.count),
                            backgroundColor: backgroundColors
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

            function initOnboardingStatusChart(colors) {
                const ctx = document.getElementById('onboardingStatusChart');
                if (!ctx) return;

                const statusData = @json($metrics['onboarding']['calls_by_status'] ?? []);

                const statusColors = {
                    'realizada': colors.success,
                    'pendiente': colors.warning,
                    'no_realizada': colors.danger,
                    'reprogramada': colors.info
                };

                const statusLabels = {
                    'realizada': 'Realizada',
                    'pendiente': 'Pendiente',
                    'no_realizada': 'No Realizada',
                    'reprogramada': 'Reprogramada'
                };

                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: statusData.map(item => statusLabels[item.status] || item.status),
                        datasets: [{
                            data: statusData.map(item => item.count),
                            backgroundColor: statusData.map(item => statusColors[item.status] || colors.secondary)
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

            function initUpsellStatusChart(colors) {
                const ctx = document.getElementById('upsellStatusChart');
                if (!ctx) return;

                const upsellData = @json($metrics['upsell']['upsells_by_status'] ?? []);

                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: upsellData.map(item => item.upsell === 'aprobado' ? 'Aprobado' : 'Pendiente'),
                        datasets: [{
                            data: upsellData.map(item => item.count),
                            backgroundColor: [
                                colors.success, // Aprobado verde
                                colors.warning  // Pendiente amarillo
                            ]
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
        </script>

        <style>
        .chart-area {
            position: relative;
            height: 300px;
        }

        .chart-pie {
            position: relative;
            height: 200px;
        }

        .chart-small {
            position: relative;
            height: 180px;
        }

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
        </style>
        @endpush
    @endif
</x-app-layout>
