# TODO LIST - API Sistem Absensi WhatsApp Bot

## 📋 FASE 1: DATABASE DESIGN & MIGRATIONS

### 1.1 Migrations - Tabel Users (Admin Web Management)
- [ ] Modify migration `users` table - tambah kolom:
  - `role_id` (foreign key ke roles)
  - `is_active` (boolean, default true)
- [ ] Create migration `roles` table:
  - `id`, `name` (super_admin/admin), `description`, `timestamps`
- [ ] Seed default roles (Super Admin, Admin)
- [ ] **NOTE**: Users untuk admin yang manage web, BUKAN untuk member yang absen

### 1.2 Migrations - Tabel Members (Anak Magang/Karyawan)
- [ ] Create migration `members` table:
  - `id`, `name`, `phone_number` (string, unique), `email` (nullable)
  - `department` (string, nullable)
  - `position` (string, nullable) - misal: "Magang Frontend", "Magang Backend"
  - `join_date` (date, nullable)
  - `is_active` (boolean, default true)
  - `created_by` (FK ke users - admin yang input)
  - `timestamps`, `deleted_at` (soft delete)
- [ ] Index untuk `phone_number` (unique)
- [ ] **NOTE**: Members adalah anak magang yang absen via WhatsApp Bot

### 1.3 Migrations - Tabel Attendances
- [ ] Create migration `attendances` table:
  - `id`, `member_id` (FK ke members), `date`, `check_in_time`, `check_out_time`
  - `check_in_location_lat`, `check_in_location_lng`
  - `check_out_location_lat`, `check_out_location_lng`
  - `status` (enum: hadir, izin, sakit, alpha, terlambat)
  - `notes` (text, nullable)
  - `is_reset` (boolean, default false)
  - `reset_count` (integer, default 0)
  - `timestamps`, `deleted_at` (soft delete)
- [ ] Index untuk `member_id + date` (untuk check duplicate)

### 1.4 Migrations - Tabel Office Locations
- [ ] Create migration `office_locations` table:
  - `id`, `name`, `address`
  - `latitude`, `longitude`, `radius` (dalam meter)
  - `is_active` (boolean)
  - `timestamps`
- [ ] Seed default office location (kantor pusat)

### 1.5 Migrations - Tabel Bot Configuration
- [ ] Create migration `bot_configurations` table:
  - `id`, `key` (unique), `value` (encrypted), `description`
  - `is_active` (boolean)
  - `timestamps`
- [ ] Seed default bot config:
  - `whatsapp_api_token`
  - `whatsapp_webhook_url`
  - `whatsapp_phone_number_id`
  - `whatsapp_verify_token`

### 1.6 Migrations - Tabel Bot Keywords
- [ ] Create migration `bot_keywords` table:
  - `id`, `command` (string, unique), `description`
  - `is_active` (boolean)
  - `category` (attendance/help/info)
  - `timestamps`
- [ ] Seed default keywords:
  - HADIR, IZIN, SAKIT, PULANG, RESET, INFO, BANTUAN

---

## 📋 FASE 2: MODELS & RELATIONSHIPS

### 2.1 Model User (Admin)
- [ ] Update `User` model:
  - Tambah fillable: `role_id`, `is_active`
  - Relationship: `belongsTo(Role)`, `hasMany(Member)` (admin yang create members)
  - Scope untuk filter by role, active users
  - **NOTE**: User model untuk admin web management saja

### 2.2 Model Role
- [ ] Create `Role` model:
  - Fillable: `name`, `description`
  - Relationship: `hasMany(User)`
  - Helper methods: `isSuperAdmin()`, `isAdmin()`

### 2.3 Model Member (Anak Magang/Karyawan)
- [ ] Create `Member` model:
  - Fillable: `name`, `phone_number`, `email`, `department`, `position`, `join_date`, `is_active`, `created_by`
  - Relationship: `hasMany(Attendance)`, `belongsTo(User, 'created_by')` (admin creator)
  - Accessor untuk format phone number (normalize +62)
  - Scope: `active()`, `byDepartment()`, `byPosition()`
  - **NOTE**: Member adalah yang absen via WhatsApp

### 2.4 Model Attendance
- [ ] Create `Attendance` model:
  - Fillable: `member_id`, `date`, `check_in_time`, `check_out_time`, dll
  - Relationship: `belongsTo(Member)` ← **BUKAN belongsTo(User)**
  - Casts: `date` -> date, `check_in_time` -> datetime
  - Scopes: `today()`, `byMember()`, `byDateRange()`, `byStatus()`
  - Accessor untuk calculate work hours

### 2.5 Model OfficeLocation
- [ ] Create `OfficeLocation` model:
  - Fillable: `name`, `address`, `latitude`, `longitude`, `radius`, `is_active`
  - Scope: `active()`
  - Helper method: `isWithinRadius($lat, $lng)` - haversine formula

### 2.6 Model BotConfiguration
- [ ] Create `BotConfiguration` model:
  - Fillable: `key`, `value`, `description`, `is_active`
  - Mutator untuk auto-encrypt `value`
  - Accessor untuk auto-decrypt `value`
  - Static method: `getConfig($key)`

### 2.7 Model BotKeyword
- [ ] Create `BotKeyword` model:
  - Fillable: `command`, `description`, `is_active`, `category`
  - Scope: `active()`, `byCategory()`

---

## 📋 FASE 3: API AUTHENTICATION & MIDDLEWARE

### 3.1 Authentication Setup (Admin Only)
- [ ] Setup Sanctum middleware di `api` route group
- [ ] Create `AuthController`:
  - `POST /api/auth/login` (email + password untuk admin)
  - `POST /api/auth/logout`
  - `GET /api/auth/me` (get current admin user)
- [ ] Swagger annotations untuk auth endpoints
- [ ] **NOTE**: Authentication hanya untuk admin web, member tidak perlu login (absen via bot)

### 3.2 Custom Middleware
- [ ] Create `CheckRole` middleware untuk role-based access
- [ ] Create `CheckBotToken` middleware untuk WhatsApp webhook security
- [ ] Register middleware di Kernel

---

## 📋 FASE 4: ATTENDANCE API ENDPOINTS

### 4.1 Attendance Controller
- [ ] Create `AttendanceController`:
  - `POST /api/attendances/check-in` (dengan member_id, location lat/lng)
  - `POST /api/attendances/check-out` (dengan member_id, location lat/lng)
  - `GET /api/attendances` (list attendances - filter by date, member, status)
  - `GET /api/attendances/{id}` (detail)
  - `DELETE /api/attendances/{id}/reset` (reset attendance)
  - `GET /api/attendances/member/{memberId}/today` (attendance hari ini untuk member)
- [ ] Swagger annotations untuk semua endpoints
- [ ] **NOTE**: Endpoints untuk consume by WhatsApp Bot + Admin Web

### 4.2 Form Requests Validation
- [ ] Create `CheckInRequest`:
  - Validate: `member_id` (required, exists in members)
  - Validate: `latitude`, `longitude` (required, numeric)
  - Validate: tidak boleh double check-in di hari yang sama
- [ ] Create `CheckOutRequest`:
  - Validate: `member_id`, `latitude`, `longitude`
  - Validate: harus sudah check-in dulu
- [ ] Create `AttendanceFilterRequest` untuk query params

### 4.3 Attendance Service Layer
- [ ] Create `AttendanceService`:
  - Method `checkIn($member, $lat, $lng)`: ← parameter member, BUKAN user
    - Validate duplicate check-in
    - Validate location dengan office_locations
    - Determine status (hadir/terlambat)
    - Create attendance record
  - Method `checkOut($member, $lat, $lng)`:
    - Find today's attendance for member
    - Update check_out_time dan location
    - Calculate total hours
  - Method `resetAttendance($attendanceId)`:
    - Soft delete atau mark as reset
    - Increment reset_count
    - Log reset action

### 4.4 Location Validation Service
- [ ] Create `LocationService`:
  - Method `validateLocation($lat, $lng)`:
    - Get all active office_locations
    - Calculate distance dengan Haversine formula
    - Return nearest office atau error jika semua di luar radius
  - Method `calculateDistance($lat1, $lng1, $lat2, $lng2)`

---

## 📋 FASE 5: WHATSAPP BOT INTEGRATION

### 5.1 WhatsApp Webhook Controller
- [ ] Create `WhatsAppWebhookController`:
  - `GET /api/webhook/whatsapp` (verification webhook)
  - `POST /api/webhook/whatsapp` (receive messages)
  - Validate request dari WhatsApp dengan verify_token
  - Parse incoming message structure

### 5.2 WhatsApp Message Handler Service
- [ ] Create `WhatsAppMessageHandler`:
  - Method `handleMessage($from, $message)`:
    - Extract phone number dari sender (normalize +62)
    - **Find MEMBER by phone_number** (bukan user)
    - Parse command dari message
    - Route ke appropriate handler
  - Method `handleCheckIn($member, $message)`: ← parameter member
    - Extract location jika ada
    - Call AttendanceService->checkIn()
    - Send response via WhatsApp
  - Method `handleCheckOut($member, $message)`
  - Method `handleLeaveRequest($member, $type, $message)` (izin/sakit)
  - Method `handleReset($member)`
  - Method `handleHelp()` - send list keywords
  - Method `handleInvalidCommand()` - send error + help
  - Method `handleUnregisteredNumber($phoneNumber)` - phone number tidak terdaftar sebagai member

### 5.3 WhatsApp API Client Service
- [ ] Create `WhatsAppApiService`:
  - Method `sendMessage($to, $message)`:
    - Get config dari BotConfiguration
    - Send POST request ke WhatsApp API
    - Handle response dan errors
  - Method `sendTemplateMessage($to, $template, $params)`

### 5.4 Bot Response Templates
- [ ] Create response messages:
  - Success check-in
  - Success check-out
  - Duplicate attendance warning
  - Invalid location error
  - Reset confirmation
  - Help message dengan list commands
  - Invalid command error

---

## 📋 FASE 6: ADMIN CONFIGURATION API

### 6.1 Office Location Management
- [ ] Create `Admin/OfficeLocationController`:
  - `GET /api/admin/office-locations` (list all)
  - `POST /api/admin/office-locations` (create)
  - `PUT /api/admin/office-locations/{id}` (update)
  - `DELETE /api/admin/office-locations/{id}` (soft delete)
  - `PATCH /api/admin/office-locations/{id}/toggle` (activate/deactivate)
- [ ] Swagger annotations
- [ ] Validation: latitude, longitude, radius required

### 6.2 Bot Configuration Management
- [ ] Create `Admin/BotConfigController`:
  - `GET /api/admin/bot/config` (get all configs)
  - `PUT /api/admin/bot/config` (update config - batch update)
  - `POST /api/admin/bot/config/test` (test connection)
- [ ] Validation untuk WhatsApp API credentials
- [ ] Mask sensitive values di response (show only last 4 chars)

### 6.3 Bot Keywords Management
- [ ] Create `Admin/BotKeywordController`:
  - `GET /api/admin/bot/keywords`
  - `POST /api/admin/bot/keywords`
  - `PUT /api/admin/bot/keywords/{id}`
  - `DELETE /api/admin/bot/keywords/{id}`
  - `PATCH /api/admin/bot/keywords/{id}/toggle`
- [ ] Validation: command unique, uppercase

### 6.4 Member Management (Anak Magang/Karyawan)
- [ ] Create `Admin/MemberController`:
  - `GET /api/admin/members` (list dengan filter)
  - `POST /api/admin/members` (create member - name, phone, department, position)
  - `PUT /api/admin/members/{id}` (update)
  - `DELETE /api/admin/members/{id}` (soft delete)
  - `PATCH /api/admin/members/{id}/toggle` (activate/deactivate)
  - `GET /api/admin/members/{id}/attendances` (history absensi member)
- [ ] Validation phone_number unique, normalize format (+62xxx)
- [ ] Auto set `created_by` dari authenticated admin

### 6.5 Admin User Management
- [ ] Create `Admin/UserController`:
  - `GET /api/admin/users` (list admin users)
  - `POST /api/admin/users` (create admin + auto generate password)
  - `PUT /api/admin/users/{id}` (update admin)
  - `DELETE /api/admin/users/{id}` (soft delete)
  - `POST /api/admin/users/{id}/reset-password`
- [ ] **NOTE**: Ini untuk manage admin yang akses web, bukan member

---

## 📋 FASE 7: REPORTING & ANALYTICS API

### 7.1 Report Controller
- [ ] Create `ReportController`:
  - `GET /api/reports/daily` (laporan harian)
  - `GET /api/reports/monthly` (laporan bulanan)
  - `GET /api/reports/by-member/{memberId}` (laporan per member)
  - `GET /api/reports/by-department` (laporan per department)
  - `GET /api/reports/summary` (summary stats)
- [ ] Filter: date_from, date_to, department, status
- [ ] Export options: JSON (default), nanti bisa tambah CSV/Excel

### 7.2 Dashboard Statistics
- [ ] Create `DashboardController`:
  - `GET /api/dashboard/stats` (total hari ini: hadir, izin, sakit, alpha)
  - `GET /api/dashboard/recent-attendances` (10 absensi terakhir)
  - `GET /api/dashboard/late-comers` (list member yang terlambat hari ini)
  - `GET /api/dashboard/members-summary` (total member aktif, department breakdown)

---

## 📋 FASE 8: SWAGGER DOCUMENTATION

### 8.1 Setup Swagger
- [ ] Configure Swagger di `config/l5-swagger.php` (jika pakai laravel-swagger) atau manual
- [ ] Create base swagger annotations di `app/Http/Controllers/Controller.php`:
  - API title, version, description
  - Base URL
  - Security schemes (Bearer token)

### 8.2 Document All Endpoints
- [ ] Auth endpoints documentation
- [ ] Attendance endpoints documentation
- [ ] Admin endpoints documentation
- [ ] Webhook endpoints documentation
- [ ] Report endpoints documentation
- [ ] Generate swagger.json: `php artisan l5-swagger:generate` atau manual

---

## 📋 FASE 9: TESTING

### 9.1 Feature Tests
- [ ] Create `Feature/AuthTest.php`:
  - Test admin login success/fail
  - Test get current admin user
- [ ] Create `Feature/MemberTest.php`:
  - Test create member
  - Test update member
  - Test member validation (phone unique)
- [ ] Create `Feature/AttendanceTest.php`:
  - Test check-in success (via member)
  - Test duplicate check-in (should fail)
  - Test check-out success
  - Test check-out without check-in (should fail)
  - Test reset attendance
- [ ] Create `Feature/WhatsAppWebhookTest.php`:
  - Test webhook verification
  - Test message handling (member found)
  - Test unregistered phone number
  - Test invalid commands

### 9.2 Unit Tests
- [ ] Test `LocationService::calculateDistance()`
- [ ] Test `LocationService::validateLocation()`
- [ ] Test `AttendanceService` methods (dengan member)
- [ ] Test `WhatsAppMessageHandler` parsers
- [ ] Test phone number normalization

---

## 📋 FASE 10: DEPLOYMENT PREPARATION

### 10.1 Environment Configuration
- [ ] Setup `.env.example` dengan semua required variables:
  - Database credentials
  - WhatsApp API config
  - App URL, timezone
- [ ] Documentation untuk environment setup

### 10.2 Seeders & Sample Data
- [ ] Create comprehensive DatabaseSeeder:
  - Roles (Super Admin, Admin)
  - Sample admin user (email, password)
  - Sample members (5-10 anak magang dengan phone_number)
  - Default office location
  - Default bot keywords (HADIR, IZIN, SAKIT, PULANG, etc)
  - Sample attendances untuk members (last 7 days)
- [ ] **NOTE**: Seed users (admin) dan members (anak magang) sebagai data terpisah

### 10.3 API Routes Documentation
- [ ] Update `routes/api.php` dengan comments
- [ ] Group routes by functionality
- [ ] Apply middleware appropriately

### 10.4 Error Handling
- [ ] Customize exception handler untuk API responses
- [ ] Return consistent JSON format:
  ```json
  {
    "success": false,
    "message": "Error message",
    "data": null,
    "errors": {}
  }
  ```
- [ ] Handle validation errors
- [ ] Handle authentication errors
- [ ] Handle authorization errors

---

## 📋 FASE 11: OPTIMIZATION & SECURITY

### 11.1 Security
- [ ] Implement rate limiting untuk webhook endpoint
- [ ] Validate WhatsApp webhook signature
- [ ] Sanitize user inputs
- [ ] Implement CORS properly
- [ ] Encrypt sensitive data (bot credentials)

### 11.2 Performance
- [ ] Add database indexes:
  - `attendances`: index on (member_id, date)
  - `members`: index on phone_number (unique)
  - `users`: index on email
- [ ] Implement query optimization
- [ ] Add eager loading untuk relationships (Member->Attendance)

### 11.3 Logging & Monitoring
- [ ] Log semua webhook requests
- [ ] Log attendance actions (check-in, check-out, reset)
- [ ] Log bot message exchanges
- [ ] Create custom log channels jika perlu

---

## 📝 NOTES

### Arsitektur Database
**PENTING - Pemisahan User & Member:**
- **`users` table**: Admin yang manage sistem via web (login dengan email/password)
- **`members` table**: Anak magang/karyawan yang absen via WhatsApp Bot (identifikasi via phone_number)
- **`attendances` table**: Reference ke `members`, BUKAN ke `users`

**Relasi:**
- User (admin) → hasMany Members (admin yang create member)
- Member → hasMany Attendances
- Attendance → belongsTo Member

### API Response Format Standard
```json
{
  "success": true,
  "message": "Success message",
  "data": { ... },
  "meta": {
    "current_page": 1,
    "total": 100
  }
}
```

### Teknologi Stack
- Laravel 10
- Laravel Sanctum (API Auth)
- MySQL
- Swagger/OpenAPI (zircote/swagger-php)
- WhatsApp Business API / Twilio / whatsapp-web.js

### Prioritas Development
1. **CRITICAL**: Database + Models + Auth
2. **HIGH**: Attendance API + Basic WhatsApp Integration
3. **MEDIUM**: Admin Config API + Reporting
4. **LOW**: Advanced features + Optimization
