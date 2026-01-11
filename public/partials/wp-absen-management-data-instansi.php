<?php
global $wpdb;

if (!defined('WPINC')) {
    die;
}

$input = shortcode_atts(array(
    'tahun_anggaran' => '2025',
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
        <div style="margin-bottom: 25px">
            <button class="btn btn-primary" onclick="tambah_data_instansi()">
                <i class="dashicons dashicons-plus"></i> Tambah Data
            </button>
        </div>
        <div class="wrap-table">
            <table id="management_data_table" cellpadding="2" cellspacing="0">
                <thead>
                    <tr>
                        <th class="text-center">Nama Instansi</th>
                        <th class="text-center">Alamat</th>
                        <th class="text-center">Koordinat</th>
                        <th class="text-center">Radius (m)</th>
                        <th class="text-center" style="width: 150px">Aksi</th>
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
                        value="100"
                        placeholder="100"
                    />
                </div>
                <div class="form-group">
                    <label for="id_user" style="display: inline-block">Admin Instansi (WP User)</label>
                    <select id="id_user" name="id_user" class="form-control">
                        <option value="0">-- Pilih User --</option>
                    </select>
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
                        "data": 'koordinat',
                        className: "text-center"
                    },
                    {
                        "data": 'radius_meter',
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
    }

    function hapus_data(id) {
        let confirmDelete = confirm("Apakah anda yakin akan menghapus data ini?");
        if (confirmDelete) {
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
                        get_data_instansi();
                    } else {
                        alert(`GAGAL! \n${response.message}`);
                    }
                }
            });
        }
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
                    jQuery('#alamat_instansi').val(res.data.alamat_instansi);
                    jQuery('#koordinat').val(res.data.koordinat);
                    jQuery('#radius_meter').val(res.data.radius_meter);
                    jQuery('#id_user').val(res.data.id_user);
                    jQuery('#modalTambahDataInstansi').modal('show');

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

    //show tambah data
    function tambah_data_instansi() {
        jQuery('#id_data').val('');
        jQuery('#nama_instansi').val('');
        jQuery('#alamat_instansi').val('');
        jQuery('#koordinat').val('');
        jQuery('#radius_meter').val('100');
        jQuery('#id_user').val('0');
        jQuery('#modalTambahDataInstansi').modal('show');

        // Populate users if empty
        if(jQuery('#id_user option').length <= 1){
            get_users_list();
        }

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
                if(res.status == 'success'){
                    var options = '<option value="0">-- Pilih User --</option>';
                    res.data.forEach(function(user){
                        options += '<option value="'+user.ID+'">'+user.display_name+' ('+user.user_login+')</option>';
                    });
                    jQuery('#id_user').html(options);
                }
            }
        });
    }

    var map;
    var marker;

    function initMap(initialCoords) {
        var defaultLat = -7.589537668248559;
        var defaultLng = 111.41982078552246;
        var zoomLevel = 16;

        if (initialCoords) {
            var parts = initialCoords.split(',');
            if (parts.length == 2) {
                defaultLat = parseFloat(parts[0].trim());
                defaultLng = parseFloat(parts[1].trim());
                zoomLevel = 16;
            }
        } else {
            // Try getting user location if no coords provided
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    if (!marker) { // Only if marker not already set (e.g. largely by manual input race condition)
                        var lat = position.coords.latitude;
                        var lng = position.coords.longitude;
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
                var position = marker.getLatLng();
                updateInput(position.lat, position.lng);
            });
        }

        updateInput(lat, lng);
        map.panTo([lat, lng]);
    }

    function updateInput(lat, lng) {
        jQuery('#koordinat').val(lat + ", " + lng);
    }

    function submitTambahDataFormInstansi() {
        var id_data = jQuery('#id_data').val();

        var alamat_instansi = jQuery('#alamat_instansi').val();
        if (alamat_instansi == '') {
            return alert('Data alamat Instansi tidak boleh kosong!');
        }

        var nama_instansi = jQuery('#nama_instansi').val();
        if (nama_instansi == '') {
            return alert('Data nama Instansi tidak boleh kosong!');
        }

        var koordinat = jQuery('#koordinat').val();
        var radius_meter = jQuery('#radius_meter').val();
        var id_user = jQuery('#id_user').val();

        jQuery('#wrap-loading').show();
        jQuery.ajax({
            method: 'post',
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            dataType: 'json',
            data: {
                'action': 'tambah_data_instansi',
                'api_key': '<?php echo get_option( ABSEN_APIKEY ); ?>',
                'id_data': id_data,
                'tahun': <?php echo $input['tahun_anggaran']; ?>,
                'alamat_instansi': alamat_instansi,
                'nama_instansi': nama_instansi,
                'koordinat': koordinat,
                'radius_meter': radius_meter,
                'id_user': id_user
            },
            success: (res) => {
                alert(res.message);
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

