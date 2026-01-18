Implementation Plan - Daily Attendance & Permission System
Goal Description
Implement a daily attendance (Absensi) and permission (Ijin) system for employees (
pegawai
). This involves a frontend interface for employees to:

Clock In/Out: Select "Kode Kerja" and Record time/location.
Submit Permissions: Request leave (Cuti, Sakit, Dinas) with file attachments.

IMPORTANT

New Database Tables: I will be creating two new tables: absensi_harian and absensi_ijin. This separates daily attendance from the existing absensi_data (SPT) system to ensure a clean, independent flow as requested.

Proposed Changes
Database Setup
[MODIFY] 
tabel.sql
Add CREATE TABLE absensi_data_kerja:
id
, id_instansi, nama_kerja (e.g. "Kantor", "Upacara"), jam_masuk, jam_pulang, hari_kerja (JSON/Text e.g. "Mon,Tue,Wed,Thu,Fri"), koordinat, radius, tahun, 
jenis
 (Primary/Secondary), update_at, active (Default 1).
Add CREATE TABLE absensi_harian:
id
, id_pegawai, id_kode_kerja (FK), tanggal, waktu_masuk, waktu_pulang, 
status
 (Hadir/Telat/Alpha), koordinat_masuk, koordinat_pulang, tahun, update_at.
Add CREATE TABLE absensi_ijin:
id
, id_pegawai, tipe_ijin, jenis_ijin, alasan, tanggal_mulai, tanggal_selesai, file_lampiran, 
status
 (Pending/Approved), tahun, update_at.
Plugin Structure
[NEW] 
class-wp-absen-public-absensi.php
Class: Wp_Absen_Public_Absensi
[NEW] 
class-wp-absen-public-absensi.php
Class: Wp_Absen_Public_Absensi
Shortcodes:
[menu_absensi]: Unified Entry Point (Replaces existing usage)
If admin_instansi: Renders Admin Dashboard.
Tabs/Menu: Pengaturan Kerja, Laporan Perijinan, Monitoring Absensi.
If 
pegawai
: Renders Employee Attendance UI.
Time/Date Display.
"Kode Kerja" Selection.
Details: Jam Masuk/Pulang.
Map: Shows User Location + Target Zones (Primary & Secondary if applicable).
Buttons: Absen Datang / Absen Pulang.
If administrator: Shows Admin Dashboard (Superuser view).
Functions:
render_admin_dashboard(): Include admin partials.
render_employee_ui(): Include employee partials.
get_valid_kode_kerja($id_instansi): Returns work codes.
Functions:
get_valid_kode_kerja($id_instansi): Returns work codes.
submit_absensi: Handle Clock In/Out.
submit_ijin: Handle Permission Request.
Admin Functions:
save_kode_kerja, delete_kode_kerja: CRUD for absensi_data_kerja.
approve_ijin, reject_ijin: Update status in absensi_ijin.
manual_add_absensi: Admin override for attendance.
get_monitoring_data: Fetch combined daily/permission data for table.
[MODIFY] 
public/class-wp-absen-public-instansi.php
Update 
tambah_data_instansi
:
Ensure Primary Kode Kerja is synced.
Access Control:
Verify admin_instansi can ONLY see/manage their own Instansi data (already present, need validation).
[MODIFY] 
includes/class-wp-absen.php
Register logic.
get_master_tipe_ijin(): Returns ["Kehadiran", "Cuti", "Dinas Luar / Dinas Dalam"].
get_master_jenis_ijin(tipe): Returns list based on type.
If Kehadiran:
Sakit
Tidak Masuk Kerja Dengan Keterangan
Lembur
Masa Persiapan Pensiun (MPP)
Dinas Dalam (DD)
Sprint (Penugasan Kepala OPD)
Force Majeur / Kondisi Mendesak
Diklat
Sosialisasi / Workshop
Rapat / Seminar
If Cuti:
Cuti Besar
Cuti Sakit
Cuti Melahirkan
Cuti Alasan Penting
Cuti Tahunan
Cuti di Luar Tanggungan Negara (CLTN)
If Dinas Luar / Dinas Dalam:
Izin Meninggalkan Kantor
Tugas Lapangan
Bimbingan Teknis
Training / Pelatihan / Diklat
Dinas Luar (DL)
[MODIFY] 
includes/class-wp-absen.php
Register the new Wp_Absen_Public_Absensi class.
Register new AJAX hooks.
[MODIFY] 
admin/class-wp-absen-admin.php
Add logic to 
crb_absen_options
 or similar to automatically generate the pages for "Absensi Harian" and "Form Ijin" so they work out-of-the-box (similar to the Change Password fix).
Frontend
[NEW] public/partials/wp-absen-absensi-harian.php
UI: Display current time.
Dropdown: "Kode Kerja".
Button: "Absen Datang" (if not in) / "Absen Pulang" (if in).
[NEW] public/partials/wp-absen-form-ijin.php
UI: Form with:
Nama (Disabled, fetched from User).
Tipe Ijin (Radio/Select: Ijin Penuh, Ijin Tidak Absen Masuk, Ijin Tidak Absen Pulang).
Jenis Ijin (Select from Master Data above).
Alasan (Textarea).
Date Range Picker (Tanggal Mulai - Tanggal Selesai).
File Upload.
