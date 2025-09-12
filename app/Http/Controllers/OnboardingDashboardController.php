<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\OnboardingCall;
use App\Models\LeadNote;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OnboardingDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $userRole = auth()->user()->getRoleNames()->first();
            if (!in_array($userRole, ['admin', 'cms'])) {
                abort(403, 'Acceso no autorizado. Solo usuarios con rol Admin o CMS pueden acceder al dashboard de onboarding.');
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));
        
        $dateFromCarbon = Carbon::parse($dateFrom)->startOfDay();
        $dateToCarbon = Carbon::parse($dateTo)->endOfDay();

        // Estadísticas principales
        $stats = $this->getMainStats($dateFromCarbon, $dateToCarbon);
        
        // Datos para gráficos
        $chartData = $this->getChartData($dateFromCarbon, $dateToCarbon);
        
        // Leads recientes con actividad
        $recentLeads = $this->getRecentLeads();
        
        // Métricas de rendimiento
        $performanceMetrics = $this->getPerformanceMetrics($dateFromCarbon, $dateToCarbon);

        if ($request->ajax()) {
            return response()->json([
                'stats' => $stats,
                'chartData' => $chartData,
                'recentLeads' => $recentLeads,
                'performanceMetrics' => $performanceMetrics
            ]);
        }

        return view('onboarding.dashboard', compact(
            'stats', 
            'chartData', 
            'recentLeads', 
            'performanceMetrics',
            'dateFrom',
            'dateTo'
        ));
    }

    private function getMainStats($dateFrom, $dateTo)
    {
        // Total de leads con ventas (candidatos para onboarding)
        $totalLeadsWithSales = Lead::whereHas('sale')->count();
        
        // Leads con llamadas programadas
        $leadsWithCalls = Lead::whereHas('onboardingCalls')->count();
        
        // Total de llamadas en el período
        $totalCalls = OnboardingCall::whereBetween('created_at', [$dateFrom, $dateTo])->count();
        
        // Llamadas realizadas vs pendientes en el período
        $callsCompleted = OnboardingCall::whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', OnboardingCall::STATUS_REALIZADA)->count();
        
        $callsPending = OnboardingCall::whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', OnboardingCall::STATUS_PENDIENTE)->count();
        
        $callsNotCompleted = OnboardingCall::whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', OnboardingCall::STATUS_NO_REALIZADA)->count();
        
        $callsRescheduled = OnboardingCall::whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', OnboardingCall::STATUS_REPROGRAMADA)->count();

        // Notas totales
        $totalNotes = LeadNote::whereBetween('created_at', [$dateFrom, $dateTo])->count();

        // Tasa de conversión (llamadas realizadas / total programadas)
        $conversionRate = $totalCalls > 0 ? ($callsCompleted / $totalCalls) * 100 : 0;

        return [
            'totalLeadsWithSales' => $totalLeadsWithSales,
            'leadsWithCalls' => $leadsWithCalls,
            'totalCalls' => $totalCalls,
            'callsCompleted' => $callsCompleted,
            'callsPending' => $callsPending,
            'callsNotCompleted' => $callsNotCompleted,
            'callsRescheduled' => $callsRescheduled,
            'totalNotes' => $totalNotes,
            'conversionRate' => round($conversionRate, 1)
        ];
    }

    private function getChartData($dateFrom, $dateTo)
    {
        // Llamadas por día en los últimos 30 días
        $dailyCalls = OnboardingCall::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as total'),
            DB::raw('SUM(CASE WHEN status = "' . OnboardingCall::STATUS_REALIZADA . '" THEN 1 ELSE 0 END) as completed'),
            DB::raw('SUM(CASE WHEN status = "' . OnboardingCall::STATUS_PENDIENTE . '" THEN 1 ELSE 0 END) as pending')
        )
        ->whereBetween('created_at', [$dateFrom, $dateTo])
        ->groupBy(DB::raw('DATE(created_at)'))
        ->orderBy('date')
        ->get();

        // Distribución de estados de llamadas
        $statusDistribution = OnboardingCall::select(
            'status',
            DB::raw('COUNT(*) as count')
        )
        ->whereBetween('created_at', [$dateFrom, $dateTo])
        ->groupBy('status')
        ->get();

        // Llamadas por usuario (quien las programa)
        $callsByUser = OnboardingCall::select(
            'users.name',
            DB::raw('COUNT(*) as total_calls'),
            DB::raw('SUM(CASE WHEN onboarding_calls.status = "' . OnboardingCall::STATUS_REALIZADA . '" THEN 1 ELSE 0 END) as completed_calls')
        )
        ->join('users', 'onboarding_calls.user_id', '=', 'users.id')
        ->whereBetween('onboarding_calls.created_at', [$dateFrom, $dateTo])
        ->groupBy('users.id', 'users.name')
        ->orderByDesc('total_calls')
        ->get();

        return [
            'dailyCalls' => $dailyCalls,
            'statusDistribution' => $statusDistribution,
            'callsByUser' => $callsByUser
        ];
    }

    private function getRecentLeads()
    {
        return Lead::with(['sale', 'onboardingCalls' => function($query) {
            $query->latest()->limit(1);
        }, 'notes' => function($query) {
            $query->latest()->limit(1);
        }])
        ->whereHas('sale')
        ->orderByDesc('id')
        ->limit(10)
        ->get()
        ->map(function($lead) {
            $lastCall = $lead->onboardingCalls->first();
            $lastNote = $lead->notes->first();
            
            return [
                'id' => $lead->id,
                'nombre' => $lead->nombre,
                'email' => $lead->email,
                'sale_date' => $lead->sale->created_at->format('d/m/Y'),
                'last_call_status' => $lastCall ? $lastCall->status : 'Sin llamadas',
                'last_call_date' => $lastCall ? $lastCall->created_at->format('d/m/Y H:i') : '-',
                'last_note_date' => $lastNote ? $lastNote->created_at->format('d/m/Y H:i') : 'Sin notas',
                'notes_count' => $lead->notes->count(),
                'calls_count' => $lead->onboardingCalls->count()
            ];
        });
    }

    private function getPerformanceMetrics($dateFrom, $dateTo)
    {
        // Tiempo promedio entre venta y primera llamada
        $avgTimeToFirstCall = Lead::select(
            DB::raw('AVG(TIMESTAMPDIFF(HOUR, sales.created_at, onboarding_calls.created_at)) as avg_hours')
        )
        ->join('sales', 'leads.id', '=', 'sales.lead_id')
        ->join('onboarding_calls', function($join) {
            $join->on('leads.id', '=', 'onboarding_calls.lead_id')
                 ->whereRaw('onboarding_calls.id = (
                     SELECT MIN(id) FROM onboarding_calls oc 
                     WHERE oc.lead_id = leads.id
                 )');
        })
        ->whereBetween('onboarding_calls.created_at', [$dateFrom, $dateTo])
        ->value('avg_hours');

        // Tasa de respuesta por franja horaria
        $hourlyResponse = OnboardingCall::select(
            DB::raw('HOUR(scheduled_date) as hour'),
            DB::raw('COUNT(*) as total'),
            DB::raw('SUM(CASE WHEN status = "' . OnboardingCall::STATUS_REALIZADA . '" THEN 1 ELSE 0 END) as completed')
        )
        ->whereBetween('created_at', [$dateFrom, $dateTo])
        ->whereNotNull('scheduled_date')
        ->groupBy(DB::raw('HOUR(scheduled_date)'))
        ->orderBy('hour')
        ->get();

        // Leads más activos (con más notas/llamadas)
        $mostActiveLeads = Lead::select(
            'leads.id',
            'leads.nombre',
            'leads.email',
            DB::raw('COUNT(DISTINCT onboarding_calls.id) as calls_count'),
            DB::raw('COUNT(DISTINCT lead_notes.id) as notes_count'),
            DB::raw('(COUNT(DISTINCT onboarding_calls.id) + COUNT(DISTINCT lead_notes.id)) as activity_score')
        )
        ->leftJoin('onboarding_calls', 'leads.id', '=', 'onboarding_calls.lead_id')
        ->leftJoin('lead_notes', 'leads.id', '=', 'lead_notes.lead_id')
        ->whereHas('sale')
        ->groupBy('leads.id', 'leads.nombre', 'leads.email')
        ->orderByDesc('activity_score')
        ->limit(5)
        ->get();

        return [
            'avgTimeToFirstCall' => $avgTimeToFirstCall ? round($avgTimeToFirstCall, 1) : 0,
            'hourlyResponse' => $hourlyResponse,
            'mostActiveLeads' => $mostActiveLeads
        ];
    }
}