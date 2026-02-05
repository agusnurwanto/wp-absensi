jQuery(document).ready(() => {
    window.options_skpd = {};
    let loading = ''
        + '<div id="wrap-loading">'
        + '<div class="lds-hourglass"></div>'
        + '<div id="persen-loading"></div>'
        + '</div>';

    if (jQuery('#wrap-loading').length == 0) {
        jQuery('body').prepend(loading);
    }

    // PWA Checkbox Listener
    jQuery('body').on('change', 'input[name="carbon_fields_compact_input[_crb_enable_pwa]"]', function (e) {
        e.preventDefault();

        let $checkbox = jQuery(this);
        let isChecked = $checkbox.is(':checked');
        let status = isChecked ? 'mengaktifkan' : 'menonaktifkan';

        if (confirm('Apakah anda yakin ingin ' + status + ' fitur PWA?')) {
            jQuery('#wrap-loading').show();

            // Disable checkbox while processing
            $checkbox.prop('disabled', true);

            jQuery.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'manage_pwa_files',
                    enable: isChecked
                },
                success: function (response) {
                    jQuery('#wrap-loading').hide();
                    $checkbox.prop('disabled', false);

                    if (response.success) {
                        alert(response.data.message);
                    } else {
                        alert('Error: ' + response.data.message);
                        // Revert checkbox state on error
                        $checkbox.prop('checked', !isChecked);
                    }
                },
                error: function (xhr, status, error) {
                    jQuery('#wrap-loading').hide();
                    $checkbox.prop('disabled', false);
                    alert('Terjadi kesalahan koneksi');
                    // Revert checkbox state on error
                    $checkbox.prop('checked', !isChecked);
                }
            });
        } else {
            // Revert if cancelled
            $checkbox.prop('checked', !isChecked);
        }
    });
});

function filePickedAbsen(oEvent) {
    jQuery('#wrap-loading').show();
    // Get The File From The Input
    let oFile = oEvent.target.files[0];
    let sFilename = oFile.name;
    // Create A File Reader HTML5
    let reader = new FileReader();

    reader.onload = (e) => {
        let data = e.target.result;
        let workbook = XLSX.read(data, {
            type: 'binary'
        });

        let cek_sheet_name = false;
        workbook.SheetNames.forEach((sheetName) => {
            console.log('sheetName', sheetName);
            if (sheetName == 'data') {
                cek_sheet_name = true;
                let XL_row_object = XLSX.utils.sheet_to_row_object_array(workbook.Sheets[sheetName]);
                let data = [];

                XL_row_object.map((b, i) => {
                    for (ii in b) {
                        b[ii] = b[ii].replace(/(\r\n|\n|\r)/g, " ").trim();
                    }
                    data.push(b);
                });

                let json_object = JSON.stringify(data);
                jQuery('#data-excel').val(json_object);
                jQuery('#wrap-loading').hide();
            }
        });
        setTimeout(() => {
            if (false == cek_sheet_name) {
                jQuery('#data-excel').val('');
                alert('Sheet dengan nama "data" tidak ditemukan!');
                jQuery('#wrap-loading').hide();
            }
        }, 2000);
    };

    reader.onerror = (ex) => {
        console.log(ex);
    };

    reader.readAsBinaryString(oFile);
}

function relayAjax(options, retries = 20, delay = 5000, timeout = 9000000) {
    options.timeout = timeout;
    options.cache = false;
    jQuery.ajax(options)
        .fail((jqXHR, exception) => {
            // console.log('jqXHR, exception', jqXHR, exception);
            if (
                jqXHR.status != '0'
                && jqXHR.status != '503'
                && jqXHR.status != '500'
            ) {
                if (jqXHR.responseJSON) {
                    options.success(jqXHR.responseJSON);
                } else {
                    options.success(jqXHR.responseText);
                }
            } else if (retries > 0) {
                console.log('Koneksi error. Coba lagi ' + retries, options);
                let new_delay = Math.random() * (delay / 1000);
                setTimeout(() => {
                    relayAjax(options, --retries, delay, timeout);
                }, new_delay * 1000);
            } else {
                alert('Capek. Sudah dicoba berkali-kali error terus. Maaf, berhenti mencoba.');
            }
        });
}

function import_excel_absen_pegawai() {
    let data = jQuery('#data-excel').val();
    if (!data) {
        return alert('Excel Data can not empty!');
    } else {
        data = JSON.parse(data);
        jQuery('#wrap-loading').show();

        let data_all = [];
        let data_sementara = [];
        let max = 100;

        data.map((b, i) => {
            data_sementara.push(b);
            if (data_sementara.length % max == 0) {
                data_all.push(data_sementara);
                data_sementara = [];
            }
        });

        if (data_sementara.length > 0) {
            data_all.push(data_sementara);
        }

        let last = data_all.length - 1;
        data_all.reduce((sequence, nextData) => {
            return sequence.then((current_data) => {
                return new Promise((resolve_reduce, reject_reduce) => {
                    relayAjax({
                        url: ajaxurl,
                        type: 'post',
                        data: {
                            action: 'import_excel_absen_pegawai',
                            data: current_data
                        },
                        success: (res) => {
                            resolve_reduce(nextData);
                        },
                        error: (e) => {
                            console.log('Error import excel', e);
                        }
                    });
                })
                    .catch((e) => {
                        console.log(e);
                        return Promise.resolve(nextData);
                    });
            })
                .catch((e) => {
                    console.log(e);
                    return Promise.resolve(nextData);
                });
        }, Promise.resolve(data_all[last]))
            .then((data_last) => {
                jQuery('#wrap-loading').hide();
                alert('Success import data pegawai dari excel!');
            })
            .catch((e) => {
                console.log(e);
                jQuery('#wrap-loading').hide();
                alert('Error!');
            });
    }
}

function sql_migrate_absen() {
    jQuery("#wrap-loading").show();
    jQuery.ajax({
        url: ajaxurl,
        type: "POST",
        data: {
            action: "sql_migrate_absen",
        },
        dataType: "json",
        success: (data) => {
            jQuery("#wrap-loading").hide();
            return alert(data.message);
        },
        error: (e) => {
            console.log(e);
            return alert(data.message);
        },
    });
}

function generate_user_absen() {
    if (confirm("Apakah anda yakin untuk menggenerate user dari tabel data_pegawai!")) {
        jQuery('#wrap-loading').show();
        relayAjax({
            url: ajaxurl,
            type: "post",
            data: {
                "action": "generate_user_absen",
                "pass": prompt('Masukan password default untuk User yang akan dibuat')
            },
            dataType: "json",
            success: (data) => {
                jQuery('#wrap-loading').hide();
                return alert(data.message);
            },
            error: (e) => {
                console.log(e);
                return alert(data.message);
            }
        });
    }
}

function get_data_unit_wpsipd() {
    jQuery("#wrap-loading").show();
    jQuery.ajax({
        url: ajaxurl,
        type: "post",
        dataType: "json",
        data: {
            action: "get_data_unit_wpsipd",
            server: jQuery(
                'input[name="carbon_fields_compact_input[_crb_url_server_wpsipd]"]'
            ).val(),
            api_key: jQuery(
                'input[name="carbon_fields_compact_input[_crb_apikey_wpsipd]"]'
            ).val(),
            tahun_anggaran: jQuery(
                'input[name="carbon_fields_compact_input[_crb_tahun_wpsipd]"]'
            ).val(),
        },
        success: (data) => {
            jQuery("#wrap-loading").hide();
            console.log(data.message);

            if (data.status == "success") {
                alert("Data berhasil disinkron");
            } else {
                alert(data.message);
            }
        },
        error: (e) => {
            console.log(e);
            return alert(e);
        },
    });
}