<?php

namespace App\Http\Controllers;

use App\Models\CannedResponse;
use App\Models\Category;
use App\Models\Department;
use App\Models\SlaPlan;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\TicketAttachment;
use App\Models\TicketReply;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TicketController extends Controller
{
    /**
     * Display a paginated listing of tickets with filters and search.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $query = Ticket::with(['user', 'assignedTo', 'department', 'category']);

        // Customer access control: customers only see tickets they created
        if ($user->isCustomer()) {
            $query->where('user_id', $user->id);
        }

        // Tab filters
        $tab = $request->query('tab', 'all');
        if ($tab === 'my_assigned' && $user->isStaff()) {
            $query->where('assigned_to_id', $user->id);
        } elseif ($tab === 'unassigned' && $user->isStaff()) {
            $query->whereNull('assigned_to_id');
        } elseif ($tab === 'breached' && $user->isStaff()) {
            $query->whereIn('status', [Ticket::STATUS_OPEN, Ticket::STATUS_IN_PROGRESS, Ticket::STATUS_PENDING])
                ->whereNotNull('resolution_due_at')
                ->where('resolution_due_at', '<', Carbon::now());
        } elseif ($tab === 'resolved') {
            $query->where('status', Ticket::STATUS_RESOLVED);
        } elseif ($tab === 'closed') {
            $query->where('status', Ticket::STATUS_CLOSED);
        }

        // Search by ticket number or text
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'LIKE', "%{$search}%")
                    ->orWhere('subject', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        // Status filter
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        // Priority filter
        if ($priority = $request->query('priority')) {
            $query->where('priority', $priority);
        }

        // Department filter
        if ($departmentId = $request->query('department_id')) {
            $query->where('department_id', $departmentId);
        }

        // Category filter
        if ($categoryId = $request->query('category_id')) {
            $query->where('category_id', $categoryId);
        }

        $tickets = $query->latest()->paginate(15)->withQueryString();

        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $agents = User::whereIn('role', [User::ROLE_ADMIN, User::ROLE_AGENT])->where('is_active', true)->orderBy('name')->get();

        return view('tickets.index', compact('tickets', 'departments', 'categories', 'agents', 'tab'));
    }

    /**
     * Show ticket creation form.
     */
    public function create(): View
    {
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $categories = Category::where('is_active', true)->orderBy('name')->get();

        return view('tickets.create', compact('departments', 'categories'));
    }

    /**
     * Store newly created ticket.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'attachments.*' => ['nullable', 'file', 'max:10240', 'mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx,zip,txt,csv'],
        ]);

        DB::beginTransaction();

        try {
            // SLA calculation
            $sla = SlaPlan::where('priority', $validated['priority'])->where('is_active', true)->first();
            $responseDue = $sla ? Carbon::now()->addHours($sla->response_time_hours) : Carbon::now()->addHours(24);
            $resolutionDue = $sla ? Carbon::now()->addHours($sla->resolution_time_hours) : Carbon::now()->addHours(72);

            $ticket = Ticket::create([
                'ticket_number' => Ticket::generateTicketNumber(),
                'user_id' => $user->id,
                'department_id' => $validated['department_id'],
                'category_id' => $validated['category_id'] ?? null,
                'sla_plan_id' => $sla?->id,
                'subject' => $validated['subject'],
                'description' => $validated['description'],
                'priority' => $validated['priority'],
                'status' => Ticket::STATUS_OPEN,
                'response_due_at' => $responseDue,
                'resolution_due_at' => $resolutionDue,
            ]);

            // Save attachments
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('attachments/'.$ticket->id, 'public');
                    TicketAttachment::create([
                        'ticket_id' => $ticket->id,
                        'ticket_reply_id' => null,
                        'uploaded_by_id' => $user->id,
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'file_size' => $file->getSize(),
                        'mime_type' => $file->getClientMimeType(),
                    ]);
                }
            }

            TicketActivity::log($ticket->id, $user->id, 'created', "Ticket submitted by {$user->name}");

            DB::commit();

            return redirect()->route('tickets.show', $ticket)
                ->with('success', "Ticket {$ticket->ticket_number} created successfully.");
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()->withErrors(['error' => 'Failed to create ticket: '.$e->getMessage()]);
        }
    }

    /**
     * Display ticket conversation and details.
     */
    public function show(Ticket $ticket): View
    {
        $user = Auth::user();

        // Customer can only view their own ticket
        if ($user->isCustomer() && $ticket->user_id !== $user->id) {
            abort(403, 'You do not have permission to view this ticket.');
        }

        $ticket->load([
            'user',
            'assignedTo',
            'department',
            'category',
            'slaPlan',
            'attachments',
            'replies.user',
            'replies.attachments',
            'activities.user',
        ]);

        $cannedResponses = [];
        $agents = [];
        if ($user->isStaff()) {
            $cannedResponses = CannedResponse::where('is_shared', true)
                ->orWhere('user_id', $user->id)
                ->orderBy('title')
                ->get();

            $agents = User::whereIn('role', [User::ROLE_ADMIN, User::ROLE_AGENT])
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        }

        return view('tickets.show', compact('ticket', 'cannedResponses', 'agents'));
    }

    /**
     * Post a public reply to the ticket.
     */
    public function reply(Request $request, Ticket $ticket): RedirectResponse
    {
        $user = Auth::user();

        if ($user->isCustomer() && $ticket->user_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'message' => ['required', 'string'],
            'attachments.*' => ['nullable', 'file', 'max:10240', 'mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx,zip,txt,csv'],
        ]);

        DB::beginTransaction();

        try {
            $reply = TicketReply::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'message' => $validated['message'],
                'is_internal_note' => false,
            ]);

            // Save attachments
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('attachments/'.$ticket->id, 'public');
                    TicketAttachment::create([
                        'ticket_id' => $ticket->id,
                        'ticket_reply_id' => $reply->id,
                        'uploaded_by_id' => $user->id,
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'file_size' => $file->getSize(),
                        'mime_type' => $file->getClientMimeType(),
                    ]);
                }
            }

            // Update ticket first response timestamp if agent reply
            if ($user->isStaff() && $ticket->first_responded_at === null) {
                $ticket->update(['first_responded_at' => now()]);
            }

            // If ticket was resolved or closed and customer replies, reopen it
            if ($user->isCustomer() && in_array($ticket->status, [Ticket::STATUS_RESOLVED, Ticket::STATUS_CLOSED], true)) {
                $ticket->update(['status' => Ticket::STATUS_OPEN]);
                TicketActivity::log($ticket->id, $user->id, 'reopened', 'Ticket reopened by customer reply');
            }

            TicketActivity::log($ticket->id, $user->id, 'replied', "Reply posted by {$user->name}");

            DB::commit();

            return back()->with('success', 'Your reply has been posted.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Failed to post reply: '.$e->getMessage()]);
        }
    }

    /**
     * Post an internal technical note (Staff only).
     */
    public function internalNote(Request $request, Ticket $ticket): RedirectResponse
    {
        $user = Auth::user();
        if (! $user->isStaff()) {
            abort(403, 'Only staff members can post internal notes.');
        }

        $validated = $request->validate([
            'note' => ['required', 'string'],
        ]);

        TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'message' => $validated['note'],
            'is_internal_note' => true,
        ]);

        TicketActivity::log($ticket->id, $user->id, 'note_added', "Internal staff note added by {$user->name}");

        return back()->with('success', 'Internal note added.');
    }

    /**
     * Update ticket status.
     */
    public function updateStatus(Request $request, Ticket $ticket): RedirectResponse
    {
        $user = Auth::user();
        if (! $user->isStaff()) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:open,in_progress,pending,resolved,closed'],
        ]);

        $oldStatus = $ticket->status;
        $newStatus = $validated['status'];

        $updates = ['status' => $newStatus];
        if ($newStatus === Ticket::STATUS_RESOLVED && ! $ticket->resolved_at) {
            $updates['resolved_at'] = now();
        }
        if ($newStatus === Ticket::STATUS_CLOSED && ! $ticket->closed_at) {
            $updates['closed_at'] = now();
        }

        $ticket->update($updates);

        TicketActivity::log(
            $ticket->id,
            $user->id,
            'status_updated',
            'Status changed from '.strtoupper($oldStatus).' to '.strtoupper($newStatus)
        );

        return back()->with('success', 'Ticket status updated to '.ucfirst(str_replace('_', ' ', $newStatus)).'.');
    }

    /**
     * Assign ticket to an agent.
     */
    public function assign(Request $request, Ticket $ticket): RedirectResponse
    {
        $user = Auth::user();
        if (! $user->isStaff()) {
            abort(403);
        }

        $validated = $request->validate([
            'assigned_to_id' => ['nullable', 'exists:users,id'],
        ]);

        $newAssigneeId = $validated['assigned_to_id'];
        $ticket->update(['assigned_to_id' => $newAssigneeId]);

        if ($newAssigneeId) {
            $agent = User::find($newAssigneeId);
            TicketActivity::log($ticket->id, $user->id, 'assigned', "Ticket assigned to {$agent->name}");
        } else {
            TicketActivity::log($ticket->id, $user->id, 'unassigned', 'Ticket unassigned');
        }

        return back()->with('success', 'Ticket assignment updated.');
    }

    /**
     * Update ticket priority and recalculate SLA.
     */
    public function updatePriority(Request $request, Ticket $ticket): RedirectResponse
    {
        $user = Auth::user();
        if (! $user->isStaff()) {
            abort(403);
        }

        $validated = $request->validate([
            'priority' => ['required', 'in:low,medium,high,urgent'],
        ]);

        $oldPriority = $ticket->priority;
        $newPriority = $validated['priority'];

        $sla = SlaPlan::where('priority', $newPriority)->where('is_active', true)->first();
        $updates = ['priority' => $newPriority];

        if ($sla && $ticket->isOpen()) {
            $updates['sla_plan_id'] = $sla->id;
            $updates['resolution_due_at'] = Carbon::now()->addHours($sla->resolution_time_hours);
        }

        $ticket->update($updates);

        TicketActivity::log(
            $ticket->id,
            $user->id,
            'priority_updated',
            'Priority escalated/changed from '.strtoupper($oldPriority).' to '.strtoupper($newPriority)
        );

        return back()->with('success', 'Ticket priority updated.');
    }

    /**
     * Submit customer satisfaction rating.
     */
    public function rate(Request $request, Ticket $ticket): RedirectResponse
    {
        $user = Auth::user();
        if ($ticket->user_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'satisfaction_rating' => ['required', 'integer', 'between:1,5'],
            'satisfaction_comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $ticket->update([
            'satisfaction_rating' => $validated['satisfaction_rating'],
            'satisfaction_comment' => $validated['satisfaction_comment'] ?? null,
        ]);

        TicketActivity::log($ticket->id, $user->id, 'rated', "Customer submitted a {$validated['satisfaction_rating']}-star satisfaction rating");

        return back()->with('success', 'Thank you for your valuable feedback!');
    }
}
