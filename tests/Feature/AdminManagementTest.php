<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $agent;

    protected User $customer;

    protected Department $department;

    protected function setUp(): void
    {
        parent::setUp();

        $this->department = Department::create([
            'name' => 'Technical Support',
            'slug' => 'technical-support',
            'is_active' => true,
        ]);

        $this->admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'department_id' => $this->department->id,
        ]);

        $this->agent = User::factory()->create([
            'role' => User::ROLE_AGENT,
            'department_id' => $this->department->id,
        ]);

        $this->customer = User::factory()->create([
            'role' => User::ROLE_CUSTOMER,
        ]);
    }

    public function test_customer_cannot_access_admin_portal(): void
    {
        $response = $this->actingAs($this->customer)->get('/admin/users');
        $response->assertStatus(403);

        $responseReport = $this->actingAs($this->customer)->get('/admin/reports');
        $responseReport->assertStatus(403);
    }

    public function test_admin_can_view_and_create_users(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/users');
        $response->assertOk();
        $response->assertSee('User Management');

        $createResponse = $this->actingAs($this->admin)->post('/admin/users', [
            'name' => 'New Support Specialist',
            'email' => 'new.specialist@helpdesk.local',
            'password' => 'secret12345',
            'password_confirmation' => 'secret12345',
            'role' => User::ROLE_AGENT,
            'department_id' => $this->department->id,
            'phone' => '+1-555-0199',
            'is_active' => 1,
        ]);

        $createResponse->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'new.specialist@helpdesk.local',
            'role' => User::ROLE_AGENT,
        ]);
    }

    public function test_admin_can_toggle_user_status(): void
    {
        $targetUser = User::factory()->create([
            'role' => User::ROLE_AGENT,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->patch("/admin/users/{$targetUser->id}/toggle");
        $response->assertSessionHas('success');

        $targetUser->refresh();
        $this->assertFalse((bool) $targetUser->is_active);
    }

    public function test_admin_can_manage_departments(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/departments', [
            'name' => 'Human Resources',
            'description' => 'Employee relations and benefits support',
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.departments.index'));
        $this->assertDatabaseHas('departments', [
            'name' => 'Human Resources',
            'slug' => 'human-resources',
        ]);
    }

    public function test_admin_can_manage_sla_plans(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/sla', [
            'name' => 'Urgent Severity SLA',
            'priority' => Ticket::PRIORITY_URGENT,
            'response_time_hours' => 1,
            'resolution_time_hours' => 4,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.sla.index'));
        $this->assertDatabaseHas('sla_plans', [
            'name' => 'Urgent Severity SLA',
            'priority' => Ticket::PRIORITY_URGENT,
        ]);
    }

    public function test_admin_can_view_reports_and_export_csv(): void
    {
        // Create sample ticket for metrics
        Ticket::create([
            'ticket_number' => Ticket::generateTicketNumber(),
            'user_id' => $this->customer->id,
            'assigned_to_id' => $this->agent->id,
            'department_id' => $this->department->id,
            'subject' => 'Report metric ticket',
            'description' => 'Test metric calculation',
            'priority' => Ticket::PRIORITY_HIGH,
            'status' => Ticket::STATUS_RESOLVED,
            'first_responded_at' => now()->subHours(2),
            'resolved_at' => now()->subHour(),
            'satisfaction_rating' => 5,
        ]);

        $reportPageResponse = $this->actingAs($this->admin)->get('/admin/reports');
        $reportPageResponse->assertOk();
        $reportPageResponse->assertSee('Service Performance');

        $exportResponse = $this->actingAs($this->admin)->get('/admin/reports/export');
        $exportResponse->assertOk();
        $exportResponse->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }
}
