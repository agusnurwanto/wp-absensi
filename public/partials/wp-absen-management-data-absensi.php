<?php
global $wpdb;

if (!defined('WPINC')) {
    die;
}

$input = shortcode_atts(array(
    'tahun_anggaran' => '2025',
), $atts);

$date = date('d-m-Y');
$get_data_unit = $wpdb->get_results($wpdb->prepare('
    SELECT 
        *
    FROM absensi_data_unit
    WHERE tahun_anggaran=%d
        AND active=1
        AND is_skpd=1
', $input['tahun_anggaran']), ARRAY_A);
// print_r($ret['data']); die($wpdb->last_query);
$get_skpd = '<option value="">Pilih SKPD</option>';
foreach($get_data_unit as $skpd){
    $get_skpd .= '<option value="'.$skpd['id_skpd'].'">'.$skpd['kode_skpd'].' '.$skpd['nama_skpd'].' ( ID = '.$skpd['id_skpd'].')</option>';
}
?>
<style type="text/css">
    .wrap-table{
        overflow: auto;
        max-height: 100vh; 
        width: 100%; 
    }
</style>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<div class="cetak">
    <div style="padding: 10px;margin:0 0 3rem 0;">
        <input type="hidden" value="<?php echo get_option( ABSEN_APIKEY ); ?>" id="api_key">
    <h1 class="text-center" style="margin:3rem;">Manajemen Data Absensi<br>Tahun <?php echo $input['tahun_anggaran']; ?></h1>
        <div style="margin-bottom: 25px;">
            <button class="btn btn-primary" onclick="tambah_data_absensi();"><i class="dashicons dashicons-plus"></i> Tambah Data</button>
        </div>
        <div class="wrap-table">
        <table id="management_data_table" cellpadding="2" cellspacing="0">
            <thead>
                <tr>
                    <th class="text-center">No</th>
                    <th class="text-center">Nama Pegawai</th>
                    <th class="text-center">Tanggal</th>
                    <th class="text-center">Keterangan Absensi</th>
                    <th class="text-center">Jam Kerja / Shift</th>
                    <th class="text-center">Nama Pasar</th>
                    <th class="text-center" style="width: 150px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
        </div>
    </div>          
</div>
<div class="modal fade mt-4" id="modalTambahDataAbsensi" tabindex="-1" role="dialog" aria-labelledby="modalTambahDataAbsensiLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTambahDataAbsensiLabel">Data Absensi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type='hidden' id='id_data' name="id_data" placeholder=''>
                <div class="form-group">
                    <label for='tanggal_absensi' style='display:inline-block'>Tanggal</label>
                    <input type="text" id='tanggal_absensi' name="tanggal_absensi" class="form-control" disabled placeholder='<?php echo $date; ?>'/>                    
                </div>
                <div class="form-group">
                    <label for='id_skpd' style='display:inline-block'>Pilih SKPD</label>
                    <select id='id_skpd' name="id_skpd" class="form-control">
                        <?php echo $get_skpd; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for='nama_pegawai' style='display:inline-block'>Nama Pegawai</label>
                    <select id='nama_pegawai' name="nama_pegawai" class="form-control">
                    </select>
                </div>
            </div> 
            <div class="modal-footer">
                <button class="btn btn-primary submitBtn" onclick="submitTambahDataFormAbsensi()">Simpan</button>
                <button type="submit" class="components-button btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<script>    
jQuery(document).ready(function(){
    // penyesuaian thema wp full width page
    jQuery('.mg-card-box').parent().removeClass('col-md-8').addClass('col-md-12');
    jQuery('#secondary').parent().remove();
    get_data_absensi();
    jQuery('#id_skpd').select2({ width: '100%' });
});

function get_data_absensi(){
    if(typeof dataabsensi == 'undefined'){
        window.dataabsensi = jQuery('#management_data_table').on('preXhr.dt', function(e, settings, data){
            jQuery("#wrap-loading").show();
        }).DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'post',
                dataType: 'json',
                data:{
                    'action': 'get_datatable_absensi',
                    'api_key': '<?php echo get_option( ABSEN_APIKEY ); ?>',
                }
            },
            lengthMenu: [[20, 50, 100, -1], [20, 50, 100, "All"]],
            order: [[0, 'asc']],
            "drawCallback": function( settings ){
                jQuery("#wrap-loading").hide();
            },
            "columns": [
                {
                    "data": '',
                    className: "text-center"
                },
                {
                    "data": '',
                    className: "text-center"
                },
                {
                    "data": '',
                    className: "text-center"
                },
                {
                    "data": '',
                    className: "text-center"
                },
                {
                    "data": '',
                    className: "text-center"
                },
                {
                    "data": '',
                    className: "text-center"
                },
                {
                    "data": 'aksi',
                    className: "text-center"
                }
            ]
        });
    }else{
        dataabsensi.draw();
    }
}

function get_pegawai(no_loading=false) {
    return new Promise(function(resolve, reject){
        var id_skpd = jQuery('#id_skpd').val();
        if(id_skpd == ''){
            jQuery('#daftar_pegawai tbody').html('');
            return;
        }
        if(typeof global_response_pegawai == 'undefined'){
            global_response_pegawai = {};
        }
        if(!global_response_pegawai[id_skpd]){
            if(!no_loading){
                jQuery("#wrap-loading").show();
            }
            jQuery.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type:'post',
                data:{
                    'action' : 'get_pegawai_absensi',
                    'api_key': '<?php echo get_option( SIMPEG_APIKEY ); ?>',
                    'id_skpd': id_skpd,
                    'tahun_anggaran': jQuery('#tahun_anggaran').val()
                },
                dataType: 'json',
                success:function(response){
                    if(!no_loading){
                        jQuery("#wrap-loading").hide();
                    }
                    if(response.status == 'success'){
                        window.global_response_pegawai[id_skpd] = response;
                        var html = html_pegawai({
                            id: 1, 
                            html: global_response_pegawai[id_skpd].html
                        });
                        jQuery('#daftar_pegawai tbody').html(html);
                        jQuery('#id_pegawai_1').html(global_response_pegawai[id_skpd].html);
                        jQuery('#id_pegawai_1').select2({'width': '100%'});
                        return resolve();
                    }else{
                        alert(`GAGAL! \n${response.message}`);
                    }
                }
            });
        }else{
            var html = html_pegawai({
                id: 1, 
                html: global_response_pegawai[id_skpd].html
            });
            jQuery('#daftar_pegawai tbody').html(html);
            jQuery('#id_pegawai_1').html(global_response_pegawai[id_skpd].html);
            jQuery('#id_pegawai_1').select2({'width': '100%'});
            return resolve();
        }
    });
}
function hapus_data(id){
    let confirmDelete = confirm("Apakah anda yakin akan menghapus data ini?");
    if(confirmDelete){
        jQuery('#wrap-loading').show();
        jQuery.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type:'post',
            data:{
                'action' : 'hapus_data_absensi_by_id',
                'api_key': '<?php echo get_option( ABSEN_APIKEY ); ?>',
                'id'     : id
            },
            dataType: 'json',
            success:function(response){
                jQuery('#wrap-loading').hide();
                if(response.status == 'success'){
                    get_data_absensi(); 
                }else{
                    alert(`GAGAL! \n${response.message}`);
                }
            }
        });
    }
}

function edit_data(_id){
    jQuery('#wrap-loading').show();
    jQuery.ajax({
        method: 'post',
        url: '<?php echo admin_url('admin-ajax.php'); ?>',
        dataType: 'json',
        data:{
            'action': 'get_data_absensi_by_id',
            'api_key': '<?php echo get_option( ABSEN_APIKEY ); ?>',
            'id': _id,
        },
        success: function(res){
            if(res.status == 'success'){
                jQuery('#id_data').val(res.data.id);
                jQuery('#nama_absensi').val(res.data.nama_absensi);
                jQuery('#alamat_absensi').val(res.data.alamat_absensi);
                jQuery('#modalTambahDataAbsensi').modal('show');
            }else{
                alert(res.message);
            }
            jQuery('#wrap-loading').hide();
        }
    });
}

//show tambah data
function tambah_data_absensi(){
    jQuery('#id_data').val('');
    jQuery('#nama_absensi').val('');
    jQuery('#alamat_absensi').val('');
    jQuery('#modalTambahDataAbsensi').modal('show');
}

function submitTambahDataFormAbsensi(){
    var id_data = jQuery('#id_data').val();
    var alamat_absensi = jQuery('#alamat_absensi').val();
    if(alamat_absensi == ''){
        return alert('Data alamat absensi tidak boleh kosong!');
    }
    var nama_absensi = jQuery('#nama_absensi').val();
    if(nama_absensi == ''){
        return alert('Data nama absensi tidak boleh kosong!');
    }

    jQuery('#wrap-loading').show();
    jQuery.ajax({
        method: 'post',
        url: '<?php echo admin_url('admin-ajax.php'); ?>',
        dataType: 'json',
        data:{
            'action': 'tambah_data_absensi',
            'api_key': '<?php echo get_option( ABSEN_APIKEY ); ?>',
            'id_data': id_data,
            'tahun': <?php echo $input['tahun_anggaran']; ?>,
            'alamat_absensi': alamat_absensi,
            'nama_absensi': nama_absensi,
        },
        success: function(res){
            alert(res.message);
            jQuery('#modalTambahDataAbsensi').modal('hide');
            if(res.status == 'success'){
                get_data_absensi();
            }else{
                jQuery('#wrap-loading').hide();
            }
        }
    });
}
</script>