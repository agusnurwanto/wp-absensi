<?php

global $wpdb;

if (!defined('WPINC')) {
    die;
}

$input = shortcode_atts(array(
    'tahun_anggaran' => '2026',
), $atts);

?>

<link
    rel="stylesheet"
    type="text/css"
    href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css"
/>
<link
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
    crossorigin=""
/>
<script
    src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
    crossorigin=""
></script>
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
        <input type="hidden" value="<?php echo get_option( ABSEN_APIKEY ); ?>" id="api_key" />
        <h1 class="text-center" style="margin: 3rem">
            Manajemen Data Instansi<br />Tahun <?php echo $input['tahun_anggaran']; ?>
        </h1>
        <?php
        $current_user = wp_get_current_user();
        $is_admin = in_array( 'administrator', (array) $current_user->roles );
        if ($is_admin) : ?>
        <div style="margin-bottom: 25px">
            <button class="btn btn-primary" onclick="tambah_data_instansi()">
                <span class="dashicons dashicons-plus"></span> Tambah Data
            </button>
        </div>
        <?php endif; ?>
        <div class="wrap-table">
            <table id="management_data_table" cellpadding="2" cellspacing="0">
                <thead>
                    <tr>
                        <th class="text-center">Nama Instansi</th>
                        <th class="text-center">Alamat</th>
                        <th class="text-center">Username Admin</th>
                        <th class="text-center">Email Instansi</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" style="width: 200px">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
<div
    class="modal fade mt-4"
    id="modalTambahDataInstansi"
    tabindex="-1"
    role="dialog"
    aria-labelledby="modalTambahDataInstansiLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTambahDataInstansiLabel">Data Instansi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="id_data" name="id_data" placeholder="" />
                <div class="form-group">
                    <label for="nama_instansi" style="display: inline-block">Nama Instansi</label>
                    <input type="text" id="nama_instansi" name="nama_instansi" class="form-control" placeholder="" />
                </div>
                <div class="form-group">
                    <label for="username" style="display: inline-block">Username (Login Admin Instansi)</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        class="form-control"
                    />
                </div>
                <div class="form-group">
                    <label for="email_instansi" style="display: inline-block">Email Instansi</label>
                    <input
                        type="email"
                        id="email_instansi"
                        name="email_instansi"
                        class="form-control"
                        placeholder="Contoh: youremail@example.com"
                    />
                </div>
                <div class="form-group">
                    <label for="alamat_instansi" style="display: inline-block"> Alamat Instansi </label>
                    <input
                        type="text"
                        id="alamat_instansi"
                        name="alamat_instansi"
                        class="form-control"
                        placeholder=""
                    />
                </div>
                <div class="form-group">
                    <label for="nama_kerja" style="display: inline-block">Nama Kode Kerja</label>
                    <input type="text" id="nama_kerja" name="nama_kerja" class="form-control" />
                </div>
                <div class="form-group">
                    <label for="koordinat" style="display: inline-block"
                        >Koordinat Lokasi Kantor (Latitude, Longitude)</label
                    >
                    <input
                        type="text"
                        id="koordinat"
                        name="koordinat"
                        class="form-control"
                        placeholder="Contoh: -7.589537668248559, 111.41982078552246"
                    />
                </div>
                <div class="form-group">
                    <div id="map" style="height: 300px; width: 100%"></div>
                    <small class="text-muted">Klik pada peta atau geser marker untuk menentukan lokasi.</small>
                </div>
                <div class="form-group">
                    <label for="radius_meter" style="display: inline-block">Jarak Maksimal Absen (Meter)</label>
                    <input
                        type="number"
                        id="radius_meter"
                        name="radius_meter"
                        class="form-control"
                    />
                </div>
                <div class="form-group">
                    <label style="display: block; font-weight: bold; margin-bottom: 10px;">Jadwal Kerja Per Hari</label>
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
                            <tbody id="schedule_body">
                                <?php
                                $days = [
                                    'Monday' => 'Senin',
                                    'Tuesday' => 'Selasa',
                                    'Wednesday' => 'Rabu',
                                    'Thursday' => 'Kamis',
                                    'Friday' => 'Jumat',
                                    'Saturday' => 'Sabtu',
                                    'Sunday' => 'Minggu'
                                ];
                                foreach ($days as $key => $label) :
                                ?>
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" class="day-check" name="hari_kerja[]" value="<?php echo $key; ?>" id="check_<?php echo $key; ?>" onchange="toggleTimeInputs('<?php echo $key; ?>')">
                                    </td>
                                    <td><label for="check_<?php echo $key; ?>" style="font-weight: normal; cursor: pointer; margin:0;"><?php echo $label; ?></label></td>
                                    <td>
                                        <input type="time" class="form-control time-input time-in" id="jam_masuk_<?php echo $key; ?>" disabled value="08:00">
                                    </td>
                                    <td>
                                        <input type="time" class="form-control time-input time-out" id="jam_pulang_<?php echo $key; ?>" disabled value="16:00">
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary submitBtn" onclick="submitTambahDataFormInstansi()">Simpan</button>
                <button type="submit" class="components-button btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>


<script>
    jQuery(document).ready(() => {
        // penyesuaian thema wp full width page
        jQuery('.mg-card-box').parent().removeClass('col-md-8').addClass('col-md-12');
        jQuery('#secondary').parent().remove();
        get_data_instansi();
    });

    function get_data_instansi() {
        if (typeof datainstansi == 'undefined') {
            window.datainstansi = jQuery('#management_data_table').on('preXhr.dt', (e, settings, data) => {
                jQuery("#wrap-loading").show();
            }).DataTable({
                "processing": true,
                "serverSide": true,
                "ajax": {
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'post',
                    dataType: 'json',
                    data: {
                        'action': 'get_datatable_instansi',
                        'api_key': '<?php echo get_option( ABSEN_APIKEY ); ?>',
                        'tahun': '<?php echo $input['tahun_anggaran']; ?>',

                    }
                },
                lengthMenu: [[20, 50, 100, -1], [20, 50, 100, "All"]],
                order: [[0, 'asc']],
                "drawCallback": ( settings ) => {
                    jQuery("#wrap-loading").hide();
                },
                "columns": [
                    {
                        "data": 'nama_instansi',
                        className: "text-center"
                    },
                    {
                        "data": 'alamat_instansi',
                        className: "text-center"
                    },
                    {
                        "data": 'username',
                        className: "text-center"
                    },
                    {
                        "data": 'email_instansi',
                        className: "text-center"
                    },
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
            datainstansi.draw();
        }

        // Hide delete buttons via CSS if not admin (cleaner than JS row callback)
        <?php if (!$is_admin) : ?>
        jQuery('body').append('<style>#management_data_table .btn-danger { display: none !important; }</style>');
        <?php endif; ?>
    }

    function hapus_data(id) {
        Swal.fire({
            title: 'Apakah anda yakin?',
            text: "Data instansi beserta user dan kode kerja akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                jQuery('#wrap-loading').show();
                jQuery.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'post',
                    data: {
                        'action' : 'hapus_data_instansi_by_id',
                        'api_key': '<?php echo get_option( ABSEN_APIKEY ); ?>',
                        'id'     : id
                    },
                    dataType: 'json',
                    success: (response) => {
                        jQuery('#wrap-loading').hide();
                        if (response.status == 'success') {
                            Swal.fire(
                                'Terhapus!',
                                response.message,
                                'success'
                            )
                            get_data_instansi();
                        } else {
                            Swal.fire(
                                'Gagal!',
                                response.message,
                                'error'
                            )
                        }
                    }
                });
            }
        })
    }

    function toggle_status_instansi(id, status) {
        let actionText = (status == 1) ? "Aktifkan" : "Nonaktifkan";
        let confirmText = (status == 1) ? "Data Instansi akan diaktifkan kembali." : "Data Instansi akan dinonaktifkan.";

        Swal.fire({
            title: 'Konfirmasi ' + actionText,
            text: confirmText,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, ' + actionText + '!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                jQuery('#wrap-loading').show();
                jQuery.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'post',
                    dataType: 'json',
                    data: {
                        'action': 'toggle_status_instansi',
                        'api_key': '<?php echo get_option( ABSEN_APIKEY ); ?>',
                        'id': id,
                        'status': status
                    },
                    success: (res) => {
                        jQuery('#wrap-loading').hide();
                        if (res.status == 'success') {
                            Swal.fire(
                                'Berhasil!',
                                res.message,
                                'success'
                            );
                            get_data_instansi(); // Refresh table
                        } else {
                            Swal.fire(
                                'Gagal!',
                                res.message,
                                'error'
                            );
                        }
                    },
                    error: () => {
                        jQuery('#wrap-loading').hide();
                        Swal.fire(
                            'Error!',
                            'Terjadi kesalahan server.',
                            'error'
                        );
                    }
                });
            }
        });
    }

    function edit_data(_id) {
        jQuery('#wrap-loading').show();

        jQuery.ajax({
            method: 'post',
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            dataType: 'json',
            data: {
                'action': 'get_data_instansi_by_id',
                'api_key': '<?php echo get_option( ABSEN_APIKEY ); ?>',
                'id': _id,
            },
            success: (res) => {
                if (res.status == 'success') {
                    jQuery('#id_data').val(res.data.id);
                    jQuery('#nama_instansi').val(res.data.nama_instansi);
                    jQuery('#nama_kerja').val(res.data.nama_kerja); // Populate nama_kerja
                    jQuery('#alamat_instansi').val(res.data.alamat_instansi);
                    jQuery('#koordinat').val(res.data.koordinat);
                    jQuery('#radius_meter').val(res.data.radius_meter);
                    jQuery('#username').val(res.data.username).attr('disabled', true);
                    jQuery('#email_instansi').val(res.data.email_instansi);

                    // Populate Days & Time
                    // Reset first
                    jQuery('.day-check').prop('checked', false);
                    jQuery('.time-input').prop('disabled', true);

                    try {
                        // Safe JSON Parse helper
                        let parseSchedule = (str) => {
                            try {
                                return JSON.parse(str); 
                            } catch(e) { 
                                return str; 
                            }
                        };

                        let days = parseSchedule(res.data.hari_kerja);
                        let jamMasuk = parseSchedule(res.data.jam_masuk);
                        let jamPulang = parseSchedule(res.data.jam_pulang);

                        // Handle legacy (string) or empty days
                        if (!Array.isArray(days)) {
                            if (typeof days === 'string' && days.indexOf('[') === 0) {
                                days = JSON.parse(days);
                            } else if (typeof days === 'string' && days.includes(',')) {
                                days = days.split(',');
                            } else if (typeof days === 'string' && days.length > 0) {
                                days = [days];
                            } else {
                                days = [];
                            }
                        }

                        days.forEach((d) => {
                            // Trim in case of weird whitespace
                            d = d.trim();

                            // Check the day
                            jQuery('#check_' + d).prop('checked', true);

                            // Enable inputs
                            jQuery('#jam_masuk_' + d).prop('disabled', false);
                            jQuery('#jam_pulang_' + d).prop('disabled', false);

                            // Set Times
                            // Case 1: New JSON format (Object: Day -> Time)
                            if (typeof jamMasuk === 'object' && jamMasuk !== null && jamMasuk[d]) {
                                jQuery('#jam_masuk_' + d).val(jamMasuk[d]);
                            } 
                            // Case 2: Legacy/Global String (Apply to all)
                            else if (typeof jamMasuk === 'string') {
                                jQuery('#jam_masuk_' + d).val(jamMasuk);
                            }

                            if (typeof jamPulang === 'object' && jamPulang !== null && jamPulang[d]) {
                                jQuery('#jam_pulang_' + d).val(jamPulang[d]);
                            } else if (typeof jamPulang === 'string') {
                                jQuery('#jam_pulang_' + d).val(jamPulang);
                            }
                        });

                    } catch(e) {
                        console.log('Error parsing schedule', e); 
                    }

                    jQuery('#modalTambahDataInstansi').modal('show');

                    setTimeout(() => {
                        initMap(res.data.koordinat);
                    }, 500);
                } else {
                    Swal.fire(
                        'Gagal!',
                        res.message,
                        'error'
                    )
                }

                jQuery('#wrap-loading').hide();
            }
        });
    }

    //show tambah data
    function tambah_data_instansi() {
        jQuery('#id_data').val('');
        jQuery('#nama_instansi').val('').attr('disabled', false);
        jQuery('#nama_kerja').val('');
        jQuery('#alamat_instansi').val('');
        jQuery('#koordinat').val('');
        jQuery('#radius_meter').val('100');
        jQuery('#username').val('').attr('disabled', false);
        jQuery('#email_instansi').val('').attr('disabled', false);
        
        // Reset Days & Time
        let defaultDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        jQuery('.day-check').prop('checked', false);
        jQuery('.time-input').prop('disabled', true).val('08:00'); // Reset all to default time first
        jQuery('.time-out').val('16:00');

        defaultDays.forEach((d) => {
            jQuery('#check_' + d).prop('checked', true);
            jQuery('#jam_masuk_' + d).prop('disabled', false);
            jQuery('#jam_pulang_' + d).prop('disabled', false);
        });

        jQuery('#modalTambahDataInstansi').modal('show');

        setTimeout(() => {
            initMap();
        }, 500);
    }

    function get_users_list(){
        jQuery.ajax({
            method: 'post',
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            dataType: 'json',
            data: {
                'action': 'get_users_for_instansi',
                'api_key': '<?php echo get_option( ABSEN_APIKEY ); ?>'
            },
            success: (res) => {
                if (res.status == 'success') {
                    let options = '<option value="0">-- Pilih User --</option>';
                    res.data.forEach((user) => {
                        options += '<option value="'+user.ID+'">'+user.display_name+' ('+user.user_login+')</option>';
                    });
                    jQuery('#id_user').html(options);
                }
            }
        });
    }

    let map;
    let marker;

    function initMap(initialCoords) {
        let defaultLat = -7.589537668248559;
        let defaultLng = 111.41982078552246;
        let zoomLevel = 16;

        if (initialCoords) {
            let parts = initialCoords.split(',');
            if (parts.length == 2) {
                defaultLat = parseFloat(parts[0].trim());
                defaultLng = parseFloat(parts[1].trim());
                zoomLevel = 16;
            }
        } else {
            // Try getting user location if no coords provided
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition((position) => {
                    if (!marker) { // Only if marker not already set (e.g. largely by manual input race condition)
                        let lat = position.coords.latitude;
                        let lng = position.coords.longitude;
                        map.setView([lat, lng], 16);
                        updateMarker(lat, lng);
                    }
                });
            }
        }

        if (map) {
            map.remove(); // Reset map if re-initializing
            marker = null;
        }

        map = L.map('map').setView([defaultLat, defaultLng], zoomLevel);

        // Force map to recalculate size after a short delay to ensure modal is fully rendered
        setTimeout(() => {
            map.invalidateSize();
        }, 100);

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);

        if (initialCoords) {
            updateMarker(defaultLat, defaultLng);
        }

        map.on('click', (e) => {
            updateMarker(e.latlng.lat, e.latlng.lng);
        });
    }

    function updateMarker(lat, lng) {
        if (marker) {
            marker.setLatLng([lat, lng]);
        } else {
            marker = L.marker([lat, lng], {draggable: true}).addTo(map);
            marker.on('dragend', (e) => {
                let position = marker.getLatLng();
                updateInput(position.lat, position.lng);
            });
        }

        updateInput(lat, lng);
        map.panTo([lat, lng]);
    }

    function toggleAllDays(el) {
        jQuery('.day-check').prop('checked', el.checked).trigger('change');
    }

    function toggleTimeInputs(day) {
        let isChecked = jQuery('#check_' + day).is(':checked');
        jQuery('#jam_masuk_' + day).prop('disabled', !isChecked);
        jQuery('#jam_pulang_' + day).prop('disabled', !isChecked);
    }

    function updateInput(lat, lng) {
        jQuery('#koordinat').val(lat + ", " + lng);
    }

    function submitTambahDataFormInstansi() {
        let id_data = jQuery('#id_data').val();

        let alamat_instansi = jQuery('#alamat_instansi').val();
        if (alamat_instansi == '') {
            return Swal.fire(
                'Gagal!',
                'Data alamat Instansi tidak boleh kosong!',
                'error'
            );
        }

        let nama_instansi = jQuery('#nama_instansi').val();
        if (nama_instansi == '') {
            return Swal.fire(
                'Gagal!',
                'Data nama Instansi tidak boleh kosong!',
                'error'
            );
        }
        
        let nama_kerja = jQuery('#nama_kerja').val();
        if (nama_kerja == '') {
            return Swal.fire(
                'Gagal!',
                'Nama Kode Kerja tidak boleh kosong!',
                'error'
            );
        }

        let koordinat = jQuery('#koordinat').val();
        let radius_meter = jQuery('#radius_meter').val();

        let username = jQuery('#username').val();
        let email_instansi = jQuery('#email_instansi').val();

        if (username == '') {
            return Swal.fire(
                'Gagal!',
                'Username tidak boleh kosong!',
                'error'
            );
        }
        if (email_instansi == '') {
            return Swal.fire(
                'Gagal!',
                'Email Instansi tidak boleh kosong!',
                'error'
            );
        }
        
        // GATHER SCHEDULE DATA
        let hari_kerja = [];
        let jam_masuk = {};
        let jam_pulang = {};

        jQuery('.day-check:checked').each((index, element) => {
            let day = jQuery(element).val();
            hari_kerja.push(day);
            jam_masuk[day] = jQuery('#jam_masuk_' + day).val();
            jam_pulang[day] = jQuery('#jam_pulang_' + day).val();
        });

        if (hari_kerja.length == 0) {
            return Swal.fire(
                'Gagal!',
                'Pilih minimal satu hari kerja!',
                'error'
            );
        }

        jQuery('#wrap-loading').show();
        jQuery.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'post',
            dataType: 'json',
            data: {
                'action': 'tambah_data_instansi',
                'api_key': '<?php echo get_option( ABSEN_APIKEY ); ?>',
                'id_data': id_data,
                'nama_instansi': nama_instansi,
                'nama_kerja': nama_kerja,
                'alamat_instansi': alamat_instansi,
                'tahun': '<?php echo $input['tahun_anggaran']; ?>',

                'koordinat': koordinat,
                'radius_meter': radius_meter,
                'username': username,
                'email_instansi': email_instansi,

                // Pass arrays/objects directly (jQuery handles serialization)
                'jam_masuk': jam_masuk,
                'jam_pulang': jam_pulang,
                'hari_kerja': hari_kerja
            },
            error: (res) => {
                Swal.fire(
                    'Gagal!',
                    res.message,
                    'error'
                );
                jQuery('#wrap-loading').hide();
            },
            success: (res) => {
                Swal.fire(
                    'Berhasil!',
                    res.message,
                    'success'
                );
                jQuery('#modalTambahDataInstansi').modal('hide');

                if (res.status == 'success') {
                    get_data_instansi();
                } else {
                    jQuery('#wrap-loading').hide();
                }
            }
        });
    }

</script>

