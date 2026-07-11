<?php /* Template Name: Đăng nhập */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['username'])) {
    $creds = [
        'user_login' => sanitize_user($_POST['username']),
        'user_password' => $_POST['password'],
        'remember' => true,
    ];
    $user = wp_signon($creds);
    if (is_wp_error($user)) {
        $login_error = $user->get_error_message();
    } else {
        ta_set_flash('success', '👋 Đăng nhập thành công! Chào mừng bạn quay lại.');
        if (isset($_GET['redirect_to'])) {
            $redirect = $_GET['redirect_to'];
        } elseif (in_array('administrator', (array) $user->roles)) {
            $redirect = admin_url();
        } elseif (in_array('tac_gia_role', (array) $user->roles)) {
            $redirect = home_url('/tac-gia-dashboard');
        } else {
            $redirect = home_url('/profile');
        }
        wp_redirect($redirect);
        exit;
    }
}

get_header();
?>

<div class="container">
    <div class="auth-form">

    <!-- Login -->
    <div id="login-form">
        <h2>Đăng nhập</h2>
        <form method="post" novalidate>
            <label>Tên đăng nhập</label>
            <input type="text" name="username" class="<?php echo isset($login_error) ? 'field-error' : ''; ?>">
            <label>Mật khẩu</label>
            <div class="pw-field <?php echo isset($login_error) ? 'field-error' : ''; ?>">
                <input type="password" name="password">
                <button type="button" class="pw-toggle" tabindex="-1">👁</button>
            </div>
            <?php if (isset($login_error)): ?>
                <p class="err-inline" style="margin:-8px 0 0 0;">❌ <?php echo $login_error; ?></p>
            <?php endif; ?>
            <button type="submit" name="ta_login" class="btn btn-primary">Đăng nhập</button>
        </form>
        <script>
        jQuery(function($) {
            $('form').has('[name="ta_login"]').on('submit', function(e) {
                var ok = true;
                $(this).find('.err-inline').remove();
                $(this).find('.field-error').removeClass('field-error');
                if (!$(this).find('[name="username"]').val().trim()) {
                    $(this).find('[name="username"]').addClass('field-error').after('<p class="err-inline err-js">Vui lòng nhập tên đăng nhập.</p>');
                    ok = false;
                }
                if (!$(this).find('[name="password"]').val()) {
                    $(this).find('[name="password"]').closest('.pw-field').addClass('field-error').after('<p class="err-inline err-js">Vui lòng nhập mật khẩu.</p>');
                    ok = false;
                }
                if (!ok) e.preventDefault();
            });
        });
        </script>
        <p style="text-align:center;margin-top:12px;font-size:13px;">
            <a href="#" id="show-forgot-link" style="color:#f0c040;">Quên mật khẩu?</a>
        </p>
        <p style="text-align:center;margin-top:8px;font-size:13px;color:#888;">
            Chưa có tài khoản? <a href="<?php echo home_url('/dang-ky'); ?>">Đăng ký</a>
        </p>
        <div class="social-login">
            <p style="text-align:center;font-size:13px;color:#888;margin-bottom:15px;">Hoặc đăng nhập bằng</p>
            <a href="<?php echo ta_fb_login_url(); ?>" class="social-btn social-facebook<?php echo !get_option('ta_fb_app_id') ? ' disabled' : ''; ?>">
                <span class="social-icon">f</span> Facebook
            </a>
            <a href="<?php echo ta_google_login_url(); ?>" class="social-btn social-google<?php echo !get_option('ta_google_client_id') ? ' disabled' : ''; ?>">
                <span class="social-icon">G</span> Google
            </a>
        </div>
    </div>

    <!-- Forgot password step 1: email -->
    <div id="forgot-step-1" style="display:none;">
        <h2>Quên mật khẩu</h2>
        <p style="color:#888;font-size:14px;margin-bottom:15px;">Nhập email đã đăng ký, chúng tôi sẽ gửi mã xác thực cho bạn.</p>
        <label>Email</label>
        <input type="email" id="fp-email" required>
        <div id="fp-msg-step1" style="font-size:13px;margin:-8px 0 12px 0;"></div>
        <button id="fp-send-btn" class="btn btn-primary" style="width:100%;">Gửi mã xác thực</button>
        <p style="text-align:center;margin-top:12px;font-size:13px;">
            <a href="#" id="back-to-login-1" style="color:#f0c040;">← Quay lại đăng nhập</a>
        </p>
    </div>

    <!-- Forgot password step 2: OTP + new password -->
    <div id="forgot-step-2" style="display:none;">
        <h2>Đặt lại mật khẩu</h2>
        <p style="color:#888;font-size:14px;margin-bottom:15px;">Nhập mã xác thực và mật khẩu mới.</p>
        <input type="hidden" id="fp-user-id">
        <label>Mã xác thực (6 chữ số)</label>
        <input type="text" id="fp-otp" maxlength="6" inputmode="numeric" pattern="[0-9]{6}" autocomplete="one-time-code" style="text-align:center;font-size:24px;letter-spacing:8px;font-weight:700;">
        <label>Mật khẩu mới</label>
        <div class="pw-field"><input type="password" id="fp-new-pass"><button type="button" class="pw-toggle" tabindex="-1">👁</button></div>
        <label>Nhập lại mật khẩu</label>
        <div class="pw-field"><input type="password" id="fp-confirm-pass"><button type="button" class="pw-toggle" tabindex="-1">👁</button></div>
        <div id="fp-msg-step2" style="font-size:13px;margin:0 0 12px 0;"></div>
        <button id="fp-reset-btn" class="btn btn-primary" style="width:100%;">Đặt lại mật khẩu</button>
        <p style="text-align:center;margin-top:12px;font-size:13px;">
            <a href="#" id="back-to-login-2" style="color:#f0c040;">← Quay lại đăng nhập</a>
        </p>
    </div>

    </div>
</div>

<script>
jQuery(function($) {
    // Show forgot form
    $('#show-forgot-link').on('click', function(e) {
        e.preventDefault();
        $('#login-form').hide();
        $('#forgot-step-1').show();
    });

    function backToLogin() {
        $('#forgot-step-1, #forgot-step-2').hide();
        $('#login-form').show();
        $('#fp-msg-step1, #fp-msg-step2').empty();
    }
    $('#back-to-login-1, #back-to-login-2').on('click', function(e) {
        e.preventDefault();
        backToLogin();
    });

    // Step 1: Send OTP
    $('#fp-send-btn').on('click', function() {
        var $btn = $(this);
        var $msg = $('#fp-msg-step1');
        var email = $('#fp-email').val().trim();
        if (!email) { $msg.html('❌ Vui lòng nhập email.').css('color','#e74c3c'); return; }

        $btn.prop('disabled', true).text('Đang gửi...');
        $msg.html('');

        $.ajax({
            type: 'POST',
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            data: { action: 'ta_forgot_password', email: email },
            success: function(res) {
                if (res.success) {
                    $msg.html('✅ ' + res.data.message).css('color','#2ecc71');
                    $('#fp-user-id').val(res.data.user_id);
                    $('#forgot-step-1').hide();
                    $('#forgot-step-2').show();
                } else {
                    $msg.html('❌ ' + res.data).css('color','#e74c3c');
                }
            },
            error: function() {
                $msg.html('❌ Lỗi kết nối.').css('color','#e74c3c');
            },
            complete: function() {
                $btn.prop('disabled', false).text('Gửi mã xác thực');
            }
        });
    });

    // Step 2: Reset password
    $('#fp-reset-btn').on('click', function() {
        var $btn = $(this);
        var $msg = $('#fp-msg-step2');
        var userId = $('#fp-user-id').val();
        var otp = $('#fp-otp').val().trim();
        var pass = $('#fp-new-pass').val();
        var confirm = $('#fp-confirm-pass').val();

        if (otp.length !== 6) { $msg.html('❌ Vui lòng nhập đủ 6 chữ số.').css('color','#e74c3c'); return; }
        var pwStrong = /[A-Z]/.test(pass) && /[a-z]/.test(pass) && /[0-9]/.test(pass) && /[^A-Za-z0-9]/.test(pass);
        if (pass.length < 8 || !pwStrong) { $msg.html('❌ Mật khẩu phải có ít nhất 8 ký tự, bao gồm chữ hoa, chữ thường, số và ký tự đặc biệt.').css('color','#e74c3c'); return; }
        if (pass !== confirm) { $msg.html('❌ Mật khẩu nhập lại không khớp.').css('color','#e74c3c'); return; }

        $btn.prop('disabled', true).text('Đang xử lý...');
        $msg.html('');

        $.ajax({
            type: 'POST',
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            data: { action: 'ta_reset_password', user_id: userId, otp: otp, password: pass },
            success: function(res) {
                if (res.success) {
                    $msg.html('✅ ' + res.data.message).css('color','#2ecc71');
                    setTimeout(function() { backToLogin(); }, 2000);
                } else {
                    $msg.html('❌ ' + res.data).css('color','#e74c3c');
                }
            },
            error: function() {
                $msg.html('❌ Lỗi kết nối.').css('color','#e74c3c');
            },
            complete: function() {
                $btn.prop('disabled', false).text('Đặt lại mật khẩu');
            }
    });
});
</script>

<style>
.field-error { border-color:#e74c3c !important; }
.err-inline { color:#e74c3c;font-size:13px;margin:-8px 0 12px 0; }
.social-login { margin-top:25px;padding-top:20px;border-top:1px solid var(--border);display:grid;gap:10px; }
.social-btn { display:flex;align-items:center;justify-content:center;gap:10px;width:100%;padding:12px;border:none;border-radius:8px;font-size:15px;font-weight:600;cursor:pointer;transition:opacity 0.2s;color:#fff;text-decoration:none !important; }
.social-btn:hover { opacity:0.9; }
.social-btn.disabled { opacity:0.35;cursor:not-allowed;pointer-events:none; }
.social-facebook { background:#1877f2; }
.social-google { background:#ea4335; }
.social-icon { display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:50%;background:rgba(255,255,255,0.2);font-size:14px;font-weight:700; }
</style>
<?php get_footer(); ?>