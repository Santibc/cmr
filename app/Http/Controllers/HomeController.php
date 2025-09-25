<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Parametros;
use App\Models\Lead;
use App\Models\Sale;
use App\Models\OnboardingCall;
use App\Models\PipelineStatus;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function index()
    {
        // Si no es admin, mostrar dashboard básico
        if (!Auth::user()->hasRole('admin')) {
            return view('dashboard');
        }

        // Obtener métricas para admin
        $metrics = $this->getAdminMetrics();

        return view('dashboard', compact('metrics'));
    }

    private function getAdminMetrics()
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $previousMonth = Carbon::now()->subMonth()->startOfMonth();
        $previousMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        return [
            'leads' => $this->getLeadsMetrics($currentMonth, $previousMonth, $previousMonthEnd),
            'onboarding' => $this->getOnboardingMetrics($currentMonth, $previousMonth, $previousMonthEnd),
            'upsell' => $this->getUpsellMetrics($currentMonth, $previousMonth, $previousMonthEnd),
            'general' => $this->getGeneralMetrics($currentMonth, $previousMonth, $previousMonthEnd),
            'charts' => $this->getChartData($currentMonth),
        ];
    }

    private function getLeadsMetrics($currentMonth, $previousMonth, $previousMonthEnd)
    {
        // Leads este mes vs mes anterior
        $leadsThisMonth = Lead::where('created_at', '>=', $currentMonth)->count();
        $leadsPreviousMonth = Lead::whereBetween('created_at', [$previousMonth, $previousMonthEnd])->count();

        // Tasa de conversión (leads con venta)
        $leadsWithSales = Lead::whereHas('sale')->where('created_at', '>=', $currentMonth)->count();
        $conversionRate = $leadsThisMonth > 0 ? ($leadsWithSales / $leadsThisMonth) * 100 : 0;

        // Distribución por pipeline
        $pipelineDistribution = Lead::select('pipeline_statuses.name', DB::raw('count(*) as count'))
            ->join('pipeline_statuses', 'leads.pipeline_status_id', '=', 'pipeline_statuses.id')
            ->where('leads.created_at', '>=', $currentMonth)
            ->groupBy('pipeline_statuses.id', 'pipeline_statuses.name')
            ->get();

        // Top performers (closers con más ventas)
        $topClosers = User::select('users.name', DB::raw('count(sales.id) as sales_count'))
            ->join('sales', 'users.id', '=', 'sales.user_id')
            ->where('sales.created_at', '>=', $currentMonth)
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('sales_count')
            ->limit(5)
            ->get();

        return [
            'total_this_month' => $leadsThisMonth,
            'total_previous_month' => $leadsPreviousMonth,
            'growth_percentage' => $leadsPreviousMonth > 0 ? (($leadsThisMonth - $leadsPreviousMonth) / $leadsPreviousMonth) * 100 : null,
            'conversion_rate' => round($conversionRate, 2),
            'pipeline_distribution' => $pipelineDistribution,
            'top_closers' => $topClosers,
        ];
    }

    private function getOnboardingMetrics($currentMonth, $previousMonth, $previousMonthEnd)
    {
        // Llamadas este mes
        $callsThisMonth = OnboardingCall::where('created_at', '>=', $currentMonth)->count();
        $callsPreviousMonth = OnboardingCall::whereBetween('created_at', [$previousMonth, $previousMonthEnd])->count();

        // Llamadas por estado
        $callsByStatus = OnboardingCall::select('status', DB::raw('count(*) as count'))
            ->where('created_at', '>=', $currentMonth)
            ->groupBy('status')
            ->get();

        // Llamadas vencidas (pendientes con fecha pasada)
        $overdueCalls = OnboardingCall::where('status', 'pendiente')
            ->where('scheduled_date', '<', Carbon::now())
            ->count();

        // Tiempo promedio entre venta y primera llamada
        $avgTimeToFirstCall = DB::table('sales')
            ->join('onboarding_calls', 'sales.lead_id', '=', 'onboarding_calls.lead_id')
            ->where('sales.created_at', '>=', $currentMonth)
            ->selectRaw('AVG(DATEDIFF(onboarding_calls.scheduled_date, sales.created_at)) as avg_days')
            ->value('avg_days');

        return [
            'total_calls_this_month' => $callsThisMonth,
            'total_calls_previous_month' => $callsPreviousMonth,
            'growth_percentage' => $callsPreviousMonth > 0 ? (($callsThisMonth - $callsPreviousMonth) / $callsPreviousMonth) * 100 : null,
            'calls_by_status' => $callsByStatus,
            'overdue_calls' => $overdueCalls,
            'avg_time_to_first_call' => round($avgTimeToFirstCall ?? 0, 1),
        ];
    }

    private function getUpsellMetrics($currentMonth, $previousMonth, $previousMonthEnd)
    {
        // Upsells este mes
        $upsellsThisMonth = Sale::where('upsell', 'aprobado')
            ->where('upsell_fecha_aprobado', '>=', $currentMonth)
            ->count();

        $upsellsPreviousMonth = Sale::where('upsell', 'aprobado')
            ->whereBetween('upsell_fecha_aprobado', [$previousMonth, $previousMonthEnd])
            ->count();

        // Tasa de conversión low ticket a high ticket
        $lowTicketSales = Sale::where('tipo_contrato', 'low ticket')
            ->where('created_at', '>=', $currentMonth)
            ->count();

        $upsellConversionRate = $lowTicketSales > 0 ? ($upsellsThisMonth / $lowTicketSales) * 100 : 0;

        // Upsells pendientes de aprobación
        $pendingUpsells = Sale::where('upsell', 'pendiente')->count();

        // Estado de upsells
        $upsellsByStatus = Sale::select('upsell', DB::raw('count(*) as count'))
            ->whereNotNull('upsell')
            ->groupBy('upsell')
            ->get();

        return [
            'total_this_month' => $upsellsThisMonth,
            'total_previous_month' => $upsellsPreviousMonth,
            'growth_percentage' => $upsellsPreviousMonth > 0 ? (($upsellsThisMonth - $upsellsPreviousMonth) / $upsellsPreviousMonth) * 100 : null,
            'conversion_rate' => round($upsellConversionRate, 2),
            'pending_approvals' => $pendingUpsells,
            'upsells_by_status' => $upsellsByStatus,
        ];
    }

    private function getGeneralMetrics($currentMonth, $previousMonth, $previousMonthEnd)
    {
        // Ventas totales este mes
        $salesThisMonth = Sale::where('created_at', '>=', $currentMonth)->count();
        $salesPreviousMonth = Sale::whereBetween('created_at', [$previousMonth, $previousMonthEnd])->count();

        // Contratos aprobados vs pendientes
        $approvedContracts = Sale::where('contract_approved', true)
            ->where('created_at', '>=', $currentMonth)
            ->count();

        $pendingContracts = Sale::where('contract_approved', false)
            ->whereNotNull('contract_template_id')
            ->count();

        // Distribución por tipo de contrato
        $contractTypes = Sale::select('tipo_contrato', DB::raw('count(*) as count'))
            ->where('created_at', '>=', $currentMonth)
            ->groupBy('tipo_contrato')
            ->get();

        // Actividad reciente (últimos 7 días)
        $recentActivity = [
            'new_leads' => Lead::where('created_at', '>=', Carbon::now()->subDays(7))->count(),
            'new_sales' => Sale::where('created_at', '>=', Carbon::now()->subDays(7))->count(),
            'completed_calls' => OnboardingCall::where('status', 'realizada')
                ->where('updated_at', '>=', Carbon::now()->subDays(7))->count(),
            'approved_upsells' => Sale::where('upsell', 'aprobado')
                ->where('upsell_fecha_aprobado', '>=', Carbon::now()->subDays(7))->count(),
        ];

        return [
            'total_sales_this_month' => $salesThisMonth,
            'total_sales_previous_month' => $salesPreviousMonth,
            'sales_growth_percentage' => $salesPreviousMonth > 0 ? (($salesThisMonth - $salesPreviousMonth) / $salesPreviousMonth) * 100 : null,
            'approved_contracts' => $approvedContracts,
            'pending_contracts' => $pendingContracts,
            'contract_types' => $contractTypes,
            'recent_activity' => $recentActivity,
        ];
    }

    private function getChartData($currentMonth)
    {
        // Datos para gráficos de los últimos 30 días
        $startDate = Carbon::now()->subDays(30);

        // Leads diarios
        $leadsDaily = Lead::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Ventas diarias
        $salesDaily = Sale::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Llamadas por día
        $callsDaily = OnboardingCall::selectRaw('DATE(created_at) as date, COUNT(*) as total, SUM(CASE WHEN status = "realizada" THEN 1 ELSE 0 END) as completed')
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Rendimiento semanal (últimas 4 semanas)
        $weeklyPerformance = [];
        for ($i = 3; $i >= 0; $i--) {
            $weekStart = Carbon::now()->subWeeks($i)->startOfWeek();
            $weekEnd = Carbon::now()->subWeeks($i)->endOfWeek();

            $weeklyPerformance[] = [
                'week' => 'S' . ($i + 1),
                'leads' => Lead::whereBetween('created_at', [$weekStart, $weekEnd])->count(),
                'sales' => Sale::whereBetween('created_at', [$weekStart, $weekEnd])->count(),
                'calls' => OnboardingCall::whereBetween('created_at', [$weekStart, $weekEnd])->count(),
                'upsells' => Sale::where('upsell', 'aprobado')->whereBetween('upsell_fecha_aprobado', [$weekStart, $weekEnd])->count(),
            ];
        }

        return [
            'leads_daily' => $leadsDaily,
            'sales_daily' => $salesDaily,
            'calls_daily' => $callsDaily,
            'weekly_performance' => $weeklyPerformance,
        ];
    }
}
