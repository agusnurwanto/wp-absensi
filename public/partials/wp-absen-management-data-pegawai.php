<?php
global $wpdb;

if (!defined('WPINC')) {
    die;
}

$input = shortcode_atts(array(
    'tahun_anggaran' => '2025',
), $atts);
$idtahun = $wpdb->get_results("
    SELECT DISTINCT 
        tahun_anggaran 
    FROM absensi_data_unit        
    ORDER BY tahun_anggaran DESC
",ARRAY_A);
$tahun = '<option value="0">Pilih Tahun</option>';

foreach ($idtahun as $val) {
    if($val['tahun_anggaran'] == $input['tahun_anggaran']){
        continue;
    }
    $selected = '';
    if($val['tahun_anggaran'] == $input['tahun_anggaran']-1){
        $selected = 'selected';
    }
    $tahun .= '<option value="'. $val['tahun_anggaran']. '" '. $selected .'>'. $val['tahun_anggaran'] .'</option>';
}
?>
<style type="text/css">
    .wrap-table{
        overflow: auto;
        max-height: 100vh; 
        width: 100%; 
    }
    .hidden {
        display: none;
    }
    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
        margin-top: 5px;
    }
    .status-active {
        background-color: #d4edda;
        color: #155724;
    }
    .status-inactive {
        background-color: #f8d7da;
        color: #721c24;
    }
    .swal2-popup {
        font-size: 14px !important;
    }
    .swal2-actions button {
        margin: 0 5px;
    }
    input[readonly], 
    textarea[readonly], 
    select[disabled] {
        background-color: #e9ecef !important;
        cursor: not-allowed !important;
        opacity: 0.7;
    }
    .filter-container {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 25px;
    }
    .filter-label {
        font-weight: 600;
        margin: 0;
    }
    #status_kerja_filter {
        min-width: 200px;
    }
</style>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="cetak">
    <div style="padding: 10px;margin:0 0 3rem 0;">
        <input type="hidden" value="<?php echo get_option( ABSEN_APIKEY ); ?>" id="api_key">
    <h1 class="text-center" style="margin:3rem;">Manajemen Data Pegawai<br>Tahun <?php echo $input['tahun_anggaran']; ?></h1>
        <div class="filter-container">
            <button class="btn btn-primary" onclick="tambah_data_pegawai();"><i class="dashicons dashicons-plus"></i> Tambah Data</button>
            
            <button class="btn btn-danger" onclick="copy_data();"><i class="dashicons dashicons-admin-page"></i> Copy Data</button>
            
            <div style="display: flex; align-items: center; gap: 10px; margin-left: 20px;">
                <label class="filter-label">Filter Status:</label>
                <select id="status_kerja_filter" class="form-control" onchange="filter_status_pegawai();">
                    <option value="">Keduanya</option>
                    <option value="1">Pegawai Active</option>
                    <option value="0">Pegawai Non Active</option>
                </select>
            </div>
        </div>
        <div class="wrap-table">
        <table id="management_data_table" cellpadding="2" cellspacing="0"  class="table table-bordered">
            <thead>
                <tr>
                    <th class="text-center">NIK</th>
                    <th class="text-center">Nama</th>
                    <th class="text-center">Tempat Lahir</th>
                    <th class="text-center">Tanggal Lahir</th>
                    <th class="text-center">Jenis Kelamin</th>
                    <th class="text-center">Jabatan</th>
                    <th class="text-center">Agama</th>
                    <th class="text-center">No Handphone</th>
                    <th class="text-center">Alamat</th>
                    <th class="text-center">Pendidikan Terakhir</th>
                    <th class="text-center">Pendidikan Sekarang</th>
                    <th class="text-center">Nama Sekolah</th>
                    <th class="text-center">Lulus</th>
                    <th class="text-center">Email</th>
                    <th class="text-center">Kartu Pegawai</th>
                    <th class="text-center">Tanggal Mulai</th>
                    <th class="text-center">Tanggal Selesai</th>
                    <th class="text-center">Gaji</th>
                    <th class="text-center">User Role</th>
                    <th class="text-center">Status Pegawai</th>
                    <th class="text-center" style="width: 150px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
        </div>
    </div>          
</div>

<div class="modal fade mt-4" id="modalTambahDataPegawai" tabindex="-1" role="dialog" aria-labelledby="modalTambahDataPegawaiLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="modalTambahDataPegawaiLabel">Data Pegawai</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="id_data" name="id_data">

                <div class="row">

                    <div class="col-md-4 form-group">
                        <label>NIK <span class="text-danger">*</span></label>
                        <input type="number" id="nik" class="form-control">
                    </div>

                    <div class="col-md-4 form-group">
                        <label>Nama <span class="text-danger">*</span></label>
                        <input type="text" id="nama" class="form-control">
                    </div>

                    <div class="col-md-4 form-group">
                        <label>Tempat Lahir</label>
                        <input type="text" id="tempat_lahir" class="form-control">
                    </div>

                    <div class="col-md-4 form-group">
                        <label>Tanggal Lahir</label>
                        <input type="date" id="tanggal_lahir" class="form-control">
                    </div>

                    <div class="col-md-4 form-group">
                        <label>Jenis Kelamin <span class="text-danger">*</span></label>
                        <select id="jenis_kelamin" class="form-control">
                            <option value="">-- Pilih Jenis Kelamin --</option>
                        </select>
                    </div>

                    <div class="col-md-4 form-group">
                        <label>Agama</label>
                        <select id="agama" class="form-control">
                            <option value="">-- Pilih Agama --</option>
                        </select>
                    </div>

                    <div class="col-md-4 form-group">
                        <label>No Handphone</label>
                        <input type="number" id="no_hp" class="form-control">
                    </div>

                    <div class="col-md-4 form-group">
                        <label>Pendidikan Terakhir</label>
                        <select id="pendidikan_terakhir" class="form-control">
                            <option value="">-- Pilih Pendidikan --</option>
                        </select>
                    </div>

                    <div class="col-md-4 form-group">
                        <label>Pendidikan Sekarang</label>
                        <select id="pendidikan_sekarang" class="form-control">
                            <option value="">-- Pilih Pendidikan --</option>
                        </select>
                    </div>

                    <div class="col-md-4 form-group">
                        <label>Nama Sekolah</label>
                        <input type="text" id="nama_sekolah" class="form-control">
                    </div>

                    <div class="col-md-4 form-group">
                        <label>Lulus (Tahun)</label>
                        <input type="number" id="lulus" class="form-control">
                    </div>

                    <div class="col-md-4 form-group">
                        <label>Email</label>
                        <input type="email" id="email" class="form-control">
                    </div>

                    <div class="col-md-12 form-group">
                        <label>Alamat</label>
                        <textarea id="alamat" class="form-control"></textarea>
                    </div>

                    <div class="col-md-4 form-group">
                        <label>Status Pegawai <span class="text-danger">*</span></label>
                        <select id="status" class="form-control" onchange="status_pegawai_teks();">
                            <option value="">-- Pilih Status --</option>
                        </select>
                    </div>

                    <div class="col-md-4 form-group hidden" id="status_teks_wrapper">
                        <label>Status Pegawai Lainnya <span class="text-danger">*</span></label>
                        <input type="text" id="status_teks" class="form-control" placeholder="Sebutkan status lainnya">
                    </div>

                    <div class="col-md-4 form-group hidden" id="jabatan_wrapper">
                        <label>Jabatan <span class="text-danger">*</span></label>
                        <input type="text" id="jabatan" class="form-control">
                    </div>

                    <div class="col-md-4 form-group hidden" id="karpeg_wrapper">
                        <label>Kartu Pegawai</label>
                        <input type="text" id="karpeg" class="form-control">
                    </div>

                    <div class="col-md-4 form-group hidden" id="tanggal_mulai_wrapper">
                        <label>Tanggal Mulai <span class="text-danger">*</span></label>
                        <input type="date" id="tanggal_mulai" class="form-control">
                    </div>

                    <div class="col-md-4 form-group hidden" id="tanggal_selesai_wrapper">
                        <label>Tanggal Selesai <span class="text-danger">*</span></label>
                        <input type="date" id="tanggal_selesai" class="form-control">
                    </div>

                    <div class="col-md-4 form-group hidden" id="gaji_wrapper">
                        <label>Gaji</label>
                        <input type="number" id="gaji" class="form-control" step="0.01">
                    </div>

                    <div class="col-md-4 form-group">
                        <label>User Role <span class="text-danger">*</span></label>
                        <select id="user_role" class="form-control">
                            <option value="">-- Pilih Role --</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-primary" onclick="submitTambahDataFormPegawai()">Simpan</button>
                <button class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="modal" data-backdrop="static"  role="dialog" aria-labelledby="modal-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modal title</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>
<script>
    var masterData = {};

    jQuery(document).ready(function(){
        jQuery('.mg-card-box').parent().removeClass('col-md-8').addClass('col-md-12');
        jQuery('#secondary').parent().remove();
        
        load_master_data(function(){
            get_data_pegawai();
        });
    });

    function copy_data(){
        let tbody = '';
        let tahun = '<?php echo $tahun; ?>';
        jQuery("#modal").find('.modal-title').html('Copy Data Pegawai');
        jQuery("#modal").find('.modal-body').html(`
            <div class="form-group row">
                <label for="staticEmail" class="col-sm-3 col-form-label">Tahun Anggaran Sumber Data Pegawai</label>
                <div class="col-sm-9 d-flex align-items-center justify-content-center">
                    <select id="tahunAnggaranCopy" class="form-control">
                        ${tahun}
                    </select>
                </div>
            </div>
        `);
        jQuery("#modal").find('.modal-footer').html(`
            <button type="button" class="btn btn-warning" data-dismiss="modal">
                Tutup
            </button>
            <button type="button" class="btn btn-danger" onclick="submitCopyData()">
                Copy Data
            </button>`);
        jQuery("#modal").find('.modal-dialog').css('maxWidth','700');
        jQuery("#modal").modal('show');
    }

    function submitCopyData() {
            if (!confirm('Apakah anda yakin akan copy data Pegawai? \nData yang sudah ada akan ditimpa oleh data baru hasil copy data!')) {
                return;
            }

            var tahun = jQuery("#tahunAnggaranCopy").val();

            jQuery('#wrap-loading').show();

            ajax_copy_data({
                tahun: tahun
            })
            .then(function() {
                alert('Berhasil Copy Data Pegawai.');
                jQuery("#modal").modal('hide');
                jQuery('#wrap-loading').hide();
                get_data_pegawai();
            })
            .catch(function(err) {
                console.log('err', err);
                alert('Ada kesalahan sistem!');
                jQuery('#wrap-loading').hide();
            });
        }

        function ajax_copy_data(options){
            return new Promise(function(resolve, reject){
                jQuery.ajax({
                    method: 'post',
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    dataType: 'json',
                    data:{
                        action: 'copy_data_pegawai',
                        api_key: '<?php echo get_option( ABSEN_APIKEY ); ?>',
                        tahun_sumber: options.tahun,
                        tahun_tujuan: <?php echo $input['tahun_anggaran']; ?>
                    },
                    success: function(response) {
                        resolve();
                    },
                    error: function(xhr, status, error) {
                        console.log('error', error);
                        resolve();
                    }
                });
            });
        }

    function load_master_data(callback){
        jQuery('#wrap-loading').show();
        jQuery.ajax({
            method: 'post',
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            dataType: 'json',
            data:{
                'action': 'get_master_data',
                'api_key': '<?php echo get_option( ABSEN_APIKEY ); ?>'
            },
            success: function(res){
                if(res.status == 'success'){
                    masterData = res.data;
                    get_master_options();
                    if(typeof callback === 'function'){
                        callback();
                    }
                }else{
                    alert('Gagal memuat master data: ' + res.message);
                }
                jQuery('#wrap-loading').hide();
            },
            error: function(){
                alert('Gagal memuat master data!');
                jQuery('#wrap-loading').hide();
            }
        });
    }

    function get_master_options(){
        var jk_select = jQuery('#jenis_kelamin');
        jk_select.find('option:not(:first)').remove();
        jQuery.each(masterData.jenis_kelamin, function(i, item){
            jk_select.append(jQuery('<option>', {
                value: item.value,
                text: item.label
            }));
        });
        
        var agama_select = jQuery('#agama');
        agama_select.find('option:not(:first)').remove();
        jQuery.each(masterData.agama, function(i, item){
            agama_select.append(jQuery('<option>', {
                value: item.value,
                text: item.label
            }));
        });
        
        var pend_terakhir = jQuery('#pendidikan_terakhir');
        pend_terakhir.find('option:not(:first)').remove();
        jQuery.each(masterData.pendidikan, function(i, item){
            if(item.value !== 'Tidak Sedang Menempuh'){
                pend_terakhir.append(jQuery('<option>', {
                    value: item.value,
                    text: item.label
                }));
            }
        });
        
        var pend_sekarang = jQuery('#pendidikan_sekarang');
        pend_sekarang.find('option:not(:first)').remove();
        jQuery.each(masterData.pendidikan, function(i, item){
            pend_sekarang.append(jQuery('<option>', {
                value: item.value,
                text: item.label
            }));
        });
        
        var status_select = jQuery('#status');
        status_select.find('option:not(:first)').remove();
        jQuery.each(masterData.status_pegawai, function(i, item){
            status_select.append(jQuery('<option>', {
                value: item.value,
                text: item.label
            }));
        });
        
        var role_select = jQuery('#user_role');
        role_select.find('option:not(:first)').remove();
        jQuery.each(masterData.user_role, function(i, item){
            role_select.append(jQuery('<option>', {
                value: item.value,
                text: item.label
            }));
        });
    }

    function status_pegawai_teks(){
        var status = jQuery('#status').val();
        
        jQuery('#status_teks_wrapper').addClass('hidden');
        jQuery('#jabatan_wrapper').addClass('hidden');
        jQuery('#karpeg_wrapper').addClass('hidden');
        jQuery('#tanggal_mulai_wrapper').addClass('hidden');
        jQuery('#tanggal_selesai_wrapper').addClass('hidden');
        jQuery('#gaji_wrapper').addClass('hidden');
        
        if(status != ''){
            jQuery('#jabatan_wrapper').removeClass('hidden');
            jQuery('#karpeg_wrapper').removeClass('hidden');
            jQuery('#tanggal_mulai_wrapper').removeClass('hidden');
            jQuery('#gaji_wrapper').removeClass('hidden');
            
            if(status == '5'){
                jQuery('#status_teks_wrapper').removeClass('hidden');
            }
            
            if(status != '1'){
                jQuery('#tanggal_selesai_wrapper').removeClass('hidden');
            }
        }
    }


    function filter_status_pegawai(){
        if(typeof datapegawai != 'undefined'){
            datapegawai.draw();
        }
    }

    function get_data_pegawai(){
        if(typeof datapegawai == 'undefined'){
            window.datapegawai = jQuery('#management_data_table').on('preXhr.dt', function(e, settings, data){
                jQuery("#wrap-loading").show();
            }).DataTable({
                "processing": true,
                "serverSide": true,
                "ajax": {
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'post',
                    dataType: 'json',
                    data: function(d){
                        d.action = 'get_datatable_pegawai';
                        d.api_key = '<?php echo get_option( ABSEN_APIKEY ); ?>';
                        d.tahun = '<?php echo $input['tahun_anggaran']; ?>';
                        d.status_kerja_filter = jQuery('#status_kerja_filter').val();
                    }
                },
                lengthMenu: [[20, 50, 100, -1], [20, 50, 100, "All"]],
                order: [[0, 'asc']],
                "drawCallback": function( settings ){
                    jQuery("#wrap-loading").hide();
                },
                "columns": [
                    {
                        "data": 'nik',
                        className: "text-center"
                    },
                    {
                        "data": 'nama',
                        className: "text-center"
                    },
                    {
                        "data": 'tempat_lahir',
                        className: "text-center"
                    },
                    {
                        "data": 'tanggal_lahir',
                        className: "text-center"
                    },
                    {
                        "data": 'jenis_kelamin',
                        className: "text-center"
                    },
                    {
                        "data": 'jabatan',
                        className: "text-center"
                    },
                    {
                        "data": 'agama',
                        className: "text-center"
                    },
                    {
                        "data": 'no_hp',
                        className: "text-center"
                    },
                    {
                        "data": 'alamat',
                        className: "text-center"
                    },
                    {
                        "data": 'pendidikan_terakhir',
                        className: "text-center"
                    },
                    {
                        "data": 'pendidikan_sekarang',
                        className: "text-center"
                    },
                    {
                        "data": 'nama_sekolah',
                        className: "text-center"
                    },
                    {
                        "data": 'lulus',
                        className: "text-center"
                    },
                    {
                        "data": 'email',
                        className: "text-center"
                    },
                    {
                        "data": 'karpeg',
                        className: "text-center"
                    },
                    {
                        "data": 'tanggal_mulai',
                        className: "text-center"
                    },
                    {
                        "data": 'tanggal_selesai',
                        className: "text-center"
                    },
                    {
                        "data": 'gaji',
                        className: "text-right"
                    },
                    {
                        "data": 'user_role',
                        className: "text-center"
                    },
                    {
                        "data": 'status_display',
                        className: "text-center"
                    },
                    {
                        "data": 'aksi',
                        className: "text-center"
                    }
                ]
            });
        }else{
            datapegawai.draw();
        }
    }

    function hapus_data(id, status_kerja){
        status_kerja = parseInt(status_kerja);
        
        if(status_kerja == 0){
            // Jika sudah non active, hanya tampilkan opsi Hapus Data
            Swal.fire({
                title: 'Hapus Data',
                text: "Apakah Anda yakin ingin menghapus data pegawai ini?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus Data',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    proses_hapus_data(id, 'hapus');
                }
            });
        } else {
            // Jika masih active, tampilkan 2 opsi
            Swal.fire({
                title: 'Pilih Aksi',
                text: "Pilih tindakan yang ingin dilakukan pada pegawai ini:",
                icon: 'question',
                showCancelButton: true,
                showDenyButton: true,
                confirmButtonColor: '#dc3545',
                denyButtonColor: '#ffc107',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Hapus Data',
                denyButtonText: 'Nonaktifkan Pegawai',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    proses_hapus_data(id, 'hapus');
                } else if (result.isDenied) {
                    proses_hapus_data(id, 'nonaktif');
                }
            });
        }
    }

    function proses_hapus_data(id, tipe){
        jQuery('#wrap-loading').show();
        jQuery.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type:'post',
            data:{
                'action' : 'hapus_data_pegawai_by_id',
                'api_key': '<?php echo get_option( ABSEN_APIKEY ); ?>',
                'id'     : id,
                'tipe'   : tipe
            },
            dataType: 'json',
            success:function(response){
                jQuery('#wrap-loading').hide();
                if(response.status == 'success'){
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    get_data_pegawai(); 
                }else{
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: response.message
                    });
                }
            }
        });
    }

    function edit_data(_id){
        jQuery('#wrap-loading').show();
        jQuery.ajax({
            method: 'post',
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            dataType: 'json',
            data:{
                'action': 'get_data_pegawai_by_id',
                'api_key': '<?php echo get_option( ABSEN_APIKEY ); ?>',
                'id': _id,
            },
            success: function(res){
                if(res.status == 'success'){
                    var status_kerja = res.data.status_kerja ? parseInt(res.data.status_kerja) : 1;
                    var is_non_active = status_kerja == 0;
                    
                    jQuery('#id_data').val(res.data.id);
                    jQuery('#nik').val(res.data.nik);
                    jQuery('#nama').val(res.data.nama);
                    jQuery('#tempat_lahir').val(res.data.tempat_lahir);
                    jQuery('#tanggal_lahir').val(res.data.tanggal_lahir);
                    jQuery('#jenis_kelamin').val(res.data.jenis_kelamin);
                    jQuery('#status').val(res.data.status);
                    jQuery('#status_teks').val(res.data.status_teks);
                    jQuery('#jabatan').val(res.data.jabatan);
                    jQuery('#agama').val(res.data.agama);
                    jQuery('#no_hp').val(res.data.no_hp);
                    jQuery('#alamat').val(res.data.alamat);
                    jQuery('#pendidikan_terakhir').val(res.data.pendidikan_terakhir);
                    jQuery('#pendidikan_sekarang').val(res.data.pendidikan_sekarang);
                    jQuery('#nama_sekolah').val(res.data.nama_sekolah);
                    jQuery('#lulus').val(res.data.lulus);
                    jQuery('#email').val(res.data.email);
                    jQuery('#karpeg').val(res.data.karpeg);
                    jQuery('#tanggal_mulai').val(res.data.tanggal_mulai);
                    jQuery('#tanggal_selesai').val(res.data.tanggal_selesai);
                    jQuery('#gaji').val(res.data.gaji);
                    jQuery('#user_role').val(res.data.user_role);
                    jQuery('#tahun').val(res.data.tahun);
                    
                    status_pegawai_teks();
                    
                    jQuery('#modalTambahDataPegawai').modal('show');
                    
                    setTimeout(function(){
                        if(is_non_active){
                            jQuery('#modalTambahDataPegawai input').prop('disabled', true).prop('readonly', true);
                            jQuery('#modalTambahDataPegawai select').prop('disabled', true);
                            jQuery('#modalTambahDataPegawai textarea').prop('disabled', true).prop('readonly', true);
                            jQuery('#modalTambahDataPegawai .modal-footer .btn-primary').hide();
                            jQuery('#modalTambahDataPegawaiLabel').text('Data Pegawai (Non Active - Hanya Baca)');
                        } else {
                            jQuery('#modalTambahDataPegawai input').prop('disabled', false).prop('readonly', false);
                            jQuery('#modalTambahDataPegawai select').prop('disabled', false);
                            jQuery('#modalTambahDataPegawai textarea').prop('disabled', false).prop('readonly', false);
                            jQuery('#modalTambahDataPegawai .modal-footer .btn-primary').show();
                            jQuery('#modalTambahDataPegawaiLabel').text('Data Pegawai');
                        }
                    }, 300);
                }else{
                    alert(res.message);
                }
                jQuery('#wrap-loading').hide();
            }
        });
    }

    function tambah_data_pegawai(){
        jQuery('#id_data').val('');
        jQuery('#nik').val('');
        jQuery('#nama').val('');
        jQuery('#tempat_lahir').val('');
        jQuery('#tanggal_lahir').val('');
        jQuery('#jenis_kelamin').val('');
        jQuery('#status').val('');
        jQuery('#status_teks').val('');
        jQuery('#jabatan').val('');
        jQuery('#agama').val('');
        jQuery('#no_hp').val('');
        jQuery('#alamat').val('');
        jQuery('#pendidikan_terakhir').val('');
        jQuery('#pendidikan_sekarang').val('');
        jQuery('#nama_sekolah').val('');
        jQuery('#lulus').val('');
        jQuery('#email').val('');
        jQuery('#karpeg').val('');
        jQuery('#tanggal_mulai').val('');
        jQuery('#tanggal_selesai').val('');
        jQuery('#gaji').val('');
        jQuery('#user_role').val('');
        
        status_pegawai_teks();
        
        jQuery('#modalTambahDataPegawai').modal('show');
        
        setTimeout(function(){
            jQuery('#modalTambahDataPegawai input').prop('disabled', false).prop('readonly', false);
            jQuery('#modalTambahDataPegawai select').prop('disabled', false);
            jQuery('#modalTambahDataPegawai textarea').prop('disabled', false).prop('readonly', false);
            jQuery('#modalTambahDataPegawai .modal-footer .btn-primary').show();
            jQuery('#modalTambahDataPegawaiLabel').text('Data Pegawai');
        }, 300);
    }

    function submitTambahDataFormPegawai(){
        if(jQuery('#nik').prop('readonly') || jQuery('#nik').prop('disabled')){
            return alert('Data pegawai Non Active tidak dapat diedit!');
        }
        
        var id_data = jQuery('#id_data').val();
        var nik = jQuery('#nik').val();
        if(nik == ''){
            return alert('Data NIK tidak boleh kosong!');
        }
        var nama = jQuery('#nama').val();
        if(nama == ''){
            return alert('Data Nama tidak boleh kosong!');
        }
        
        var jenis_kelamin = jQuery('#jenis_kelamin').val();
        if(jenis_kelamin == ''){
            return alert('Jenis Kelamin tidak boleh kosong!');
        }
        
        var status = jQuery('#status').val();
        if(status == ''){
            return alert('Status Pegawai tidak boleh kosong!');
        }
        
        var status_teks = jQuery('#status_teks').val();
        if(status == '5' && status_teks == ''){
            return alert('Status Pegawai Lainnya tidak boleh kosong!');
        }
        
        var jabatan = jQuery('#jabatan').val();
        if(jabatan == ''){
            return alert('Jabatan tidak boleh kosong!');
        }
        
        var tanggal_mulai = jQuery('#tanggal_mulai').val();
        if(tanggal_mulai == ''){
            return alert('Tanggal Mulai tidak boleh kosong!');
        }
        
        var tanggal_selesai = jQuery('#tanggal_selesai').val();
        if(status != '1' && tanggal_selesai == ''){
            return alert('Tanggal Selesai tidak boleh kosong!');
        }
        
        var user_role = jQuery('#user_role').val();
        if(user_role == ''){
            return alert('User Role tidak boleh kosong!');
        }
        
        var tempat_lahir = jQuery('#tempat_lahir').val();
        var tanggal_lahir = jQuery('#tanggal_lahir').val();
        var agama = jQuery('#agama').val();
        var no_hp = jQuery('#no_hp').val();
        var alamat = jQuery('#alamat').val();
        var pendidikan_terakhir = jQuery('#pendidikan_terakhir').val();
        var pendidikan_sekarang = jQuery('#pendidikan_sekarang').val();
        var nama_sekolah = jQuery('#nama_sekolah').val();
        var lulus = jQuery('#lulus').val();
        var email = jQuery('#email').val();
        var karpeg = jQuery('#karpeg').val();
        var gaji = jQuery('#gaji').val();

        jQuery('#wrap-loading').show();
        jQuery.ajax({
            method: 'post',
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            dataType: 'json',
            data:{
                'action': 'tambah_data_pegawai',
                'api_key': '<?php echo get_option( ABSEN_APIKEY ); ?>',
                'id_data': id_data,
                'nik': nik,
                'nama': nama,
                'tempat_lahir': tempat_lahir,
                'tanggal_lahir': tanggal_lahir,
                'jenis_kelamin': jenis_kelamin,
                'status': status,
                'status_teks': status_teks,
                'jabatan': jabatan,
                'agama': agama,
                'no_hp': no_hp,
                'alamat': alamat,
                'pendidikan_terakhir': pendidikan_terakhir,
                'pendidikan_sekarang': pendidikan_sekarang,
                'nama_sekolah': nama_sekolah,
                'lulus': lulus,
                'email': email,
                'karpeg': karpeg,
                'tanggal_mulai': tanggal_mulai,
                'tanggal_selesai': tanggal_selesai,
                'gaji': gaji,
                'user_role': user_role,
                'tahun': <?php echo $input['tahun_anggaran']; ?>
            },
            success: function(res){
                alert(res.message);
                jQuery('#modalTambahDataPegawai').modal('hide');
                if(res.status == 'success'){
                    get_data_pegawai();
                }
                jQuery('#wrap-loading').hide();
            }
        });
    }
</script>