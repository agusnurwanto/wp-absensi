<?php
global $wpdb;

if (!defined('WPINC')) {
    die;
}

$input = shortcode_atts(array(
    'tahun_anggaran' => '2026',
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

// Get field visibility options
$hide_tempat_lahir = carbon_get_theme_option('crb_hide_tempat_lahir');
$hide_tanggal_lahir = carbon_get_theme_option('crb_hide_tanggal_lahir');
$hide_jenis_kelamin = carbon_get_theme_option('crb_hide_jenis_kelamin');
$hide_agama = carbon_get_theme_option('crb_hide_agama');
$hide_pendidikan_terakhir = carbon_get_theme_option('crb_hide_pendidikan_terakhir');
$hide_pendidikan_sekarang = carbon_get_theme_option('crb_hide_pendidikan_sekarang');
$hide_nama_sekolah = carbon_get_theme_option('crb_hide_nama_sekolah');
$hide_lulus = carbon_get_theme_option('crb_hide_lulus');
$hide_alamat = carbon_get_theme_option('crb_hide_alamat');

$current_user = wp_get_current_user();
$is_admin_instansi = in_array('admin_instansi', (array) $current_user->roles) && !in_array('administrator', (array) $current_user->roles);
$current_user_id = $current_user->ID;


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
            

        </div>
        <div class="wrap-table">
        <table id="management_data_table" cellpadding="2" cellspacing="0"  class="table table-bordered">
            <thead>
                <tr>
                    <th class="text-center">NIP / NIK</th>
                    <th class="text-center">Nama</th>
                    <th class="text-center">No Handphone</th>

                    <th class="text-center">Email</th>
                    <th class="text-center">Admin Instansi</th>
                    <th class="text-center">Status</th>
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
                        <label>NIP / NIK <span class="text-danger">*</span></label>
                        <input type="number" id="nik" class="form-control">
                    </div>

                    <div class="col-md-4 form-group">
                        <label>Nama <span class="text-danger">*</span></label>
                        <input type="text" id="nama" class="form-control">
                    </div>

                    <div class="col-md-4 form-group"<?php if($hide_tempat_lahir) echo ' style="display:none;"'; ?>>
                        <label>Tempat Lahir</label>
                        <input type="text" id="tempat_lahir" class="form-control">
                    </div>

                    <div class="col-md-4 form-group"<?php if($hide_tanggal_lahir) echo ' style="display:none;"'; ?>>
                        <label>Tanggal Lahir</label>
                        <input type="date" id="tanggal_lahir" class="form-control">
                    </div>

                    <div class="col-md-4 form-group"<?php if($hide_jenis_kelamin) echo ' style="display:none;"'; ?>>
                        <label>Jenis Kelamin <span class="text-danger">*</span></label>
                        <select id="jenis_kelamin" class="form-control">
                            <option value="">-- Pilih Jenis Kelamin --</option>
                        </select>
                    </div>

                    <div class="col-md-4 form-group"<?php if($hide_agama) echo ' style="display:none;"'; ?>>
                        <label>Agama</label>
                        <select id="agama" class="form-control">
                            <option value="">-- Pilih Agama --</option>
                        </select>
                    </div>


                    <div class="col-md-4 form-group">
                        <label>No Handphone</label>
                        <input type="number" id="no_hp" class="form-control">
                    </div>

                    <div class="col-md-4 form-group"<?php if($hide_pendidikan_terakhir) echo ' style="display:none;"'; ?>>
                        <label>Pendidikan Terakhir</label>
                        <select id="pendidikan_terakhir" class="form-control">
                            <option value="">-- Pilih Pendidikan --</option>
                        </select>
                    </div>

                    <div class="col-md-4 form-group"<?php if($hide_pendidikan_sekarang) echo ' style="display:none;"'; ?>>
                        <label>Pendidikan Sekarang</label>
                        <select id="pendidikan_sekarang" class="form-control">
                            <option value="">-- Pilih Pendidikan --</option>
                        </select>
                    </div>

                    <div class="col-md-4 form-group"<?php if($hide_nama_sekolah) echo ' style="display:none;"'; ?>>
                        <label>Nama Sekolah</label>
                        <input type="text" id="nama_sekolah" class="form-control">
                    </div>

                    <div class="col-md-4 form-group"<?php if($hide_lulus) echo ' style="display:none;"'; ?>>
                        <label>Lulus (Tahun)</label>
                        <input type="number" id="lulus" class="form-control">
                    </div>

                    <div class="col-md-4 form-group">
                        <label>Email <span class="text-danger">*</span></label>
                        <input type="email" id="email" class="form-control">
                    </div>

                    <div class="col-md-12 form-group"<?php if($hide_alamat) echo ' style="display:none;"'; ?>>
                        <label>Alamat</label>
                        <textarea id="alamat" class="form-control"></textarea>
                    </div>
                    
                    <div class="col-md-4 form-group">
                        <label>Admin Instansi (Parent Role) <span class="text-danger">*</span></label>
                        <select id="admin_instansi" class="form-control">
                            <option value="">-- Pilih Admin Instansi --</option>
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
    var isAdminInstansi = <?php echo $is_admin_instansi ? 'true' : 'false'; ?>;
    var currentUserId = '<?php echo $current_user_id; ?>';


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
        
        
        var admin_instansi = jQuery('#admin_instansi');
        admin_instansi.find('option:not(:first)').remove();
        if(masterData.admin_instansi){
            jQuery.each(masterData.admin_instansi, function(i, item){
                admin_instansi.append(jQuery('<option>', {
                    value: item.value,
                    text: item.label
                }));
            });
        }

    }

    // status_pegawai_teks function removed

    function get_data_pegawai(){
        if(typeof datapegawai == 'undefined'){
            window.datapegawai = jQuery('#management_data_table').on('preXhr.dt', function ( e, settings, data ) {
                jQuery("#wrap-loading").show();
            }).DataTable({
                "processing": true, 
                "serverSide": true,
                "ajax": {
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type:'post',
                    dataType: 'json',
                    data: function(d){
                        d.action = 'get_datatable_pegawai';
                        d.api_key = '<?php echo get_option( ABSEN_APIKEY ); ?>';
                        d.tahun = '<?php echo $input['tahun_anggaran']; ?>';
                        return d;
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
                        "data": 'no_hp',
                        className: "text-center"
                    },

                    {
                        "data": 'email',
                        className: "text-center"
                    },
                    {
                        "data": 'admin_instansi_name',
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
        }else{
            datapegawai.draw();
        }
    }

    function hapus_data(id){
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

    function toggle_status_pegawai(id, status) {
        var actionText = (status == 1) ? "Aktifkan" : "Nonaktifkan";
        var confirmText = (status == 1) ? "Data Pegawai akan diaktifkan kembali." : "Data Pegawai akan dinonaktifkan.";
        
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
                        'action': 'toggle_status_pegawai',
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
                            get_data_pegawai(); // Refresh table
                        } else {
                            Swal.fire(
                                'Gagal!',
                                res.message,
                                'error'
                            );
                        }
                    },
                    error: function() {
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
                    jQuery('#nik').val(res.data.nik).prop('readonly', true).prop('disabled', true);
                    jQuery('#nama').val(res.data.nama);
                    jQuery('#tempat_lahir').val(res.data.tempat_lahir);
                    jQuery('#tanggal_lahir').val(res.data.tanggal_lahir);
                    jQuery('#lulus').val(res.data.lulus);
                    jQuery('#email').val(res.data.email);
                    jQuery('#admin_instansi').val(res.data.id_instansi);
                    jQuery('#no_hp').val(res.data.no_hp);

                    jQuery('#tahun').val(res.data.tahun);

                    if (isAdminInstansi) {
                        jQuery('#admin_instansi').val(res.data.id_instansi).prop('disabled', true).prop('readonly', true);
                    }
                    
                    jQuery('#modalTambahDataPegawai').modal('show');
                    
                    // setTimeout(function(){
                    //     if(is_non_active){
                    //         jQuery('#modalTambahDataPegawai input').prop('disabled', true).prop('readonly', true);
                    //         jQuery('#modalTambahDataPegawai select').prop('disabled', true);
                    //         jQuery('#modalTambahDataPegawai textarea').prop('disabled', true).prop('readonly', true);
                    //         jQuery('#modalTambahDataPegawai .modal-footer .btn-primary').hide();
                    //         jQuery('#modalTambahDataPegawaiLabel').text('Data Pegawai (Non Active - Hanya Baca)');
                    //     } else {
                    //         jQuery('#modalTambahDataPegawai input').prop('disabled', false).prop('readonly', false);
                    //         jQuery('#modalTambahDataPegawai select').prop('disabled', false);
                    //         jQuery('#modalTambahDataPegawai textarea').prop('disabled', false).prop('readonly', false);
                    //         jQuery('#modalTambahDataPegawai .modal-footer .btn-primary').show();
                    //         jQuery('#modalTambahDataPegawaiLabel').text('Data Pegawai');
                    //     }
                    // }, 300);
                }else{
                    alert(res.message);
                }
                jQuery('#wrap-loading').hide();
            }
        });
    }

    function tambah_data_pegawai(){
        jQuery('#id_data').val('');
        jQuery('#admin_instansi').val('');
        jQuery('#no_hp').val('');
        jQuery('#nama').val('');
        jQuery('#tempat_lahir').val('');
        jQuery('#tanggal_lahir').val('');
        jQuery('#email').val('');
        jQuery('#nik').val('').prop('readonly', false).prop('disabled', false);
        
        if (isAdminInstansi) {
            jQuery('#admin_instansi').val(currentUserId).trigger('change').prop('disabled', true).prop('readonly', true);
        }
        
        // status_pegawai_teks(); (Removed)

        
        jQuery('#modalTambahDataPegawai').modal('show');
        
        // setTimeout(function(){
        //     jQuery('#modalTambahDataPegawai input').prop('disabled', false).prop('readonly', false);
        //     jQuery('#modalTambahDataPegawai select').prop('disabled', false);
        //     jQuery('#modalTambahDataPegawai textarea').prop('disabled', false).prop('readonly', false);
            
        //     if(isAdminInstansi){
        //         jQuery('#admin_instansi').prop('disabled', true);
        //     }

        //     jQuery('#modalTambahDataPegawai .modal-footer .btn-primary').show();
        //     jQuery('#modalTambahDataPegawaiLabel').text('Data Pegawai');
        // }, 300);

    }

    function submitTambahDataFormPegawai() {
        
        var id_data = jQuery('#id_data').val();
        var admin_instansi = jQuery('#admin_instansi').val();
        if(admin_instansi == ''){
            return alert('Admin Instansi (Parent Role) tidak boleh kosong!');
        }
        var nik = jQuery('#nik').val();

        if(nik == ''){
            return alert('Data NIK tidak boleh kosong!');
        }
        var nama = jQuery('#nama').val();
        if(nama == ''){
            return alert('Data Nama tidak boleh kosong!');
        }
        
        var jenis_kelamin = jQuery('#jenis_kelamin').val();
        if(jQuery('#jenis_kelamin').is(':visible') && jenis_kelamin == ''){
            return alert('Jenis Kelamin tidak boleh kosong!');
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
        if(email == ''){
            return alert('Email tidak boleh kosong!');
        }
        var email = jQuery('#email').val();


        jQuery('#wrap-loading').show();
        jQuery.ajax({
            method: 'post',
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            dataType: 'json',
            data:{
                'action': 'tambah_data_pegawai',
                'api_key': '<?php echo get_option( ABSEN_APIKEY ); ?>',
                'id_data': id_data,
                'admin_instansi': admin_instansi,
                'nik': nik,

                'nama': nama,
                'tempat_lahir': tempat_lahir,
                'tanggal_lahir': tanggal_lahir,
                'jenis_kelamin': jenis_kelamin,
                'agama': agama,
                'no_hp': no_hp,

                'alamat': alamat,
                'pendidikan_terakhir': pendidikan_terakhir,
                'pendidikan_sekarang': pendidikan_sekarang,
                'nama_sekolah': nama_sekolah,
                'lulus': lulus,
                'email': email,
                'email': email,



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