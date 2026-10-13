<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class BulkImportTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $this->customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
    }

    public function test_admin_can_access_bulk_import_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/bulk-import');
        $response->assertStatus(200);
        $response->assertSee('Bulk Data Import Center');
    }

    public function test_customer_cannot_access_bulk_import_dashboard(): void
    {
        $response = $this->actingAs($this->customer)->get('/admin/bulk-import');
        $response->assertStatus(403);
    }

    public function test_admin_can_download_sample_csv_template(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/bulk-import/template/users');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('name,email,role,department,phone,password', $response->getContent());
    }

    public function test_admin_can_bulk_import_departments_from_csv(): void
    {
        $csvContent = "name,slug,email,description\n".
            "Logistics,logistics,logistics@company.internal,Warehouse and fleet dispatch\n".
            "Procurement,procurement,procurement@company.internal,Equipment purchases\n";

        $file = UploadedFile::fake()->createWithContent('departments.csv', $csvContent);

        $response = $this->actingAs($this->admin)->post('/admin/bulk-import', [
            'entity_type' => 'departments',
            'csv_file' => $file,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('departments', ['slug' => 'logistics', 'name' => 'Logistics']);
        $this->assertDatabaseHas('departments', ['slug' => 'procurement', 'name' => 'Procurement']);
        $this->assertDatabaseHas('bulk_import_logs', [
            'entity_type' => 'departments',
            'successful_rows' => 2,
            'status' => 'completed',
        ]);
    }

    public function test_admin_can_bulk_import_users_from_csv(): void
    {
        Department::create(['name' => 'IT Support', 'slug' => 'it-support', 'is_active' => true]);

        $csvContent = "name,email,role,department,phone,password\n".
            "Stanley Hudson,stanley@company.internal,customer,IT Support,+1-555-0901,Secret123!\n".
            "Angela Martin,angela@company.internal,agent,IT Support,+1-555-0902,Secret123!\n";

        $file = UploadedFile::fake()->createWithContent('users.csv', $csvContent);

        $response = $this->actingAs($this->admin)->post('/admin/bulk-import', [
            'entity_type' => 'users',
            'csv_file' => $file,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', ['email' => 'stanley@company.internal', 'role' => 'customer']);
        $this->assertDatabaseHas('users', ['email' => 'angela@company.internal', 'role' => 'agent']);
    }

    public function test_admin_can_bulk_import_tickets_from_csv(): void
    {
        Department::create(['name' => 'IT Support', 'slug' => 'it-support', 'is_active' => true]);

        $csvContent = "customer_email,subject,description,department,category,priority,status,assigned_to_email\n".
            "employee.test@company.internal,Monitor flickering on desk 4,Display port cable replaced but issue persists,IT Support,,high,open,\n";

        $file = UploadedFile::fake()->createWithContent('tickets.csv', $csvContent);

        $response = $this->actingAs($this->admin)->post('/admin/bulk-import', [
            'entity_type' => 'tickets',
            'csv_file' => $file,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('tickets', [
            'subject' => 'Monitor flickering on desk 4',
            'priority' => 'high',
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'employee.test@company.internal',
        ]);
    }
}
