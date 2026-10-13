# HelpDesk Information System
### Enterprise IT Service Management & Air-Gapped Help Desk Platform

[![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-18-4169E1?style=for-the-badge&logo=postgresql&logoColor=white)](https://www.postgresql.org)
[![PHP](https://img.shields.io/badge/PHP-8.3+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)](https://getbootstrap.com)
[![FontAwesome](https://img.shields.io/badge/FontAwesome-6_Free-528DD7?style=for-the-badge&logo=fontawesome&logoColor=white)](https://fontawesome.com)
[![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)](LICENSE)

A production-ready, enterprise-grade **Help Desk Information System** designed for high-availability IT support operations, corporate service desks, and secure air-gapped intranet networks.

Built on the latest **Laravel 13** and architected for **PostgreSQL 18**, this application features full offline capability with zero external CDN dependencies, an enterprise bulk data import engine, multi-tier SLA monitoring, customer self-service, CSAT rating workflows, and an integrated technical knowledge base.

---

## Table of Contents

- [Key Architectural Highlights](#key-architectural-highlights)
- [System Features](#system-features)
  - [Role-Based Access Control (RBAC)](#role-based-access-control-rbac)
  - [Ticket Lifecycle & SLA Engine](#ticket-lifecycle--sla-engine)
  - [Admin Bulk Import Data Center](#admin-bulk-import-data-center)
  - [Knowledge Base & Self-Service Portal](#knowledge-base--self-service-portal)
  - [Analytics, Reports & CSAT Scorecard](#analytics-reports--csat-scorecard)
- [100% Offline Air-Gapped Intranet Ready](#100-offline-air-gapped-intranet-ready)
- [Prerequisites & System Requirements](#prerequisites--system-requirements)
- [Installation & Setup Guide](#installation--setup-guide)
  - [1. Environment Configuration](#1-environment-configuration)
  - [2. PostgreSQL 18 Setup](#2-postgresql-18-setup)
  - [3. Laragon Setup Instructions](#3-laragon-setup-instructions)
  - [4. Migrations & Database Seeding](#4-migrations--database-seeding)
  - [5. Storage Linking](#5-storage-linking)
  - [6. Running the Local Server](#6-running-the-local-server)
- [Pre-configured Demo Credentials](#pre-configured-demo-credentials)
- [Admin Bulk Import Data Specifications](#admin-bulk-import-data-specifications)
- [Automated Testing & Code Quality](#automated-testing--code-quality)
- [Database Schema Reference](#database-schema-reference)
- [Project Directory Structure](#project-directory-structure)
- [License](#license)

---

## Key Architectural Highlights

- **Modern Stack**: Laravel 13 framework running on PHP 8.3 with strict type declarations and PHP 8 constructor property promotion.
- **PostgreSQL 18 Native**: Built for PostgreSQL 18's high concurrency, ACID transactions, relational integrity, and advanced indexing.
- **Zero Internet/CDN Requirement**: Standalone Bootstrap 5.3 CSS/JS bundles, FontAwesome 6 Webfonts, Bootstrap Icons, and Chart.js UMD are stored locally inside `public/vendor/`. No assets are fetched from third-party CDNs.
- **Transactional CSV Importer**: Resilient admin bulk import engine with character encoding detection (UTF-8 with BOM support), comma/semicolon auto-detection, row-level validation, and audit logging.
- **Multi-Tenant / Multi-Department**: Isolate ticket queues, routing, and agents across IT, HR, Facilities, Finance, and Security.

---

## System Features

### Role-Based Access Control (RBAC)
- **Administrator**: Full administrative control, bulk data import suite, system analytics, user provisioning, department & SLA policy configuration, and canned response library.
- **Support Agent**: Interactive queue dashboard, assigned ticket workbench, ticket re-assignment, canned response insertion, internal private notes, and ticket status progression.
- **Customer / Employee**: Self-service portal, ticket submission with file attachments, live ticket thread discussion, CSAT satisfaction ratings (1-5 stars with comments), and knowledge base searching.

### Ticket Lifecycle & SLA Engine
- **Unique Ticket Numbering**: Automated `HD-YYYYMMDD-XXXX` format.
- **SLA Policy Matrix**: Automated calculation of `response_due_at` and `resolution_due_at` based on priority levels (`Low`, `Medium`, `High`, `Urgent`).
- **Real-Time SLA Badging**: Visual countdown timers, approaching breach indicators, and SLA breached tags.
- **Private Internal Notes**: Staff-only discussion thread hidden from end-customers for internal diagnosis.
- **Canned Responses**: Pre-written macro replies for rapid resolution of common technical inquiries.
- **Audit Activity Trail**: Immutable history log of all updates (status transitions, priority changes, reassignments, notes).
- **Customer Satisfaction (CSAT)**: 5-star rating system and customer feedback for resolved tickets.

### Admin Bulk Import Data Center
- Bulk import capabilities for:
  1. **Users** (Agents, Admins, Customers)
  2. **Tickets** (Historical support requests and backlog tickets)
  3. **Departments** (Business units and teams)
  4. **Categories** (Issue classifications with color hex codes)
  5. **Knowledge Base Articles** (Technical documentation, guides, FAQs)
- **Live CSV Templates**: Built-in one-click download for sample CSV templates for all 5 entities.
- **Detailed Audit Trail**: Logs file name, total rows, success count, error count, and a modal breakdown of validation failures per line.

### Knowledge Base & Self-Service Portal
- Publicly accessible or intranet-wide knowledge base with category navigation.
- Full-text keyword search for troubleshooting articles.
- Article view counters and community helpfulness feedback counters (**Yes, helpful** / **No, need help**).

### Analytics, Reports & CSAT Scorecard
- Executive KPI dashboard displaying Total Volume, SLA Compliance Rate, Average Turnaround Time, and CSAT Average.
- Agent performance scorecard showing ticket volume, resolution percentage, and individual CSAT scores.
- Departmental workload distribution breakdown.
- One-click CSV export of ticket records for external reporting.

---

## 100% Offline Air-Gapped Intranet Ready

The entire user interface is decoupled from the public internet. All frontend dependencies reside directly within `public/vendor/`:

```
public/vendor/
├── bootstrap/
│   ├── css/bootstrap.min.css
│   └── js/bootstrap.bundle.min.js
├── fontawesome/
│   ├── css/all.min.css
│   └── webfonts/
│       ├── fa-brands-400.woff2
│       ├── fa-regular-400.woff2
│       ├── fa-solid-900.woff2
│       └── ...
├── bootstrap-icons/
│   ├── bootstrap-icons.min.css
│   └── fonts/
│       ├── bootstrap-icons.woff
│       └── bootstrap-icons.woff2
└── chartjs/
    └── chart.umd.js
```

There are **zero external CDN links**, **zero Google Font calls**, and **zero external analytics tags**. The application operates seamlessly in closed military, government, hospital, banking, and enterprise internal networks.

---

## Prerequisites & System Requirements

- **PHP**: PHP 8.3 or higher (PHP 8.3.33+ included in Laragon or standalone).
- **Required PHP Extensions**: `pdo_pgsql`, `pgsql`, `mbstring`, `fileinfo`, `gd`, `openssl`, `curl`, `zip`.
- **Database**: PostgreSQL 18 (or PostgreSQL 15, 16, 17).
- **Web Server**: Apache, Nginx, or PHP built-in server (`php artisan serve`).
- **Composer**: Composer 2.x.
- **Node.js**: Node 18+ (Only needed if customizing Sass/Vite; all CSS/JS assets are already pre-compiled).

---

## Installation & Setup Guide

### 1. Environment Configuration

Clone or copy the application to your web directory, then initialize your `.env` file:

```bash
cp .env.example .env
```

Generate the unique application encryption key:

```bash
php artisan key:generate
```

### 2. PostgreSQL 18 Setup

In your PostgreSQL 18 instance (via `psql` or pgAdmin), create the application database:

```sql
CREATE DATABASE helpdesk_db;
CREATE USER helpdesk_user WITH ENCRYPTED PASSWORD 'your_secure_password';
GRANT ALL PRIVILEGES ON DATABASE helpdesk_db TO helpdesk_user;
```

Update your `.env` file with your PostgreSQL 18 credentials:

```dotenv
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=helpdesk_db
DB_USERNAME=helpdesk_user
DB_PASSWORD=your_secure_password
```

### 3. Laragon Setup Instructions

If you are using **Laragon**:

1. **PHP 8.3 Activation**:
   - Ensure Laragon has PHP 8.3 selected (*Right Click Laragon > PHP > Version > php-8.3.x*).
   - Verify that `extension=pdo_pgsql` and `extension=pgsql` are enabled in `php.ini`.
2. **PostgreSQL Service**:
   - If PostgreSQL is added to Laragon (*Laragon > Menu > PostgreSQL > Start*), it will listen on port `5432`.
   - If running the CLI commands from PowerShell, prepend the Laragon PHP 8.3 path if needed:
     ```powershell
     $env:PATH = "C:\laragon\bin\php\php-8.3.33-Win32-vs16-x64;" + $env:PATH
     ```

### 4. Migrations & Database Seeding

Run the database schema migrations and preload the seed data:

```bash
php artisan migrate --seed
```

The database seeder automatically initializes:
- Default organizational departments (IT Infrastructure, Software Support, HR, Facilities, Security)
- Ticket categories with assigned hex badge colors
- Standard SLA policies (Urgent: 1h response / 4h resolution; High: 4h / 12h; Medium: 8h / 24h; Low: 24h / 72h)
- Administrator, Agent, and Customer demonstration accounts
- Canned responses and knowledge base articles
- Sample tickets showcasing open, in-progress, and resolved lifecycles

### 5. Storage Linking

Create the symbolic link from `public/storage` to `storage/app/public` for ticket file attachments and avatars:

```bash
php artisan storage:link
```

### 6. Running the Local Server

Start the Laravel development server:

```bash
php artisan serve
```

Visit the application at: `http://127.0.0.1:8000`

---

## Pre-configured Demo Credentials

All seeded accounts share the default password: **`password123`**

| Role | Name | Email Address | Access Level |
| :--- | :--- | :--- | :--- |
| **Administrator** | Super Admin | `admin@helpdesk.local` | Full System Control, Bulk Import, Analytics, User Management |
| **Support Agent** | John Agent | `agent.john@helpdesk.local` | Ticket Queue, Internal Notes, Reassignment, Canned Responses |
| **Support Agent** | Sarah Tech | `agent.sarah@helpdesk.local` | Ticket Queue, Customer Communication, KB Publishing |
| **Customer** | Alice Customer | `user.alice@example.com` | Ticket Creation, Knowledge Base, CSAT Ratings |
| **Customer** | Bob Martinez | `user.bob@example.com` | Ticket Creation, Knowledge Base, CSAT Ratings |

> *Tip: On the login page (`/login`), click any of the one-click demo badge buttons to immediately auto-fill the login form.*

---

## Admin Bulk Import Data Specifications

Administrators can navigate to **Administration > Bulk Import Data** (`/admin/bulk-import`) to perform batch operations via CSV files.

### 1. Users Bulk Import
- **CSV Headers**: `name,email,role,password,department,phone`
- **Rules**:
  - `role`: Must be one of `admin`, `agent`, `customer`.
  - `department`: Department name or slug (optional for customer, resolved automatically).
  - `password`: Minimum 8 characters (defaults to `password123` if left empty).

### 2. Tickets Bulk Import
- **CSV Headers**: `subject,description,priority,user_email,department,category,status`
- **Rules**:
  - `priority`: `low`, `medium`, `high`, `urgent`.
  - `status`: `open`, `in_progress`, `pending`, `resolved`, `closed` (defaults to `open`).
  - `user_email`: Existing user email who created the ticket.
  - `department`: Existing department name or slug.

### 3. Departments Bulk Import
- **CSV Headers**: `name,description,is_active`
- **Rules**:
  - `name`: Unique department name. Slugs are auto-generated.
  - `is_active`: `1` (true) or `0` (false).

### 4. Categories Bulk Import
- **CSV Headers**: `name,department,color,description`
- **Rules**:
  - `color`: Hex color code (e.g. `#3b82f6`).
  - `department`: Existing department name or slug.

### 5. Knowledge Base Articles Bulk Import
- **CSV Headers**: `title,category,content,is_published`
- **Rules**:
  - `title`: Unique title. Slugs are auto-generated.
  - `category`: Existing category name.
  - `is_published`: `1` (published) or `0` (draft).

---

## Automated Testing & Code Quality

The test suite covers Authentication, Ticket Lifecycle, SLA calculations, Customer CSAT rating, Public & Staff Knowledge Base, and Admin Bulk Import.

### Running PHPUnit Feature Tests

```bash
php artisan test --compact
```

### Code Formatting (Laravel Pint)

All PHP code adheres to modern Laravel and PSR standards:

```bash
vendor/bin/pint --format agent
```

---

## Database Schema Reference

The system database consists of the following primary tables:

1. **`users`**: User identity, roles (`admin`, `agent`, `customer`), department association, avatar path, active state.
2. **`departments`**: Organizational divisions and routing queues.
3. **`categories`**: Issue classifications and visual badge styling.
4. **`sla_plans`**: Response and resolution hour thresholds mapped to priorities.
5. **`tickets`**: Support cases, SLA due timestamps, resolution logs, satisfaction rating (1-5), and feedback comments.
6. **`ticket_replies`**: Thread messages, supporting both public customer replies and staff internal notes.
7. **`ticket_attachments`**: Files linked to tickets and replies with size and MIME validation.
8. **`ticket_activities`**: Comprehensive audit log recording every change event.
9. **`canned_responses`**: Staff macro responses with shortcut titles and categories.
10. **`knowledge_base_articles`**: Technical guides with view counters and helpfulness votes.
11. **`bulk_import_logs`**: Administrative audit history tracking CSV uploads, row counts, and line-item errors.
12. **`system_settings`**: Key-value table for branding, support email, and SLA breach thresholds.

---

## Project Directory Structure

```
helpdesk-information-system/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   │   ├── BulkImportController.php
│   │   │   │   ├── CannedResponseController.php
│   │   │   │   ├── CategoryController.php
│   │   │   │   ├── DepartmentController.php
│   │   │   │   ├── ReportController.php
│   │   │   │   ├── SlaPlanController.php
│   │   │   │   └── UserController.php
│   │   │   ├── AuthController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── KnowledgeBaseController.php
│   │   │   └── TicketController.php
│   │   └── Middleware/
│   │       └── RoleMiddleware.php
│   ├── Models/
│   │   ├── BulkImportLog.php
│   │   ├── CannedResponse.php
│   │   ├── Category.php
│   │   ├── Department.php
│   │   ├── KnowledgeBaseArticle.php
│   │   ├── SlaPlan.php
│   │   ├── SystemSetting.php
│   │   ├── Ticket.php
│   │   ├── TicketActivity.php
│   │   ├── TicketAttachment.php
│   │   ├── TicketReply.php
│   │   └── User.php
│   └── Services/
│       └── BulkImportService.php
├── database/
│   ├── migrations/
│   └── seeders/
│       └── DatabaseSeeder.php
├── public/
│   └── vendor/
│       ├── bootstrap/
│       ├── bootstrap-icons/
│       ├── chartjs/
│       └── fontawesome/
├── resources/
│   ├── views/
│   │   ├── admin/
│   │   ├── auth/
│   │   ├── dashboard/
│   │   ├── knowledge-base/
│   │   ├── layouts/
│   │   ├── profile/
│   │   └── tickets/
├── routes/
│   └── web.php
└── tests/
    └── Feature/
        ├── AdminManagementTest.php
        ├── AuthTest.php
        ├── BulkImportTest.php
        ├── KnowledgeBaseTest.php
        └── TicketManagementTest.php
```

---

## License

This software is released under the **MIT License**.
