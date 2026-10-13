<?php

namespace App\Services;

use App\Models\BulkImportLog;
use App\Models\Category;
use App\Models\Department;
use App\Models\KnowledgeBaseArticle;
use App\Models\SlaPlan;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BulkImportService
{
    /**
     * Process bulk CSV import for the given entity type.
     *
     * @return array{success: bool, total: int, imported: int, failed: int, errors: array<int, string>, log: BulkImportLog}
     */
    public function import(string $entityType, UploadedFile $file, User $admin): array
    {
        $rows = $this->parseCsv($file);
        if (empty($rows)) {
            throw new Exception('The uploaded CSV file is empty or formatted incorrectly.');
        }

        $total = count($rows);
        $imported = 0;
        $failed = 0;
        $errors = [];

        DB::beginTransaction();

        try {
            foreach ($rows as $index => $row) {
                $rowNum = $index + 2; // Accounting for 1-based index and header line

                try {
                    $this->importRow($entityType, $row, $rowNum);
                    $imported++;
                } catch (Exception $e) {
                    $failed++;
                    $errors[] = "Row {$rowNum}: ".$e->getMessage();
                }
            }

            $status = $failed === 0 ? 'completed' : ($imported > 0 ? 'partial' : 'failed');

            $log = BulkImportLog::create([
                'user_id' => $admin->id,
                'entity_type' => $entityType,
                'file_name' => $file->getClientOriginalName(),
                'total_rows' => $total,
                'successful_rows' => $imported,
                'failed_rows' => $failed,
                'status' => $status,
                'errors' => $errors,
            ]);

            DB::commit();

            return [
                'success' => $imported > 0 || $total === 0,
                'total' => $total,
                'imported' => $imported,
                'failed' => $failed,
                'errors' => $errors,
                'log' => $log,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Route and import single row based on entity type.
     */
    protected function importRow(string $entityType, array $row, int $rowNum): void
    {
        match ($entityType) {
            'users' => $this->importUserRow($row, $rowNum),
            'tickets' => $this->importTicketRow($row, $rowNum),
            'departments' => $this->importDepartmentRow($row, $rowNum),
            'categories' => $this->importCategoryRow($row, $rowNum),
            'articles' => $this->importArticleRow($row, $rowNum),
            default => throw new Exception("Unsupported entity type: {$entityType}"),
        };
    }

    /**
     * Import a user row.
     */
    protected function importUserRow(array $row, int $rowNum): void
    {
        $name = trim($row['name'] ?? '');
        $email = trim($row['email'] ?? '');
        $role = strtolower(trim($row['role'] ?? 'customer'));
        $departmentName = trim($row['department'] ?? '');
        $phone = trim($row['phone'] ?? '');
        $password = trim($row['password'] ?? 'password123');

        if (empty($name) || empty($email)) {
            throw new Exception('Name and email are required fields.');
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format: '{$email}'.");
        }

        if (User::where('email', $email)->exists()) {
            throw new Exception("A user with email '{$email}' already exists.");
        }

        if (! in_array($role, [User::ROLE_ADMIN, User::ROLE_AGENT, User::ROLE_CUSTOMER], true)) {
            $role = User::ROLE_CUSTOMER;
        }

        $departmentId = null;
        if (! empty($departmentName)) {
            $department = Department::where('name', $departmentName)
                ->orWhere('slug', Str::slug($departmentName))
                ->orWhereRaw('LOWER(name) = ?', [strtolower($departmentName)])
                ->first();
            $departmentId = $department?->id;
        }

        User::create([
            'name' => $name,
            'email' => $email,
            'role' => $role,
            'department_id' => $departmentId,
            'phone' => $phone ?: null,
            'password' => Hash::make($password),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }

    /**
     * Import a ticket row.
     */
    protected function importTicketRow(array $row, int $rowNum): void
    {
        $customerEmail = trim($row['customer_email'] ?? '');
        $subject = trim($row['subject'] ?? '');
        $description = trim($row['description'] ?? '');
        $departmentName = trim($row['department'] ?? '');
        $categoryName = trim($row['category'] ?? '');
        $priority = strtolower(trim($row['priority'] ?? 'medium'));
        $status = strtolower(trim($row['status'] ?? 'open'));
        $assignedEmail = trim($row['assigned_to_email'] ?? '');

        if (empty($customerEmail) || empty($subject) || empty($description)) {
            throw new Exception('customer_email, subject, and description are required.');
        }

        // Find or create customer
        $customer = User::where('email', $customerEmail)->first();
        if (! $customer) {
            $customer = User::create([
                'name' => explode('@', $customerEmail)[0],
                'email' => $customerEmail,
                'role' => User::ROLE_CUSTOMER,
                'password' => Hash::make('password123'),
                'is_active' => true,
            ]);
        }

        // Find department or fallback to first
        $department = Department::where('name', $departmentName)
            ->orWhere('slug', Str::slug($departmentName))
            ->orWhereRaw('LOWER(name) = ?', [strtolower($departmentName)])
            ->first() ?? Department::first();

        if (! $department) {
            throw new Exception('No department found. Please create or import departments first.');
        }

        // Find category
        $category = null;
        if (! empty($categoryName)) {
            $category = Category::where('name', $categoryName)
                ->orWhere('slug', Str::slug($categoryName))
                ->orWhereRaw('LOWER(name) = ?', [strtolower($categoryName)])
                ->first();
        }

        // Find assigned agent
        $assignedUser = null;
        if (! empty($assignedEmail)) {
            $assignedUser = User::where('email', $assignedEmail)->first();
        }

        if (! in_array($priority, [Ticket::PRIORITY_LOW, Ticket::PRIORITY_MEDIUM, Ticket::PRIORITY_HIGH, Ticket::PRIORITY_URGENT], true)) {
            $priority = Ticket::PRIORITY_MEDIUM;
        }

        if (! in_array($status, [Ticket::STATUS_OPEN, Ticket::STATUS_IN_PROGRESS, Ticket::STATUS_PENDING, Ticket::STATUS_RESOLVED, Ticket::STATUS_CLOSED], true)) {
            $status = Ticket::STATUS_OPEN;
        }

        // Calculate SLA due date
        $sla = SlaPlan::where('priority', $priority)->where('is_active', true)->first();
        $responseDue = $sla ? Carbon::now()->addHours($sla->response_time_hours) : Carbon::now()->addHours(24);
        $resolutionDue = $sla ? Carbon::now()->addHours($sla->resolution_time_hours) : Carbon::now()->addHours(72);

        $ticket = Ticket::create([
            'ticket_number' => Ticket::generateTicketNumber(),
            'user_id' => $customer->id,
            'assigned_to_id' => $assignedUser?->id,
            'department_id' => $department->id,
            'category_id' => $category?->id,
            'sla_plan_id' => $sla?->id,
            'subject' => $subject,
            'description' => $description,
            'priority' => $priority,
            'status' => $status,
            'response_due_at' => $responseDue,
            'resolution_due_at' => $resolutionDue,
            'resolved_at' => $status === Ticket::STATUS_RESOLVED ? now() : null,
            'closed_at' => $status === Ticket::STATUS_CLOSED ? now() : null,
        ]);

        TicketActivity::log($ticket->id, null, 'imported', 'Ticket imported via Bulk Import by Admin');
    }

    /**
     * Import a department row.
     */
    protected function importDepartmentRow(array $row, int $rowNum): void
    {
        $name = trim($row['name'] ?? '');
        $email = trim($row['email'] ?? '');
        $description = trim($row['description'] ?? '');
        $slug = trim($row['slug'] ?? '') ?: Str::slug($name);

        if (empty($name)) {
            throw new Exception('Department name is required.');
        }

        if (Department::where('slug', $slug)->exists()) {
            throw new Exception("Department with slug '{$slug}' already exists.");
        }

        Department::create([
            'name' => $name,
            'slug' => $slug,
            'email' => $email ?: null,
            'description' => $description ?: null,
            'is_active' => true,
        ]);
    }

    /**
     * Import a category row.
     */
    protected function importCategoryRow(array $row, int $rowNum): void
    {
        $name = trim($row['name'] ?? '');
        $departmentName = trim($row['department'] ?? '');
        $description = trim($row['description'] ?? '');
        $color = trim($row['color'] ?? '#3b82f6');
        $slug = trim($row['slug'] ?? '') ?: Str::slug($name);

        if (empty($name)) {
            throw new Exception('Category name is required.');
        }

        if (Category::where('slug', $slug)->exists()) {
            throw new Exception("Category with slug '{$slug}' already exists.");
        }

        $department = null;
        if (! empty($departmentName)) {
            $department = Department::where('name', $departmentName)
                ->orWhere('slug', Str::slug($departmentName))
                ->orWhereRaw('LOWER(name) = ?', [strtolower($departmentName)])
                ->first();
        }

        Category::create([
            'department_id' => $department?->id,
            'name' => $name,
            'slug' => $slug,
            'description' => $description ?: null,
            'color' => $color,
            'is_active' => true,
        ]);
    }

    /**
     * Import a knowledge base article row.
     */
    protected function importArticleRow(array $row, int $rowNum): void
    {
        $title = trim($row['title'] ?? '');
        $content = trim($row['content'] ?? '');
        $categoryName = trim($row['category'] ?? '');
        $authorEmail = trim($row['author_email'] ?? '');
        $slug = trim($row['slug'] ?? '') ?: Str::slug($title);

        if (empty($title) || empty($content)) {
            throw new Exception('Article title and content are required.');
        }

        if (KnowledgeBaseArticle::where('slug', $slug)->exists()) {
            throw new Exception("Article with slug '{$slug}' already exists.");
        }

        $category = null;
        if (! empty($categoryName)) {
            $category = Category::where('name', $categoryName)
                ->orWhere('slug', Str::slug($categoryName))
                ->orWhereRaw('LOWER(name) = ?', [strtolower($categoryName)])
                ->first();
        }

        $author = null;
        if (! empty($authorEmail)) {
            $author = User::where('email', $authorEmail)->first();
        }

        if (! $author) {
            $author = User::where('role', User::ROLE_ADMIN)->first() ?? User::first();
        }

        KnowledgeBaseArticle::create([
            'category_id' => $category?->id,
            'author_id' => $author->id,
            'title' => $title,
            'slug' => $slug,
            'content' => $content,
            'is_published' => true,
        ]);
    }

    /**
     * Parse CSV file into an array of associative rows.
     *
     * @return list<array<string, string>>
     */
    public function parseCsv(UploadedFile $file): array
    {
        $path = $file->getRealPath();
        $handle = fopen($path, 'r');
        if (! $handle) {
            return [];
        }

        // Remove UTF-8 BOM if present
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        // Determine delimiter
        $firstLine = fgets($handle);
        rewind($handle);
        if ($bom === "\xEF\xBB\xBF") {
            fread($handle, 3);
        }

        $delimiter = str_contains($firstLine, ';') ? ';' : ',';

        $header = fgetcsv($handle, 0, $delimiter);
        if (! $header) {
            fclose($handle);

            return [];
        }

        // Clean headers: lowercase and trim
        $header = array_map(fn ($col) => strtolower(trim((string) $col)), $header);

        $rows = [];
        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (empty(array_filter($data))) {
                continue; // Skip empty lines
            }

            $row = [];
            foreach ($header as $keyIndex => $colName) {
                $row[$colName] = $data[$keyIndex] ?? '';
            }
            $rows[] = $row;
        }

        fclose($handle);

        return $rows;
    }

    /**
     * Generate template CSV content for the given entity type.
     */
    public function getCsvTemplate(string $entityType): string
    {
        return match ($entityType) {
            'users' => "name,email,role,department,phone,password\n".
                "Michael Scott,michael.scott@company.internal,agent,IT Support,+1-555-0101,SecurePass123!\n".
                "Pam Beesly,pam.beesly@company.internal,customer,Human Resources,+1-555-0102,SecurePass123!\n".
                "Dwight Schrute,dwight.schrute@company.internal,admin,Network & Infrastructure,+1-555-0103,SecurePass123!\n",

            'tickets' => "customer_email,subject,description,department,category,priority,status,assigned_to_email\n".
                "pam.beesly@company.internal,Printer toner depleted in HR annex,The color laser printer is out of cyan and magenta cartridges,IT Support,Hardware & Peripherals,medium,open,agent.john@helpdesk.local\n".
                "user.alice@example.com,Unable to connect to branch VPN,VPN times out after entering credentials,Network & Infrastructure,VPN & Internet Access,high,in_progress,agent.sarah@helpdesk.local\n",

            'departments' => "name,slug,email,description\n".
                "Customer Success,customer-success,success@company.internal,Customer satisfaction and onboarding support\n".
                "Legal & Compliance,legal-compliance,legal@company.internal,Contracts and compliance policies\n",

            'categories' => "name,slug,department,color,description\n".
                "Mobile Device Management,mobile-device-mgmt,IT Support,#0284c7,Smartphones and tablet configurations\n".
                "Security Incidents,security-incidents,Network & Infrastructure,#dc2626,Phishing emails and suspicious link alerts\n",

            'articles' => "title,slug,category,author_email,content\n".
                "\"How to request a replacement laptop\",\"how-to-request-replacement-laptop\",\"Hardware & Peripherals\",\"admin@helpdesk.local\",\"Submit an equipment request form via the help desk portal with department manager approval.\"\n",

            default => "id,name\n",
        };
    }
}
