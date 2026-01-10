<?php
/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://github.com/agusnurwanto
 * @since      1.0.0
 *
 * @package    Wp_Absen
 * @subpackage Wp_Absen/public/partials
 */

$user_id = get_current_user_id();
$user_info = get_userdata($user_id);
if (!$user_id) {
    echo '<div style="text-align:center; padding: 20px;">Silahkan login terlebih dahulu.</div>';
    return;
}

// Fetch User Data
$nip = get_user_meta($user_id, '_nip', true) ?: '-';
$skpd = get_user_meta($user_id, '_crb_nama_skpd', true) ?: '-';
$nama = $user_info->display_name;
$jabatan = 'PEGAWAI'; // Placeholder as it's not directly in meta based on previous reads

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2e7d32; /* Green from header */
            --primary-light: #4caf50;
            --bg-color: #f5f7fa;
            --card-bg: #ffffff;
            --text-color: #333333;
            --text-light: #666666;
            --border-radius: 12px;
            --shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
        }

        .absen-wrapper {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-color);
            min-height: 100vh;
            color: var(--text-color);
            max-width: 480px;
            margin: 0 auto;
            position: relative;
            padding-bottom: 20px;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
        }

        /* Header */
        .absen-header {
            background-color: var(--primary-color);
            color: white;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            height: 60px;
        }

        .absen-header img {
            height: 40px;
        }

        .menu-btn {
            position: absolute;
            left: 20px;
            font-size: 24px;
            cursor: pointer;
        }

        /* User Card */
        .user-card {
            background: var(--card-bg);
            margin: 20px;
            margin-top: -20px; /* Overlap header slighty if needed, though design separates it */
            margin-top: 20px;
            padding: 20px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            gap: 15px;
            border-top: 5px solid var(--primary-color);
        }

        .user-avatar {
            width: 80px;
            height: 80px;
            background: #e0e0e0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            color: #757575;
            border: 3px solid #333;
        }

        .user-info {
            flex: 1;
        }

        .user-name {
            font-weight: 700;
            font-size: 16px;
            text-transform: uppercase;
            color: #1a237e; /* Dark Blue */
            margin-bottom: 4px;
            border-bottom: 2px solid #ccc;
            padding-bottom: 4px;
            display: inline-block;
            width: 100%;
        }

        .user-meta {
            font-size: 12px;
            color: #333;
            line-height: 1.4;
            font-weight: 500;
        }

        .user-skpd {
            font-weight: 600;
            margin-top: 4px;
            font-size: 12px;
            color: #1a237e;
        }

        /* Clock & Date */
        .time-display {
            text-align: center;
            margin: 10px 0;
            color: #1a237e;
        }

        .clock {
            font-size: 28px;
            font-weight: 700;
        }

        .date {
            font-size: 14px;
            color: #666;
            font-weight: 500;
        }

        /* Work Code & Schedule */
        .info-card {
            background: var(--card-bg);
            margin: 15px 20px;
            padding: 15px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            text-align: center;
            border: 1px solid #eee;
        }

        .work-code-select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-top: 5px;
            font-weight: 600;
            font-size: 14px;
            text-align: center;
            background: white;
        }

        .schedule-info {
            margin-top: 15px;
        }
        .schedule-title {
            font-weight: 600;
            color: #1a237e;
            font-size: 16px;
        }
        .schedule-times {
            font-size: 14px;
            margin-top: 5px;
            color: #333;
            font-weight: 500;
        }
        .schedule-times span {
            display: block;
        }

        /* WFH */
        .wfh-card {
            background: #bbdefb; /* Light Blue */
            margin: 15px 20px;
            padding: 15px;
            border-radius: var(--border-radius);
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-weight: 600;
            color: #000;
        }
        
        .wfh-checkbox {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 15px;
            padding: 0 20px;
            margin-bottom: 20px;
        }

        .btn-absen {
            flex: 1;
            padding: 15px;
            border-radius: 10px;
            border: none;
            color: #333;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: transform 0.2s;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .btn-absen:active {
            transform: scale(0.98);
        }

        .btn-datang {
            background-color: #b9f6ca; /* Light Green */
            color: #1b5e20;
        }

        .btn-pulang {
            background-color: #ffcdd2; /* Light Red */
            color: #b71c1c;
        }
        
        .btn-icon {
            font-size: 24px;
        }

        /* Presence Info */
        .presence-info {
            text-align: center;
            padding: 0 20px;
            margin-bottom: 20px;
        }
        .presence-title {
            font-size: 16px;
            font-weight: 600;
            color: #666;
            border-bottom: 2px solid #1a237e;
            display: inline-block;
            margin-bottom: 15px;
            padding-bottom: 5px;
            width: 100%;
            text-align: left;
        }

        .presence-placeholder {
            font-weight: 600;
            font-size: 16px;
            color: #333;
            margin: 20px 0;
        }

        /* Map */
        .map-container {
            margin: 0 20px;
            height: 250px;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            background: #eee;
            position: relative;
        }

        #map-placeholder {
           width: 100%;
           height: 100%;
           display: flex;
           align-items: center;
           justify-content: center;
           background-image: url('https://mt1.google.com/vt/lyrs=m&x=130&y=94&z=8'); /* Placeholder bg */
           background-size: cover;
           background-position: center;
        }
    </style>
</head>
<body>

<div class="absen-wrapper">
    <!-- Header -->
    <div class="absen-header">
        <div class="menu-btn"><i class="fas fa-bars"></i></div>
        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/a/ac/Lambang_Kabupaten_Magetan.png/438px-Lambang_Kabupaten_Magetan.png" alt="Logo"> <!-- Placeholder Logo based on image -->
    </div>

    <!-- User Card -->
    <div class="user-card">
        <div class="user-avatar">
            <i class="fas fa-user"></i>
        </div>
        <div class="user-info">
            <div class="user-name"><?php echo esc_html($nama); ?></div>
            <div class="user-meta"><?php echo esc_html($jabatan); ?></div>
            <div class="user-meta"><?php echo esc_html($nip); ?></div>
            <div class="user-skpd"><?php echo esc_html($skpd); ?></div>
        </div>
    </div>

    <!-- Time -->
    <div class="time-display">
        <div class="clock" id="live-clock">00.00</div>
        <div class="date" id="live-date">Sabtu, 10 Januari 2026</div>
    </div>

    <!-- Work Code -->
    <div class="info-card">
        <label for="work-code" style="font-size: 12px; font-weight: 600; color: #1a237e;">Pilih Work Code:</label>
        <div class="work-code-display" style="border: 1px solid #ccc; padding: 10px; border-radius: 5px; margin-top: 5px; font-weight: bold;">
            (GLOBAL) 5 HARI (NON SEKOLAH)
        </div>
        <div style="font-size: 10px; color: #666; margin-top: 5px;">(GLOBAL) 5 HARI (NON SEKOLAH)</div>
    </div>

    <!-- Schedule -->
    <div class="info-card">
        <div class="schedule-title">Sabtu</div>
        <div class="schedule-times">
            <span>Jam Masuk</span>
            <strong>00:00:00 - 00:00:00</strong>
        </div>
        <div class="schedule-times">
            <span>Jam Keluar</span>
            <strong>00:00:00 - 00:00:00</strong>
        </div>
    </div>

    <!-- WFH -->
    <div class="wfh-card">
        <input type="checkbox" id="wfh-check" class="wfh-checkbox">
        <label for="wfh-check">Work From Home</label>
    </div>

    <!-- Buttons -->
    <div class="action-buttons">
        <button class="btn-absen btn-datang" onclick="doAbsen('datang')">
            <i class="fas fa-sign-in-alt btn-icon"></i> 
            <span>Datang</span>
        </button>
        <button class="btn-absen btn-pulang" onclick="doAbsen('pulang')">
            <i class="fas fa-sign-out-alt btn-icon"></i>
            <span>Pulang</span>
        </button>
    </div>

    <!-- Presence Info -->
    <div class="presence-info">
        <div class="presence-title">Presensi Info</div>
        <div class="presence-placeholder">
            DATA Presensi akan ditampilkan disini
        </div>
    </div>

    <!-- Map -->
    <div class="map-container">
        <div id="map-placeholder">
           <!-- <span style="background: rgba(255,255,255,0.8); padding: 5px 10px; border-radius: 5px;">Map Simulator</span> -->
           <iframe width="100%" height="100%" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?q=-7.6298,111.4599&hl=es&z=14&amp;output=embed"></iframe>
        </div>
    </div>
</div>

<script>
    function updateClock() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        document.getElementById('live-clock').textContent = `${hours}.${minutes}`;

        // Date format: Sabtu, 10 Januari 2026
        const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        
        const dayName = days[now.getDay()];
        const dayDate = now.getDate();
        const monthName = months[now.getMonth()];
        const year = now.getFullYear();
        
        document.getElementById('live-date').textContent = `${dayName}, ${dayDate} ${monthName} ${year}`;
    }

    setInterval(updateClock, 1000);
    updateClock();

    function doAbsen(type) {
        alert('Melakukan absen ' + type + '...');
        // Here you would add the AJAX call to the backend
    }
    
    // Simple geolocation check
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                console.log("Location found:", position.coords);
            },
            (error) => {
                console.error("Error getting location:", error);
            }
        );
    }
</script>

</body>
</html>
