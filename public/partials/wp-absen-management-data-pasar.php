<?php
global $wpdb;

if (!defined('WPINC')) {
    die;
}

$input = shortcode_atts(array(
    'tahun' => '2025',
), $atts);
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
    <h1 class="text-center" style="margin:3rem;">Manajemen Data Pasar<br>Tahun <?php echo $input['tahun']; ?></h1>
        <div style="margin-bottom: 25px;">
            <button class="btn btn-primary" onclick="tambah_data_pasar();"><i class="dashicons dashicons-plus"></i> Tambah Data</button>
        </div>
        <div class="wrap-table">
        <table id="management_data_table" cellpadding="2" cellspacing="0">
            <thead>
                <tr>
                    <th class="text-center">Nama Pasar</th>
                    <th class="text-center">Alamat</th>
                    <th class="text-center" style="width: 150px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
        </div>
    </div>          
</div>
<div class="modal fade mt-4" id="modalTambahDataPasar" tabindex="-1" role="dialog" aria-labelledby="modalTambahDataPasarLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTambahDataPasarLabel">Data Pasar</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type='hidden' id='id_data' name="id_data" placeholder=''>
                <div class="form-group">
                    <label for='nama_pasar' style='display:inline-block'>Nama Pasar</label>
                    <input type="text" id='nama_pasar' name="nama_pasar" class="form-control" placeholder=''/>
                </div>
                <div class="form-group">
                    <label for='alamat_pasar' style='display:inline-block'>Alamat Pasar</label>
                    <input type='text' id='alamat_pasar' name="alamat_pasar" class="form-control" placeholder=''>
                </div>
            </div> 
            <div class="modal-footer">
                <button class="btn btn-primary submitBtn" onclick="submitTambahDataFormPasar()">Simpan</button>
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
    get_data_pasar();
});

function get_data_pasar(){
    if(typeof datapasar == 'undefined'){
        window.datapasar = jQuery('#management_data_table').on('preXhr.dt', function(e, settings, data){
            jQuery("#wrap-loading").show();
        }).DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'post',
                dataType: 'json',
                data:{
                    'action': 'get_datatable_pasar',
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
                    "data": 'nama_pasar',
                    className: "text-center"
                },
                {
                    "data": 'alamat_pasar',
                    className: "text-center"
                },
                {
                    "data": 'aksi',
                    className: "text-center"
                }
            ]
        });
    }else{
        datapasar.draw();
    }
}

function hapus_data(id){
    let confirmDelete = confirm("Apakah anda yakin akan menghapus data ini?");
    if(confirmDelete){
        jQuery('#wrap-loading').show();
        jQuery.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type:'post',
            data:{
                'action' : 'hapus_data_pasar_by_id',
                'api_key': '<?php echo get_option( ABSEN_APIKEY ); ?>',
                'id'     : id
            },
            dataType: 'json',
            success:function(response){
                jQuery('#wrap-loading').hide();
                if(response.status == 'success'){
                    get_data_pasar(); 
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
            'action': 'get_data_pasar_by_id',
            'api_key': '<?php echo get_option( ABSEN_APIKEY ); ?>',
            'id': _id,
        },
        success: function(res){
            if(res.status == 'success'){
                jQuery('#id_data').val(res.data.id);
                jQuery('#nama_pasar').val(res.data.nama_pasar);
                jQuery('#alamat_pasar').val(res.data.alamat_pasar);
                jQuery('#modalTambahDataPasar').modal('show');
            }else{
                alert(res.message);
            }
            jQuery('#wrap-loading').hide();
        }
    });
}

//show tambah data
function tambah_data_pasar(){
    jQuery('#id_data').val('');
    jQuery('#nama_pasar').val('');
    jQuery('#alamat_pasar').val('');
    jQuery('#modalTambahDataPasar').modal('show');
}

function submitTambahDataFormPasar(){
    var id_data = jQuery('#id_data').val();
    var alamat_pasar = jQuery('#alamat_pasar').val();
    if(alamat_pasar == ''){
        return alert('Data alamat pasar tidak boleh kosong!');
    }
    var nama_pasar = jQuery('#nama_pasar').val();
    if(nama_pasar == ''){
        return alert('Data nama pasar tidak boleh kosong!');
    }

    jQuery('#wrap-loading').show();
    jQuery.ajax({
        method: 'post',
        url: '<?php echo admin_url('admin-ajax.php'); ?>',
        dataType: 'json',
        data:{
            'action': 'tambah_data_pasar',
            'api_key': '<?php echo get_option( ABSEN_APIKEY ); ?>',
            'id_data': id_data,
            'tahun': <?php echo $input['tahun']; ?>,
            'alamat_pasar': alamat_pasar,
            'nama_pasar': nama_pasar,
        },
        success: function(res){
            alert(res.message);
            jQuery('#modalTambahDataPasar').modal('hide');
            if(res.status == 'success'){
                get_data_pasar();
            }else{
                jQuery('#wrap-loading').hide();
            }
        }
    });
}
</script>