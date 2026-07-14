<?php

// no direct access!
defined('ABSPATH') or die("No direct access");

add_action('wp_ajax_wpgsp_front_js',     'wpgsp_output_front_js');
add_action('wp_ajax_nopriv_wpgsp_front_js', 'wpgsp_output_front_js');

function wpgsp_output_front_js() {
    nocache_headers();
    header('Content-Type: application/javascript; charset=UTF-8');
    header('X-Content-Type-Options: nosniff');
    if (!defined('GSPEECH_PLG_VERSION')) { define('GSPEECH_PLG_VERSION','1.0.0'); }

    $file = __DIR__ . '/js/gspeech_front.js';
    if (!is_file($file)) {
        status_header(404);
        echo '/* GSpeech: gspeech_front.js not found */';
        exit;
    }

    readfile($file);
    exit;
}

// ===== Safe script tag flags for our handles =====
add_filter('script_loader_tag', function ($tag, $handle) {
    if ($handle === 'wpgs-script776' || $handle === 'wpgs-script777') {
        $tag = str_replace([' defer', ' async'], '', $tag);
        $tag = str_replace('<script ', '<script data-no-defer="1" data-no-optimize="1" data-no-minify="1" data-cfasync="false" nowprocket ', $tag);
        if (strpos($tag, ' id=') === false) {
            $tag = str_replace('<script ', '<script id="'.$handle.'-js" ', $tag);
        }
    }
    return $tag;
}, 10, 2);

// ===== Frontend fallback if optimizers stripped our inline/localize/enqueue =====
add_action('wp_print_footer_scripts', function () {

	if (wp_doing_ajax()) return;
    if (is_admin()) return;
    if (strpos($_SERVER['REQUEST_URI'] ?? '', '/wp-admin/') !== false) return;

    $cache_key = 'gspeech_footer_settings_cache';
    $settings  = get_transient($cache_key);

    if ($settings === false) {
        $settings = [
            'widget_id'      => (string) get_option('gspeech_widget_id', ''),
            'version'        => defined('GSPEECH_PLG_VERSION') ? GSPEECH_PLG_VERSION : '1.0.0',
            'lazy_load'      => (int) get_option('gspeech_lazy_load', 1),
            'reload_session' => (int) get_option('gspeech_reload_session', 0),
            'version_index'  => (int) get_option('gspeech_version_index', 0),
            'gtranslate'     => get_option('GTranslate', []),
        ];

        set_transient($cache_key, $settings, 5 * MINUTE_IN_SECONDS);
    }

    $widget_id       = $settings['widget_id'];
    $version         = $settings['version'];
    $lazy_load       = $settings['lazy_load'];
    $reload_session  = $settings['reload_session'];
    $version_index_1 = $settings['version_index'];
    $gtranslate      = $settings['gtranslate'];

    if ($widget_id === '') return;

    $plugin_main = realpath(dirname(__DIR__) . '/gspeech.php');
	$front_src   = plugin_dir_url($plugin_main) . 'includes/js/gspeech_front.js';
    $jquery_url = includes_url('js/jquery/jquery.min.js');
    $front_src_ajax = admin_url('admin-ajax.php?action=wpgsp_front_js');

    $ajax_url   = admin_url('admin-ajax.php');
    $ajax_nonce = wp_create_nonce('wpgsp_ajax_nonce_value_1');
    $referer    = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

    $wrapper_selector = 'gsp_clgtranslate_wrapper';
    if (!empty($gtranslate) && !empty($gtranslate['wrapper_selector'])) {
        $ws = sanitize_text_field($gtranslate['wrapper_selector']);
        $ws = str_replace('.', 'gsp_cl', $ws);
        $ws = str_replace('#', 'gsp_id', $ws);
        $wrapper_selector = $ws;
    }
    ?>
    <script type="text/javascript" id="wpgs-script777-js-extra-fallback" data-no-defer="1" data-no-optimize="1" data-no-minify="1" data-cfasync="false" nowprocket>var gsp_ajax_obj = {"ajax_url": <?php echo json_encode($ajax_url); ?>, "nonce": <?php echo json_encode($ajax_nonce); ?>};if (!window.gsp_ajax_obj) { window.gsp_ajax_obj = gsp_ajax_obj; }</script>
    <script id="wpgs-fallback-init" data-no-defer="1" data-no-optimize="1" data-no-minify="1" data-cfasync="false" nowprocket>(function(){try{if(window.gspeechFront){console.log("[GSpeech.io] gspeech_front already initialized");return;}function waitFor(e,n,t,o,a){var r=Date.now(),i=setInterval(function(){if(e()){clearInterval(i);n&&n();return}if(Date.now()-r>(o||1500)){clearInterval(i);t&&t()}},a||50)}function ensureDataDiv(){if(!document.getElementById("gsp_data_html")){var e=document.createElement("div");e.id="gsp_data_html";e.setAttribute("data-g_version",<?php echo json_encode($version); ?>);e.setAttribute("data-w_id",<?php echo json_encode($widget_id); ?>);e.setAttribute("data-s_enc","");e.setAttribute("data-h_enc","");e.setAttribute("data-hh_enc","");e.setAttribute("data-lazy_load",<?php echo json_encode($lazy_load); ?>);e.setAttribute("data-reload_session",<?php echo json_encode($reload_session); ?>);e.setAttribute("data-gt-w",<?php echo json_encode($wrapper_selector); ?>);e.setAttribute("data-vv_index",<?php echo json_encode($version_index_1); ?>);e.setAttribute("data-ref",encodeURI(<?php echo json_encode($referer); ?>));(document.body||document.documentElement).appendChild(e)}}"loading"===document.readyState?document.addEventListener("DOMContentLoaded",ensureDataDiv):ensureDataDiv();function loadScript(e,n,t,o){var a=document.createElement("script");if(n)a.id=n;a.setAttribute("data-no-defer","");a.setAttribute("data-no-optimize","");a.setAttribute("data-cfasync","false");a.src=e;a.onload=function(){t&&t()};a.onerror=function(){o&&o()};(document.head||document.documentElement).appendChild(a)}function loadFrontWithFallback(){var e=<?php echo json_encode($front_src.(strpos($front_src,"?")===false?"?v=":"&v=").$version); ?>,n=<?php echo json_encode($front_src_ajax.(strpos($front_src_ajax,"?")===false?"?v=":"&v=").$version); ?>;if(window.gspeechFront)return;loadScript(e,"wpgs-script777-js",null,function(){console.warn("[GSpeech.io] primary JS failed, switching to AJAX proxy");loadScript(n,"wpgs-script777-js",null,function(){console.error("[GSpeech.io] both primary and AJAX fallback failed")})})}var jqUrl=<?php echo json_encode($jquery_url.(strpos($jquery_url,'?')===false?'?v=':'&v=').$version); ?>;function start(){window.jQuery?loadFrontWithFallback():loadScript(jqUrl,"wpgs-jquery-fallback",loadFrontWithFallback,loadFrontWithFallback)}waitFor(function(){return!!window.gspeechFront},function(){console.log("[GSpeech.io] initialized by external loader")},start,1500,50)}catch(e){console.error("[GSpeech.io] fallback error:",e)}})();</script>

    <?php
}, 999);

?>