<?php

// no direct access!
defined('ABSPATH') or die("No direct access");

class GSpeech_Admin {

	public static function admin_init() {

		// admin init
	}

    public static function admin_menu() {

		$icon_url = plugins_url( '/images/g_logo_small.png' , __FILE__ );

		$page = add_menu_page(__('GSpeech Plugin Options', 'gspeech'), 'GSpeech', 'manage_options', 'gspeech', array('GSpeech_Admin', 'render_admin'), $icon_url);

		// create submenus
		$page1 = add_submenu_page('gspeech', 'GSpeech - Dashboard', 'Dashboard', 'manage_options', 'gspeech', array('GSpeech_Admin', 'render_admin'));
		$page1 = add_submenu_page('gspeech', 'GSpeech - Cloud Console', 'Cloud Console', 'manage_options', 'gspeech_cloud_console', array('GSpeech_Admin', 'render_admin'));
		$page2 = add_submenu_page('gspeech', 'GSpeech - 2.X', 'GSpeech 2.X', 'manage_options', 'gspeech_2x', array('GSpeech_Admin', 'render_admin'));
		$page3 = add_submenu_page('gspeech', 'GSpeech - FAQ', 'FAQ', 'manage_options', 'gspeech_faq', array('GSpeech_Admin', 'render_admin'));
		$page4 = add_submenu_page('gspeech', 'GSpeech - Contact Us', 'Contact Us', 'manage_options', 'gspeech_contact_us', array('GSpeech_Admin', 'render_admin'));
		$page5 = add_submenu_page('gspeech', 'GSpeech - Upgrade', 'Upgrade  ➤', 'manage_options', 'gspeech_upgrade', array('GSpeech_Admin', 'render_admin'));

		add_action('admin_print_scripts-' . $page, array('GSpeech_Admin', 'load_admin_scripts'));
		add_action('admin_print_scripts-' . $page1, array('GSpeech_Admin', 'load_admin_scripts'));
		add_action('admin_print_scripts-' . $page2, array('GSpeech_Admin', 'load_admin_scripts'));
		add_action('admin_print_scripts-' . $page3, array('GSpeech_Admin', 'load_admin_scripts'));
		add_action('admin_print_scripts-' . $page4, array('GSpeech_Admin', 'load_admin_scripts'));
		add_action('admin_print_scripts-' . $page5, array('GSpeech_Admin', 'load_admin_scripts'));
	}

	public static function admin_settings() {
		
		// creates our settings in the options table
		register_setting('wpgs_settings_group', 'wpgs_settings');
	}

	public static function load_admin_scripts() {

		$plugin_version = GSPEECH_PLG_VERSION;

		wp_enqueue_style('wpgs-admin-styles-1', plugin_dir_url( __FILE__ ) . 'css/ui-lightness/jquery-ui-1.10.1.custom.css', false, $plugin_version);
		wp_enqueue_style('wpgs-admin-styles-2', plugin_dir_url( __FILE__ ) . 'css/admin.css', false, $plugin_version);
		wp_enqueue_style('wpgs-admin-styles-3', plugin_dir_url( __FILE__ ) . 'css/colorpicker.css', false, $plugin_version);
		wp_enqueue_style('wpgs-admin-styles-4', plugin_dir_url( __FILE__ ) . 'css/layout.css', false, $plugin_version);
		wp_enqueue_style('wpgs-admin-styles-5', plugin_dir_url( __FILE__ ) . 'css/the-tooltip.css', false, $plugin_version);
		
		wp_enqueue_script('wpgs-admin-script-1', plugin_dir_url( __FILE__ ) . 'js/colorpicker.js', array('jquery'), $plugin_version);
		wp_enqueue_script('wpgs-admin-script-2', plugin_dir_url( __FILE__ ) . 'js/eye.js', array('jquery'), $plugin_version);
		wp_enqueue_script('wpgs-admin-script-3', plugin_dir_url( __FILE__ ) . 'js/utils.js', array('jquery'), $plugin_version);
		wp_enqueue_script('wpgs-admin-script-4', plugin_dir_url( __FILE__ ) . 'js/highstock.js', array('jquery'), $plugin_version);
		wp_enqueue_script('wpgs-admin-script-5', plugin_dir_url( __FILE__ ) . 'js/admin.js', array('jquery','jquery-ui-core','jquery-ui-accordion','jquery-ui-tabs','jquery-ui-slider'), $plugin_version);

		$ajax_nonce = wp_create_nonce("wpgsp_ajax_nonce_value_1");
        wp_localize_script(
            'wpgs-admin-script-5',
            'wpgsp_ajax_obj_1',
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => $ajax_nonce,
            )
        );
	}

	public static function plugin_action_links( $links ) {

		$settings_link = '<a href="' . admin_url('admin.php?page=gspeech') . '">'.__('Settings', 'gspeech').'</a>';
		$gopro_link = sprintf( '<a href="%1$s" target="_blank" class="gspeech-plugin-gopro">%2$s</a>', 'https://gspeech.io/#pricing', esc_html__( 'Upgrade', 'gspeech' ) );

		$links['set'] = $settings_link;
		$links['go_pro'] = $gopro_link;

		return $links;
	}

	public static function wpgsp_apply_feedback() {

		header('Content-Type: text/plain');

		check_ajax_referer('wpgsp_ajax_nonce_value');

		$plugin_version = GSPEECH_PLG_VERSION;

		$sel_v = isset($_POST['sel_v']) ? $_POST['sel_v'] : '';
		$sel_d = isset($_POST['sel_d']) ? $_POST['sel_d'] : '';

		$domain = get_site_url();
		$m_ = get_option('admin_email','');
		$n_ = get_option('blogname','');

		$str = 'domain=' . $domain . '&email=' . $m_  . '&name=' . $n_  . '&version=' . $plugin_version . '&sel_v=' . $sel_v . '&sel_d=' . $sel_d;
		$d_ = base64_encode($str);

		$context = stream_context_create(array('ssl'=>array('verify_peer' => false)));
		
		$fh = @fopen('https://gspeech.io/apply-feedback/'.$d_, 'r', false, $context);
		if($fh !== false)
			@fclose($fh);

		echo '{}';

		exit;
	}

	public static function wpgsp_apply_ajax_save() {

		header('Content-Type: text/plain');
	
		// nonce
		check_ajax_referer('wpgsp_ajax_nonce_value_1');
	
		// дополнительная проверка прав (рекомендуется)
		if ( ! current_user_can('manage_options') ) {
			status_header(403);
			echo '{"e":"forbidden"}';
			exit;
		}
	
		$plugin_version = GSPEECH_PLG_VERSION;
	
		global $wpdb;
	
		$type = isset($_POST['type']) ? (string) $_POST['type'] : '';
	
		// table name
		// $table = $wpdb->prefix . 'gspeech_data'; keep it safe 2350
		$table = $wpdb->prefix . 'gspeech_data';
	
		// Получаем реальные колонки таблицы — allow-list
		$cols = $wpdb->get_col( "SHOW COLUMNS FROM `{$table}`" );
		if ( empty($cols) || !is_array($cols) ) {
			status_header(500);
			echo '{"e":"schema"}';
			exit;
		}
		$col_set = array_fill_keys($cols, true);
	
		if ($type == 'save_data') {
			$field = isset($_POST['field']) ? wp_unslash($_POST['field']) : '';
			$val   = isset($_POST['val'])   ? wp_unslash($_POST['val'])   : '';
	
			if ($field === '' || $val === '') {
				status_header(400);
				echo '{"e":"args"}';
				exit;
			}
	
			// поддерживаем старый формат: несколько полей через "," и значения через ":"
			$fields = array_map('trim', explode(',', $field));
			// нормализуем имена полей — используем sanitize_key (строгое имя)
			$fields = array_map(function($f){ return sanitize_key((string)$f); }, $fields);
			// убираем пустые
			$fields = array_values(array_filter($fields, static function($x){ return $x !== ''; }));
	
			if ( empty($fields) ) {
				status_header(400);
				echo '{"e":"no_fields"}';
				exit;
			}
	
			// проверяем имена колонок против реальной схемы
			foreach ($fields as $f) {
				if (!isset($col_set[$f])) {
					status_header(400);
					echo '{"e":"bad_field","field":"'.$f.'"}';
					exit;
				}
			}
	
			if (count($fields) > 1) {
	
				// значения
				$vals = explode(':', (string)$val);
				// очищаем значения
				$vals_clean = array_map(function($v){ return sanitize_text_field(wp_unslash($v)); }, $vals);
	
				// длины должны совпадать
				if (count($vals_clean) !== count($fields)) {
					status_header(400);
					echo '{"e":"len_mismatch"}';
					exit;
				}
	
				// построим SET части с проверенными идентификаторами колонок
				$assign = [];
				foreach ($fields as $f) {
					$assign[] = "`{$f}` = %s";
				}
				$sql = "UPDATE `{$table}` SET " . implode(', ', $assign);
	
				// prepare: распаковываем значения корректно
				$prepare_args = array_merge([$sql], $vals_clean);
				$prepared = call_user_func_array([$wpdb, 'prepare'], $prepare_args);
				$wpdb->query($prepared);
	
				// обновляем опции (старое поведение)
				foreach ($fields as $i => $f) {
					update_option('gspeech_' . $f, $vals_clean[$i]);
				}
	
			} else {
				// single-field
				$f = $fields[0];
				$v = sanitize_text_field($val);
	
				// $f уже проверен на существование в $col_set
				$sql = "UPDATE `{$table}` SET `{$f}` = %s";
				$prepared = $wpdb->prepare($sql, $v);
				$wpdb->query($prepared);
	
				update_option('gspeech_' . $f, $v);
			}
	
			// очищаем кэши (как раньше)
			delete_transient('gspeech_settings_cache');
			delete_transient('gsp_crypto_cache');
	
		} else if ($type == 'increase_index') {
	
			// убедимся, что колонка есть
			if (!isset($col_set['version_index'])) {
				status_header(500);
				echo '{"e":"schema"}';
				exit;
			}
	
			// безопасный инкремент (нет внешних данных)
			$wpdb->query("UPDATE `{$table}` SET `version_index` = `version_index` + 1");
	
			$version_index = get_option('gspeech_version_index', 0);
			update_option('gspeech_version_index', $version_index + 1);
	
			delete_transient('gspeech_settings_cache');
			delete_transient('gsp_crypto_cache');
		} else {
			// неизвестный тип — просто возвращаем версию, как раньше (или можно вернуть ошибку)
			// echo '{"v":"'.$plugin_version.'"}';
			// exit;
		}
	
		// success
		echo '{"v":"'.$plugin_version.'"}';
		exit;
	}
	

	public static function wpgsp_validate_enc_data() {
		
	    check_ajax_referer('wpgsp_ajax_nonce_value_1');

	    $crypto_settings = [
	        'crypto' => get_option('gspeech_crypto', ''),
	        'reload_session' => intval(get_option('gspeech_reload_session', 0)),
	    ];

	    $gsp_crypto = $crypto_settings['crypto'];
	    $gsp_reload_session = $crypto_settings['reload_session'];

	    $s_enc = "";
	    $h_enc = "";
	    $hh_enc = "";

	    if (!empty($gsp_crypto) && is_string($gsp_crypto) && function_exists('sodium_crypto_box_seal')) {
	        try {
	            $gsp_crypto_pk = hex2bin($gsp_crypto);
	            $magic_str = "Simon you are great!";
	            $h_enc = bin2hex(random_bytes(32));
	            $s_enc = sodium_crypto_box_seal($magic_str, $gsp_crypto_pk);
	            $s_enc = bin2hex($s_enc);
	            $hh_enc = sodium_crypto_box_seal($h_enc, $gsp_crypto_pk);
	            $hh_enc = bin2hex($hh_enc);
	        } catch (Exception $e) {
	            error_log('GSpeech encryption error: ' . $e->getMessage());
	            wp_send_json_error(['message' => 'Encryption error']);
	        }
	    }

	    wp_send_json_success([
	        's_enc' => $s_enc,
	        'h_enc' => $h_enc,
	        'hh_enc' => $hh_enc
	    ]);
	}

    public static function render_admin() {

		global $wpdb;

	    // 3.9.0
	    $categories = get_categories();
	    $list_cat = array();
	    foreach ($categories as $k => $cat) {
	        $list_cat[] = [$cat->name, $cat->slug];
	    } 

		$query = "SELECT DISTINCT(`post_type`) FROM `".$wpdb->prefix."posts`";
	    $rows = $wpdb->get_results($query);

	    $post_types = array('page', 'post', 'attachment');
	    $blocked_types = array('page', 'post', 'attachment', 'revision', 'wp_global_styles', 'wp_navigation');
	    foreach ($rows as $k => $row) {
	        $type = $row->post_type;
	        if (!in_array($type, $blocked_types)) {
	            $post_types[] = $type;
	        }
	    }

	    $wpgs_load_sh = intval(get_option('gspeech_sh_w_loaded', 0));
	    $sh_ = intval(get_option('gspeech_sh_', 0));
	    $plan = intval(get_option('gspeech_plan', 0));
	    $appsumo = intval(get_option('gspeech_appsumo', 0));

	    $gsp_page = isset($_GET['page']) ? $_GET['page'] : '';

	    $data = get_option('wpgs_settings');
	    GSpeech::load_defaults($data);

		$wpgs_options = GSpeech::load_settings($data);

		$tab_ident_v = 'gspeech';
		if($gsp_page == 'gspeech_upgrade')
			$tab_ident_v = 'upgrade';
		else if($gsp_page == 'gspeech_cloud_console')
			$tab_ident_v = 'cloud';
		else if($gsp_page == 'gspeech_2x')
			$tab_ident_v = 'gspeech_2x';
		else if($gsp_page == 'gspeech_faq')
			$tab_ident_v = 'faq';
		
		$tooltips = array("apple-green" => "Apple Green","apricot" => "Apricot","black" => "Black","bright-lavender" => "Bright Lavender","carrot-orange" => "Carrot Orange","dark-midnight-blue" => "Dark Midnight Blue","eggplant" => "Eggplant","forest-green" => "Forest Green","magic-mint" => "Magic Mint","mustard" => "Mustard","sienna" => "Sienna","sky-blue" => "Sky Blue");
		$languages = array('af' => 'Afrikaans','sq' => 'Albanian','ar' => 'Arabic','eu' => 'Basque','be' => 'Belarusian','bg' => 'Bulgarian','zh-CN' => 'Chinese (Simplified)','zh-TW' => 'Chinese (Traditional)','hr' => 'Croatian','cs' => 'Czech','da' => 'Danish','nl' => 'Dutch','en' => 'English','et' => 'Estonian','tl' => 'Filipino','fi' => 'Finnish','fr' => 'French','gl' => 'Galician','ka' => 'Georgian','de' => 'German','el' => 'Greek','ht' => 'Haitian Creole','iw' => 'Hebrew','hi' => 'Hindi','hu' => 'Hungarian','is' => 'Icelandic','id' => 'Indonesian','it' => 'Italian','ja' => 'Japanese','ko' => 'Korean','lv' => 'Latvian','lt' => 'Lithuanian','mk' => 'Macedonian','ms' => 'Malay','mt' => 'Maltese','no' => 'Norwegian','fa' => 'Persian','pl' => 'Polish','pt' => 'Portuguese','ro' => 'Romanian','ru' => 'Russian','sr' => 'Serbian','sk' => 'Slovak','sl' => 'Slovenian','es' => 'Spanish','sw' => 'Swahili','sv' => 'Swedish','th' => 'Thai','uk' => 'Ukrainian','ur' => 'Urdu','vi' => 'Vietnamese','cy' => 'Welsh','yi' => 'Afrikaans','yi' => 'Yiddish');

		ob_start(); ?>
		<form method="post" action="options.php" id="gsp_form" class="submit_disabled">

			<div id="gsp_old_block"  class="wrap" style="overflow: hidden;margin-bottom: 10px; display: none;">
				<?php settings_fields('wpgs_settings_group'); ?>
			</div>

			<div class="gsp_dashboard_wrapper" >

				<div class="gsp_dash_title"><img width="64px" src="<?php echo plugin_dir_url( __FILE__ ); ?>/images/g_logo.png" /><span>GSpeech</span><span class="gsp_v_i">(Version: <?php echo GSPEECH_PLG_VERSION;?>)</span></div>

				<div id="gsp_data">
					<?php
			        	$current_user = wp_get_current_user();
			        	
			        	$username =  $current_user->user_login;
			        	$useremail =  $current_user->user_email;
			        	$realname = $current_user->display_name;
			        	$userid = get_current_user_id();

			        	$sitename = get_bloginfo('name');

			        	$old_plugin_lang = isset($wpgs_options['language']) ? $wpgs_options['language'] : '';
			        	$old_plugin_speak_any_text = isset($wpgs_options['speak_any_text']) ? $wpgs_options['speak_any_text'] : 0;

			        	// get gspeech data
						$sql_g = "SELECT * FROM ".$wpdb->prefix."gspeech_data";
						$row_g = $wpdb->get_row($sql_g);
						$widget_id = esc_html($row_g->widget_id);
						$email_us = esc_html($row_g->email);

						$email_127 = $email_us == '' ? $useremail : $email_us;
					?>
					<div id="gsp_site_name"><?php echo $sitename; ?></div>
					<div id="gsp_username"><?php echo $username; ?></div>
					<div id="gsp_realname"><?php echo $realname; ?></div>
					<div id="gsp_useremail"><?php echo $email_127; ?></div>
					<div id="gsp_useremail_written"><?php echo $email_us; ?></div>
					<div id="gsp_userid"><?php echo $userid; ?></div>
					<div id="gsp_old_p_lang"><?php echo $old_plugin_lang; ?></div>
					<div id="gsp_old_p_speak_any_text"><?php echo $old_plugin_speak_any_text; ?></div>
					<div id="gsp_widget_id_val"><?php echo $widget_id; ?></div>
					<div id="gsp_load_shortcode_widgets"><?php echo $wpgs_load_sh; ?></div>
					<div id="gsp_sh_"><?php echo $sh_; ?></div>
					<div id="gsp_plan"><?php echo $plan; ?></div>
					<div id="gsp_appsumo"><?php echo $appsumo; ?></div>
					<div id="gsp_version"><?php echo GSPEECH_PLG_VERSION; ?></div>
				</div>

				<?php
					$dash_url = esc_url(add_query_arg(array('page' => "gspeech")));
					$gspeech_2x_url = esc_url(add_query_arg(array('page' => "gspeech_2x")));
					$console_url = esc_url(add_query_arg(array('page' => "gspeech_cloud_console")));
				?>

				<div id="gsp_tabs_wrapper" data-active_tab="<?php echo $tab_ident_v; ?>">
					<div data-tab_ident="video_demo" data-menu_ident="gspeech" class="gsp_tab gsp_tab_video_demo gsp_tab_selected gsp_naviagte_item menu_ident_gspeech"><div class="ss_top_menu_icon"></div><span>Dashboard</span></div>
					<div data-tab_ident="website_settings" data-menu_ident="gspeech_cloud_console" class="gsp_tab gsp_tab_website_settings gsp_hidden gsp_naviagte_item menu_ident_gspeech_cloud_console"><div class="ss_top_menu_icon"></div><span>Cloud Console</span></div>
					<div data-tab_ident="sign_up" data-menu_ident="gspeech_cloud_console" class="gsp_tab gsp_tab_sign_up gsp_naviagte_item menu_ident_gspeech_cloud_console"><div class="ss_top_menu_icon"></div><span>Activate</span></div>
					<div data-tab_ident="sign_in" data-menu_ident="gspeech_cloud_console" class="gsp_tab gsp_tab_sign_in gsp_naviagte_item menu_ident_gspeech_cloud_console"><div class="ss_top_menu_icon"></div><span>Login</span></div>
					<div data-tab_ident="sign_out" class="gsp_tab gsp_tab_sign_out gsp_hidden"><div class="ss_top_menu_icon"></div><span>Logout</span></div>
					<div data-tab_ident="add_website" class="gsp_tab gsp_tab_add_website gsp_hidden"><div class="ss_top_menu_icon"></div><span>Activate</span></div>
					<div data-tab_ident="faq" data-menu_ident="gspeech_faq" class="gsp_tab gsp_tab_faq gsp_naviagte_item menu_ident_gspeech_faq"><div class="ss_top_menu_icon"></div><span>FAQ</span></div>
					<div data-tab_ident="upgrade" data-menu_ident="gspeech_upgrade" class="gsp_tab gsp_tab_upgrade gsp_naviagte_item menu_ident_gspeech_upgrade"><div class="ss_top_menu_icon"></div><span>Upgrade</span></div>
					<a data-tab_ident="contact_us" class="gsp_tab gsp_tab_link gsp_tab_contact_us" href="https://gspeech.io/contact-us" target="_blank"><div class="ss_top_menu_icon"></div><span>Contact Us</span></a>
					<a data-tab_ident="rate_us" class="gsp_tab gsp_tab_link gsp_tab_rate_us" href="https://wordpress.org/plugins/gspeech/#reviews" target="_blank"><div class="ss_top_menu_icon"></div><span>Rate Us</span></a>
					<div data-tab_ident="old_basic" data-menu_ident="gspeech_2x" class="gsp_tab gsp_tab_old_basic gsp_naviagte_item menu_ident_gspeech_2x"><div class="ss_top_menu_icon"></div><span>GSpeech 2.X</span></div>
					<div data-tab_ident="old_styles" class="gsp_tab gsp_tab_old_styles gsp_hidden"><div class="ss_top_menu_icon"></div><span>Styles</span></div>

					<div class="ss_upgrade_info_top">
						<div class="ss_locked_icon"><img src="<?php echo plugin_dir_url( __FILE__ ); ?>/images/svg/lock.svg" /></div>
						<a class="gsp_activate_upgrade" href="https://gspeech.io/#pricing" target="_blank">Upgrade</a><span>to Activate Locked Features</span>
					</div>
				</div>

				<div class="gsp_tab_c gsp_tab_c_sign_in " style="display: none">

					<div class="gsp_login_wrapper gsp_login_wrapper_element">
						<div class="gsp_login_title">Welcome back!</div>
						<div class="gsp_login_subtitle">Enter your credentials to login</div>
						<input type="text" class="gsp_login_input gsp_login_email_uni" id="gsp_login_email" placeholder="Email" />
						<input type="password" class="gsp_login_input gsp_login_password_uni" id="" placeholder="Password" />
						<input type="text" class="gsp_login_input gsp_login_custom_widget gsp_hidden" id="" placeholder="Custom Widget" />
						<div class="gsp_login_button gsp_login_button_uni" id="">Login</div>
						<div class="gsp_input_forgot_wrapper"><a href="https://gspeech.io/forgot" target="_blank" class="gsp_forgot_link">Forgot Password</a></div>
						<div class="gsp_input_cw_wrapper"><span class="gsp_input_cw_val">Input custom widget</span></div>
					</div>
					
				</div>

				<div class="gsp_tab_c gsp_tab_c_video_demo gsp_tab_active">

					<?php include('tab_videos.php'); ?>
					
				</div>

				<div class="gsp_tab_c gsp_tab_c_sign_up" style="display: none">

					<div class="gsp_login_wrapper gsp_register_wrapper">
						<div class="gsp_login_title">Activate Cloud Console</div>
						<div class="gsp_login_subtitle">Just a single click is required :)</div>
						<input type="text" class="gsp_input" id="gsp_reg_name" placeholder="Name" />
						<input type="text" class="gsp_input" id="gsp_reg_email" placeholder="Email" />
						<input type="password" class="gsp_input" id="gsp_reg_password" placeholder="Password" />
						<input type="password" class="gsp_input" id="gsp_reg_password_retype" placeholder="Retype Password" />
						<div class="gsp_input_holder">
							<div id="reg_website_lang" data-val="" class="items_select_filter_wrapper" data-def_txt="Select language (for voices)">
								<div class="items_select_filter">
									<div class="items_select_filter_content">
										<span>Select language (for voices)</span>
										<input type="text" class="li_search_input" />
									</div>
									<div class="items_select_filter_icon_wrapper">
										<div class="items_select_filter_icon_holder">
											<div class="items_select_filter_icon_inner">
												<span class="items_select_filter_icon">
													<svg class="" aria-hidden="true" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="currentColor" d="M424.4 214.7L72.4 6.6C43.8-10.3 0 6.1 0 47.9V464c0 37.5 40.7 60.1 72.4 41.3l352-208c31.4-18.5 31.5-64.1 0-82.6z"></path></svg>
												</span>
											</div>
										</div>
									</div>
									<div class="items_select_ul_wrapper">
										<div class="items_select_ul_holder">
											<div class="items_select_ul_inner">
												<ul class="items_select_ul">
												</ul>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="gsp_terms_holder">
							<span class="ss_checkbox_wrapper ss_checked" id="gsp_agree_terms">
								<span class="ss_checkbox_line1"></span>
								<span class="ss_checkbox_line2"></span>
								<span class="ss_checkbox_ripple"></span>
								<span class="ss_checkbox_bg"></span>
							</span>
							<span class="ss_checkbox_label">Agree to <a href="https://gspeech.io/terms" target="_blank" class="ss_label_link">terms</a>.</span>
						</div>
						<div class="gsp_login_button" id="gsp_reg_button">Activate</div>
					</div>
					
				</div>

				<div class="gsp_tab_c gsp_tab_c_add_website" style="display: none">

					<div class="gsp_login_wrapper gsp_add_website_wrapper">
						<div class="gsp_login_title">Activate Cloud Console</div>
						<div class="gsp_login_subtitle">Start your audio journey :)</div>
						<input type="hidden" class="gsp_input" id="gsp_add_w_title" placeholder="Title" />
						<input type="hidden" class="gsp_input" id="gsp_add_w_url" placeholder="Url" />
						<div class="gsp_input_holder">
							<div id="add_website_lang" data-val="" class="items_select_filter_wrapper" data-def_txt="Select language (for voices)">
								<div class="items_select_filter">
									<div class="items_select_filter_content">
										<span>Select language (for voices)</span>
										<input type="text" class="li_search_input" />
									</div>
									<div class="items_select_filter_icon_wrapper">
										<div class="items_select_filter_icon_holder">
											<div class="items_select_filter_icon_inner">
												<span class="items_select_filter_icon">
													<svg class="" aria-hidden="true" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="currentColor" d="M424.4 214.7L72.4 6.6C43.8-10.3 0 6.1 0 47.9V464c0 37.5 40.7 60.1 72.4 41.3l352-208c31.4-18.5 31.5-64.1 0-82.6z"></path></svg>
												</span>
											</div>
										</div>
									</div>
									<div class="items_select_ul_wrapper">
										<div class="items_select_ul_holder">
											<div class="items_select_ul_inner">
												<ul class="items_select_ul">
												</ul>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="gsp_login_button" id="gsp_create_website_button">Activate</div>
					</div>
					
				</div>

				<div class="gsp_tab_c gsp_tab_c_faq" style="display: none">

					<?php include('tab_faq.php'); ?>
					
				</div>

				<div class="gsp_tab_c gsp_tab_c_upgrade" style="display: none">

					<?php 

					if($appsumo == 0)
						include('tab_upgrade.php');
					else
						include('tab_upgrade_appsumo.php');

				?>
					
				</div>

				<div class="gsp_tab_c gsp_tab_c_sign_out" style="display: none">

					<div class="gsp_sign_out_button_wrapper">
						<div class="gsp_login_button" id="gsp_sign_out_button">Logout</div>
					</div>
					
				</div>

				<div class="gsp_tab_c gsp_tab_c_website_settings" style="display: none">

					<div class="gsp_dash_col_1">

						<div data-tab_ident="settings" class="gsp_left_menu gsp_left_menu_settings gsp_left_m_selected">Settings</div>
						<div data-tab_ident="widgets" class="gsp_left_menu gsp_left_menu_widgets">Widgets</div>
						<div data-tab_ident="audios" class="gsp_left_menu gsp_left_menu_audios">Audios</div>
						<div data-tab_ident="analytics" class="gsp_left_menu gsp_left_menu_analytics">Analytics</div>
						<div data-tab_ident="" class="gsp_left_menu_dummy"><a class="gsp_left_link" href="https://gspeech.io/docs" target="_blank">Docs and guides</a></div>
						<div data-tab_ident="" class="gsp_left_menu_dummy"><a class="gsp_left_link" href="https://gspeech.io/contact-us" target="_blank">Contact Us</a></div>
						
					</div>
					<div class="gsp_dash_col_2">
						
						<div class="gsp_left_m_c gsp_left_m_c_settings gsp_left_m_c_active">
							<?php include('tab_website_settings.php'); ?>
						</div>
						<div class="gsp_left_m_c gsp_left_m_c_widgets">
							<?php include('tab_widgets.php'); ?>
						</div>
						<div class="gsp_left_m_c gsp_left_m_c_widget">
							<?php include('tab_widget.php'); ?>
						</div>
						<div class="gsp_left_m_c gsp_left_m_c_audios">
							<?php include('tab_audios.php'); ?>
						</div>
						<div class="gsp_left_m_c gsp_left_m_c_audio">
							<?php include('tab_audio.php'); ?>
						</div>
						<div class="gsp_left_m_c gsp_left_m_c_analytics">
							<?php include('tab_analytics.php'); ?>
						</div>
					</div>
					
				</div>

				<div class="gsp_tab_c gsp_tab_c_old_basic" style="display: none">
					<?php include('tab1.php');?>
				</div>

				<div class="gsp_tab_c gsp_tab_c_old_styles" style="display: none">
			  		<?php include('tab2.php');?>
				</div>
				
				<div class="old_p" class="submit">
					<input type="submit" class="gsp_login_button gsp_submit_button gsp_hidden" value="<?php _e('Save', 'GSpeech'); ?>" />
				</div>

			</div>
		</form>
		<script>
		  window.intercomSettings = {
		    api_base: "https://api-iam.intercom.io",
		    app_id: "anal0f8q",
		  };
		</script>
		<script>
		  (function(){var w=window;var ic=w.Intercom;if(typeof ic==="function"){ic('reattach_activator');ic('update',w.intercomSettings);}else{var d=document;var i=function(){i.c(arguments);};i.q=[];i.c=function(args){i.q.push(args);};w.Intercom=i;var l=function(){var s=d.createElement('script');s.type='text/javascript';s.async=true;s.src='https://widget.intercom.io/widget/anal0f8q';var x=d.getElementsByTagName('script')[0];x.parentNode.insertBefore(s,x);};if(document.readyState==='complete'){l();}else if(w.attachEvent){w.attachEvent('onload',l);}else{w.addEventListener('load',l,false);}}})();
		</script>
		<?php
		echo ob_get_clean();
	}
}
