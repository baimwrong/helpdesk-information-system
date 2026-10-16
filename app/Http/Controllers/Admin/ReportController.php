<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

        // Agent performance with bulk aggregates (solves N+1 query problem)
        $resolvedCounts = Ticket::selectRaw('assigned_to_id, count(*) as count')
            ->whereIn('status', [Ticket::STATUS_RESOLVED, Ticket::STATUS_CLOSED])
            ->when($fromDate, fn ($q) => $q->where('created_at', '>=', $fromDate))
            ->whereNotNull('assigned_to_id')
            ->groupBy('assigned_to_id')
            ->pluck('count', 'assigned_to_id');

        $avgRatings = Ticket::selectRaw('assigned_to_id, avg(satisfaction_rating) as avg_rating')
            ->whereNotNull('satisfaction_rating')
            ->when($fromDate, fn ($q) => $q->where('created_at', '>=', $fromDate))
            ->whereNotNull('assigned_to_id')
            ->groupBy('assigned_to_id')
            ->pluck('avg_rating', 'assigned_to_id');

        $agents = User::with('department')
            ->whereIn('role', [User::ROLE_ADMIN, User::ROLE_AGENT])
            ->withCount(['assignedTickets' => function ($q) use ($fromDate) {
                if ($fromDate) {
                    $q->where('created_at', '>=', $fromDate);
                }
            }])
            ->get()
            ->map(function ($agent) use ($resolvedCounts, $avgRatings) {
                return [
                    'id' => $agent->id,
                    'name' => $agent->name,
                    'email' => $agent->email,
                    'department' => $agent->department?->name ?? 'Global',
                    'assigned_count' => $agent->assigned_tickets_count,
                    'resolved_count' => (int) ($resolvedCounts[$agent->id] ?? 0),
                    'avg_rating' => round((float) ($avgRatings[$agent->id] ?? 0), 1),
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
     * Export ticket summary report as streamed CSV with injection protection.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $filename = 'helpdesk_tickets_export_'.date('Ymd_His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Microsoft Excel compatibility
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Ticket Number',
                'Subject',
                'Customer Name',
                'Customer Email',
                'Department',
                'Category',
                'Priority',
                'Status',
                'Assigned Agent',
                'Created At',
                'Resolved At',
                'Rating',
            ]);

            Ticket::with(['user', 'assignedTo', 'department', 'category'])
                ->latest()
                ->chunk(250, function ($tickets) use ($handle) {
                    foreach ($tickets as $t) {
                        $sanitize = function (?string $val): string {
                            if ($val === null) {
                                return '';
                            }
                            // Neutralize CSV formula injection
                            if (in_array(substr($val, 0, 1), ['=', '+', '-', '@'], true)) {
                                return "'".$val;
                            }

                            return $val;
                        };

                        fputcsv($handle, [
                            $sanitize($t->ticket_number),
                            $sanitize($t->subject),
                            $sanitize($t->user?->name),
                            $sanitize($t->user?->email),
                            $sanitize($t->department?->name),
                            $sanitize($t->category?->name),
                            $t->priority,
                            $t->status,
                            $sanitize($t->assignedTo?->name ?? 'Unassigned'),
                            (string) $t->created_at,
                            (string) $t->resolved_at,
                            (string) $t->satisfaction_rating,
                        ]);
                    }
                });

            fclose($handle);
        }, 200, $headers);
    }
}
