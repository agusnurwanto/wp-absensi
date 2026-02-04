# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

WP-ABSENSI is a WordPress attendance management plugin for Indonesian government institutions (SKPD). Built on WordPress Plugin Boilerplate architecture with Carbon Fields for admin options.

## Commands

```bash
# Install PHP dependencies (Carbon Fields)
composer install

# Database migration - via WordPress Admin: Absensi Options > SQL Migrate button
# Or trigger AJAX action: sql_migrate_absen
```

No build process, automated tests, or npm scripts. Enable `WP_DEBUG` in wp-config.php for development.

## Environment

**Shell preference (Windows):** Check if Git Bash is installed first (`bash --version`). If available, use Git Bash instead of PowerShell or CMD for better Unix command compatibility.

**Prefer `bun` and `bunx` over `npm` or `npx`** for any JavaScript/Node.js tooling in this project. Check if bun is installed first (`bun --version`), if not available, fall back to npm/npx.

## Architecture

### Directory Structure
- `includes/` - Core classes: main orchestrator, loader, activator, deactivator, i18n, functions utility
- `admin/` - WordPress admin functionality, Carbon Fields integration
- `public/` - Frontend: shortcode handlers, AJAX endpoints, view templates in `partials/`
- `public/trait/` - Shared PHP traits for public classes
- `public/partials/` - View templates for shortcodes
- `vendor/` - Composer dependencies (Carbon Fields)
- `core/Libraries/` - Custom libraries (Plugin_Update_Warning)

### Key Classes

| Class | File | Role |
|-------|------|------|
| Wp_Absen | includes/class-wp-absen.php | Main orchestrator, loads dependencies and hooks |
| Wp_Absen_Loader | includes/class-wp-absen-loader.php | Hook registration system |
| Wp_Absen_Activator | includes/class-wp-absen-activator.php | Plugin activation handler |
| Wp_Absen_Deactivator | includes/class-wp-absen-deactivator.php | Plugin deactivation handler |
| Wp_Absen_i18n | includes/class-wp-absen-i18n.php | Internationalization |
| ABSEN_Functions | includes/class-wp-absen-functions.php | Utility library (API keys, uploads, Telegram, password management) |
| Wp_Absen_Admin | admin/class-wp-absen-admin.php | Admin area, Carbon Fields options, Excel import, WP-SIPD integration |
| Wp_Absen_Public | public/class-wp-absen-public.php | Attendance management, legacy employee handlers, menu shortcode |
| Wp_Absen_Public_Instansi | public/class-wp-absen-public-instansi.php | Institution management with geofencing, auto user creation |
| Wp_Absen_Public_Pegawai | public/class-wp-absen-public-pegawai.php | Employee management, master data, copy between years |
| Wp_Absen_Public_Kode_Kerja | public/class-wp-absen-public-kode-kerja.php | Work code/schedule management with flexible hours |
| Wp_Absen_Public_Absensi | public/class-wp-absen-public-absensi.php | Daily attendance submission, clock in/out, GPS tracking |
| Wp_Absen_Public_Kegiatan | public/class-wp-absen-public-kegiatan.php | Activity/event management per employee |
| Wp_Absen_Public_Ijin | public/class-wp-absen-public-ijin.php | Leave/permission request management with approval workflow |
| CustomTraitAbsen | public/trait/CustomTrait.php | Shared file upload trait |

### Data Flow
1. Shortcodes render views from `public/partials/`
2. Frontend JS (jQuery + DataTables) makes AJAX calls
3. PHP AJAX handlers validate API key, process with `$wpdb`, return JSON
4. All data operations require `tahun_anggaran` (fiscal year) filtering
5. Permission filtering based on user role (admin vs admin_instansi)

### Database Tables (prefix: none, direct names)

| Table | Purpose |
|-------|---------|
| `absensi_data_pegawai` | Employee records with dual status (active + status_kerja) |
| `absensi_data_instansi` | Institutions with GPS geofencing, linked WordPress users |
| `absensi_data_kerja` | Work codes/schedules with flexible hours, geofencing, primary flag |
| `absensi_harian` | Daily attendance records (clock in/out, coordinates, photos) |
| `absensi_ijin` | Leave/permission requests with approval workflow (Pending/Approved/Rejected) |
| `absensi_kegiatan` | Employee activities/events with time, location, and descriptions |
| `absensi_data` | Attendance/overtime header records (legacy) |
| `absensi_data_detail` | Detailed attendance entries per employee (legacy) |
| `absensi_data_unit` | Organizational units (SKPD) from WP-SIPD |
| `absensi_data_rekening_akun` | Account codes from WP-SIPD |
| `absensi_data_satuan` | Unit/measurement data from WP-SIPD |

### Custom Roles

| Role | Capabilities | Purpose |
|------|-------------|---------|
| `admin_instansi` | `read` | Institution administrator, sees only their data |
| `pegawai` | `read` | Employee role |

## Shortcodes

| Shortcode | Handler | Purpose |
|-----------|---------|---------|
| `[management_data_pegawai_absensi]` | Wp_Absen_Public_Pegawai | Employee management interface |
| `[management_data_instansi]` | Wp_Absen_Public_Instansi | Institution management with Leaflet map |
| `[management_data_absensi]` | Wp_Absen_Public | Attendance records management |
| `[management_data_kerja]` | Wp_Absen_Public_Kode_Kerja | Work code/schedule management |
| `[manajemen_data_kerja]` | Wp_Absen_Public_Kode_Kerja | Work code management (alias) |
| `[management_data_kegiatan]` | Wp_Absen_Public_Kegiatan | Employee activity/event management |
| `[management_data_ijin]` | Wp_Absen_Public_Ijin | Leave/permission request management |
| `[absensi_pegawai]` | Wp_Absen_Public_Absensi | Employee attendance submission interface |
| `[menu_absensi]` | Wp_Absen_Public | Dynamic menu based on user role |
| `[ubah_password_absen]` | ABSEN_Functions | Password change form with force-change support |
| `[laporan_bulanan_absensi]` | (Auto-generated page) | Monthly attendance report |

## View Partials

| File | Purpose |
|------|---------|
| `wp-absen-management-data-pegawai.php` | Employee management UI |
| `wp-absen-management-data-instansi.php` | Institution management with Leaflet map |
| `wp-absen-management-data-kode-kerja.php` | Work code management UI |
| `wp-absen-management-data-absensi.php` | Attendance records UI with photo attachments |
| `wp-absen-absensi-pegawai.php` | Employee attendance submission UI |
| `wp-absen-management-data-kegiatan.php` | Activity/event management UI |
| `wp-absen-management-data-ijin.php` | Leave/permission request UI |
| `wp-absen-public-display.php` | Generic public display |

## AJAX Endpoints

### Admin (Wp_Absen_Admin)
- `sql_migrate_absen` - Run database migrations
- `import_excel_absen_pegawai` - Import employees from Excel
- `generate_user_absen` - Auto-generate WordPress users from employees
- `get_data_unit_wpsipd` - Pull SKPD/account data from WP-SIPD API

### Pegawai Management (Wp_Absen_Public_Pegawai)
- `get_datatable_pegawai` - DataTable with permission filtering
- `tambah_data_pegawai` - Add/update employee
- `get_data_pegawai_by_id` - Get employee details
- `hapus_data_pegawai_by_id` - Soft delete (modes: `hapus`, `nonaktif`)
- `toggle_status_pegawai` - Toggle employee active status
- `copy_data_pegawai` - Copy employees between fiscal years
- `get_master_data` - All dropdown master data
- `get_master_jenis_kelamin` - Gender options (L/P)
- `get_master_agama` - Religion options
- `get_master_pendidikan` - Education levels (SD to S3)
- `get_master_status_pegawai` - Employment status types
- `get_master_user_role` - User role options
- `get_master_pegawai_search` - Employee search for Select2

### Instansi Management (Wp_Absen_Public_Instansi)
- `get_datatable_instansi` - DataTable with permission filtering
- `tambah_data_instansi` - Add/update institution (auto-creates WordPress user)
- `get_data_instansi_by_id` - Get institution details
- `hapus_data_instansi_by_id` - Soft delete
- `toggle_status_instansi` - Toggle institution active status
- `get_master_instansi` - Institution dropdown with permission filtering
- `get_users_for_instansi` - Get WordPress users
- `mutakhirkan_user_instansi` - Update/create user for institution

### Kode Kerja Management (Wp_Absen_Public_Kode_Kerja)
- `get_datatable_kode_kerja` - DataTable for work codes
- `tambah_data_kode_kerja` - Add/update work code
- `get_data_kode_kerja_by_id` - Get work code details
- `hapus_data_kode_kerja_by_id` - Delete work code
- `toggle_status_kode_kerja` - Toggle work code status
- `check_primary_kode_kerja` - Validate one primary per institution

### Absensi Operations (Wp_Absen_Public_Absensi)
- `get_server_time` - Get current server time for frontend clock
- `get_valid_kode_kerja` - Get valid work codes for current institution
- `submit_absensi_pegawai` - Submit attendance (clock in/out with GPS/photo)
- `check_status_absensi` - Check today's attendance status
- `get_datatable_absensi` - DataTable for attendance records
- `get_data_absensi_by_id` - Get attendance record details
- `tambah_data_absensi_manual` - Manual attendance entry by admin
- `hapus_data_absensi` - Delete attendance record

### Kegiatan Management (Wp_Absen_Public_Kegiatan)
- `get_datatable_kegiatan` - DataTable for activities (permission filtered)
- `tambah_data_kegiatan` - Add/update activity with file upload
- `get_data_kegiatan_by_id` - Get activity details
- `hapus_data_kegiatan_by_id` - Delete activity (hard delete, permission checked)

### Ijin Management (Wp_Absen_Public_Ijin)
- `get_datatable_ijin` - DataTable for leave requests (permission filtered)
- `tambah_data_ijin` - Add/update leave request with file upload
- `get_data_ijin_by_id` - Get leave request details
- `hapus_data_ijin_by_id` - Delete leave request (hard delete)
- `update_status_ijin` - Approve/reject leave request (admin only)

### Password Management (ABSEN_Functions)
- `absen_change_password` - Change password, clears force-change flag

### Legacy Endpoints (Wp_Absen_Public - deprecated)
- `get_datatable_karyawan` - Old employee DataTable
- `hapus_data_karyawan_by_id` - Old employee delete
- `get_data_karyawan_by_id` - Old employee get
- `tambah_data_karyawan` - Old employee add/update

## Critical Patterns

### AJAX Handler Template
```php
$ret = array('status' => 'success', 'message' => '', 'data' => array());
if (!empty($_POST['api_key']) && $_POST['api_key'] == get_option(ABSEN_APIKEY)) {
    // Process request with $wpdb->prepare()
} else {
    $ret['status'] = 'error';
    $ret['message'] = 'Api key tidak ditemukan!';
}
```

### Hook Registration
```php
$this->loader->add_action('wp_ajax_action_name', $instance, 'method_name');
$this->loader->add_action('wp_ajax_nopriv_action_name', $instance, 'method_name');
add_shortcode('shortcode_name', array($instance, 'method_name'));
```

### Soft Delete (never use DELETE)
```php
$wpdb->update('absensi_data_pegawai', array('active' => 0), array('id' => $id));
```

### Toggle Status Pattern
```php
// Get current status then toggle
$current = $wpdb->get_var($wpdb->prepare("SELECT active FROM table WHERE id = %d", $id));
$new_status = ($current == 1) ? 0 : 1;
$wpdb->update('table', array('active' => $new_status), array('id' => $id));
```

### Permission Filtering Pattern
```php
$current_user = wp_get_current_user();
if (in_array('administrator', $current_user->roles)) {
    // Show all records
} else if (in_array('admin_instansi', $current_user->roles)) {
    // Filter by id_instansi or id_user
    $where .= " AND id_instansi = " . $instansi_id;
}
```

### Auto User Creation (Institution)
```php
// When adding institution with username:
// 1. Create role if needed
if (!get_role('admin_instansi')) {
    add_role('admin_instansi', 'Admin Instansi', array('read' => true));
}
// 2. Create user (username = password)
$user_id = wp_insert_user(array(
    'user_login' => $username,
    'user_pass' => $username,
    'role' => 'admin_instansi'
));
// 3. Set force password change
update_user_meta($user_id, 'absen_force_password_change', 1);
// 4. Link to institution
$wpdb->update('absensi_data_instansi', array('id_user' => $user_id), array('id' => $id));
```

### Using Traits
```php
require_once plugin_dir_path(dirname(__FILE__)) . 'public/trait/CustomTrait.php';

class Wp_Absen_Public_Pegawai {
    use CustomTraitAbsen;
    // ...
}
```

### JSON-Encoded Flexible Data (Kode Kerja)
```php
// Work schedules store multiple times as JSON arrays
$jam_masuk = json_encode(['08:00', '13:00']);  // Multiple check-in times
$jam_pulang = json_encode(['12:00', '17:00']); // Multiple check-out times
$hari_kerja = json_encode([1, 2, 3, 4, 5]);    // Monday-Friday
```

## Work Code System (Kode Kerja)

Work codes define attendance schedules with the following features:

- **Primary vs Secondary**: Each institution can have one primary schedule (is_primary = 1)
- **Flexible Hours**: JSON arrays for multiple check-in/check-out times per day
- **Working Days**: JSON array specifying which days (1=Monday to 7=Sunday)
- **Geofencing**: Coordinates (latitude, longitude) and radius for location validation
- **Tolerance**: Minutes allowed before/after scheduled time

## Daily Attendance System (Absensi Harian)

Real-time attendance tracking with:

- **Clock In/Out**: Separate timestamps for masuk (in) and pulang (out)
- **GPS Tracking**: Coordinates captured for both check-in and check-out
- **Photo Documentation**: Required foto_masuk (check-in photo) and foto_pulang (check-out photo) stored in `public/img/absensi/`
- **Status Types**: Hadir (present), Telat (late), Ijin (permission), Sakit (sick), Alpha (absent)
- **Manual Entry**: Admins can manually add/edit attendance records

## Activity System (Kegiatan)

Activity/event tracking for employees:

- **Activity Details**: nama_kegiatan (activity name), tanggal (date), jam_mulai/jam_selesai (start/end time)
- **Location**: tempat (place/venue)
- **Description**: uraian (activity description)
- **File Attachment**: Support for PDF, JPG, PNG uploads (stored in `public/img/kegiatan/`)
- **Permission Hierarchy**:
  - Administrator: Can manage all activities
  - Admin Instansi: Can manage activities for their institution
  - Pegawai: Can manage only their own activities
- **Soft Delete**: Uses `active` field (0/1)

## Leave/Permission System (Ijin)

Leave and permission request management with approval workflow:

- **Request Types (tipe_ijin)**: Sakit (sick), Ijin (permission), Cuti (leave), Dinas Luar (external duty)
- **Date Range**: tanggal_mulai and tanggal_selesai for multi-day requests
- **Approval Workflow**:
  - `Pending` - Initial status when submitted
  - `Approved` - Approved by admin
  - `Rejected` - Rejected by admin
- **File Attachment**: Support for PDF, JPG, PNG uploads (stored in `public/img/ijin/`)
- **Permission Logic**:
  - Pegawai: Can submit requests, edit/delete only if status is Pending
  - Admin Instansi: Can manage requests for their institution, approve/reject
  - Administrator: Full access to all requests

## Constants (defined in wp-absen.php)
- `ABSEN_PLUGIN_URL` - Plugin URL path
- `ABSEN_PLUGIN_PATH` - Plugin file path
- `ABSEN_APIKEY` - Option name: `_crb_apikey_absen`
- `WP_ABSEN_VERSION` - Current version: 1.0.0

## Frontend Libraries
- Bootstrap 4.3.1 (CSS + Bundle JS)
- DataTables (server-side processing)
- Select2 (enhanced dropdowns)
- Chart.js (charts)
- Animate.css (animations)
- Vegas.js (background slideshow/video)
- SweetAlert2 (alerts, CDN)
- Leaflet.js v1.9.4 (maps, CDN)
- DateRangePicker (date selection, CDN)

### Admin Libraries
- JSZip + XLSX.js (Excel import/export)

## ABSEN_Functions Utility Methods

| Method | Purpose |
|--------|---------|
| `generateRandomString()` | Generate random strings for API keys |
| `CekNull()` | Number padding utility |
| `user_has_role()` | Check if user has specific role |
| `get_option_complex()` | Complex option retrieval for Carbon Fields |
| `get_option_multiselect()` | Multiselect option handling |
| `curl_post()` | cURL POST wrapper |
| `uploadTelegram()` | Telegram integration |
| `allow_access_private_post()` | Toggle post status via encoded key |
| `gen_key()` / `decode_key()` | Secure URL key generation/validation |
| `get_link_post()` | Generate secured links with keys |
| `generatePage()` | Auto-create/update WordPress pages with shortcodes |
| `shortcode_ubah_password()` | Password change form shortcode |
| `absen_change_password()` | AJAX handler for password change |

## Master Data Reference

### Status Pegawai (status_kerja)
| Value | Label |
|-------|-------|
| 1 | Tetap |
| 2 | Kontrak |
| 3 | Magang |
| 4 | Probation |
| 5 | Lainnya |

### Status Absensi Harian
| Value | Label |
|-------|-------|
| Hadir | Present |
| Telat | Late |
| Ijin | Permission |
| Sakit | Sick |
| Alpha | Absent |

### Jenis Absensi (Legacy)
| Value | Label |
|-------|-------|
| Masuk | Regular attendance |
| Ijin | Permission/leave |
| Sakit | Sick leave |
| GantiHari | Substitute day |
| Alasan | Excused absence |
| Cuti | Annual leave |
| Lembur | Overtime |

### User Roles
| Value | Label |
|-------|-------|
| admin_instansi | Admin Instansi |
| pegawai | Pegawai |

## Indonesian Terminology
- `pegawai` - Employee
- `instansi` - Institution/Agency
- `absensi` - Attendance
- `kode_kerja` - Work code/schedule
- `tahun_anggaran` - Fiscal year
- `SKPD` - Satuan Kerja Perangkat Daerah (Regional Work Unit)
- `lembur` - Overtime
- `cuti` - Leave
- `ijin` - Permission/leave request
- `NIK` - Nomor Induk Kependudukan (National ID Number)
- `NIP` - Nomor Induk Pegawai (Employee ID Number)
- `jam_masuk` - Check-in time
- `jam_pulang` - Check-out time
- `hari_kerja` - Working days
- `kegiatan` - Activity/event

## Tipe Ijin (Leave Types)
| Value | Label |
|-------|-------|
| Sakit | Sick leave |
| Ijin | Permission/leave |
| Cuti | Annual leave |
| Dinas Luar | External duty |

## Status Ijin (Leave Status)
| Value | Label |
|-------|-------|
| Pending | Awaiting approval |
| Approved | Approved by admin |
| Rejected | Rejected by admin |

## File Upload Directories

| Directory | Purpose |
|-----------|---------|
| `public/img/kegiatan/` | Activity file attachments |
| `public/img/ijin/` | Leave request attachments (medical certificates, etc.) |
| `public/img/absensi/` | Attendance photo attachments (check-in/check-out selfies) |

### File Naming Convention
- Kegiatan files: `kegiatan_[timestamp].[ext]`
- Ijin files: `ijin_[timestamp].[ext]`
- Absensi photos: `absensi_[masuk/pulang]_[id_pegawai]_[timestamp].[ext]`

## Maintenance

When making significant changes to the codebase (new dependencies, refactoring, architectural changes), update this CLAUDE.md file to keep documentation in sync.
