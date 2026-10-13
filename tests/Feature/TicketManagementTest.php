<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\SlaPlan;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Department $department;

    protected User $customer;

    protected User $agent;

    protected User $admin;

    protected SlaPlan $sla;

    protected function setUp(): void
    {
        parent::setUp();

        $this->department = Department::create([
            'name' => 'IT Infrastructure',
            'slug' => 'it-infrastructure',
            'is_active' => true,
        ]);

        $this->sla = SlaPlan::create([
            'name' => 'High SLA',
            'priority' => Ticket::PRIORITY_HIGH,
            'response_time_hours' => 4,
            'resolution_time_hours' => 12,
            'is_active' => true,
        ]);

        $this->customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $this->agent = User::factory()->create(['role' => User::ROLE_AGENT, 'department_id' => $this->department->id]);
        $this->admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    }

    public function test_customer_can_create_ticket_with_sla_calculation(): void
    {
        $response = $this->actingAs($this->customer)->post('/tickets', [
            'department_id' => $this->department->id,
            'priority' => Ticket::PRIORITY_HIGH,
            'subject' => 'VPN gateway disconnection issue',
            'description' => 'Cannot establish connection to server node.',
        ]);

        $this->assertDatabaseHas('tickets', [
            'user_id' => $this->customer->id,
            'subject' => 'VPN gateway disconnection issue',
            'priority' => Ticket::PRIORITY_HIGH,
            'status' => Ticket::STATUS_OPEN,
        ]);

        $ticket = Ticket::where('subject', 'VPN gateway disconnection issue')->first();
        $this->assertNotNull($ticket->resolution_due_at);
        $this->assertNotNull($ticket->ticket_number);
        $response->assertRedirect(route('tickets.show', $ticket));
    }

    public function test_agent_can_reply_and_add_internal_note(): void
    {
        $ticket = Ticket::create([
            'ticket_number' => Ticket::generateTicketNumber(),
            'user_id' => $this->customer->id,
            'department_id' => $this->department->id,
            'subject' => 'Network defect',
            'description' => 'Details...',
            'priority' => Ticket::PRIORITY_MEDIUM,
            'status' => Ticket::STATUS_OPEN,
        ]);

        // Agent public reply
        $this->actingAs($this->agent)->post("/tickets/{$ticket->id}/reply", [
            'message' => 'We are reviewing the network logs.',
        ]);

        $this->assertDatabaseHas('ticket_replies', [
            'ticket_id' => $ticket->id,
            'user_id' => $this->agent->id,
            'message' => 'We are reviewing the network logs.',
            'is_internal_note' => false,
        ]);

        // Agent internal note
        $this->actingAs($this->agent)->post("/tickets/{$ticket->id}/note", [
            'note' => 'Internal switch 3 port error diagnosed.',
        ]);

        $this->assertDatabaseHas('ticket_replies', [
            'ticket_id' => $ticket->id,
            'user_id' => $this->agent->id,
            'message' => 'Internal switch 3 port error diagnosed.',
            'is_internal_note' => true,
        ]);
    }

    public function test_agent_can_update_ticket_status(): void
    {
        $ticket = Ticket::create([
            'ticket_number' => Ticket::generateTicketNumber(),
            'user_id' => $this->customer->id,
            'department_id' => $this->department->id,
            'subject' => 'Resolution test',
            'description' => 'Details...',
            'priority' => Ticket::PRIORITY_LOW,
            'status' => Ticket::STATUS_OPEN,
        ]);

        $this->actingAs($this->agent)->patch("/tickets/{$ticket->id}/status", [
            'status' => Ticket::STATUS_RESOLVED,
        ]);

        $ticket->refresh();
        $this->assertEquals(Ticket::STATUS_RESOLVED, $ticket->status);
        $this->assertNotNull($ticket->resolved_at);
    }

    public function test_customer_can_submit_csat_satisfaction_rating(): void
    {
        $ticket = Ticket::create([
            'ticket_number' => Ticket::generateTicketNumber(),
            'user_id' => $this->customer->id,
            'department_id' => $this->department->id,
            'subject' => 'Rating test',
            'description' => 'Details...',
            'priority' => Ticket::PRIORITY_LOW,
            'status' => Ticket::STATUS_RESOLVED,
            'resolved_at' => now(),
        ]);

        $response = $this->actingAs($this->customer)->post("/tickets/{$ticket->id}/rate", [
            'satisfaction_rating' => 5,
            'satisfaction_comment' => 'Outstanding support experience!',
        ]);

        $ticket->refresh();
        $this->assertEquals(5, $ticket->satisfaction_rating);
        $this->assertEquals('Outstanding support experience!', $ticket->satisfaction_comment);
        $response->assertSessionHas('success');
    }
}
