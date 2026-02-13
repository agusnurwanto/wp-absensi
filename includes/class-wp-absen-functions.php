<?php
class ABSEN_Functions
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */

	public function __construct($plugin_name, $version){

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

    function allow_access_private_post() {
		if (
			!empty($_GET) 
			&& !empty($_GET['key'])
		) {
			$key = base64_decode($_GET['key']);
			$decode = $this->decode_key($_GET['key']);
			if (!empty($decode['skip'])) {
				return;
			}

			$key_db = md5(get_option( ABSEN_APIKEY ));
			$key = explode($key_db, $key);
			$valid = 0;
			if (
				!empty($key[1]) 
				&& $key[0] == $key[1]
				&& is_numeric($key[1])
			) {
				$tgl1 = new DateTime();
				$date = substr($key[1], 0, strlen($key[1])-3);
				$tgl2 = new DateTime(date('Y-m-d', $date));
				$valid = $tgl2->diff($tgl1)->days+1;
			}
			if ($valid == 1) {
				global $wp_query;

				if (!empty($wp_query->queried_object)) {
					if ($wp_query->queried_object->post_status == 'private') {
						wp_update_post(array(
							'ID'    =>  $wp_query->queried_object->ID,
							'post_status'   =>  'publish'
						));
						die('<script>window.location =  window.location.href;</script>');
					} else {
						wp_update_post(array(
							'ID'    =>  $wp_query->queried_object->ID,
							'post_status'   =>  'private'
						));
					}
				} else if ($wp_query->found_posts >= 1) {
					global $wpdb;
					$sql = $wp_query->request;
					$post = $wpdb->get_results($sql, ARRAY_A);
					if (!empty($post)) {
						if ($post[0]['post_status'] == 'private') {
							wp_update_post(array(
								'ID'    =>  $post[0]['ID'],
								'post_status'   =>  'publish'
							));
							die('<script>window.location =  window.location.href;</script>');
						} else {
							wp_update_post(array(
								'ID'    =>  $post[0]['ID'],
								'post_status'   =>  'private'
							));
						}
					}
				}
			}
		}
    }

	function gen_key($key_db = false, $options = array()) {
		$now = time()*1000;
		if (empty($key_db)) {
			$key_db = md5(get_option( ABSEN_APIKEY ));
		}
		$tambahan_url = '';
		if (!empty($options['custom_url'])) {
			$custom_url = array();
			foreach ($options['custom_url'] as $k => $v) {
				$custom_url[] = $v['key'].'='.$v['value'];
			}
			$tambahan_url = $key_db.implode('&', $custom_url);
		}
		$key = base64_encode($now.$key_db.$now.$tambahan_url);
		return $key;
	}

	public function decode_key($value) {
		$key = base64_decode($value);
		$key_db = md5(get_option( ABSEN_APIKEY ));
		$key = explode($key_db, $key);
		$get = array();
		if (!empty($key[2])) {
			$all_get = explode('&', $key[2]);
			foreach ($all_get as $k => $v) {
				$current_get = explode('=', $v);
				$get[$current_get[0]] = $current_get[1];
			}
		}
		return $get;
	}

	public function get_link_post($custom_post) {
		$link = get_permalink($custom_post);
		$options = array();
		if (!empty($custom_post->custom_url)) {
			$options['custom_url'] = $custom_post->custom_url;
		}
		if (strpos($link, '?') === false) {
			$link .= '?key=' . $this->gen_key(false, $options);
		} else {
			$link .= '&key=' . $this->gen_key(false, $options);
		}
		return $link;
	}

	public function get_page_by_title( $page_title, $output = OBJECT, $post_type = 'page' ) {
		global $wpdb;
		if ( is_array( $post_type ) ) {
			$post_type = esc_sql( $post_type );
			$post_type_in_string = "'" . implode( "','", $post_type ) . "'";
			$sql = $wpdb->prepare("
				SELECT ID
				FROM $wpdb->posts
				WHERE post_title = %s
					AND post_type IN ($post_type_in_string)
			", $page_title);
		} else {
			$sql = $wpdb->prepare("
				SELECT ID
				FROM $wpdb->posts
				WHERE post_title = %s
					AND post_type = %s
			", $page_title, $post_type);
		}
		$page = $wpdb->get_var( $sql );
		if ( $page ) {
			return get_post( $page, $output );
		}
		return null;
	}

	public function generatePage($options = array()) {
		$post_type = 'page';
		$status = 'private';
		if (!empty($options['post_status'])) {
			$status = $options['post_status'];
		}
		if (!empty($options['post_type'])) {
			$post_type = $options['post_type'];
		}

		if (!empty($options['post_id'])) {
			$custom_post = get_post($options['post_id']);
		} else {
			$custom_post = $this->get_page_by_title($options['nama_page'], OBJECT, $post_type);
		}
		$_post = array(
			'post_title'	=> $options['nama_page'],
			'post_content'	=> $options['content'],
			'post_type'		=> $post_type,
			'post_status'	=> $status,
			'comment_status'	=> 'closed'
		);
		if (empty($custom_post) || empty($custom_post->ID)) {
			$id = wp_insert_post($_post);
			$_post['insert'] = 1;
			$_post['ID'] = $id;
			$custom_post = $this->get_page_by_title($options['nama_page'], OBJECT, $post_type);
			if(empty($options['show_header'])){
				update_post_meta($custom_post->ID, 'ast-main-header-display', 'disabled');
				update_post_meta($custom_post->ID, 'footer-sml-layout', 'disabled');
			} else if (empty($options['show_footer'])) {
				update_post_meta($custom_post->ID, 'footer-sml-layout', 'disabled');
			}
			update_post_meta($custom_post->ID, 'ast-breadcrumbs-content', 'disabled');
			update_post_meta($custom_post->ID, 'ast-featured-img', 'disabled');
			update_post_meta($custom_post->ID, 'site-content-layout', 'page-builder');
			update_post_meta($custom_post->ID, 'site-post-title', 'disabled');
			update_post_meta($custom_post->ID, 'site-sidebar-layout', 'no-sidebar');
			update_post_meta($custom_post->ID, 'theme-transparent-header-meta', 'disabled');
		} else if (!empty($options['update'])) {
			if (empty($options['show_header'])) {
				update_post_meta($custom_post->ID, 'ast-main-header-display', 'disabled');
				update_post_meta($custom_post->ID, 'footer-sml-layout', 'disabled');
			} else if (empty($options['show_footer'])) {
				update_post_meta($custom_post->ID, 'footer-sml-layout', 'disabled');
			}
			$_post['ID'] = $custom_post->ID;
			wp_update_post( $_post );
			$_post['update'] = 1;
		}
		if (!empty($options['custom_url'])) {
			$custom_post->custom_url = $options['custom_url'];
		}
		if (!empty($options['no_key'])) {
			$link = get_permalink($custom_post);
		} else {
			$link = $this->get_link_post($custom_post);
		}
		return array(
			'post' => $custom_post,
			'id' => $custom_post->ID,
			'title' => $options['nama_page'],
			'url' => $link
		);
	}

	public function generateRandomString($length = 10) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}

	public function CekNull($number, $length=2) {
        $l = strlen($number);
        $ret = '';
        for ($i=0; $i<$length; $i++) {
            if ($i+1 > $l) {
                $ret .= '0';
            }
        }
        $ret .= $number;
        return $ret;
    }

	function user_has_role($user_id, $role_name, $return = false) {
		if (empty($user_id)) {
			return false;
		}
		$user_meta = get_userdata($user_id);
		$user_roles = $user_meta->roles;
		if ($return) {
			return $user_roles;
		} else {
			return in_array($role_name, $user_roles);
		}
	}

	function get_option_complex($key, $type) {
		global $wpdb;
        $ret = $wpdb->get_results('select option_name, option_value from '.$wpdb->prefix.'options where option_name like \''.$key.'|%\'', ARRAY_A);
        $res = array();
        $types = array();
        foreach ($ret as $v) {
            $k = explode('|', $v['option_name']);
            $column = $k[1];
            $group = $k[3];
            if ($column == '') {
                $types[$group] = $v['option_value'];
            }
        }
        foreach ($ret as $v) {
            $k = explode('|', $v['option_name']);
            $column = $k[1];
            $loop = $k[2];
            $group = $k[3];
            if ($column != '') {
                if (
                    isset($types[$loop])
                    && $type == $types[$loop]
                ) {
                    if (empty($res[$loop])) {
                        $res[$loop] = array();
                    }
                    $res[$loop][$column] = $v['option_value'];
                }
            }
        }
        return $res;
    }

	function get_option_multiselect($key) {
		global $wpdb;
        $ret = $wpdb->get_results('select option_name, option_value from '.$wpdb->prefix.'options where option_name like \''.$key.'|||%\'', ARRAY_A);
        $res = array();
        foreach ($ret as $v) {
            $res[$v['option_value']] = $v['option_value'];
        }
        return $res;
    }

	function isInteger($input) {
		return(ctype_digit(strval($input)));
	}

	function curl_post($options) {
        $curl = curl_init();
        set_time_limit(0);
        $req = http_build_query($options['data']);
        $url = $options['url'];
        if (empty($url)) {
            return false;
        }
        $opsi_curl = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $req,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_CONNECTTIMEOUT => 0,
            CURLOPT_TIMEOUT => 10000
        );

        if (!empty($options['header'])) {
            $opsi_curl[CURLOPT_HTTPHEADER] = $options['header'];
        }

        curl_setopt_array($curl, $opsi_curl);

        $response = curl_exec($curl);
        // die($response);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            $msg = "cURL Error #:".$err." (".$url.")";
            if ($options['debug'] == 1) {
                die($msg);
            } else {
                return $msg;
            }
        } else {
            return $response;
        }
    }

    function send_tg($options) {
		$login = false;
		if (is_user_logged_in()) {
            $current_user = wp_get_current_user();
            if ($this->user_has_role($current_user->ID, 'administrator')) {
                $login = true;
            }
		}
        $bot_tg = get_option('_crb_satset_bot_tg');
        $id_akun_tg = get_option('_crb_satset_akun_tg');
        $ret = array();
        if (!empty($bot_tg) && !empty($id_akun_tg)) {
            $message = $options['message'];
            $id_akun_tg = explode(',', $id_akun_tg);
            foreach ($id_akun_tg as $id_akun) {
                $url = "https://api.telegram.org/$bot_tg/sendMessage?chat_id=$id_akun&text=$message";
                $ret_url = file_get_contents($url);
                if (true == $login) {
                    $ret[] = array(
                        'return'=> $ret_url,
                        'url'=> $url
                    );
                }
            }
        }
        return $ret;
    }

    public static function uploadFile(
		string $api_key = '', 
		string $path = '', 
		array $file = array(),  
		array $ext = array(),
		int $maxSize = 1048576, // default 1MB
		string $nama_file = ''
	) {
		try {
			if (!empty($api_key) && $api_key == get_option( ABSEN_APIKEY )) {
				if (!empty($file)) {

					if (empty($ext)) {
						throw new Exception('Extensi file belum ditentukan');
					}

					if (empty($path)) {
						throw new Exception('Lokasi folder belum ditentukan');
					}

					$imageFileType = strtolower(pathinfo($path.basename($file["name"]), PATHINFO_EXTENSION));
					if (!in_array($imageFileType, $ext)) {
						throw new Exception('Lampiran wajib ber-type ' . implode(", ", $ext));
					}

					if ($file['size'] > $maxSize) {
						throw new Exception('Ukuran file melebihi ukuran maksimal');
					}

					if (!empty($nama_file)) {
						$file['name'] = $nama_file . '.' . $imageFileType;
					} else {
						$nama_file = date('Y-m-d-H-i-s');
						$file['name'] = $nama_file . '-' . $file['name'];
					}
					$target = $path . $file['name'];
					if (move_uploaded_file($file['tmp_name'], $target)) {
						return [
							'status' => true,
							'filename' => $file['name']
						];
					}
					throw new Exception('Oops, gagal upload file, hubungi admin');
				}
				throw new Exception('Oops, file belum dipilih');
			}
			throw new Exception('Api key tidak ditemukan');
		} catch (Exception $e) {
			return array(
				'status' => false,
				'message' => $e->getMessage()
			);
		}
	}

	/**
	 * Check if user needs to change password on login and redirect if needed
	 */
	public function check_force_password_change() {
		if (!is_user_logged_in()) {
			return;
		}

		$user_id = get_current_user_id();
		$force_change = get_user_meta($user_id, 'absen_force_password_change', true);

		if ($force_change == 1) {
			// Get password change page URL
			$change_password_url = home_url('/ubah-password-absen/');

			// Don't redirect if already on the password change page or admin-ajax
			$current_url = $_SERVER['REQUEST_URI'];
			if (
				strpos($current_url, 'ubah-password-absen') === false &&
				strpos($current_url, 'admin-ajax.php') === false &&
				strpos($current_url, 'wp-login.php') === false &&
				strpos($current_url, 'wp-admin') === false
			) {
				wp_redirect($change_password_url);
				exit;
			}
		}
	}

	/**
	 * AJAX handler for changing password
	 */
	public function ajax_change_password() {
		$ret = array(
			'status' => 'success',
			'message' => 'Password berhasil diubah!',
			'data' => array()
		);

		if (!is_user_logged_in()) {
			$ret['status'] = 'error';
			$ret['message'] = 'Anda harus login terlebih dahulu!';
			die(json_encode($ret));
		}

		if (!empty($_POST)) {
			if (!empty($_POST['api_key']) && $_POST['api_key'] == get_option(ABSEN_APIKEY)) {
				$new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
				$confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

				if (empty($new_password)) {
					$ret['status'] = 'error';
					$ret['message'] = 'Password baru tidak boleh kosong!';
				} elseif (strlen($new_password) < 6) {
					$ret['status'] = 'error';
					$ret['message'] = 'Password minimal 6 karakter!';
				} elseif ($new_password !== $confirm_password) {
					$ret['status'] = 'error';
					$ret['message'] = 'Konfirmasi password tidak cocok!';
				} else {
					$user_id = get_current_user_id();

					// ambil prefix dari setting
					$prefix = carbon_get_theme_option('crb_default_password_prefix');

					// tambahkan prefix ke password baru
					$final_password = $prefix . $final_password;

					// Update password
					wp_set_password($final_password, $user_id);

					// Remove the force change flag
					delete_user_meta($user_id, 'absen_force_password_change');

					// Force re-login by destroying session
					wp_destroy_current_session();
					wp_clear_auth_cookie();

					$ret['message'] = 'Password berhasil diubah! Silakan login kembali dengan password baru.';
					$ret['redirect'] = wp_login_url();
				}
			} else {
				$ret['status'] = 'error';
				$ret['message'] = 'Api key tidak ditemukan!';
			}
		} else {
			$ret['status'] = 'error';
			$ret['message'] = 'Format salah!';
		}

		die(json_encode($ret));
	}

	/**
	 * Shortcode for password change form
	 */
	public function shortcode_ubah_password($atts) {
		if (!is_user_logged_in()) {
			return '<div class="alert alert-warning">Anda harus login terlebih dahulu.</div>';
		}

		$user_id = get_current_user_id();
		$force_change = get_user_meta($user_id, 'absen_force_password_change', true);
		$current_user = wp_get_current_user();

		ob_start();
		?>
		<div class="container" style="max-width: 500px; margin: 50px auto;">
			<input type="hidden" value="<?php echo get_option(ABSEN_APIKEY); ?>" id="api_key" />

			<div class="card">
				<div class="card-header">
					<h4 class="mb-0">Ubah Password</h4>
				</div>
				<div class="card-body">
					<?php if ($force_change == 1): ?>
					<div class="alert alert-info">
						<strong>Perhatian!</strong> Ini adalah login pertama Anda. Silakan ubah password untuk keamanan akun.
					</div>
					<?php endif; ?>

					<div class="form-group">
						<label>Username</label>
						<input type="text" class="form-control" value="<?php echo esc_attr($current_user->user_login); ?>" disabled />
					</div>

					<div class="form-group">
						<label for="new_password">Password Baru <span class="text-danger">*</span></label>
						<input type="password" id="new_password" name="new_password" class="form-control" placeholder="Minimal 6 karakter" required />
					</div>

					<div class="form-group">
						<label for="confirm_password">Konfirmasi Password <span class="text-danger">*</span></label>
						<input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Ulangi password baru" required />
					</div>

					<button type="button" class="btn btn-primary btn-block" onclick="submitChangePassword()">
						Simpan Password Baru
					</button>
				</div>
			</div>
		</div>

		<script>
		function submitChangePassword() {
			let new_password = jQuery('#new_password').val();
			let confirm_password = jQuery('#confirm_password').val();

			if (new_password == '') {
				return alert('Password baru tidak boleh kosong!');
			}
			if (new_password.length < 6) {
				return alert('Password minimal 6 karakter!');
			}
			if (new_password != confirm_password) {
				return alert('Konfirmasi password tidak cocok!');
			}

			jQuery.ajax({
				method: 'post',
				url: '<?php echo admin_url('admin-ajax.php'); ?>',
				dataType: 'json',
				data: {
					'action': 'absen_change_password',
					'api_key': jQuery('#api_key').val(),
					'new_password': new_password,
					'confirm_password': confirm_password
				},
				success: (res) => {
					alert(res.message);
					if (res.status == 'success' && res.redirect) {
						window.location.href = res.redirect;
					}
				},
				error: () => {
					alert('Terjadi kesalahan. Silakan coba lagi.');
				}
			});
		}
		</script>
		<?php
		return ob_get_clean();
	}
}