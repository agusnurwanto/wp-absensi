<?php
// Ensure strict type checking is not enabled for this file if we are mixing HTML/PHP
if (!defined('ABSPATH')) {
    exit;
}

$input = shortcode_atts(array(
    'tahun_anggaran' => '2026',
), $atts);

?>

<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="cetak">
    <div style="padding: 10px; margin: 0 0 3rem 0">
        <h3 class="text-center" style="margin: 3rem">
            Manajemen Data Ijin / Cuti / Sakit<br />Tahun <?php echo esc_html($input['tahun_anggaran']); ?>
        </h3>

        <div style="margin-bottom: 25px; display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
            <button class="btn btn-primary" onclick="tambah_data_ijin()">
                <span class="dashicons dashicons-plus"></span> Tambah Data
            </button>
            <button type="button" class="btn btn-danger" onclick="print_laporan_perijinan();">
                <i class="dashicons dashicons-printer"></i> Print
            </button>
        <div>
            <select id="filter_bulan" style="height:38px; width:auto; margin-left:auto;">
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
        <div class="table-responsive">
            <table id="management_data_ijin_table">
                <thead>
                    <tr>
                        <th class="text-center">No</th>
                        <th class="text-center">Nama Pegawai</th>
                        <th class="text-center">Tipe</th>
                        <th class="text-center">Tanggal</th>
                        <th class="text-center">Alasan</th>
                        <th class="text-center">Lampiran</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah/Edit Data -->
<div class="modal fade" id="modalTambahDataIjin" tabindex="-1" data-backdrop="static" data-keyboard="false" role="dialog" aria-labelledby="modalTambahDataIjinLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTambahDataIjinLabel">Pengajuan Ijin</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formTambahDataIjin" enctype="multipart/form-data">
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
                        <label for="tipe_ijin">Tipe Ijin</label>
                        <select class="form-control" id="tipe_ijin" name="tipe_ijin" required>
                            <option value="">-- Pilih Tipe --</option>
                            <option value="Sakit">Sakit</option>
                            <option value="Ijin">Ijin</option>
                            <option value="Cuti">Cuti</option>
                            <option value="Dinas Luar">Dinas Luar</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tanggal_mulai">Tanggal Mulai</label>
                                <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tanggal_selesai">Tanggal Selesai</label>
                                <input type="date" class="form-control" id="tanggal_selesai" name="tanggal_selesai" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="alasan">Alasan / Keterangan</label>
                        <textarea class="form-control" id="alasan" name="alasan" rows="3"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="file_lampiran">Lampiran (Bukti/Surat Dokter) <small class="text-muted">*Maks 2MB (PDF/JPG/PNG)</small></label>
                        <input type="file" class="form-control-file" id="file_lampiran" name="file_lampiran">
                        <small id="file_existing" class="form-text text-info"></small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" onclick="submitTambahDataFormIjin()">Simpan</button>
            </div>
        </div>
    </div>
</div>

<script>
    jQuery(document).ready(function($) {
        let ajaxurl = ajax.url;
        let apikey = ajax.api_key;
        let dataijin;

        // Initialize Select2 if exists
        if ($('#nama_pegawai_select').length) {
            $('#nama_pegawai_select').select2({
                dropdownParent: $('#modalTambahDataIjin')
            });
        }

        get_data_ijin();

        function get_data_ijin() {
            if (typeof dataijin == 'undefined') {
                dataijin = $('#management_data_ijin_table').on('preXhr.dt', function(e, settings, data) {
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
                            d.action = 'get_datatable_ijin';
                            d.api_key = apikey;
                            d.tahun = '<?php echo $input['tahun_anggaran']; ?>';
                            d.bulan = $('#filter_bulan').val(); // 🔥 INI PENTING
                        }
                    },
                    lengthMenu: [
                        [20, 50, 100, -1],
                        [20, 50, 100, "All"]
                    ],
                    "drawCallback": function(settings) {
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
                            "data": 'tipe_ijin',
                            className: "text-center"
                        },
                        {
                            "data": 'tanggal',
                            className: "text-center"
                        },
                        {
                            "data": 'alasan'
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
                        "targets": [0, 5, 7]
                    }]
                });
            } else {
                dataijin.draw();
            }
        }
        $('#filter_bulan').on('change', function () {
            dataijin.ajax.reload();
        });

        window.tambah_data_ijin = function() {
            $('#id_data').val('');
            if ($('#nama_pegawai_select').length) {
                $('#nama_pegawai_select').val('').trigger('change');
            }
            $('#tipe_ijin').val('');
            $('#tanggal_mulai').val('');
            $('#tanggal_selesai').val('');
            $('#alasan').val('');
            $('#file_lampiran').val('');
            $('#file_existing').text('');

            $('#modalTambahDataIjinLabel').text('Pengajuan Ijin');
            $('#modalTambahDataIjin').modal('show');
        }

        window.edit_data_ijin = function(id) {
            $("#wrap-loading").show();
            $.ajax({
                url: ajaxurl,
                type: 'post',
                data: {
                    action: 'get_data_ijin_by_id',
                    api_key: apikey,
                    id: id
                },
                dataType: 'json',
                success: function(response) {
                    $("#wrap-loading").hide();
                    if (response.status == 'success') {
                        let data = response.data;
                        $('#id_data').val(data.id);

                        if ($('#nama_pegawai_select').length) {
                            $('#nama_pegawai_select').val(data.id_pegawai).trigger('change');
                        }

                        $('#tipe_ijin').val(data.tipe_ijin);
                        $('#tanggal_mulai').val(data.tanggal_mulai);
                        $('#tanggal_selesai').val(data.tanggal_selesai);
                        $('#alasan').val(data.alasan);

                        if (data.file_lampiran) {
                            $('#file_existing').text('Lampiran Saat Ini: ' + data.file_lampiran);
                        } else {
                            $('#file_existing').text('');
                        }

                        $('#modalTambahDataIjinLabel').text('Edit Ijin');
                        $('#modalTambahDataIjin').modal('show');
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

        window.hapus_data_ijin = function(id) {
            Swal.fire({
                title: 'Hapus Data',
                text: "Apakah anda yakin ingin menghapus data ini?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Ya, Hapus!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $("#wrap-loading").show();
                    $.ajax({
                        url: ajaxurl,
                        type: 'post',
                        data: {
                            action: 'hapus_data_ijin_by_id',
                            api_key: apikey,
                            id: id
                        },
                        dataType: 'json',
                        success: function(response) {
                            $("#wrap-loading").hide();
                            if (response.status == 'success') {
                                Swal.fire('Terhapus!', response.message, 'success');
                                dataijin.ajax.reload();
                            } else {
                                Swal.fire('Gagal!', response.message, 'error');
                            }
                        },
                        error: function() {
                            $("#wrap-loading").hide();
                        }
                    });
                }
            })
        }

        window.update_status_ijin = function(id, status) {
            Swal.fire({
                title: status == 'Approved' ? 'Setujui Ijin?' : 'Tolak Ijin?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: status == 'Approved' ? '#28a745' : '#dc3545',
                confirmButtonText: 'Ya'
            }).then((result) => {
                if (result.isConfirmed) {
                    $("#wrap-loading").show();
                    $.ajax({
                        url: ajaxurl,
                        type: 'post',
                        data: {
                            action: 'update_status_ijin',
                            api_key: apikey,
                            id: id,
                            status: status
                        },
                        dataType: 'json',
                        success: function(response) {
                            $("#wrap-loading").hide();
                            if (response.status == 'success') {
                                Swal.fire('Berhasil!', response.message, 'success');
                                dataijin.ajax.reload();
                            } else {
                                Swal.fire('Gagal!', response.message, 'error');
                            }
                        },
                        error: function() {
                            $("#wrap-loading").hide();
                        }
                    });
                }
            })
        }

        window.submitTambahDataFormIjin = function() {
            let fd = new FormData(document.getElementById('formTambahDataIjin'));
            fd.append('action', 'tambah_data_ijin');
            fd.append('api_key', apikey);
            fd.set('id', $('#id_data').val()); // FormData takes name, but ensuring

            // Validation
            let id_pegawai = 0;
            if ($('#nama_pegawai_select').length) {
                id_pegawai = $('#nama_pegawai_select').val();
                if (!id_pegawai) {
                    return Swal.fire('Gagal', 'Pilih Pegawai', 'error');
                }
            }

            // Ensure id_pegawai is in FormData if it's admin select
            if (id_pegawai > 0) {
                fd.set('id_pegawai', id_pegawai);
            }

            if ($('#tipe_ijin').val() == '' || $('#tanggal_mulai').val() == '') {
                return Swal.fire('Gagal', 'Lengkapi form wajib', 'error');
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
                        Swal.fire('Berhasil', response.message, 'success');
                        $('#modalTambahDataIjin').modal('hide');
                        dataijin.ajax.reload();
                    } else {
                        Swal.fire('Gagal', response.message, 'error');
                    }
                },
                error: function() {
                    $("#wrap-loading").hide();
                    Swal.fire('Error', 'Koneksi Gagal', 'error');
                }
            });
        }
    });
    window.print_laporan_perijinan = function() {

        let bulan = jQuery('#filter_bulan').val();
        let tahun = '<?php echo esc_html($input['tahun_anggaran']); ?>';

        window.open(
            ajax.url + 
            '?action=print_laporan_perijinan' +
            '&api_key=' + ajax.api_key +
            '&tahun=' + tahun +
            '&bulan=' + bulan,
            '_blank'
        );
    }


</script>