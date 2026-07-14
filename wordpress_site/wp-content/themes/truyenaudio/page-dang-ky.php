<?php /* Template Name: Đăng ký */

$pending_user_id = isset($_GET['pending_user']) ? intval($_GET['pending_user']) : 0;
$pending_method = isset($_GET['method']) ? $_GET['method'] : '';

$err = []; // field => message

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['ta_register'])) {
    $reg_type = $_POST['reg_type'] ?? 'email';
    $password = $_POST['password'];

    if ($reg_type === 'email') {
        $username = sanitize_user($_POST['username']);
        $email = sanitize_email($_POST['email']);

        if (empty($username)) $err['username'] = 'Vui lòng nhập tên đăng nhập.';
        elseif (username_exists($username)) $err['username'] = 'Tên đăng nhập đã tồn tại.';

        if (empty($email)) $err['email'] = 'Vui lòng nhập email.';
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $err['email'] = 'Email không đúng định dạng.';
        elseif (email_exists($email)) $err['email'] = 'Email đã được sử dụng.';

        if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password) || !preg_match('/[^A-Za-z0-9]/', $password))
            $err['password'] = 'Mật khẩu phải có ít nhất 8 ký tự, gồm chữ hoa, chữ thường, số và ký tự đặc biệt.';

        if (empty($err)) {
            $user_id = wp_insert_user([
                'user_login' => $username,
                'user_email' => $email,
                'user_pass'  => $password,
                'role'       => 'subscriber',
            ]);
            if (is_wp_error($user_id)) {
                $err['general'] = $user_id->get_error_message();
            } else {
                $otp = ta_generate_otp();
                update_user_meta($user_id, '_email_otp', $otp);
                update_user_meta($user_id, '_email_otp_expires', time() + 600);
                update_user_meta($user_id, '_email_verified', '0');
                ta_send_otp_email($user_id, $email, $otp);
                ta_set_flash('success', '📧 Mã xác thực đã được gửi. Vui lòng kiểm tra email!');
                wp_redirect(home_url('/dang-ky?pending_user=' . $user_id . '&method=email'));
                exit;
            }
        }
    } elseif ($reg_type === 'phone') {
        $phone = preg_replace('/[^0-9]/', '', $_POST['phone']);

        if (strlen($phone) < 9 || strlen($phone) > 11) $err['phone'] = 'Số điện thoại không hợp lệ.';
        else {
            $users = get_users(['meta_key' => '_phone', 'meta_value' => $phone]);
            if (!empty($users)) $err['phone'] = 'Số điện thoại này đã được đăng ký.';
        }

        if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password) || !preg_match('/[^A-Za-z0-9]/', $password))
            $err['password'] = 'Mật khẩu phải có ít nhất 8 ký tự, gồm chữ hoa, chữ thường, số và ký tự đặc biệt.';

        if ($_POST['confirm_password'] !== $password) $err['confirm_password'] = 'Mật khẩu nhập lại không khớp.';

        if (empty($err)) {
            $username = 'user_' . $phone;
            $email = $phone . '@ta-user.com';
            $suffix = 1;
            while (username_exists($username)) { $username = 'user_' . $phone . '_' . $suffix; $suffix++; }
            $user_id = wp_insert_user([
                'user_login' => $username,
                'user_email' => $email,
                'user_pass'  => $password,
                'role'       => 'subscriber',
            ]);
            if (is_wp_error($user_id)) {
                $err['general'] = $user_id->get_error_message();
            } else {
                update_user_meta($user_id, '_phone', $phone);
                update_user_meta($user_id, '_email_verified', '1');
                wp_set_current_user($user_id);
                wp_set_auth_cookie($user_id);
                ta_set_flash('success', '🎉 Đăng ký thành công!');
                wp_redirect(home_url('/profile'));
                exit;
            }
        }
    }
}

get_header();
?>

<div class="container">
    <div class="auth-form">

    <?php if ($pending_user_id && $pending_method === 'email'): ?>
        <h2>📧 Xác thực email</h2>
        <p style="color:#888;font-size:14px;margin-bottom:20px;">Mã xác thực đã được gửi. Vui lòng kiểm tra hộp thư đến hoặc thư rác.</p>
        <div id="otp-area">
            <label>Mã xác thực (6 chữ số)</label>
            <input type="text" id="otp-input" maxlength="6" inputmode="numeric" pattern="[0-9]{6}" autocomplete="one-time-code" style="width:100%;text-align:center;font-size:24px;letter-spacing:8px;font-weight:700;background:var(--input-bg);color:var(--text);border:1px solid var(--border);padding:12px;border-radius:6px;box-sizing:border-box;">
            <div id="otp-msg" style="margin-top:10px;font-size:14px;color:#e74c3c;"></div>
            <button class="btn btn-primary" id="otp-verify-btn" style="width:100%;margin-top:15px;">Xác thực</button>
            <p style="text-align:center;margin-top:12px;font-size:13px;color:#888;">
                Không nhận được mã? <a href="#" id="otp-resend-btn" style="color:#f0c040;">Gửi lại mã</a>
            </p>
        </div>

        <script>
        jQuery(function($) {
            var userId = <?php echo $pending_user_id; ?>;
            $('#otp-verify-btn').on('click', function() {
                var $btn = $(this), $msg = $('#otp-msg');
                var otp = $('#otp-input').val().trim();
                if (otp.length !== 6) { $msg.html('❌ Vui lòng nhập đủ 6 chữ số.'); return; }
                $btn.prop('disabled', true).text('Đang xác thực...'); $msg.html('');
                $.ajax({
                    type: 'POST', url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    data: { action: 'ta_verify_otp', user_id: userId, otp: otp },
                    success: function(res) {
                        if (res.success) { $msg.html('✅ ' + res.data.message); $msg.css('color','#2ecc71'); setTimeout(function(){window.location.href='<?php echo home_url('/profile'); ?>';},1000); }
                        else { $msg.html('❌ ' + res.data); $btn.prop('disabled', false).text('Xác thực'); }
                    },
                    error: function() { $msg.html('❌ Lỗi kết nối.'); $btn.prop('disabled', false).text('Xác thực'); }
                });
            });
            $('#otp-resend-btn').on('click', function(e) {
                e.preventDefault(); var $btn = $(this), $msg = $('#otp-msg');
                $btn.text('Đang gửi...');
                $.ajax({
                    type: 'POST', url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    data: { action: 'ta_resend_otp', user_id: userId },
                    success: function(res) { $msg.html((res.success?'✅ ':'❌ ')+res.data); $btn.text('Gửi lại mã'); },
                    error: function() { $msg.html('❌ Lỗi kết nối.'); $btn.text('Gửi lại mã'); }
                });
            });
        });
        </script>

    <?php else: ?>

        <h2>Đăng ký</h2>

        <?php if (!empty($err['general'])): ?>
            <p style="color:#e74c3c;margin-bottom:15px;"><?php echo $err['general']; ?></p>
        <?php endif; ?>

        <div class="reg-tabs">
            <button class="reg-tab active" data-tab="email">📧 Email</button>
            <button class="reg-tab" data-tab="phone">📱 Số điện thoại</button>
        </div>

        <form method="post" class="reg-form" id="reg-email" style="display:block;" novalidate>
            <input type="hidden" name="reg_type" value="email">
            <label>Tên đăng nhập</label>
            <input type="text" name="username" value="<?php echo esc_attr($_POST['username'] ?? ''); ?>" class="<?php echo isset($err['username']) ? 'field-error' : ''; ?>">
            <?php if (isset($err['username'])): ?><p class="err-inline"><?php echo $err['username']; ?></p><?php endif; ?>

            <label>Email</label>
            <input type="email" name="email" value="<?php echo esc_attr($_POST['email'] ?? ''); ?>" class="<?php echo isset($err['email']) ? 'field-error' : ''; ?>">
            <?php if (isset($err['email'])): ?><p class="err-inline"><?php echo $err['email']; ?></p><?php endif; ?>

            <label>Mật khẩu</label>
            <div class="pw-field <?php echo isset($err['password']) ? 'field-error' : ''; ?>">
                <input type="password" name="password">
                <button type="button" class="pw-toggle" tabindex="-1">👁</button>
            </div>
            <p class="pw-hint">Gồm chữ hoa, chữ thường, số, ký tự đặc biệt, ≥8 ký tự</p>
            <?php if (isset($err['password'])): ?><p class="err-inline"><?php echo $err['password']; ?></p><?php endif; ?>

            <button type="submit" name="ta_register" class="btn btn-primary">Đăng ký</button>
        </form>

        <form method="post" class="reg-form" id="reg-phone" style="display:none;" novalidate>
            <input type="hidden" name="reg_type" value="phone">
            <label>Số điện thoại</label>
            <div class="phone-input-wrap <?php echo isset($err['phone']) ? 'field-error' : ''; ?>">
                <span class="phone-prefix">+84</span>
                <input type="tel" name="phone" class="phone-input" placeholder="912345678" value="<?php echo esc_attr($_POST['phone'] ?? ''); ?>">
            </div>
            <?php if (isset($err['phone'])): ?><p class="err-inline"><?php echo $err['phone']; ?></p><?php endif; ?>

            <label>Mật khẩu</label>
            <div class="pw-field <?php echo isset($err['password']) ? 'field-error' : ''; ?>">
                <input type="password" name="password">
                <button type="button" class="pw-toggle" tabindex="-1">👁</button>
            </div>
            <p class="pw-hint">Gồm chữ hoa, chữ thường, số, ký tự đặc biệt, ≥8 ký tự</p>
            <?php if (isset($err['password'])): ?><p class="err-inline"><?php echo $err['password']; ?></p><?php endif; ?>

            <label>Nhập lại mật khẩu</label>
            <div class="pw-field <?php echo isset($err['confirm_password']) ? 'field-error' : ''; ?>">
                <input type="password" name="confirm_password">
                <button type="button" class="pw-toggle" tabindex="-1">👁</button>
            </div>
            <?php if (isset($err['confirm_password'])): ?><p class="err-inline"><?php echo $err['confirm_password']; ?></p><?php endif; ?>

            <button type="submit" name="ta_register" class="btn btn-primary">Đăng ký</button>
        </form>

        <script>
        jQuery(function($) {
            $('.reg-tab').on('click', function() {
                $('.reg-tab').removeClass('active');
                $(this).addClass('active');
                $('.reg-form').hide();
                $('#reg-' + $(this).data('tab')).show();
            });

            // Client-side validation — inline errors, no browser popups
            $('.reg-form').on('submit', function(e) {
                var form = this;
                var ok = true;

                // Clear previous inline errors (both JS and PHP)
                $(form).find('.err-inline').remove();
                $(form).find('.field-error').removeClass('field-error');

                function showErr(name, msg) {
                    var inp = $(form).find('[name="'+name+'"]');
                    if (!inp.length) inp = $(form).find('#'+name);
                    var wrap = inp.closest('.phone-input-wrap, .pw-field');
                    if (wrap.length) {
                        wrap.addClass('field-error');
                        wrap.after('<p class="err-inline err-js">'+msg+'</p>');
                    } else {
                        inp.addClass('field-error');
                        inp.after('<p class="err-inline err-js">'+msg+'</p>');
                    }
                    ok = false;
                }

                var type = $(form).find('[name="reg_type"]').val();

                if (type === 'email') {
                    var user = $(form).find('[name="username"]').val().trim();
                    var email = $(form).find('[name="email"]').val().trim();
                    var pass = $(form).find('[name="password"]').val();

                    if (!user) showErr('username', 'Vui lòng nhập tên đăng nhập.');
                    if (!email) showErr('email', 'Vui lòng nhập email.');
                    else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) showErr('email', 'Email không đúng định dạng.');
                    if (!pass || pass.length < 8 || !/[A-Z]/.test(pass) || !/[a-z]/.test(pass) || !/[0-9]/.test(pass) || !/[^A-Za-z0-9]/.test(pass))
                        showErr('password', 'Mật khẩu phải có ≥8 ký tự, gồm chữ hoa, chữ thường, số và ký tự đặc biệt.');
                }

                if (type === 'phone') {
                    var phone = $(form).find('[name="phone"]').val().replace(/\D/g,'');
                    var pass = $(form).find('[name="password"]').val();
                    var confirm = $(form).find('[name="confirm_password"]').val();

                    if (!phone || phone.length < 9 || phone.length > 11) showErr('phone', 'Số điện thoại không hợp lệ.');
                    if (!pass || pass.length < 8 || !/[A-Z]/.test(pass) || !/[a-z]/.test(pass) || !/[0-9]/.test(pass) || !/[^A-Za-z0-9]/.test(pass))
                        showErr('password', 'Mật khẩu phải có ≥8 ký tự, gồm chữ hoa, chữ thường, số và ký tự đặc biệt.');
                    if (pass !== confirm) showErr('confirm_password', 'Mật khẩu nhập lại không khớp.');
                }

                if (!ok) e.preventDefault();
            });
        });
        </script>

        <div class="social-login">
            <p style="text-align:center;font-size:13px;color:#888;margin-bottom:15px;">Hoặc đăng nhập bằng</p>
            <a href="<?php echo ta_fb_login_url(); ?>" class="social-btn social-facebook<?php echo !get_option('ta_fb_app_id') ? ' disabled' : ''; ?>">
                <span class="social-icon">f</span> Facebook
            </a>
            <a href="<?php echo ta_google_login_url(); ?>" class="social-btn social-google<?php echo !get_option('ta_google_client_id') ? ' disabled' : ''; ?>">
                <span class="social-icon">G</span> Google
            </a>
        </div>

        <p style="text-align:center;margin-top:15px;font-size:13px;color:#888;">
            Đã có tài khoản? <a href="<?php echo home_url('/dang-nhap'); ?>">Đăng nhập</a>
        </p>

    <?php endif; ?>

    </div>
</div>

<style>
.reg-tabs { display:flex;gap:0;margin-bottom:20px;border:1px solid var(--border);border-radius:8px;overflow:hidden; }
.reg-tab { flex:1;padding:12px;text-align:center;background:transparent;border:none;color:var(--text-muted);font-size:14px;cursor:pointer;transition:all 0.2s; }
.reg-tab.active { background:var(--accent);color:#1a1a2e;font-weight:600; }
.reg-tab:not(.active):hover { background:var(--border); }
.phone-input-wrap { display:flex;align-items:center;background:var(--input-bg);border:1px solid var(--border);border-radius:6px;overflow:hidden; }
.phone-input-wrap:focus-within { border-color:var(--accent); }
.phone-prefix { padding:0 14px;font-size:15px;font-weight:600;color:#f0c040;background:rgba(240,192,64,0.1);line-height:44px;border-right:1px solid var(--border);white-space:nowrap; }
.phone-input { flex:1;border:none !important;background:transparent !important;padding:10px 14px !important;font-size:15px;color:var(--text);outline:none;border-radius:0 !important; }
.field-error { border-color:#e74c3c !important; }
.err-inline { color:#e74c3c;font-size:13px;margin:-8px 0 12px 0; }
.pw-hint { color:#888;font-size:12px;margin:-8px 0 12px 0; }
.social-login { margin-top:25px;padding-top:20px;border-top:1px solid var(--border);display:grid;gap:10px; }
.social-btn { display:flex;align-items:center;justify-content:center;gap:10px;width:100%;padding:12px;border:none;border-radius:8px;font-size:15px;font-weight:600;cursor:pointer;transition:opacity 0.2s;color:#fff;text-decoration:none !important; }
.social-btn:hover { opacity:0.9; }
.social-btn.disabled { opacity:0.35;cursor:not-allowed;pointer-events:none; }
.social-facebook { background:#1877f2; }
.social-google { background:#ea4335; }
.social-icon { display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:50%;background:rgba(255,255,255,0.2);font-size:14px;font-weight:700; }
</style>

<?php get_footer(); ?>