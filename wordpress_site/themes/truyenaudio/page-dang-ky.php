<?php /* Template Name: Đăng ký */

$err = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['ta_register'])) {
    $username = sanitize_user($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (empty($username)) $err['username'] = 'Vui lòng nhập tên đăng nhập.';
    elseif (strlen($username) < 3) $err['username'] = 'Tên đăng nhập phải từ 3 ký tự.';
    elseif (username_exists($username)) $err['username'] = 'Tên đăng nhập đã tồn tại.';

    if (empty($password)) $err['password'] = 'Vui lòng nhập mật khẩu.';
    elseif (strlen($password) < 6) $err['password'] = 'Mật khẩu phải từ 6 ký tự.';

    if ($password !== $confirm) $err['confirm_password'] = 'Mật khẩu nhập lại không khớp.';

    if (empty($err)) {
        $email = $username . '@ta-user.local';
        $user_id = wp_insert_user([
            'user_login' => $username,
            'user_email' => $email,
            'user_pass'  => $password,
            'role'       => 'subscriber',
        ]);
        if (is_wp_error($user_id)) {
            $err['general'] = $user_id->get_error_message();
        } else {
            wp_set_current_user($user_id);
            wp_set_auth_cookie($user_id);
            ta_set_flash('success', '🎉 Đăng ký thành công!');
            wp_redirect(home_url('/profile'));
            exit;
        }
    }
}

get_header();
?>

<div class="container">
    <div class="auth-form">

        <h2>Đăng ký</h2>

        <?php if (!empty($err['general'])): ?>
            <p style="color:#e74c3c;margin-bottom:15px;"><?php echo $err['general']; ?></p>
        <?php endif; ?>

        <form method="post" novalidate>
            <label>Tên đăng nhập</label>
            <input type="text" name="username" value="<?php echo esc_attr($_POST['username'] ?? ''); ?>" class="<?php echo isset($err['username']) ? 'field-error' : ''; ?>" autocomplete="username">
            <?php if (isset($err['username'])): ?><p class="err-inline"><?php echo $err['username']; ?></p><?php endif; ?>

            <label>Mật khẩu</label>
            <div class="pw-field <?php echo isset($err['password']) ? 'field-error' : ''; ?>">
                <input type="password" name="password" autocomplete="new-password">
                <button type="button" class="pw-toggle" tabindex="-1">👁</button>
            </div>
            <?php if (isset($err['password'])): ?><p class="err-inline"><?php echo $err['password']; ?></p><?php endif; ?>

            <label>Nhập lại mật khẩu</label>
            <div class="pw-field <?php echo isset($err['confirm_password']) ? 'field-error' : ''; ?>">
                <input type="password" name="confirm_password" autocomplete="new-password">
                <button type="button" class="pw-toggle" tabindex="-1">👁</button>
            </div>
            <?php if (isset($err['confirm_password'])): ?><p class="err-inline"><?php echo $err['confirm_password']; ?></p><?php endif; ?>

            <button type="submit" name="ta_register" class="btn btn-primary">Đăng ký</button>
        </form>

        <p style="text-align:center;margin-top:15px;font-size:13px;color:#888;">
            Đã có tài khoản? <a href="<?php echo home_url('/dang-nhap'); ?>">Đăng nhập</a>
        </p>

    </div>
</div>

<script>
jQuery(function($) {
    $('form').has('[name="ta_register"]').on('submit', function(e) {
        var ok = true;
        $(this).find('.err-inline').remove();
        $(this).find('.field-error').removeClass('field-error');

        var user = $(this).find('[name="username"]').val().trim();
        var pass = $(this).find('[name="password"]').val();
        var confirm = $(this).find('[name="confirm_password"]').val();

        if (!user) { $(this).find('[name="username"]').addClass('field-error').after('<p class="err-inline err-js">Vui lòng nhập tên đăng nhập.</p>'); ok = false; }
        else if (user.length < 3) { $(this).find('[name="username"]').addClass('field-error').after('<p class="err-inline err-js">Tên đăng nhập phải từ 3 ký tự.</p>'); ok = false; }
        if (!pass) { $(this).find('[name="password"]').closest('.pw-field').addClass('field-error').after('<p class="err-inline err-js">Vui lòng nhập mật khẩu.</p>'); ok = false; }
        else if (pass.length < 6) { $(this).find('[name="password"]').closest('.pw-field').addClass('field-error').after('<p class="err-inline err-js">Mật khẩu phải từ 6 ký tự.</p>'); ok = false; }
        if (pass !== confirm) { $(this).find('[name="confirm_password"]').closest('.pw-field').addClass('field-error').after('<p class="err-inline err-js">Mật khẩu nhập lại không khớp.</p>'); ok = false; }

        if (!ok) e.preventDefault();
    });

    // Password toggle
    $(document).on('click', '.pw-toggle', function() {
        var inp = $(this).siblings('input');
        var isPw = inp.attr('type') === 'password';
        inp.attr('type', isPw ? 'text' : 'password');
        $(this).text(isPw ? '🙈' : '👁');
    });
});
</script>

<style>
.field-error { border-color:#e74c3c !important; }
.err-inline { color:#e74c3c;font-size:13px;margin:-8px 0 12px 0; }
</style>

<?php get_footer(); ?>
