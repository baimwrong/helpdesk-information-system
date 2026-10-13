@extends('layouts.app')

@section('title', "Ticket #{$ticket->ticket_number}")

@section('content')
<div class="container-fluid">
    <!-- Top Action Breadcrumb -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('tickets.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> Tickets
            </a>
            <span class="fs-5 fw-bold font-monospace text-primary">#{{ $ticket->ticket_number }}</span>
            <span class="badge badge-status-{{ $ticket->status }} text-uppercase px-2 py-1">
                {{ str_replace('_', ' ', $ticket->status) }}
            </span>
            <span class="badge badge-priority-{{ $ticket->priority }} text-uppercase px-2 py-1">
                {{ $ticket->priority }} Priority
            </span>
        </div>

        @if($ticket->isBreached())
            <div class="badge bg-danger fs-6 px-3 py-2 shadow-sm">
                <i class="fa-solid fa-triangle-exclamation me-1"></i> SLA Resolution Breached!
            </div>
        @elseif($ticket->resolution_due_at && $ticket->isOpen())
            <div class="badge bg-light text-secondary border px-3 py-2">
                <i class="fa-regular fa-clock me-1 text-primary"></i> Resolution Due: {{ $ticket->resolution_due_at->diffForHumans() }}
            </div>
        @endif
    </div>

    <div class="row g-4">
        <!-- Main Conversation Area -->
        <div class="col-lg-8">
            <!-- Ticket Original Request Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-primary text-white rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            {{ strtoupper(substr($ticket->user?->name ?? 'U', 0, 1)) }}
                        </div>
                        <div>
                            <span class="fw-bold d-block text-dark">{{ $ticket->user?->name }}</span>
                            <small class="text-secondary">{{ $ticket->created_at->format('M d, Y h:i A') }}</small>
                        </div>
                    </div>
                    <span class="badge bg-light text-secondary border">Requester Initial Report</span>
                </div>
                <div class="card-body p-4">
                    <h4 class="fw-bold mb-3 text-dark">{{ $ticket->subject }}</h4>
                    <div class="ticket-description text-dark lh-base" style="white-space: pre-line;">
                        {{ $ticket->description }}
                    </div>

                    <!-- Attachments -->
                    @if($ticket->attachments->whereNull('ticket_reply_id')->count() > 0)
                        <div class="mt-4 pt-3 border-top">
                            <span class="small fw-semibold text-secondary d-block mb-2">
                                <i class="fa-solid fa-paperclip me-1"></i> Attachments ({{ $ticket->attachments->whereNull('ticket_reply_id')->count() }}):
                            </span>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($ticket->attachments->whereNull('ticket_reply_id') as $att)
                                    <a href="{{ Storage::url($att->file_path) }}" target="_blank" class="btn btn-sm btn-light border d-inline-flex align-items-center gap-2 text-decoration-none">
                                        <i class="fa-solid fa-file text-primary"></i>
                                        <span class="text-truncate" style="max-width: 180px;">{{ $att->file_name }}</span>
                                        <span class="badge bg-secondary text-white">{{ $att->formatted_size }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Conversation Replies Stream -->
            <div class="conversation-thread mb-4">
                @foreach($ticket->replies as $reply)
                    @if($reply->is_internal_note)
                        {{-- Internal Note: ONLY visible to staff --}}
                        @if(auth()->user()->isStaff())
                            <div class="chat-bubble chat-bubble-internal shadow-sm mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-warning text-dark"><i class="fa-solid fa-lock me-1"></i>Internal Staff Note</span>
                                        <strong class="text-dark">{{ $reply->user?->name }}</strong>
                                    </div>
                                    <small class="text-muted">{{ $reply->created_at->diffForHumans() }} ({{ $reply->created_at->format('M d, H:i') }})</small>
                                </div>
                                <div class="text-dark lh-base" style="white-space: pre-line;">
                                    {{ $reply->message }}
                                </div>
                            </div>
                        @endif
                    @else
                        {{-- Public Reply --}}
                        <div class="chat-bubble {{ $reply->user?->isStaff() ? 'chat-bubble-agent' : 'chat-bubble-customer' }} shadow-sm mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="bg-{{ $reply->user?->isStaff() ? 'success' : 'primary' }} text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 0.8rem;">
                                        {{ strtoupper(substr($reply->user?->name ?? 'U', 0, 1)) }}
                                    </div>
                                    <div>
                                        <strong class="text-dark">{{ $reply->user?->name }}</strong>
                                        <span class="badge bg-{{ $reply->user?->isStaff() ? 'success' : 'secondary' }} text-uppercase ms-1" style="font-size: 0.65rem;">
                                            {{ $reply->user?->role }}
                                        </span>
                                    </div>
                                </div>
                                <small class="text-muted">{{ $reply->created_at->diffForHumans() }} ({{ $reply->created_at->format('M d, H:i') }})</small>
                            </div>
                            <div class="text-dark lh-base" style="white-space: pre-line;">
                                {{ $reply->message }}
                            </div>

                            @if($reply->attachments->count() > 0)
                                <div class="mt-3 pt-2 border-top">
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($reply->attachments as $rAtt)
                                            <a href="{{ Storage::url($rAtt->file_path) }}" target="_blank" class="btn btn-sm btn-white bg-white border d-inline-flex align-items-center gap-2 text-decoration-none">
                                                <i class="fa-solid fa-file text-primary"></i>
                                                <span class="text-truncate" style="max-width: 160px;">{{ $rAtt->file_name }}</span>
                                                <span class="badge bg-secondary text-white">{{ $rAtt->formatted_size }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>

            <!-- Satisfaction Rating Display or Prompt (Customer Only) -->
            @if(in_array($ticket->status, ['resolved', 'closed']))
                <div class="card border-0 shadow-sm rounded-4 mb-4 bg-light">
                    <div class="card-body p-4 text-center">
                        @if($ticket->satisfaction_rating)
                            <h5 class="fw-bold mb-2">Customer Satisfaction Rating</h5>
                            <div class="text-warning fs-3 mb-2">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= $ticket->satisfaction_rating)
                                        <i class="fa-solid fa-star"></i>
                                    @else
                                        <i class="fa-regular fa-star"></i>
                                    @endif
                                @endfor
                            </div>
                            @if($ticket->satisfaction_comment)
                                <p class="text-secondary fst-italic mb-0">"{{ $ticket->satisfaction_comment }}"</p>
                            @endif
                        @elseif(auth()->user()->id === $ticket->user_id)
                            <h5 class="fw-bold mb-2">How was our service?</h5>
                            <p class="text-secondary small mb-3">This ticket is resolved. Please share your rating with our support engineers.</p>
                            <form action="{{ route('tickets.rate', $ticket) }}" method="POST" class="col-md-8 mx-auto">
                                @csrf
                                <div class="d-flex justify-content-center gap-3 mb-3">
                                    @for($i = 1; $i <= 5; $i++)
                                        <div class="form-check form-check-inline m-0">
                                            <input class="form-check-input" type="radio" name="satisfaction_rating" id="star{{ $i }}" value="{{ $i }}" required>
                                            <label class="form-check-label fw-bold" for="star{{ $i }}">{{ $i }} <i class="fa-solid fa-star text-warning"></i></label>
                                        </div>
                                    @endfor
                                </div>
                                <div class="mb-3">
                                    <textarea name="satisfaction_comment" rows="2" class="form-control" placeholder="Optional comments regarding resolution speed, agent helpfulness..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm px-4">Submit Rating</button>
                            </form>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Reply Box Tabs -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-bottom p-0">
                    <ul class="nav nav-tabs card-header-tabs m-0 border-0" id="replyTabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active px-4 py-3 fw-semibold border-0" id="public-reply-tab" data-bs-toggle="tab" data-bs-target="#public-reply" type="button">
                                <i class="fa-solid fa-reply me-2 text-primary"></i>Public Reply
                            </button>
                        </li>
                        @if(auth()->user()->isStaff())
                            <li class="nav-item">
                                <button class="nav-link px-4 py-3 fw-semibold border-0 text-warning" id="internal-note-tab" data-bs-toggle="tab" data-bs-target="#internal-note" type="button">
                                    <i class="fa-solid fa-lock me-2"></i>Internal Technical Note
                                </button>
                            </li>
                        @endif
                    </ul>
                </div>

                <div class="card-body p-4">
                    <div class="tab-content" id="replyTabContent">
                        <!-- Public Reply Tab -->
                        <div class="tab-pane fade show active" id="public-reply" role="tabpanel">
                            <!-- Canned responses dropdown (Staff only) -->
                            @if(auth()->user()->isStaff() && count($cannedResponses) > 0)
                                <div class="mb-3 d-flex align-items-center gap-2">
                                    <label class="small text-secondary fw-semibold mb-0"><i class="fa-solid fa-bolt me-1 text-warning"></i>Insert Quick Reply:</label>
                                    <select class="form-select form-select-sm w-auto" id="cannedResponseSelect" onchange="insertCanned(this)">
                                        <option value="">-- Select Template --</option>
                                        @foreach($cannedResponses as $canned)
                                            <option value="{{ e($canned->content) }}">{{ $canned->title }} ({{ $canned->shortcut }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <form action="{{ route('tickets.reply', $ticket) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <textarea name="message" id="replyMessage" rows="5" class="form-control" placeholder="Type your reply here..." required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="replyAttachments" class="form-label small text-secondary fw-semibold">Attach files (Optional)</label>
                                    <input type="file" name="attachments[]" id="replyAttachments" class="form-control form-control-sm" multiple>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-secondary">Visible to requester and all assigned staff.</small>
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="fa-solid fa-paper-plane me-2"></i>Send Reply
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Internal Note Tab (Staff Only) -->
                        @if(auth()->user()->isStaff())
                            <div class="tab-pane fade" id="internal-note" role="tabpanel">
                                <form action="{{ route('tickets.note', $ticket) }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <textarea name="note" rows="5" class="form-control bg-warning bg-opacity-10 border-warning border-opacity-50" placeholder="Add private notes for staff eyes only (troubleshooting steps, vendor reference numbers, root cause observations)..." required></textarea>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-warning fw-semibold"><i class="fa-solid fa-shield me-1"></i>This note is strictly invisible to the customer.</small>
                                        <button type="submit" class="btn btn-warning px-4">
                                            <i class="fa-solid fa-lock me-2"></i>Save Internal Note
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar / Details Panel -->
        <div class="col-lg-4">
            <!-- Ticket Info Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="card-title fw-bold mb-0"><i class="fa-solid fa-circle-info me-2 text-primary"></i>Ticket Metadata</h5>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3 pb-3 border-bottom">
                        <small class="text-secondary text-uppercase fw-semibold d-block">Requester</small>
                        <div class="fw-semibold text-dark">{{ $ticket->user?->name }}</div>
                        <div class="small text-secondary">{{ $ticket->user?->email }}</div>
                        @if($ticket->user?->phone)
                            <div class="small text-secondary"><i class="fa-solid fa-phone me-1"></i>{{ $ticket->user->phone }}</div>
                        @endif
                    </div>

                    <div class="mb-3 pb-3 border-bottom">
                        <small class="text-secondary text-uppercase fw-semibold d-block">Department & Category</small>
                        <div class="fw-semibold text-dark">{{ $ticket->department?->name ?? 'General' }}</div>
                        <div class="small text-secondary">{{ $ticket->category?->name ?? 'None' }}</div>
                    </div>

                    <div class="mb-3 pb-3 border-bottom">
                        <small class="text-secondary text-uppercase fw-semibold d-block">Assigned Engineer</small>
                        @if($ticket->assignedTo)
                            <div class="fw-semibold text-success"><i class="fa-solid fa-circle-user me-1"></i>{{ $ticket->assignedTo->name }}</div>
                            <div class="small text-secondary">{{ $ticket->assignedTo->email }}</div>
                        @else
                            <div class="text-muted fst-italic">Currently unassigned</div>
                        @endif
                    </div>

                    <div class="mb-3 pb-3 border-bottom">
                        <small class="text-secondary text-uppercase fw-semibold d-block">SLA & Timestamps</small>
                        <div class="small d-flex justify-content-between mb-1">
                            <span class="text-secondary">Created:</span>
                            <span class="fw-semibold">{{ $ticket->created_at->format('Y-m-d H:i') }}</span>
                        </div>
                        @if($ticket->resolution_due_at)
                            <div class="small d-flex justify-content-between mb-1">
                                <span class="text-secondary">Resolution Target:</span>
                                <span class="fw-semibold {{ $ticket->isBreached() ? 'text-danger' : 'text-dark' }}">
                                    {{ $ticket->resolution_due_at->format('Y-m-d H:i') }}
                                </span>
                            </div>
                        @endif
                        @if($ticket->resolved_at)
                            <div class="small d-flex justify-content-between mb-1">
                                <span class="text-secondary">Resolved At:</span>
                                <span class="fw-semibold text-success">{{ $ticket->resolved_at->format('Y-m-d H:i') }}</span>
                            </div>
                        @endif
                    </div>

                    <!-- Staff Operations Panel -->
                    @if(auth()->user()->isStaff())
                        <div class="mt-3">
                            <h6 class="fw-bold text-dark mb-3">Ticket Actions</h6>

                            <!-- Change Status Form -->
                            <form action="{{ route('tickets.status', $ticket) }}" method="POST" class="mb-3">
                                @csrf
                                @method('PATCH')
                                <label class="small text-secondary fw-semibold">Update Status</label>
                                <div class="input-group input-group-sm">
                                    <select name="status" class="form-select">
                                        <option value="open" {{ $ticket->status === 'open' ? 'selected' : '' }}>Open</option>
                                        <option value="in_progress" {{ $ticket->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                        <option value="pending" {{ $ticket->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="resolved" {{ $ticket->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                                        <option value="closed" {{ $ticket->status === 'closed' ? 'selected' : '' }}>Closed</option>
                                    </select>
                                    <button class="btn btn-outline-primary" type="submit">Update</button>
                                </div>
                            </form>

                            <!-- Assign Agent Form -->
                            <form action="{{ route('tickets.assign', $ticket) }}" method="POST" class="mb-3">
                                @csrf
                                @method('PATCH')
                                <label class="small text-secondary fw-semibold">Assign Agent</label>
                                <div class="input-group input-group-sm">
                                    <select name="assigned_to_id" class="form-select">
                                        <option value="">-- Unassigned --</option>
                                        @foreach($agents as $agent)
                                            <option value="{{ $agent->id }}" {{ $ticket->assigned_to_id === $agent->id ? 'selected' : '' }}>
                                                {{ $agent->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button class="btn btn-outline-success" type="submit">Assign</button>
                                </div>
                            </form>

                            <!-- Change Priority Form -->
                            <form action="{{ route('tickets.priority', $ticket) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <label class="small text-secondary fw-semibold">Change Priority / SLA</label>
                                <div class="input-group input-group-sm">
                                    <select name="priority" class="form-select">
                                        <option value="low" {{ $ticket->priority === 'low' ? 'selected' : '' }}>Low</option>
                                        <option value="medium" {{ $ticket->priority === 'medium' ? 'selected' : '' }}>Medium</option>
                                        <option value="high" {{ $ticket->priority === 'high' ? 'selected' : '' }}>High</option>
                                        <option value="urgent" {{ $ticket->priority === 'urgent' ? 'selected' : '' }}>Urgent</option>
                                    </select>
                                    <button class="btn btn-outline-danger" type="submit">Apply</button>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Activity Audit Trail -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="card-title fw-bold mb-0"><i class="fa-solid fa-list-timeline me-2 text-primary"></i>Audit Trail</h5>
                </div>
                <div class="card-body p-3">
                    <div class="timeline" style="max-height: 380px; overflow-y: auto;">
                        @foreach($ticket->activities as $act)
                            <div class="d-flex align-items-start gap-2 mb-3 pb-2 border-bottom border-light">
                                <div class="small text-secondary">
                                    <i class="fa-solid fa-circle-dot text-primary" style="font-size: 0.6rem;"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="small fw-semibold text-dark">{{ $act->description }}</div>
                                    <div class="text-secondary" style="font-size: 0.7rem;">
                                        {{ $act->created_at->format('M d, Y H:i:s') }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function insertCanned(select) {
        if (!select.value) return;
        const textarea = document.getElementById('replyMessage');
        if (textarea.value) {
            textarea.value += "\n\n" + select.value;
        } else {
            textarea.value = select.value;
        }
    }
</script>
@endsection
