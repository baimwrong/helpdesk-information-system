<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\KnowledgeBaseArticle;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the main role-specific dashboard.
     */
    public function index(): View
    {
        $user = Auth::user();

        if ($user->isCustomer()) {
            return $this->customerDashboard($user);
        }

        return $this->staffDashboard($user);
    }

    /**
     * Render dashboard for staff members (Admins and Agents).
     */
    protected function staffDashboard(User $user): View
    {
        $baseQuery = Ticket::query();

        // Agents can optionally filter or focus on their department/assignment
        $totalTickets = (clone $baseQuery)->count();
        $openTickets = (clone $baseQuery)->where('status', Ticket::STATUS_OPEN)->count();
        $inProgressTickets = (clone $baseQuery)->where('status', Ticket::STATUS_IN_PROGRESS)->count();
        $pendingTickets = (clone $baseQuery)->where('status', Ticket::STATUS_PENDING)->count();
        $resolvedTickets = (clone $baseQuery)->where('status', Ticket::STATUS_RESOLVED)->count();
        $closedTickets = (clone $baseQuery)->where('status', Ticket::STATUS_CLOSED)->count();

        // SLA breached tickets (active tickets where resolution_due_at < now)
        $breachedTickets = (clone $baseQuery)
            ->whereIn('status', [Ticket::STATUS_OPEN, Ticket::STATUS_IN_PROGRESS, Ticket::STATUS_PENDING])
            ->whereNotNull('resolution_due_at')
            ->where('resolution_due_at', '<', Carbon::now())
            ->count();

        // My assigned tickets
        $myAssignedTickets = Ticket::where('assigned_to_id', $user->id)
            ->whereIn('status', [Ticket::STATUS_OPEN, Ticket::STATUS_IN_PROGRESS, Ticket::STATUS_PENDING])
            ->count();

        // Average satisfaction rating
        $avgRating = Ticket::whereNotNull('satisfaction_rating')->avg('satisfaction_rating') ?? 0;

        // Department breakdown
        $departments = Department::withCount('tickets')->orderByDesc('tickets_count')->take(5)->get();

        // Priority breakdown
        $priorityCounts = [
            'urgent' => (clone $baseQuery)->where('priority', Ticket::PRIORITY_URGENT)->count(),
            'high' => (clone $baseQuery)->where('priority', Ticket::PRIORITY_HIGH)->count(),
            'medium' => (clone $baseQuery)->where('priority', Ticket::PRIORITY_MEDIUM)->count(),
            'low' => (clone $baseQuery)->where('priority', Ticket::PRIORITY_LOW)->count(),
        ];

        // Recent Tickets
        $recentTickets = Ticket::with(['user', 'assignedTo', 'department', 'category'])
            ->latest()
            ->take(8)
            ->get();

        // Recent Activities
        $recentActivities = TicketActivity::with(['ticket', 'user'])
            ->latest('created_at')
            ->take(8)
            ->get();

        return view('dashboard.staff', compact(
            'totalTickets',
            'openTickets',
            'inProgressTickets',
            'pendingTickets',
            'resolvedTickets',
            'closedTickets',
            'breachedTickets',
            'myAssignedTickets',
            'avgRating',
            'departments',
            'priorityCounts',
            'recentTickets',
            'recentActivities'
        ));
    }

    /**
     * Render dashboard for customers.
     */
    protected function customerDashboard(User $user): View
    {
        $myTicketsQuery = Ticket::where('user_id', $user->id);

        $totalMyTickets = (clone $myTicketsQuery)->count();
        $openMyTickets = (clone $myTicketsQuery)->whereIn('status', [Ticket::STATUS_OPEN, Ticket::STATUS_IN_PROGRESS, Ticket::STATUS_PENDING])->count();
        $resolvedMyTickets = (clone $myTicketsQuery)->where('status', Ticket::STATUS_RESOLVED)->count();
        $closedMyTickets = (clone $myTicketsQuery)->where('status', Ticket::STATUS_CLOSED)->count();

        $recentTickets = (clone $myTicketsQuery)
            ->with(['assignedTo', 'department', 'category'])
            ->latest()
            ->take(5)
            ->get();

        $popularArticles = KnowledgeBaseArticle::where('is_published', true)
            ->with('category')
            ->orderByDesc('views_count')
            ->take(4)
            ->get();

        return view('dashboard.customer', compact(
            'totalMyTickets',
            'openMyTickets',
            'resolvedMyTickets',
            'closedMyTickets',
            'recentTickets',
            'popularArticles'
        ));
    }
}
