<?php
global $wpdb;

if (!defined('WPINC')) {
    die;
}

$current_user = wp_get_current_user();
$is_admin_instansi = in_array('admin_instansi', (array) $current_user->roles) && !in_array('administrator', (array) $current_user->roles);
$current_user_id = $current_user->ID;

?>

<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style type="text/css">
    .wrap-table {
        overflow: auto;
        max-height: 100vh;
        width: 100%;
    }
</style>

<div class="cetak">
    <div style="padding: 10px; margin: 0 0 3rem 0">
        <input type="hidden" value="<?php echo get_option(ABSEN_APIKEY); ?>" id="api_key" />
        <h1 class="text-center" style="margin: 3rem">
            Manajemen Data Kode Kerja
        </h1>
        <div style="margin-bottom: 25px">
            <button class="btn btn-primary" onclick="tambah_data_kode_kerja()">
                <span class="dashicons dashicons-plus"></span> Tambah Data
            </button>
        </div>
        <div class="table-responsive">
            <table id="management_data_table">
                <thead>
                    <tr>
                        <th class="text-center">Nama Kode Kerja</th>
                        <th class="text-center">Jenis</th>
                        <th class="text-center">Admin Instansi</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" style="width: 200px">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade mt-4" id="modalTambahDataKodeKerja" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false" aria-labelledby="modalTambahDataKodeKerjaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTambahDataKodeKerjaLabel">Data Kode Kerja</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="id_data" name="id_data" />
                <div class="form-group">
                    <label for="nama_kerja">Nama Kode Kerja <span style="color:red">*</span></label>
                    <input type="text" id="nama_kerja" name="nama_kerja" class="form-control" />
                </div>
                <div class="form-group">
                    <label for="admin_instansi">Admin Instansi <span style="color:red">*</span></label>
                    <select class="form-control" id="admin_instansi"></select>
                </div>
                <div class="form-group">
                    <label for="jenis">Jenis <span style="color:red">*</span></label>
                    <select id="jenis" name="jenis" class="form-control">
                        <option value="Primary">Primary (Jadwal Utama)</option>
                        <option value="Secondary">Secondary (Jadwal Khusus)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="koordinat">Koordinat Lokasi (Latitude, Longitude) <span style="color:red">*</span></label>
                    <input type="text" id="koordinat" name="koordinat" class="form-control" placeholder="-7.589..., 111.419..." />
                </div>
                <div class="form-group">
                    <div id="map" style="height: 300px; width: 100%"></div>
                    <small class="text-muted">Klik pada peta atau geser marker untuk menentukan lokasi.</small>
                </div>
                <div class="form-group">
                    <label for="radius_meter">Jarak Maksimal Absen (Meter) <span style="color:red">*</span></label>
                    <input type="number" id="radius_meter" name="radius_meter" class="form-control" value="100" />
                </div>

                <div class="form-group">
                    <label style="display: block; font-weight: bold; margin-bottom: 10px;">Jadwal Kerja Per Hari <span style="color:red">*</span></label>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th width="10" class="text-center"><input type="checkbox" id="check_all_days" onclick="toggleAllDays(this)"></th>
                                    <th>Hari</th>
                                    <th width="150">Jam Masuk</th>
                                    <th width="150">Jam Pulang</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $days = ['Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'];
                                foreach ($days as $key => $label) : ?>
                                    <tr>
                                        <td class="text-center">
                                            <input type="checkbox" class="day-check" name="hari_kerja[]" value="<?php echo $key; ?>" id="check_<?php echo $key; ?>" onchange="toggleTimeInputs('<?php echo $key; ?>')">
                                        </td>
                                        <td><label for="check_<?php echo $key; ?>" style="font-weight: normal; cursor: pointer; margin:0;"><?php echo $label; ?></label></td>
                                        <td><input type="time" class="form-control time-input time-in" id="jam_masuk_<?php echo $key; ?>" disabled value="08:00"></td>
                                        <td><input type="time" class="form-control time-input time-out" id="jam_pulang_<?php echo $key; ?>" disabled value="16:00"></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" onclick="submitTambahData()">Simpan</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
    let map;
    let marker;
    let isAdminInstansi = <?php echo $is_admin_instansi ? 'true' : 'false'; ?>;
    let currentUserId = '<?php echo $current_user_id; ?>';

    jQuery(document).ready(() => {
        jQuery('.mg-card-box').parent().removeClass('col-md-8').addClass('col-md-12');
        jQuery('#secondary').parent().remove();
        load_master_data();
        get_data_kode_kerja();
    });

    function get_data_kode_kerja() {
        if (typeof datatable == 'undefined') {
            window.datatable = jQuery('#management_data_table').on('preXhr.dt', (e, settings, data) => {
                jQuery("#wrap-loading").show();
            }).DataTable({
                "processing": true,
                "serverSide": true,
                "responsive": true,
                "rowReorder": {
                    selector: 'td:nth-child(2)'
                },
                "ajax": {
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'post',
                    dataType: 'json',
                    data: {
                        'action': 'get_datatable_kode_kerja',
                        'api_key': '<?php echo get_option(ABSEN_APIKEY); ?>'
                    }
                },
                lengthMenu: [
                    [20, 50, 100, -1],
                    [20, 50, 100, "All"]
                ],
                order: [
                    [2, 'desc']
                ], // ID Desc
                "drawCallback": (settings) => {
                    jQuery("#wrap-loading").hide();
                },
                "columns": [{
                        "data": 'nama_kerja',
                        className: "text-center"
                    },
                    {
                        "data": 'jenis',
                        className: "text-center"
                    },
                    {
                        "data": 'nama_instansi',
                        className: "text-center"
                    }, // Verify this key matches backend
                    {
                        "data": 'status_badge',
                        className: "text-center"
                    },
                    {
                        "data": 'aksi',
                        className: "text-center"
                    }
                ]
            });
        } else {
            datatable.draw();
        }
    }

    function load_master_data() {
        jQuery.ajax({
            method: 'post',
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            dataType: 'json',
            data: {
                'action': 'get_master_data',
                'api_key': '<?php echo get_option(ABSEN_APIKEY); ?>'
            },
            success: (res) => {
                if (res.status == 'success') {
                    let options = '<option value="">-- Pilih Admin Instansi --</option>';
                    res.data.admin_instansi.forEach((item) => {
                        options += `<option value="${item.value}">${item.label}</option>`;
                    });
                    jQuery('#admin_instansi').html(options);
                }
            }
        });
    }

    function tambah_data_kode_kerja() {
        jQuery('#id_data').val('');
        jQuery('#nama_kerja').val('');
        jQuery('#jenis')
            .val('Primary')
            .prop('disabled', true);

        jQuery('#jenis option[value="Primary"]')
            .prop('disabled', false)
            .show();
        jQuery('#koordinat').val('');
        jQuery('#radius_meter').val('100');
        jQuery('#admin_instansi').val('');

        // Handle Restricted Access and Auto-Check
        if (isAdminInstansi) {
            jQuery('#admin_instansi').val(currentUserId).trigger('change').prop('disabled', true);
            checkPrimaryAvailability(currentUserId);
        } else {
            jQuery('#admin_instansi').prop('disabled', false);
        }

        // Reset Schedule
        jQuery('.day-check').prop('checked', false);
        jQuery('.time-input').prop('disabled', true).val('08:00');
        jQuery('.time-out').val('16:00');

        // Set Default Days (Mon-Fri)
        let defaultDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        defaultDays.forEach((day) => {
            jQuery('#check_' + day).prop('checked', true).trigger('change');
        });

        jQuery('#modalTambahDataKodeKerja').modal('show');
        setTimeout(() => {
            initMap();
        }, 500);
    }

    // Add Admin Instansi Listener
    jQuery('#admin_instansi').on('change', function() {
        let id_instansi = jQuery(this).val();
        if (id_instansi) {
            checkPrimaryAvailability(id_instansi);
        } else {
            jQuery('#jenis').prop('disabled', true);
        }
    });

    function checkPrimaryAvailability(id_instansi, exclude_id = 0) {
        jQuery.ajax({
            method: 'post',
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            dataType: 'json',
            data: {
                'action': 'check_primary_kode_kerja',
                'api_key': '<?php echo get_option(ABSEN_APIKEY); ?>',
                'id_instansi': id_instansi,
                'exclude_id': exclude_id
            },
            success: (res) => {
                if (res.status == 'success') {
                    if (res.data.primary_exists) {
                        jQuery('#jenis').val('Secondary');
                        // Option 1: Disable user interaction but keep value
                        // jQuery('#jenis option[value="Primary"]').prop('disabled', true);
                        // jQuery('#jenis').prop('disabled', false); // Allow seeing it's secondary

                        // User request: "Select secondary by default... and if not allow both"
                        // Making Primary unselectable/hidden is safer
                        jQuery('#jenis option[value="Primary"]').prop('disabled', true).hide();
                        jQuery('#jenis').prop('disabled', false);
                    } else {
                        jQuery('#jenis').val('Primary');
                        jQuery('#jenis option[value="Primary"]').prop('disabled', false).show();
                        jQuery('#jenis').prop('disabled', false);
                    }
                }
            }
        });
    }

    function edit_data(id) {
        jQuery('#wrap-loading').show();
        jQuery.ajax({
            method: 'post',
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            dataType: 'json',
            data: {
                'action': 'get_data_kode_kerja_by_id',
                'api_key': '<?php echo get_option(ABSEN_APIKEY); ?>',
                'id': id
            },
            success: (res) => {
                if (res.status == 'success') {
                    jQuery('#id_data').val(res.data.id);
                    jQuery('#nama_kerja').val(res.data.nama_kerja);
                    jQuery('#jenis').val(res.data.jenis);
                    jQuery('#koordinat').val(res.data.koordinat);
                    jQuery('#radius_meter').val(res.data.radius_meter);
                    jQuery('#admin_instansi').val(res.data.id_instansi);

                    if (isAdminInstansi) {
                        jQuery('#admin_instansi').prop('disabled', true);
                    } else {
                        jQuery('#admin_instansi').prop('disabled', false);
                    }

                    // Check availability excluding current ID
                    // Note: If current is Primary, we want to allow it to stay Primary
                    // If current is Secondary, we check if Primary exists elsewhere

                    // Simple logic: Trigger check but handle current status
                    if (res.data.jenis == 'Primary') {
                        jQuery('#jenis').prop('disabled', false);
                        jQuery('#jenis option[value="Primary"]').prop('disabled', false).show();
                    } else {
                        checkPrimaryAvailability(res.data.id_instansi, res.data.id);
                    }

                    // Populate Schedule
                    jQuery('.day-check').prop('checked', false);
                    jQuery('.time-input').prop('disabled', true);

                    try {
                        let days = res.data.hari_kerja || [];
                        let jamMasuk = res.data.jam_masuk || {};
                        let jamPulang = res.data.jam_pulang || {};

                        // Fallback string parsing if not auto-decoded by simple JSON
                        if (typeof days === 'string') days = JSON.parse(days);
                        if (typeof jamMasuk === 'string') jamMasuk = JSON.parse(jamMasuk);
                        if (typeof jamPulang === 'string') jamPulang = JSON.parse(jamPulang);

                        if (Array.isArray(days)) {
                            days.forEach((d) => {
                                jQuery('#check_' + d).prop('checked', true);
                                jQuery('#jam_masuk_' + d).prop('disabled', false);
                                jQuery('#jam_pulang_' + d).prop('disabled', false);

                                if (jamMasuk[d]) jQuery('#jam_masuk_' + d).val(jamMasuk[d]);
                                if (jamPulang[d]) jQuery('#jam_pulang_' + d).val(jamPulang[d]);
                            });
                        }
                    } catch (e) {
                        console.log(e);
                    }

                    jQuery('#modalTambahDataKodeKerja').modal('show');
                    setTimeout(() => {
                        initMap(res.data.koordinat);
                    }, 500);
                } else {
                    alert(res.message);
                }
                jQuery('#wrap-loading').hide();
            }
        });
    }

    function hapus_kode_kerja(id) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                jQuery('#wrap-loading').show();
                jQuery.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'post',
                    dataType: 'json',
                    data: {
                        'action': 'hapus_data_kode_kerja_by_id',
                        'api_key': '<?php echo get_option(ABSEN_APIKEY); ?>',
                        'id': id
                    },
                    success: (res) => {
                        jQuery('#wrap-loading').hide();
                        if (res.status == 'success') {
                            Swal.fire('Terhapus!', res.message, 'success');
                            get_data_kode_kerja();
                        } else {
                            Swal.fire('Gagal', res.message, 'error');
                        }
                    },
                    error: (err) => {
                        jQuery('#wrap-loading').hide();
                        Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
                        console.log(err);
                    }
                });
            }
        });
    }

    function toggle_status_kode_kerja_js(id, current_status) {
        let action = current_status == 1 ? 'Nonaktifkan' : 'Aktifkan';
        let actionText = current_status == 1 ? 'Data akan disembunyikan dari absensi' : 'Data akan muncul kembali di absensi';

        Swal.fire({
            title: 'Konfirmasi ' + action,
            text: actionText,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, ' + action + '!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                jQuery('#wrap-loading').show();
                jQuery.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'post',
                    dataType: 'json',
                    data: {
                        'action': 'toggle_status_kode_kerja',
                        'api_key': '<?php echo get_option(ABSEN_APIKEY); ?>',
                        'id': id,
                        'current_status': current_status
                    },
                    success: (res) => {
                        jQuery('#wrap-loading').hide();
                        if (res.status == 'success') {
                            Swal.fire('Berhasil', res.message, 'success');
                            get_data_kode_kerja();
                        } else {
                            Swal.fire('Gagal', res.message, 'error');
                        }
                    },
                    error: (err) => {
                        jQuery('#wrap-loading').hide();
                        Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
                        console.log(err);
                    }
                });
            }
        });
    }

    function submitTambahData() {
        let id_data = jQuery('#id_data').val();
        let nama_kerja = jQuery('#nama_kerja').val();
        let admin_instansi = jQuery('#admin_instansi').val();
        // If disabled (for admin instansi), value might not submit normally, but we handle it in backend via user ID session check too. 
        // However, ensure value is grabbed.

        if (nama_kerja == '') return alert('Nama Kode Kerja wajib diisi!');
        if (admin_instansi == '') return alert('Admin Instansi wajib dipilih!');
        if (jQuery('#jenis').val() == '') return alert('Jenis Kode Kerja wajib dipilih!');
        if (jQuery('#koordinat').val() == '') return alert('Koordinat Lokasi wajib diisi!');
        if (jQuery('#radius_meter').val() == '') return alert('Jarak Maksimal Absen wajib diisi!');

        let hari_kerja = [];
        let jam_masuk = {};
        let jam_pulang = {};

        jQuery('.day-check:checked').map(function(a, b) {
            let day = jQuery(b).val();
            hari_kerja.push(day);
            jam_masuk[day] = jQuery('#jam_masuk_' + day).val();
            jam_pulang[day] = jQuery('#jam_pulang_' + day).val();
        });

        if (hari_kerja.length == 0) return alert('Pilih minimal satu hari kerja!');

        jQuery('#wrap-loading').show();
        jQuery.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'post',
            dataType: 'json',
            data: {
                'action': 'tambah_data_kode_kerja',
                'api_key': '<?php echo get_option(ABSEN_APIKEY); ?>',
                'id_data': id_data,
                'nama_kerja': nama_kerja,
                'jenis': jQuery('#jenis').val(),
                'admin_instansi': admin_instansi,
                'koordinat': jQuery('#koordinat').val(),
                'radius_meter': jQuery('#radius_meter').val(),
                'jam_masuk': jam_masuk,
                'jam_pulang': jam_pulang,
                'hari_kerja': hari_kerja
            },
            success: (res) => {
                jQuery('#wrap-loading').hide();
                alert(res.message);
                if (res.status == 'success') {
                    jQuery('#modalTambahDataKodeKerja').modal('hide');
                    get_data_kode_kerja();
                }
            }
        });
    }

    /* MAP LOGIC */
    function initMap(initialCoords) {
        let defaultLat = -7.589537668248559;
        let defaultLng = 111.41982078552246;
        let zoomLevel = 16;

        if (initialCoords) {
            let parts = initialCoords.split(',');
            if (parts.length == 2) {
                defaultLat = parseFloat(parts[0].trim());
                defaultLng = parseFloat(parts[1].trim());
            }
        } else if (navigator.geolocation && !marker) {
            navigator.geolocation.getCurrentPosition((position) => {
                if (!marker) {
                    map.setView([position.coords.latitude, position.coords.longitude], 16);
                    updateMarker(position.coords.latitude, position.coords.longitude);
                }
            });
        }

        if (map) {
            map.remove();
            marker = null;
        }
        map = L.map('map').setView([defaultLat, defaultLng], zoomLevel);
        setTimeout(() => {
            map.invalidateSize();
        }, 100);

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19
        }).addTo(map);

        if (initialCoords) updateMarker(defaultLat, defaultLng);

        map.on('click', (e) => {
            updateMarker(e.latlng.lat, e.latlng.lng);
        });
    }

    function updateMarker(lat, lng) {
        if (marker) marker.setLatLng([lat, lng]);
        else {
            marker = L.marker([lat, lng], {
                draggable: true
            }).addTo(map);
            marker.on('dragend', (e) => {
                let position = marker.getLatLng();
                updateInput(position.lat, position.lng);
            });
        }
        updateInput(lat, lng);
        map.panTo([lat, lng]);
    }

    function updateInput(lat, lng) {
        jQuery('#koordinat').val(lat + ", " + lng);
    }

    function toggleAllDays(el) {
        jQuery('.day-check').prop('checked', el.checked).trigger('change');
    }

    function toggleTimeInputs(day) {
        let isChecked = jQuery('#check_' + day).is(':checked');
        jQuery('#jam_masuk_' + day).prop('disabled', !isChecked);
        jQuery('#jam_pulang_' + day).prop('disabled', !isChecked);
    }
</script>