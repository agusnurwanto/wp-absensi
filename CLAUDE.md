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
- `includes/` - Core classes: main orchestrator, loader, activator, functions utility
- `admin/` - WordPress admin functionality, Carbon Fields integration
- `public/` - Frontend: shortcode handlers, AJAX endpoints, view templates in `partials/`
- `public/trait/` - Shared PHP traits for public classes
- `vendor/` - Composer dependencies (Carbon Fields)
- `core/Libraries/` - Custom libraries (Plugin_Update_Warning)

### Key Classes
| Class | File | Role |
|-------|------|------|
| Wp_Absen | includes/class-wp-absen.php | Main orchestrator, loads dependencies and hooks |
| Wp_Absen_Admin | admin/class-wp-absen-admin.php | Admin area, Carbon Fields options, Excel import, WP-SIPD integration |
| Wp_Absen_Public | public/class-wp-absen-public.php | Attendance management, legacy employee handlers, menu shortcode |
| Wp_Absen_Public_Instansi | public/class-wp-absen-public-instansi.php | Institution management with geofencing, auto user creation |
| Wp_Absen_Public_Pegawai | public/class-wp-absen-public-pegawai.php | Employee management, master data, copy between years |
| ABSEN_Functions | includes/class-wp-absen-functions.php | Utility library (API keys, uploads, Telegram, password management) |
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
| `absensi_data` | Attendance/overtime header records |
| `absensi_data_detail` | Detailed attendance entries per employee |
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
| `[menu_absensi]` | Wp_Absen_Public | Dynamic menu based on user role |
| `[ubah_password_absen]` | ABSEN_Functions | Password change form with force-change support |

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
- `copy_data_pegawai` - Copy employees between fiscal years
- `get_master_data` - All dropdown master data
- `get_master_jenis_kelamin` - Gender options (L/P)
- `get_master_agama` - Religion options
- `get_master_pendidikan` - Education levels (SD to S3)
- `get_master_status_pegawai` - Employment status types
- `get_master_user_role` - User role options

### Instansi Management (Wp_Absen_Public_Instansi)
- `get_datatable_instansi` - DataTable with permission filtering
- `tambah_data_instansi` - Add/update institution (auto-creates WordPress user)
- `get_data_instansi_by_id` - Get institution details
- `hapus_data_instansi_by_id` - Soft delete
- `get_master_instansi` - Institution dropdown with permission filtering
- `get_users_for_instansi` - Get WordPress users
- `mutakhirkan_user_instansi` - Update/create user for institution

### Password Management (ABSEN_Functions)
- `absen_change_password` - Change password, clears force-change flag

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

### Permission Filtering Pattern
```php
$current_user = wp_get_current_user();
if (in_array('administrator', $current_user->roles)) {
    // Show all records
} else {
    // Filter by id_user or id_instansi
    $where .= " AND id_user = " . $current_user->ID;
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
- SweetAlert2 (alerts, CDN)
- Leaflet.js v1.9.4 (maps, CDN)
- DateRangePicker (date selection, CDN)

### Admin Libraries
- JSZip + XLSX.js (Excel import/export)

## Master Data Reference

### Status Pegawai (status_kerja)
| Value | Label |
|-------|-------|
| 1 | Tetap |
| 2 | Kontrak |
| 3 | Magang |
| 4 | Probation |
| 5 | Lainnya |

### Jenis Absensi
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
- `tahun_anggaran` - Fiscal year
- `SKPD` - Satuan Kerja Perangkat Daerah (Regional Work Unit)
- `lembur` - Overtime
- `cuti` - Leave
- `NIK` - Nomor Induk Kependudukan (National ID Number)
- `NIP` - Nomor Induk Pegawai (Employee ID Number)

## Maintenance

When making significant changes to the codebase (new dependencies, refactoring, architectural changes), update this CLAUDE.md file to keep documentation in sync.