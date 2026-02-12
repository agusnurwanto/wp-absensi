<?php

global $wpdb;

if (!defined('WPINC')) {
    die;
}

$input = shortcode_atts(array(
    'tahun_anggaran' => '2026',
), $atts);

?>

<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="wrap box_dashboard_absensi">
    <div style="padding: 10px;margin:0 0 3rem 0;">
        <input type="hidden" value="<?php echo get_option(ABSEN_APIKEY); ?>" id="api_key">
        <h1 class="text-center" style="margin:3rem;">Manajemen Data Kegiatan<br>Tahun <?php echo $input['tahun_anggaran']; ?></h1>
       <div class="no-print"
        style="margin-bottom:25px; display:flex; gap:10px; align-items:center; flex-wrap:nowrap;">
        <div style="display:flex; gap:10px;">
            <button type="button" class="btn btn-primary" onclick="tambah_data_kegiatan();">
                <i class="dashicons dashicons-plus"></i> Tambah Data
            </button>
            <button type="button" class="btn btn-danger" onclick="print_laporan_kegiatan();">
                <i class="dashicons dashicons-printer"></i> Print
            </button>
        </div>
        <div>
            <select id="filter_bulan" style="height:38px; width:auto;">
                <option value="">Semua Bulan</option>
                <option value="01">Januari</option>
                <option value="02">Februari</option>
                <option value="03">Maret</option>
                <option value="04">April</option>
                <option value="05">Mei</option>
                <option value="06">Juni</option>
                <option value="07">Juli</option>
                <option value="08">Agustus</option>
                <option value="09">September</option>
                <option value="10">Oktober</option>
                <option value="11">November</option>
                <option value="12">Desember</option>
            </select>
            </div>
        </div>    
        <div class="table-responsive">
            <table id="management_data_kegiatan_table">
                <thead>
                    <tr>
                        <th class="text-center">No</th>
                        <th class="text-center">Nama Pegawai</th>
                        <th class="text-center">Nama Kegiatan</th>
                        <th class="text-center">Tanggal</th>
                        <th class="text-center">Waktu</th>
                        <th class="text-center">Tempat</th>
                        <th class="text-center">Uraian</th>
                        <th class="text-center">Lampiran</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" style="width: 100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah/Edit Data -->
<div class="modal fade" id="modalTambahDataKegiatan" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false" aria-labelledby="modalTambahDataKegiatanLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTambahDataKegiatanLabel">Tambah Data Kegiatan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formTambahDataKegiatan" enctype="multipart/form-data">
                    <input type="hidden" id="id_data" name="id_data">
                    <div class="form-group">
                        <label for="nama_pegawai">Nama Pegawai</label>
                        <?php if ($is_admin): ?>
                            <select class="form-control" id="nama_pegawai_select" name="id_pegawai" style="width: 100%;">
                                <option value="">-- Pilih Pegawai --</option>
                                <?php foreach ($list_pegawai as $p): ?>
                                    <option value="<?php echo esc_attr($p['id']); ?>"><?php echo esc_html($p['nama']); ?> (<?php echo esc_html($p['nik']); ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        <?php else: ?>
                            <input type="hidden" id="id_pegawai_hidden" name="id_pegawai" value="<?php echo esc_attr($pegawai_info['id']); ?>">
                            <input type="text" class="form-control" id="nama_pegawai_readonly" value="<?php echo esc_attr($pegawai_info['nama']); ?>" readonly>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="nama_kegiatan">Nama Kegiatan</label>
                        <input type="text" class="form-control" id="nama_kegiatan" name="nama_kegiatan" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tanggal">Tanggal</label>
                                <input type="date" class="form-control" id="tanggal" name="tanggal" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="jam_mulai">Jam Mulai</label>
                                <input type="time" class="form-control" id="jam_mulai" name="jam_mulai">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="jam_selesai">Jam Selesai</label>
                                <input type="time" class="form-control" id="jam_selesai" name="jam_selesai">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="tempat">Tempat</label>
                        <textarea class="form-control" id="tempat" name="tempat" rows="2"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="uraian">Uraian / Keterangan</label>
                        <textarea class="form-control" id="uraian" name="uraian" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="file_lampiran">Lampiran (Foto/Dokumen) <small class="text-muted">*Maks 2MB (JPG/PNG)</small></label>
                        <input type="file" class="form-control-file" id="file_lampiran" name="file_lampiran">
                        <small id="file_existing" class="form-text text-info"></small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" onclick="submitTambahDataFormKegiatan()">Simpan</button>
            </div>
        </div>
    </div>
</div>

<script>
    jQuery(document).ready(($) => {
        let ajaxurl = ajax.url;
        let apikey = ajax.api_key;
        let datakegiatan;

        // Initialize Select2 if exists
        if ($('#nama_pegawai_select').length) {
            $('#nama_pegawai_select').select2({
                dropdownParent: $('#modalTambahDataKegiatan')
            });
        }

        get_data_kegiatan();

        function get_data_kegiatan() {
            if (typeof datakegiatan == 'undefined') {
                datakegiatan = $('#management_data_kegiatan_table').on('preXhr.dt', (e, settings, data) => {
                    $("#wrap-loading").show();
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
                        data: function(d) {
                        d.action = 'get_datatable_kegiatan';
                        d.api_key = apikey;
                        d.tahun = '<?php echo $input['tahun_anggaran']; ?>';
                        d.bulan = $('#filter_bulan').val();
                        }
                    },
                    lengthMenu: [
                        [20, 50, 100, -1],
                        [20, 50, 100, "All"]
                    ],
                    "drawCallback": (settings) => {
                        $("#wrap-loading").hide();
                    },
                    "columns": [{
                            "data": 'no',
                            className: "text-center"
                        },
                        {
                            "data": 'nama_pegawai'
                        },
                        {
                            "data": 'nama_kegiatan'
                        },
                        {
                            "data": 'tanggal',
                            className: "text-center"
                        },
                        {
                            "data": 'waktu',
                            className: "text-center"
                        },
                        {
                            "data": 'tempat'
                        },
                        {
                            "data": 'uraian'
                        },
                        {
                            "data": 'lampiran',
                            className: "text-center"
                        },
                        {
                            "data": 'status',
                            className: "text-center"
                        },
                        {
                            "data": 'aksi',
                            className: "text-center"
                        }
                    ],
                    "columnDefs": [{
                        "orderable": false,
                        "targets": [0, 7, 9]
                    }]
                });
            } else {
                datakegiatan.draw();
            }
        }
        $('#filter_bulan').on('change', function () {
            datakegiatan.ajax.reload();
        });

        window.tambah_data_kegiatan = () => {
            $('#id_data').val('');

            // Reset Pegawai Select if Admin
            if ($('#nama_pegawai_select').length) {
                $('#nama_pegawai_select').val('').trigger('change');
            }

            $('#nama_kegiatan').val('');
            $('#tanggal').val('');
            $('#jam_mulai').val('');
            $('#jam_selesai').val('');
            $('#tempat').val('');
            $('#tempat').val('');
            $('#uraian').val('');
            $('#file_lampiran').val('');
            $('#file_existing').text('');
            $('#modalTambahDataKegiatanLabel').text('Tambah Data Kegiatan');
            $('#modalTambahDataKegiatan').modal('show');
        }

        window.edit_data_kegiatan = (id) => {
            $("#wrap-loading").show();
            $.ajax({
                url: ajaxurl,
                type: 'post',
                data: {
                    action: 'get_data_kegiatan_by_id',
                    api_key: apikey,
                    id: id
                },
                dataType: 'json',
                success: (response) => {
                    $("#wrap-loading").hide();
                    if (response.status == 'success') {
                        let data = response.data;
                        $('#id_data').val(data.id);

                        // Set Pegawai Value if Admin
                        if ($('#nama_pegawai_select').length) {
                            $('#nama_pegawai_select').val(data.id_pegawai).trigger('change');
                        }

                        $('#nama_kegiatan').val(data.nama_kegiatan);
                        $('#tanggal').val(data.tanggal);
                        // Handle Time format if needed (Backend returns HH:MM:SS usually, input type time expects HH:MM)
                        $('#jam_mulai').val(data.jam_mulai ? data.jam_mulai.substring(0, 5) : '');
                        $('#jam_selesai').val(data.jam_selesai ? data.jam_selesai.substring(0, 5) : '');
                        $('#tempat').val(data.tempat);
                        $('#uraian').val(data.uraian);

                        if (data.file_lampiran) {
                            $('#file_existing').text('Lampiran Saat Ini: ' + data.file_lampiran);
                        } else {
                            $('#file_existing').text('');
                        }

                        $('#modalTambahDataKegiatanLabel').text('Edit Data Kegiatan');
                        $('#modalTambahDataKegiatan').modal('show');
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: response.message
                        });
                    }
                },
                error: () => {
                    $("#wrap-loading").hide();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Koneksi Gagal!'
                    });
                }
            });
        }

        window.hapus_data_kegiatan = (id) => {
            Swal.fire({
                title: 'Hapus Data',
                text: "Apakah anda yakin ingin menghapus data ini?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $("#wrap-loading").show();
                    $.ajax({
                        url: ajaxurl,
                        type: 'post',
                        data: {
                            action: 'hapus_data_kegiatan_by_id',
                            api_key: apikey,
                            id: id
                        },
                        dataType: 'json',
                        success: (response) => {
                            $("#wrap-loading").hide();
                            if (response.status == 'success') {
                                Swal.fire('Terhapus!', response.message, 'success');
                                datakegiatan.ajax.reload();
                            } else {
                                Swal.fire('Gagal!', response.message, 'error');
                            }
                        },
                        error: () => {
                            $("#wrap-loading").hide();
                            Swal.fire('Error!', 'Terjadi kesalahan server', 'error');
                        }
                    });
                }
            })
        }

        window.submitTambahDataFormKegiatan = () => {
            let fd = new FormData(document.getElementById('formTambahDataKegiatan'));
            fd.append('action', 'tambah_data_kegiatan');
            fd.append('api_key', apikey);
            fd.set('id', $('#id_data').val());

            let nama_kegiatan = $('#nama_kegiatan').val();
            let tanggal = $('#tanggal').val();

            // Validation for Admin select
            let id_pegawai = 0;
            if ($('#nama_pegawai_select').length) {
                id_pegawai = $('#nama_pegawai_select').val();
                if (id_pegawai == '') {
                    return Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Silahkan Pilih Pegawai!'
                    });
                }
            }

            if (nama_kegiatan == '' || tanggal == '') {
                return Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Nama Kegiatan dan Tanggal Wajib Diisi!'
                });
            }

            $("#wrap-loading").show();
            $.ajax({
                url: ajaxurl,
                type: 'post',
                data: fd,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function(response) {
                    $("#wrap-loading").hide();
                    if (response.status == 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message
                        });
                        $('#modalTambahDataKegiatan').modal('hide');
                        datakegiatan.ajax.reload();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: response.message
                        });
                    }
                },
                error: function() {
                    $("#wrap-loading").hide();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Koneksi Gagal!'
                    });
                }
            });
        }
        window.print_laporan_kegiatan = function() {

            let bulan = document.getElementById('filter_bulan').value;

            let form = document.createElement("form");
            form.method = "POST";
            form.action = "<?php echo admin_url('admin-ajax.php'); ?>";
            form.target = "_blank";

            form.innerHTML = `
                <input type="hidden" name="action" value="print_laporan_kegiatan">
                <input type="hidden" name="api_key" value="<?php echo get_option(ABSEN_APIKEY); ?>">
                <input type="hidden" name="tahun" value="<?php echo $input['tahun_anggaran']; ?>">
                <input type="hidden" name="bulan" value="${bulan}">
            `;

            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        };
    });
    

</script>