<?php
global $wpdb;

if (!defined('WPINC')) {
    die;
}

$input = shortcode_atts(array(
    'tahun_anggaran' => '2026',
), $atts);

$date = date('d-m-Y');
?>

<style type="text/css">
    .wrap-table {
        overflow: auto;
        max-height: 100vh;
        width: 100%;
    }
</style>

<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="cetak">
    <div style="padding: 10px;margin:0 0 3rem 0;">
        <input type="hidden" value="<?php echo get_option(ABSEN_APIKEY); ?>" id="api_key">
        <h1 class="text-center" style="margin:3rem;">Manajemen Data Absensi<br>Tahun <?php echo $input['tahun_anggaran']; ?></h1>
        <div style="margin-bottom: 25px;">
            <?php
            $current_user = wp_get_current_user();
            $is_pegawai = in_array('pegawai', (array) $current_user->roles) && !in_array('administrator', (array) $current_user->roles) && !in_array('admin_instansi', (array) $current_user->roles);

            if (!$is_pegawai): ?>
                <button class="btn btn-primary" onclick="tambah_data_absensi();"><i class="dashicons dashicons-plus"></i> Tambah Data Manual</button>
            <?php endif; ?>
        </div>
        <div class="table-responsive">
            <table id="management_data_table">
                <thead>
                    <tr>
                        <!-- <th class="text-center">No</th> -->
                        <th class="text-center">Nama Pegawai</th>
                        <th class="text-center">Tanggal</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Waktu (Masuk - Pulang)</th>
                        <th class="text-center">Foto Masuk</th>
                        <th class="text-center">Foto Pulang</th>
                        <th class="text-center">Kode Kerja</th>
                        <th class="text-center" style="width: 100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Data Manual -->
<div class="modal fade mt-4" id="modalTambahDataAbsensi" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="modalTambahDataAbsensiLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTambahDataAbsensiLabel">Input Absensi Manual</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type='hidden' id='id_data' name="id_data" placeholder=''>

                <div class="form-group">
                    <label for='id_pegawai_manual'>Pilih Pegawai</label>
                    <select id='id_pegawai_manual' name="id_pegawai_manual" class="form-control" style="width:100%">
                        <option value="">-- Cari Pegawai --</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for='id_kode_kerja_manual'>Pilih Kode Kerja</label>
                    <select id='id_kode_kerja_manual' name="id_kode_kerja_manual" class="form-control" style="width:100%">
                        <option value="">-- Pilih Kode Kerja --</option>
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for='tanggal_manual'>Tanggal</label>
                            <input type="date" id='tanggal_manual' name="tanggal_manual" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for='waktu_masuk_manual'>Jam Masuk</label>
                            <input type="time" id='waktu_masuk_manual' name="waktu_masuk_manual" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for='waktu_pulang_manual'>Jam Pulang</label>
                            <input type="time" id='waktu_pulang_manual' name="waktu_pulang_manual" class="form-control">
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button class="btn btn-primary submitBtn" onclick="submitTambahDataFormAbsensi()">Simpan</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
    jQuery(document).ready(() => {
        // Define AJAX vars manually to be safe
        let ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
        let apikey = jQuery('#api_key').val();

        // Initialize DataTable
        get_data_absensi();

        // Initialize Select2 for Pegawai
        jQuery('#id_pegawai_manual').select2({
            ajax: {
                url: ajaxurl,
                dataType: 'json',
                delay: 250,
                data: (params) => {
                    return {
                        q: params.term, // search term
                        action: 'get_master_pegawai_search',
                        api_key: apikey
                    };
                },
                processResults: (data) => {
                    return {
                        results: data.items
                    };
                },
                cache: true
            },
            placeholder: 'Cari Pegawai...',
            minimumInputLength: 0, // Allow showing list on click
        });

        // Listener: When Pegawai Selected -> Load Kode Kerja
        jQuery('#id_pegawai_manual').on('select2:select', (e) => {
            let data = e.params.data;
            if (data.id_instansi) {
                loadKodeKerjaManual(data.id_instansi);
            }
        });

        // Helper: Get Data Table
        function get_data_absensi() {
            if (typeof dataabsensi == 'undefined') {
                window.dataabsensi = jQuery('#management_data_table').on('preXhr.dt', (e, settings, data) => {
                    jQuery("#wrap-loading").show();
                }).DataTable({
                    "processing": true,
                    "serverSide": true,
                    "responsive": true,
                    "rowReorder": {
                        selector: 'td:nth-child(2)'
                    },
                    "ajax": {
                        url: ajaxurl,
                        type: 'post',
                        dataType: 'json',
                        data: {
                            'action': 'get_datatable_absensi',
                            'api_key': apikey,
                        }
                    },
                    lengthMenu: [
                        [20, 50, 100, -1],
                        [20, 50, 100, "All"]
                    ],
                    order: [
                        [2, 'desc'],
                        [4, 'desc']
                    ],
                    "drawCallback": (settings) => {
                        jQuery("#wrap-loading").hide();
                    },
                    "columns": [
                        // { "data": 'no', className: "text-center" },
                        {
                            "data": 'nama_pegawai'
                        },
                        {
                            "data": 'tanggal',
                            className: "text-center"
                        },
                        {
                            "data": 'status',
                            className: "text-center"
                        },
                        {
                            "data": 'waktu',
                            className: "text-center"
                        },
                        {
                            "data": 'foto_masuk',
                            className: "text-center"
                        },
                        {
                            "data": 'foto_pulang',
                            className: "text-center"
                        },
                        {
                            "data": 'nama_kerja',
                            className: "text-center"
                        },
                        {
                            "data": 'aksi',
                            className: "text-center"
                        }
                    ]
                });
            } else {
                dataabsensi.draw();
            }
        }

        // Load Kode Kerja (Specific to Instansi)
        function loadKodeKerjaManual(id_instansi, selected_id = 0) {
            if (!id_instansi) return;

            jQuery.ajax({
                url: ajaxurl,
                type: 'post',
                data: {
                    action: 'get_valid_kode_kerja',
                    api_key: apikey,
                    id_instansi: id_instansi // Pass specific instansi
                },
                dataType: 'json',
                success: (response) => {
                    if (response.status == 'success') {
                        let options = '<option value="">-- Pilih Kode Kerja --</option>';
                        jQuery.each(response.data, (i, item) => {
                            let selected = (item.id == selected_id) ? 'selected' : '';
                            options += `<option value="${item.id}" ${selected}>${item.nama_kerja}</option>`;
                        });
                        jQuery('#id_kode_kerja_manual').html(options);
                    } else {
                        jQuery('#id_kode_kerja_manual').html('<option value="">-- Tidak Ada Jadwal --</option>');
                    }
                }
            });
        }

        // Global function for onclick button
        window.tambah_data_absensi = () => {
            jQuery('#id_data').val('');
            jQuery('#modalTambahDataAbsensiLabel').text('Input Absensi Manual');

            // Default to Current Time
            let now = new Date();
            let hours = String(now.getHours()).padStart(2, '0');
            let minutes = String(now.getMinutes()).padStart(2, '0');
            let timeValue = hours + ":" + minutes;

            jQuery('#waktu_masuk_manual').val(timeValue);
            jQuery('#waktu_pulang_manual').val(timeValue);

            jQuery('#modalTambahDataAbsensi').modal('show');
        }

        // Edit Function
        window.edit_data_absensi = (id) => {
            jQuery('#id_data').val(id);
            jQuery('#modalTambahDataAbsensiLabel').text('Edit Absensi Manual');

            jQuery("#wrap-loading").show();
            jQuery.ajax({
                url: ajaxurl,
                type: 'post',
                data: {
                    action: 'get_data_absensi_by_id',
                    api_key: apikey,
                    id: id
                },
                dataType: 'json',
                success: (response) => {
                    jQuery("#wrap-loading").hide();
                    if (response.status == 'success') {
                        let data = response.data;

                        // Populate Date & Time
                        jQuery('#tanggal_manual').val(data.tanggal);
                        jQuery('#waktu_masuk_manual').val(data.jam_masuk);
                        jQuery('#waktu_pulang_manual').val(data.jam_pulang);

                        // Populate Select2 Pegawai
                        if (jQuery('#id_pegawai_manual').find("option[value='" + data.id_pegawai + "']").length) {
                            jQuery('#id_pegawai_manual').val(data.id_pegawai).trigger('change');
                        } else {
                            // Create a DOM Option and pre-select it
                            let newOption = new Option(data.pegawai_text, data.id_pegawai, true, true);
                            jQuery('#id_pegawai_manual').append(newOption).trigger('change');
                        }

                        // Trigger Code Load manually properly after setting ID instansi
                        // We need id_instansi to load codes. It comes from backend.
                        if (data.id_instansi) {
                            loadKodeKerjaManual(data.id_instansi, data.id_kode_kerja);
                        }

                        jQuery('#modalTambahDataAbsensi').modal('show');
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: response.message,
                        });
                    }
                },
                error: () => {
                    jQuery("#wrap-loading").hide();
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Terjadi kesalahan koneksi!',
                    });
                }
            });
        }

        // Delete Function
        window.hapus_data_absensi = (id) => {
            Swal.fire({
                title: 'Hapus Data',
                text: "Apakah Anda yakin ingin menghapus data absensi ini?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    jQuery("#wrap-loading").show();
                    jQuery.ajax({
                        url: '<?php echo admin_url('admin-ajax.php'); ?>',
                        type: 'post',
                        data: {
                            action: 'hapus_data_absensi',
                            api_key: '<?php echo get_option(ABSEN_APIKEY); ?>',
                            id: id
                        },
                        dataType: 'json',
                        success: (response) => {
                            jQuery("#wrap-loading").hide();
                            if (response.status == 'success') {
                                Swal.fire(
                                    'Terhapus!',
                                    response.message,
                                    'success'
                                );
                                dataabsensi.ajax.reload(); // Refresh Table
                            } else {
                                Swal.fire(
                                    'Gagal!',
                                    response.message,
                                    'error'
                                );
                            }
                        },
                        error: () => {
                            jQuery("#wrap-loading").hide();
                            Swal.fire(
                                'Error!',
                                'Terjadi kesalahan server.',
                                'error'
                            );
                        }
                    });
                }
            })
        }

        // Submit Logic
        window.submitTambahDataFormAbsensi = () => {
            let id_pegawai = jQuery('#id_pegawai_manual').val();
            let id_kode_kerja = jQuery('#id_kode_kerja_manual').val();
            let tanggal = jQuery('#tanggal_manual').val();
            let jam_masuk = jQuery('#waktu_masuk_manual').val();
            let jam_pulang = jQuery('#waktu_pulang_manual').val();
            // let status = jQuery('#status_manual').val();

            if (id_pegawai == '' || id_kode_kerja == '' || tanggal == '') {
                return Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Pegawai, Kode Kerja, dan Tanggal WAJIB diisi!',
                });
            }

            jQuery("#wrap-loading").show();
            jQuery.ajax({
                url: ajaxurl,
                type: 'post',
                data: {
                    action: 'tambah_data_absensi_manual',
                    api_key: apikey,
                    id_pegawai: id_pegawai,
                    id_kode_kerja: id_kode_kerja,
                    tanggal: tanggal,
                    jam_masuk: jam_masuk,
                    jam_pulang: jam_pulang
                    // status: status
                },
                dataType: 'json',
                success: (response) => {
                    jQuery("#wrap-loading").hide();
                    if (response.status == 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message,
                        });
                        jQuery('#modalTambahDataAbsensi').modal('hide');
                        get_data_absensi();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: response.message,
                        });
                    }
                },
                error: () => {
                    jQuery("#wrap-loading").hide();
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Terjadi kesalahan koneksi!',
                    });
                }
            });
        }
    });
</script>