<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * Display the analytics and reports dashboard.
     */
    public function index(Request $request): View
    {
        $range = $request->query('range', '30');
        $fromDate = match ($range) {
            '7' => Carbon::now()->subDays(7),
            '30' => Carbon::now()->subDays(30),
            '90' => Carbon::now()->subDays(90),
            '365' => Carbon::now()->subDays(365),
            default => null,
        };

        $query = Ticket::query();
        if ($fromDate) {
            $query->where('created_at', '>=', $fromDate);
        }

        $totalTickets = (clone $query)->count();
        $resolvedTickets = (clone $query)->where('status', Ticket::STATUS_RESOLVED)->count();
        $closedTickets = (clone $query)->where('status', Ticket::STATUS_CLOSED)->count();

        // SLA Breached tickets in period
        $breachedTickets = (clone $query)
            ->whereNotNull('resolution_due_at')
            ->where(function ($q) {
                $q->where(function ($sub) {
                    $sub->whereNotNull('resolved_at')
                        ->whereColumn('resolved_at', '>', 'resolution_due_at');
                })->orWhere(function ($sub) {
                    $sub->whereNull('resolved_at')
                        ->whereIn('status', [Ticket::STATUS_OPEN, Ticket::STATUS_IN_PROGRESS, Ticket::STATUS_PENDING])
                        ->where('resolution_due_at', '<', Carbon::now());
                });
            })
            ->count();

        $slaComplianceRate = $totalTickets > 0 ? round((($totalTickets - $breachedTickets) / $totalTickets) * 100, 1) : 100;

        // CSAT Average
        $avgCsat = (clone $query)->whereNotNull('satisfaction_rating')->avg('satisfaction_rating') ?? 0;
        $totalRatingsCount = (clone $query)->whereNotNull('satisfaction_rating')->count();

        // Department breakdown
        $departmentStats = Department::withCount(['tickets' => function ($q) use ($fromDate) {
            if ($fromDate) {
                $q->where('created_at', '>=', $fromDate);
            }
        }])->get();

        // Agent performance
        $agents = User::whereIn('role', [User::ROLE_ADMIN, User::ROLE_AGENT])
            ->withCount(['assignedTickets' => function ($q) use ($fromDate) {
                if ($fromDate) {
                    $q->where('created_at', '>=', $fromDate);
                }
            }])
            ->get()
            ->map(function ($agent) use ($fromDate) {
                $resolvedCount = Ticket::where('assigned_to_id', $agent->id)
                    ->whereIn('status', [Ticket::STATUS_RESOLVED, Ticket::STATUS_CLOSED])
                    ->when($fromDate, fn ($q) => $q->where('created_at', '>=', $fromDate))
                    ->count();

                $avgAgentCsat = Ticket::where('assigned_to_id', $agent->id)
                    ->whereNotNull('satisfaction_rating')
                    ->when($fromDate, fn ($q) => $q->where('created_at', '>=', $fromDate))
                    ->avg('satisfaction_rating') ?? 0;

                return [
                    'id' => $agent->id,
                    'name' => $agent->name,
                    'email' => $agent->email,
                    'department' => $agent->department?->name ?? 'Global',
                    'assigned_count' => $agent->assigned_tickets_count,
                    'resolved_count' => $resolvedCount,
                    'avg_rating' => round($avgAgentCsat, 1),
                ];
            });

        return view('admin.reports.index', compact(
            'range',
            'totalTickets',
            'resolvedTickets',
            'closedTickets',
            'breachedTickets',
            'slaComplianceRate',
            'avgCsat',
            'totalRatingsCount',
            'departmentStats',
            'agents'
        ));
    }

    /**
     * Export ticket summary report as CSV.
     */
    public function exportCsv(Request $request): Response
    {
        $tickets = Ticket::with(['user', 'assignedTo', 'department', 'category', 'slaPlan'])
            ->latest()
            ->get();

        $csv = "Ticket Number,Subject,Customer Name,Customer Email,Department,Category,Priority,Status,Assigned Agent,Created At,Resolved At,Rating\n";

        foreach ($tickets as $t) {
            $customerName = str_replace('"', '""', $t->user?->name ?? '');
            $customerEmail = str_replace('"', '""', $t->user?->email ?? '');
            $subject = str_replace('"', '""', $t->subject);
            $dept = str_replace('"', '""', $t->department?->name ?? '');
            $cat = str_replace('"', '""', $t->category?->name ?? '');
            $agent = str_replace('"', '""', $t->assignedTo?->name ?? 'Unassigned');

            $csv .= "\"{$t->ticket_number}\",\"{$subject}\",\"{$customerName}\",\"{$customerEmail}\",\"{$dept}\",\"{$cat}\",\"{$t->priority}\",\"{$t->status}\",\"{$agent}\",\"{$t->created_at}\",\"{$t->resolved_at}\",\"{$t->satisfaction_rating}\"\n";
        }

        $filename = 'helpdesk_tickets_export_'.date('Ymd_His').'.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
