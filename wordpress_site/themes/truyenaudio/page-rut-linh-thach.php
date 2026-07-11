<?php /* Template Name: Rút Linh Thạch */
ta_require_role(['tac_gia_role', 'administrator']);
get_header();
$user = wp_get_current_user();

$balance = get_user_meta($user->ID, '_linh_thach', true) ?: 0;
$withdrawn = get_user_meta($user->ID, '_author_withdrawn', true) ?: 0;
$available = $balance - $withdrawn;
$min_wd = get_option('ta_min_withdrawal', 10000);
$lt_vnd = get_option('ta_lt_to_vnd', 1000);
$wd_fee = get_option('ta_withdrawal_fee', 3);
?>

<div class="container" style="padding:40px 15px;">
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:15px;margin-bottom:30px;">
        <h1 style="color:#fff;">💎 Rút Linh Thạch</h1>
        <a href="<?php echo home_url('/tac-gia-dashboard'); ?>" class="btn btn-outline">← Quay lại Dashboard</a>
    </div>

    <!-- Balance -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;margin-bottom:30px;">
        <div class="profile-card" style="text-align:center;">
            <div style="color:#888;font-size:13px;">Linh Thạch tồn kho</div>
            <div style="font-size:32px;font-weight:700;color:#2ecc71;margin:10px 0;">💎<?php echo number_format($balance, 1); ?></div>
        </div>
        <div class="profile-card" style="text-align:center;">
            <div style="color:#888;font-size:13px;">Đã rút</div>
            <div style="font-size:32px;font-weight:700;color:#e74c3c;margin:10px 0;">💎<?php echo number_format($withdrawn); ?></div>
        </div>
        <div class="profile-card" style="text-align:center;">
            <div style="color:#888;font-size:13px;">Có thể rút</div>
            <div style="font-size:32px;font-weight:700;color:#f0c040;margin:10px 0;">💎<?php echo number_format(max(0, $available)); ?></div>
            <div style="color:#888;font-size:12px;">≈ <?php echo number_format(max(0, $available) * $lt_vnd); ?>₫</div>
        </div>
    </div>

    <!-- Withdrawal Form -->
    <div class="profile-card" style="max-width:500px;margin:0 auto;">
        <h3>📤 Yêu cầu rút tiền</h3>
        <p style="color:#888;font-size:13px;margin-bottom:20px;">Rút tối thiểu <strong><?php echo number_format($min_wd); ?> Linh Thạch</strong></p>

        <form id="withdrawal-form">
            <label style="display:block;font-size:13px;color:#888;margin-bottom:4px;">Số lượng Linh Thạch</label>
            <input type="number" id="wd-amount" min="<?php echo $min_wd; ?>" max="<?php echo $available; ?>" value="<?php echo $min_wd; ?>" style="width:100%;background:#0f0f1a;color:#e0e0e0;border:1px solid #2a2a4e;padding:10px 14px;border-radius:6px;margin-bottom:15px;font-size:14px;" required>

            <label style="display:block;font-size:13px;color:#888;margin-bottom:4px;">Phương thức nhận tiền</label>
            <select id="wd-method" style="width:100%;background:#0f0f1a;color:#e0e0e0;border:1px solid #2a2a4e;padding:10px 14px;border-radius:6px;margin-bottom:15px;font-size:14px;" required>
                <option value="">-- Chọn phương thức --</option>
                <option value="Banking">Chuyển khoản ngân hàng</option>
                <option value="Momo">Ví Momo</option>
                <option value="ZaloPay">ZaloPay</option>
            </select>

            <label style="display:block;font-size:13px;color:#888;margin-bottom:4px;">Thông tin tài khoản</label>
            <input type="text" id="wd-account" placeholder="Số tài khoản / SĐT / STK" style="width:100%;background:#0f0f1a;color:#e0e0e0;border:1px solid #2a2a4e;padding:10px 14px;border-radius:6px;margin-bottom:20px;font-size:14px;" required>

            <p id="wd-preview" style="color:#888;font-size:13px;margin-bottom:15px;padding:10px;background:#0f0f1a;border-radius:6px;text-align:center;">
                Rút <strong id="preview-amount" style="color:#f0c040;"><?php echo number_format($min_wd); ?></strong> LT |
                Phí <strong id="preview-fee" style="color:#e74c3c;"><?php echo number_format(floor($min_wd * $wd_fee / 100)); ?></strong> LT (<?php echo $wd_fee; ?>%) |
                Thực nhận <strong id="preview-net" style="color:#2ecc71;"><?php echo number_format($min_wd - floor($min_wd * $wd_fee / 100)); ?></strong> LT ≈ <strong id="preview-vnd" style="color:#fff;"><?php echo number_format(($min_wd - floor($min_wd * $wd_fee / 100)) * $lt_vnd); ?>₫</strong>
            </p>

            <button type="submit" id="wd-btn" class="btn btn-primary" style="width:100%;font-size:16px;padding:12px;">Gửi yêu cầu rút</button>
            <div id="wd-status" style="margin-top:15px;"></div>
        </form>
    </div>
</div>

<script>
var ajaxurl = typeof ta_ajax !== 'undefined' ? ta_ajax.ajax_url : '<?php echo admin_url('admin-ajax.php'); ?>';

jQuery(function($) {
    function updatePreview() {
        var amount = parseInt($('#wd-amount').val()) || 0;
        var rate = <?php echo $lt_vnd; ?>;
        var feePct = <?php echo $wd_fee; ?>;
        var fee = Math.floor(amount * feePct / 100);
        var net = amount - fee;
        $('#preview-amount').text(amount.toLocaleString());
        $('#preview-fee').text(fee.toLocaleString());
        $('#preview-net').text(net.toLocaleString());
        $('#preview-vnd').text((net * rate).toLocaleString() + '₫');
    }
    $('#wd-amount').on('input', updatePreview);

    $('#withdrawal-form').on('submit', function(e) {
        e.preventDefault();
        var $btn = $('#wd-btn').prop('disabled', true).text('Đang xử lý...');
        var $status = $('#wd-status').empty();

        var amount = parseInt($('#wd-amount').val());
        var method = $('#wd-method').val();
        var account = $('#wd-account').val();

        if (amount < <?php echo $min_wd; ?>) {
            $status.html('<p style="color:#e74c3c;">Số lượng rút tối thiểu là <?php echo number_format($min_wd); ?> Linh Thạch</p>');
            $btn.prop('disabled', false).text('Gửi yêu cầu rút');
            return;
        }
        if (amount > <?php echo $available; ?>) {
            $status.html('<p style="color:#e74c3c;">Số dư không đủ</p>');
            $btn.prop('disabled', false).text('Gửi yêu cầu rút');
            return;
        }

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: { action: 'ta_request_withdrawal', amount: amount, method: method, account_info: account },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    ta_toast('✅ ' + res.data.message, 'success');
                    setTimeout(function() { window.location.href = '<?php echo home_url('/tac-gia-dashboard'); ?>'; }, 1500);
                } else {
                    ta_toast(res.data, 'error');
                    $status.html('<p style="color:#e74c3c;">❌ ' + res.data + '</p>');
                    $btn.prop('disabled', false).text('Gửi yêu cầu rút');
                }
            },
            error: function(xhr, status, err) {
                ta_toast('Lỗi kết nối', 'error');
                $status.html('<p style="color:#e74c3c;">❌ Lỗi: ' + err + '</p>');
                $btn.prop('disabled', false).text('Thử lại');
            }
        });
    });
});
</script>

<?php get_footer(); ?>
