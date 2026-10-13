<?php

namespace Database\Seeders;

use App\Models\CannedResponse;
use App\Models\Category;
use App\Models\Department;
use App\Models\KnowledgeBaseArticle;
use App\Models\SlaPlan;
use App\Models\SystemSetting;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\TicketReply;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Departments
        $deptIT = Department::create([
            'name' => 'IT Support',
            'slug' => 'it-support',
            'email' => 'it-support@helpdesk.local',
            'description' => 'Technical assistance, hardware, workstation, and software support.',
            'is_active' => true,
        ]);

        $deptNetwork = Department::create([
            'name' => 'Network & Infrastructure',
            'slug' => 'network-infra',
            'email' => 'network@helpdesk.local',
            'description' => 'LAN/WAN, VPN, firewall, switches, and telecommunication infrastructure.',
            'is_active' => true,
        ]);

        $deptHR = Department::create([
            'name' => 'Human Resources',
            'slug' => 'human-resources',
            'email' => 'hr@helpdesk.local',
            'description' => 'Employee relations, benefits, onboarding, and payroll inquiries.',
            'is_active' => true,
        ]);

        $deptFacilities = Department::create([
            'name' => 'Facilities & Maintenance',
            'slug' => 'facilities-maintenance',
            'email' => 'facilities@helpdesk.local',
            'description' => 'Building access, air conditioning, office furniture, and electrical systems.',
            'is_active' => true,
        ]);

        $deptFinance = Department::create([
            'name' => 'Finance & Procurement',
            'slug' => 'finance-procurement',
            'email' => 'finance@helpdesk.local',
            'description' => 'Expense claims, software licenses procurement, and vendor billing.',
            'is_active' => true,
        ]);

        // 2. Categories
        $catSoftware = Category::create([
            'department_id' => $deptIT->id,
            'name' => 'Software & Applications',
            'slug' => 'software-applications',
            'description' => 'Operating systems, MS Office, enterprise ERP, and business tools.',
            'color' => '#2563eb',
            'is_active' => true,
        ]);

        $catHardware = Category::create([
            'department_id' => $deptIT->id,
            'name' => 'Hardware & Peripherals',
            'slug' => 'hardware-peripherals',
            'description' => 'Monitors, laptops, docking stations, mice, and keyboards.',
            'color' => '#ea580c',
            'is_active' => true,
        ]);

        $catNetwork = Category::create([
            'department_id' => $deptNetwork->id,
            'name' => 'VPN & Internet Access',
            'slug' => 'vpn-internet-access',
            'description' => 'Remote VPN tunneling, WiFi disconnects, and firewall whitelisting.',
            'color' => '#059669',
            'is_active' => true,
        ]);

        $catAccess = Category::create([
            'department_id' => $deptIT->id,
            'name' => 'Account & Access Permissions',
            'slug' => 'account-permissions',
            'description' => 'Password reset, MFA reconfiguration, Active Directory roles.',
            'color' => '#7c3aed',
            'is_active' => true,
        ]);

        $catFacilities = Category::create([
            'department_id' => $deptFacilities->id,
            'name' => 'Desk & Office Ergonomics',
            'slug' => 'office-ergonomics',
            'description' => 'Standing desks, ergonomic chairs, power strips, and office lighting.',
            'color' => '#d97706',
            'is_active' => true,
        ]);

        // 3. SLA Plans
        $slaUrgent = SlaPlan::create([
            'name' => 'Critical Incident SLA',
            'priority' => Ticket::PRIORITY_URGENT,
            'response_time_hours' => 1,
            'resolution_time_hours' => 4,
            'description' => 'System outages impacting entire offices or core business transactions.',
            'is_active' => true,
        ]);

        $slaHigh = SlaPlan::create([
            'name' => 'High Priority SLA',
            'priority' => Ticket::PRIORITY_HIGH,
            'response_time_hours' => 4,
            'resolution_time_hours' => 12,
            'description' => 'Major degradation affecting multiple users without full outage.',
            'is_active' => true,
        ]);

        $slaMedium = SlaPlan::create([
            'name' => 'Standard SLA',
            'priority' => Ticket::PRIORITY_MEDIUM,
            'response_time_hours' => 12,
            'resolution_time_hours' => 48,
            'description' => 'Standard user requests, minor defects, and single-user disruptions.',
            'is_active' => true,
        ]);

        $slaLow = SlaPlan::create([
            'name' => 'Low Priority SLA',
            'priority' => Ticket::PRIORITY_LOW,
            'response_time_hours' => 24,
            'resolution_time_hours' => 96,
            'description' => 'Non-urgent questions, documentation requests, and cosmetic feedback.',
            'is_active' => true,
        ]);

        // 4. Users (Admin, Agents, Customers)
        $admin = User::create([
            'name' => 'System Administrator',
            'email' => 'admin@helpdesk.local',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
            'department_id' => $deptIT->id,
            'phone' => '+1 (555) 019-2834',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $agent1 = User::create([
            'name' => 'John Carter (Lead Agent)',
            'email' => 'agent.john@helpdesk.local',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_AGENT,
            'department_id' => $deptIT->id,
            'phone' => '+1 (555) 019-3342',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $agent2 = User::create([
            'name' => 'Sarah Connor (Network Engineer)',
            'email' => 'agent.sarah@helpdesk.local',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_AGENT,
            'department_id' => $deptNetwork->id,
            'phone' => '+1 (555) 019-7788',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $customer1 = User::create([
            'name' => 'Alice Morgan',
            'email' => 'user.alice@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_CUSTOMER,
            'department_id' => $deptFinance->id,
            'phone' => '+1 (555) 012-9921',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $customer2 = User::create([
            'name' => 'Bob Davis',
            'email' => 'user.bob@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_CUSTOMER,
            'department_id' => $deptHR->id,
            'phone' => '+1 (555) 014-4412',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // 5. Canned Responses
        CannedResponse::create([
            'user_id' => $admin->id,
            'department_id' => null,
            'title' => 'Standard Ticket Acknowledgment',
            'shortcut' => '!ack',
            'content' => "Hello,\n\nThank you for reaching out to the Help Desk. We have received your request and our technical team is currently reviewing the details. An engineer will be assigned shortly.\n\nBest regards,\nHelp Desk Support Team",
            'is_shared' => true,
        ]);

        CannedResponse::create([
            'user_id' => $agent1->id,
            'department_id' => $deptIT->id,
            'title' => 'VPN Reset Protocol',
            'shortcut' => '!vpn-reset',
            'content' => "Hello,\n\nPlease follow these steps to reset your local VPN cache:\n1. Disconnect from any active VPN profile.\n2. Open command prompt and run: ipconfig /flushdns\n3. Restart the VPN client and reconnect using your corporate domain credentials.\n\nLet us know if the issue persists.",
            'is_shared' => true,
        ]);

        CannedResponse::create([
            'user_id' => $agent1->id,
            'department_id' => null,
            'title' => 'Resolution & Survey Request',
            'shortcut' => '!resolved',
            'content' => "Hello,\n\nWe have verified that the issue has been resolved. Please review the changes and let us know if you require any further assistance.\n\nIf everything is working properly, please take a moment to rate this service.\n\nThank you for your cooperation!",
            'is_shared' => true,
        ]);

        // 6. Knowledge Base Articles
        KnowledgeBaseArticle::create([
            'category_id' => $catNetwork->id,
            'author_id' => $agent2->id,
            'title' => 'How to Connect to Corporate VPN from Remote Locations',
            'slug' => 'how-to-connect-to-corporate-vpn',
            'content' => "### Connecting to the Corporate VPN\n\n1. Ensure you have installed the official company VPN client.\n2. In the Gateway Server field, enter: `vpn.internal.company.net`\n3. Enter your corporate email and domain password.\n4. When prompted, approve the MFA notification on your authenticator device.\n\n#### Troubleshooting Common Errors\n- **Error 800**: Check that your local internet connection is active.\n- **Error 691**: Verify that your password has not expired in Active Directory.",
            'is_published' => true,
            'views_count' => 142,
            'helpful_count' => 38,
            'not_helpful_count' => 2,
        ]);

        KnowledgeBaseArticle::create([
            'category_id' => $catAccess->id,
            'author_id' => $admin->id,
            'title' => 'Self-Service Domain Password Reset Guidelines',
            'slug' => 'self-service-password-reset-guidelines',
            'content' => "### Password Policy Requirements\n\nAll passwords must meet the following enterprise complexity standards:\n- At least 12 characters in length.\n- Must contain uppercase, lowercase, numbers, and at least one special character.\n- Cannot match any of your previous 5 passwords.\n- Passwords expire every 90 days.\n\nTo reset your password, visit the self-service portal or click 'Forgot Password' on the login screen.",
            'is_published' => true,
            'views_count' => 210,
            'helpful_count' => 54,
            'not_helpful_count' => 1,
        ]);

        KnowledgeBaseArticle::create([
            'category_id' => $catSoftware->id,
            'author_id' => $agent1->id,
            'title' => 'Configuring Enterprise Email in Microsoft Outlook and Mobile Devices',
            'slug' => 'configuring-enterprise-email-outlook',
            'content' => "### Setting up Outlook on Windows\n\n1. Launch Microsoft Outlook from the Start menu.\n2. Enter your work email address and select **Connect**.\n3. Complete the multi-factor authentication prompt.\n4. Allow Outlook to synchronize your mailboxes, calendar, and contacts.\n\nFor mobile setup, download the official Outlook App from Google Play or Apple App Store.",
            'is_published' => true,
            'views_count' => 95,
            'helpful_count' => 29,
            'not_helpful_count' => 0,
        ]);

        // 7. System Settings
        SystemSetting::set('app_organization', 'Enterprise Global Corporation', 'general', 'Organization name');
        SystemSetting::set('app_support_email', 'helpdesk@company.internal', 'general', 'Default support dispatch email');
        SystemSetting::set('app_allow_registration', '1', 'auth', 'Enable customer self-registration');
        SystemSetting::set('app_auto_assign_tickets', '1', 'tickets', 'Automatically route tickets to department agents');
        SystemSetting::set('app_sla_enabled', '1', 'sla', 'Enforce SLA response and resolution countdowns');

        // 8. Sample Tickets & Discussions
        // Ticket 1: Open, High Priority
        $t1 = Ticket::create([
            'ticket_number' => 'HD-'.date('Ym').'-0001',
            'user_id' => $customer1->id,
            'assigned_to_id' => $agent1->id,
            'department_id' => $deptIT->id,
            'category_id' => $catSoftware->id,
            'sla_plan_id' => $slaHigh->id,
            'subject' => 'Accounting software crashing when generating monthly revenue report',
            'description' => "Whenever I click 'Export to Excel' in the accounting ledger application, the program freezes and throws error code #0x80004005. This is blocking month-end financial closing.",
            'priority' => Ticket::PRIORITY_HIGH,
            'status' => Ticket::STATUS_IN_PROGRESS,
            'response_due_at' => Carbon::now()->addHours(4),
            'resolution_due_at' => Carbon::now()->addHours(12),
            'first_responded_at' => Carbon::now()->subHour(),
        ]);

        TicketReply::create([
            'ticket_id' => $t1->id,
            'user_id' => $agent1->id,
            'message' => "Hello Alice,\n\nI am investigating this issue. Could you let me know if you recently had any Windows Updates installed, and what version of Microsoft Office is shown under File > Account?",
            'is_internal_note' => false,
        ]);

        TicketReply::create([
            'ticket_id' => $t1->id,
            'user_id' => $agent1->id,
            'message' => 'Internal Note: Checked the server application event log. There is an OLE automation DLL version mismatch on workstation FIN-PC-04.',
            'is_internal_note' => true,
        ]);

        TicketActivity::log($t1->id, $customer1->id, 'created', 'Ticket created by Alice Morgan');
        TicketActivity::log($t1->id, $admin->id, 'assigned', 'Assigned ticket to John Carter');
        TicketActivity::log($t1->id, $agent1->id, 'replied', 'Public reply sent to requester');
        TicketActivity::log($t1->id, $agent1->id, 'note_added', 'Added internal technical note');

        // Ticket 2: Resolved, Medium Priority
        $t2 = Ticket::create([
            'ticket_number' => 'HD-'.date('Ym').'-0002',
            'user_id' => $customer2->id,
            'assigned_to_id' => $agent2->id,
            'department_id' => $deptNetwork->id,
            'category_id' => $catNetwork->id,
            'sla_plan_id' => $slaMedium->id,
            'subject' => 'Cannot access file share drive Z: over home office Wi-Fi',
            'description' => 'I can connect to the VPN client but when trying to open Z: drive (\\\\nas01\\departments\\hr) it says destination unreachable.',
            'priority' => Ticket::PRIORITY_MEDIUM,
            'status' => Ticket::STATUS_RESOLVED,
            'response_due_at' => Carbon::now()->subHours(20),
            'resolution_due_at' => Carbon::now()->addHours(28),
            'first_responded_at' => Carbon::now()->subHours(18),
            'resolved_at' => Carbon::now()->subHours(2),
            'satisfaction_rating' => 5,
            'satisfaction_comment' => 'Very fast resolution! Sarah resolved the routing conflict in minutes.',
        ]);

        TicketReply::create([
            'ticket_id' => $t2->id,
            'user_id' => $agent2->id,
            'message' => "Hi Bob,\n\nYour home router was using the 192.168.1.0/24 subnet which conflicted with the branch office IP range. We updated your split-tunnel route policy. Please try accessing the Z: drive again.",
            'is_internal_note' => false,
        ]);

        TicketReply::create([
            'ticket_id' => $t2->id,
            'user_id' => $customer2->id,
            'message' => 'That worked perfectly! Thank you so much for the quick help.',
            'is_internal_note' => false,
        ]);

        TicketActivity::log($t2->id, $customer2->id, 'created', 'Ticket created by Bob Davis');
        TicketActivity::log($t2->id, $agent2->id, 'resolved', 'Ticket marked as resolved');
        TicketActivity::log($t2->id, $customer2->id, 'rated', 'Customer submitted 5-star rating');

        // Ticket 3: Urgent Outage
        $t3 = Ticket::create([
            'ticket_number' => 'HD-'.date('Ym').'-0003',
            'user_id' => $customer1->id,
            'assigned_to_id' => $agent1->id,
            'department_id' => $deptIT->id,
            'category_id' => $catAccess->id,
            'sla_plan_id' => $slaUrgent->id,
            'subject' => 'Multiple accounts locked out on finance workstation cluster',
            'description' => 'At least 5 team members in finance are experiencing simultaneous account lockouts every 15 minutes.',
            'priority' => Ticket::PRIORITY_URGENT,
            'status' => Ticket::STATUS_OPEN,
            'response_due_at' => Carbon::now()->addMinutes(45),
            'resolution_due_at' => Carbon::now()->addHours(3),
        ]);

        TicketActivity::log($t3->id, $customer1->id, 'created', 'Urgent ticket created by Alice Morgan');
    }
}
