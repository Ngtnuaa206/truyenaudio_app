(function waitForjQuery(attempts){
    if (typeof window.jQuery !== "undefined") {

(function($) {

window.gspeechFront = function(options) {

    var $this = this,
        thisPage = this;

    this.initVars = function() {

        // get options
        this.options = options;
        this.is_touch_devise = 'ontouchstart' in window ? true : false;
        this.options.lazy_load_timeout = 400;

    };

    this.init = function() {

        this.initVars();

        this.runFunctions();
    };

    // Start ///////////////////////////////////////////////////////////////////////

    this.runFunctions = function() {

        this.applyCookies();

        this.applyFunctions();
    };

    this.applyFunctions = function() {

        var $gsp_data_html = $("#gsp_data_html");

        if (!$gsp_data_html.length) {
            console.log("GSpeech: required html is missing.")
            return;
        }

        var lazy_load = $gsp_data_html.data("lazy_load");
        var reload_session = parseInt($gsp_data_html.data("reload_session"));
        var widget_id = $gsp_data_html.data("w_id");
        var v_ind = $gsp_data_html.data("vv_index");
        var gt_w = $gsp_data_html.data("gt-w");

        let storage;
        try {
            storage = window.localStorage;
            storage.setItem('test', 'test');
            storage.removeItem('test');
        } catch (e) {
            storage = window.sessionStorage;
        }

        let encData = storage.getItem('gsp_enc_data') ? JSON.parse(storage.getItem('gsp_enc_data')) : {};

        var canAjax = (typeof window.gsp_ajax_obj === 'object') &&
                      window.gsp_ajax_obj &&
                      typeof gsp_ajax_obj.ajax_url === 'string' &&
                      gsp_ajax_obj.ajax_url &&
                      typeof gsp_ajax_obj.nonce === 'string' &&
                      gsp_ajax_obj.nonce;

        if (reload_session == 1 || !encData.h_enc || !encData.s_enc || !encData.hh_enc) {

            if (canAjax) {
                jQuery.post(gsp_ajax_obj.ajax_url, {
                    action: 'wpgsp_validate_enc_data',
                    _ajax_nonce: gsp_ajax_obj.nonce
                }, function(response) {
                    if (response && response.success && response.data) {
                        encData = {
                            s_enc: response.data.s_enc || '',
                            h_enc: response.data.h_enc || '',
                            hh_enc: response.data.hh_enc || ''
                        };
                        try { storage.setItem('gsp_enc_data', JSON.stringify(encData)); } catch(e){}
                    }
                    loadCloudWidget(encData);
                }).fail(function() {
                    loadCloudWidget(encData);
                });
            } else {
                console.warn('[GSpeech] gsp_ajax_obj is missing — loading widget without enc data');
                loadCloudWidget(encData);
            }

        } else {
            loadCloudWidget(encData);
        }

        function loadCloudWidget(encData) {
            var load_timeout = lazy_load == 1 ? thisPage.options.lazy_load_timeout : 0;
        
            if ($("#gspeech_cloud_widget").length) return; // already loaded
        
            setTimeout(function () {
                if ($("#gspeech_cloud_widget").length) return; // double-check
                $("body").append(
                    '<script id="gspeech_cloud_widget" defer src="https://widget.gspeech.io/' + widget_id +
                    '?v_ind=' + v_ind + '"' +
                    ' data-widget_id="' + widget_id + '"' +
                    ' data-s="' + (encData.s_enc || '') + '"' +
                    ' data-h="' + (encData.h_enc || '') + '"' +
                    ' data-hh="' + (encData.hh_enc || '') + '"' +
                    ' data-gt_w="' + gt_w + '"></script>'
                );
            }, load_timeout);
        }
        
    };

    // Inner methods ///////////////////////////////////////////////////////////////////////

    this.bytesToHex = function(bytes) {

        var hexstring='', h;
        for(var i=0; i<bytes.length; i++) {
            h=bytes[i].toString(16);
            if(h.length==1) { h='0'+h; }
            hexstring+=h;
        }   
        return hexstring;        
    };

    this.applyCookies = function() {

        this.setCookie = function(key, value, expiry) {

            var cookie_val = key + '=' + value + ';path=/';
            if(expiry != -1) {
                var expires = new Date();
                expires.setTime(expires.getTime() + (expiry * 60 * 60 * 1000)); // in hours
                cookie_val += ';expires=' + expires.toUTCString();
            }
            document.cookie = cookie_val;
        };

        this.getCookie = function(key) {

            var keyValue = document.cookie.match('(^|;) ?' + key + '=([^;]*)(;|$)');
            return keyValue ? keyValue[2] : '';
        };

        this.eraseCookie = function(key) {

            var keyValue = this.getCookie(key);
            this.setCookie(key, keyValue, '-2');
        };
    };

    // Call init ///////////////////////////////////////////////////////////////////////

    this.init();
};

$(document).ready(function() {

    // gspeech 3.x
    var gsp_options = {};
    window.gspeech_front = new gspeechFront(gsp_options);
});

})(jQuery);

} else if (attempts < 100) {
    setTimeout(function(){ waitForjQuery(attempts+1); }, 50);
} else {
    console.warn("[GSpeech] jQuery not available after waiting.");
}
})(0);