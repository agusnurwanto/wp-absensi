<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$current_user = wp_get_current_user();

?>

<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .clock-container {
        text-align: center;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 10px;
        margin-bottom: 20px;
    }

    #server-clock {
        font-size: 3rem;
        font-weight: bold;
        color: #333;
    }

    #server-date {
        font-size: 1.2rem;
        color: #666;
    }

    .absensi-action-area {
        text-align: center;
    }

    .info-box {
        background: #e9ecef;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
    }
</style>

<div class="row">
    <div class="col-md-12">
        <div class="card card-box" style="margin-bottom: 2rem;">
            <div class="card-body">
                <h3 class="text-center mb-4">Absensi Harian Pegawai</h3>

                <!-- Clock Section -->
                <div class="clock-container">
                    <div id="server-clock">--:--:--</div>
                    <div id="server-date">-- -- ----</div>
                </div>

                <!-- Work Code Selection -->
                <div class="form-group">
                    <label for="id_kode_kerja_pegawai">Pilih Kode Kerja / Jadwal:</label>
                    <select class="form-control" id="id_kode_kerja_pegawai" style="width: 100%;">
                        <option value="">-- Pilih Kode Kerja --</option>
                    </select>
                </div>
                
                <div id="jadwal-info" class="alert alert-info" style="display:none;">
                    <strong>Jadwal:</strong> <span id="jadwal-detail">-</span>
                </div>

                <!-- Status Info -->
                <div class="info-box" id="absensi-status-box" style="display:none;">
                    <p class="mb-1"><strong>Masuk:</strong> <span id="info-masuk">-</span></p>
                    <p class="mb-0"><strong>Pulang:</strong> <span id="info-pulang">-</span></p>
                </div>

                <!-- Action Buttons -->
                <div class="absensi-action-area">
                    <input type="hidden" id="current_koordinat" value="">
                    
                    <button id="btn-absen-masuk" class="btn btn-lg btn-primary mb-2" style="display:none;" onclick="submit_absensi('masuk')">
                        <i class="dashicons dashicons-location-alt"></i> Absen Masuk
                    </button>
                    
                    <button id="btn-absen-pulang" class="btn btn-lg btn-success mb-2" style="display:none;" onclick="submit_absensi('pulang')">
                        <i class="dashicons dashicons-location-alt"></i> Absen Pulang
                    </button>

                    <div id="location-status" class="text-muted mt-2"><small>Mencari Lokasi...</small></div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(() => {
    // 1. Initialize Clock
    initClientClock();

    // 2. Load Work Codes
    loadKodeKerja();

    // 3. Initialize Location
    getLocation();

    // 4. Listener for Code Change
    jQuery('#id_kode_kerja_pegawai').change(() => {
        checkStatusAbsensi();
    });
});

function initClientClock() {
    setInterval(updateClock, 1000);
    updateClock(); // Initial call
}

function updateClock() {
    let now = new Date();

    let hours = String(now.getHours()).padStart(2, '0');
    let minutes = String(now.getMinutes()).padStart(2, '0');
    let seconds = String(now.getSeconds()).padStart(2, '0');
    
    // Date formatting (Indonesian style simple)
    let options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    let dateString = now.toLocaleDateString('id-ID', options);

    jQuery('#server-clock').text(`${hours}:${minutes}:${seconds}`);
    jQuery('#server-date').text(dateString);
}


function loadKodeKerja() {
    jQuery.ajax({
        url: ajax.url,
        type: 'post',
        data: {
            action: 'get_valid_kode_kerja',
            api_key: ajax.api_key
        },
        dataType: 'json',
        success: (response) => {
            if (response.status == 'success') {
                let options = '<option value="">-- Pilih Kode Kerja --</option>';

                jQuery.each(response.data, (i, item) => {
                    options += `<option value="${item.id}" data-masuk="${item.jam_masuk}" data-pulang="${item.jam_pulang}">${item.nama_kerja}</option>`;
                });

                jQuery('#id_kode_kerja_pegawai').html(options);
            }
        }
    });
}

function checkStatusAbsensi() {
    let id_kode = jQuery('#id_kode_kerja_pegawai').val();
    
    // Hide buttons initially
    jQuery('#btn-absen-masuk').hide();
    jQuery('#btn-absen-pulang').hide();
    jQuery('#absensi-status-box').hide();
    jQuery('#jadwal-info').hide();

    if (!id_kode) return;

    // Show Details
    let selectedOption = jQuery('#id_kode_kerja_pegawai').find(':selected');
    let jamMasuk = selectedOption.data('masuk');
    let jamPulang = selectedOption.data('pulang');
    /* Get Current Day Name in Indonesian */
    let days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    let dayName = days[new Date().getDay()];

    if (jamMasuk && jamPulang) {
        jQuery('#jadwal-detail').text(`${dayName}, ${jamMasuk} - ${jamPulang}`);
        jQuery('#jadwal-info').show();
    }

    jQuery.ajax({
        url: ajax.url,
        type: 'post',
        data: {
            action: 'check_status_absensi',
            api_key: ajax.api_key,
            id_kode_kerja: id_kode
        },
        dataType: 'json',
        success: (response) => {
            if (response.status == 'success') {
                let waktu_masuk = response.waktu_masuk;
                let waktu_pulang = response.waktu_pulang;

                // Show only if there is data
                if (waktu_masuk || waktu_pulang) {
                    jQuery('#absensi-status-box').show();
                } else {
                    jQuery('#absensi-status-box').hide();
                }

                jQuery('#info-masuk').text(waktu_masuk ? waktu_masuk : '-');
                jQuery('#info-pulang').text(waktu_pulang ? waktu_pulang : '-');

                if (!waktu_masuk) {
                    // Belum Absen Masuk
                    jQuery('#btn-absen-masuk').show();
                } else {
                    // Sudah Masuk, bisa Pulang (atau update pulang)
                    jQuery('#btn-btn-absen-masuk').hide();
                    jQuery('#btn-absen-pulang').show();
                    // Optional: If already pulang, maybe change text to "Update Pulang"
                    if (waktu_pulang) {
                        jQuery('#btn-absen-pulang').html('<i class="dashicons dashicons-update"></i> Update Absen Pulang');
                    }
                }
            } else if (response.status == 'error') {
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan',
                    text: response.message
                });
            }
        }
    });
}

function getLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(showPosition, showError);
    } else { 
        jQuery('#location-status').text("Geolocation is not supported by this browser.");
    }
}

function showPosition(position) {
    let lat = position.coords.latitude;
    let lng = position.coords.longitude;
    jQuery('#current_koordinat').val(lat + ',' + lng);
    jQuery('#location-status').html(`<span class="text-success"><i class="dashicons dashicons-location"></i> Lokasi Terkunci: ${lat}, ${lng}</span>`);
}

function showError(error) {
    let msg = "";
    switch (error.code) {
        case error.PERMISSION_DENIED:
            msg = "User denied the request for Geolocation."
            break;
        case error.POSITION_UNAVAILABLE:
            msg = "Location information is unavailable."
            break;
        case error.TIMEOUT:
            msg = "The request to get user location timed out."
            break;
        case error.UNKNOWN_ERROR:
            msg = "An unknown error occurred."
            break;
    }
    jQuery('#location-status').html(`<span class="text-danger">${msg}</span>`);
}

function submit_absensi(tipe) {
    let id_kode = jQuery('#id_kode_kerja_pegawai').val();
    let koordinat = jQuery('#current_koordinat').val();

    if (!id_kode) {
        alert("Pilih Kode Kerja Terlebih Dahulu!");
        return;
    }
    if (!koordinat) {
        alert("Lokasi belum ditemukan! Pastikan GPS aktif dan browser diizinkan.");
        getLocation(); // Retry
        return;
    }

    if (!confirm(`Apakah anda yakin ingin Absen ${tipe.toUpperCase()}?`)) return;

    jQuery('#wrap-loading').show(); // Assume global loader exists

    jQuery.ajax({
        url: ajax.url,
        type: 'post',
        data: {
            action: 'submit_absensi_pegawai',
            api_key: ajax.api_key,
            id_kode_kerja: id_kode,
            koordinat: koordinat,
            tipe_absen: tipe
        },
        dataType: 'json',
        success: (response) => {
            jQuery('#wrap-loading').hide();
            if (response.status == 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: response.message
                }).then(() => {
                    checkStatusAbsensi(); // Refresh status
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: response.message
                });
            }
        },
        error: () => {
            jQuery('#wrap-loading').hide();
            alert("Terjadi kesalahan koneksi server.");
        }
    });

}
</script>
