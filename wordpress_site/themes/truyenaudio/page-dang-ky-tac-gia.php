<?php /* Template Name: Đăng ký tác giả */
ta_require_auth();
get_header();
$user = wp_get_current_user();
$is_author = in_array('tac_gia_role', (array) $user->roles);
$is_admin = in_array('administrator', (array) $user->roles);
?>

<div class="container" style="padding:40px 15px;">
    <div class="profile-card" style="max-width:600px;margin:0 auto;text-align:center;">
        <?php if ($is_admin): ?>
            <h2>👑 Admin</h2>
            <p style="color:#888;margin:15px 0;">Bạn là Admin, đã có toàn quyền đăng truyện.</p>
            <a href="<?php echo admin_url('post-new.php?post_type=truyen'); ?>" class="btn btn-primary">Đăng truyện ngay</a>
        <?php elseif ($is_author): ?>
            <h2>✍️ Bạn đã là Tác giả</h2>
            <div style="margin:20px 0;">
                <p style="color:#888;">Bạn đã có quyền đăng và quản lý truyện.</p>
            </div>
            <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;">
                <a href="<?php echo admin_url('post-new.php?post_type=truyen'); ?>" class="btn btn-primary">Đăng truyện mới</a>
                <a href="<?php echo home_url('/tac-gia-dashboard'); ?>" class="btn btn-outline">Dashboard tác giả</a>
            </div>
        <?php else: ?>
            <h2>✍️ Đăng ký trở thành Tác giả</h2>
            <p style="color:#888;margin:15px 0;">Bạn muốn đăng truyện lên TruyenAudio? Hãy đăng ký trở thành tác giả ngay!</p>

            <div style="text-align:left;margin:20px 0;padding:20px;background:#0f0f1a;border-radius:8px;">
                <p style="margin-bottom:10px;">✅ Đăng truyện không giới hạn</p>
                <p style="margin-bottom:10px;">✅ Kiếm Linh Thạch từ VIP chapter</p>
                <p style="margin-bottom:10px;">✅ Nhận hoa hồng từ doanh thu truyện</p>
                <p style="margin-bottom:10px;">✅ Rút Linh Thạch về tài khoản</p>
            </div>

            <div id="upgrade-status" style="display:none;"></div>
            <button id="upgrade-btn" class="btn btn-primary" style="font-size:16px;padding:12px 40px;">Đăng ký ngay</button>
            <p style="color:#666;font-size:12px;margin-top:10px;">Miễn phí, không cần admin phê duyệt</p>
        <?php endif; ?>
    </div>
</div>

<?php if (!$is_author && !$is_admin): ?>
<script>
jQuery(function($) {
    $('#upgrade-btn').on('click', function() {
        var $btn = $(this).prop('disabled', true).text('Đang xử lý...');
        var $status = $('#upgrade-status').show();

        $.post(ta_ajax.ajax_url, {
            action: 'ta_upgrade_to_author'
        }, function(res) {
            if (res.success) {
                ta_toast('🎉 ' + res.data.message, 'success');
                setTimeout(function() { location.reload(); }, 1500);
            } else {
                ta_toast(res.data, 'error');
                $btn.prop('disabled', false).text('Đăng ký ngay');
            }
        });
    });
});
</script>
<?php endif; ?>

<?php get_footer(); ?>
