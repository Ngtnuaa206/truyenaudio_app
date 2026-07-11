<?php /* Template Name: Linh Thạch */ get_header(); ?>

<div class="container" style="padding:40px 15px;">
    <h1 style="color:#fff;margin-bottom:20px;">💎 Mua Linh Thạch</h1>

    <?php if (!is_user_logged_in()): ?>
        <div class="profile-card" style="text-align:center;padding:60px;">
            <p style="font-size:18px;">Vui lòng <a href="<?php echo home_url('/dang-nhap'); ?>">đăng nhập</a> để nạp Linh Thạch.</p>
        </div>
    <?php else:
        $lt = get_user_meta(get_current_user_id(), '_linh_thach', true) ?: 0;
        $packages = get_option('ta_lt_packages', []);
        $history = get_user_meta(get_current_user_id(), '_lt_history', true) ?: [];
    ?>
        <div class="profile-card">
            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:15px;">
                <div>
                    <h3>Số dư hiện tại</h3>
                    <div class="linh-thach-box" style="margin-top:10px;">
                        <span class="lt-icon">💎</span>
                        <span style="font-size:20px;"><strong id="ta-balance"><?php echo number_format($lt); ?></strong> Linh Thạch</span>
                    </div>
                </div>
                <a href="<?php echo home_url('/profile'); ?>" class="btn btn-outline">← Quay lại</a>
            </div>
        </div>

        <div class="profile-card">
            <h3>Linh Thạch là gì?</h3>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;margin-top:15px;">
                <div><h4 style="color:#f0c040;">💎 Đơn vị tiền tệ cao cấp</h4><p style="color:#888;font-size:13px;">Chìa khóa vạn năng giúp bạn mở khóa nội dung VIP.</p></div>
                <div><h4 style="color:#f0c040;">🔓 Mở khóa VIP</h4><p style="color:#888;font-size:13px;">Mua các chương truyện VIP chất lượng cao.</p></div>
                <div><h4 style="color:#f0c040;">♾️ Vĩnh viễn</h4><p style="color:#888;font-size:13px;">Linh Thạch trong ví không bao giờ hết hạn.</p></div>
            </div>
        </div>

        <div class="profile-card">
            <h3>Chọn gói nạp</h3>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:15px;margin-top:20px;">
                <?php foreach ($packages as $pkg): ?>
                <div class="pkg-card" data-pkg-id="<?php echo $pkg['id']; ?>" data-lt="<?php echo $pkg['lt']; ?>" data-vnd="<?php echo $pkg['vnd']; ?>" style="background:#0f0f1a;border:1px solid #2a2a4e;border-radius:10px;padding:20px;text-align:center;cursor:pointer;transition:.2s;">
                    <div style="font-size:36px;margin-bottom:10px;">💎</div>
                    <div style="font-size:26px;font-weight:700;color:#f0c040;"><?php echo number_format($pkg['lt']); ?></div>
                    <?php if ($pkg['bonus'] > 0): ?>
                        <div style="font-size:12px;color:#2ecc71;">+<?php echo $pkg['bonus']; ?>% bonus</div>
                    <?php endif; ?>
                    <div style="font-size:16px;font-weight:600;color:#fff;margin:10px 0;"><?php echo number_format($pkg['vnd']); ?>₫</div>
                    <button class="btn btn-primary btn-sm ta-buy-pkg" style="width:100%;">Mua ngay</button>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="profile-card" id="ta-payment-section" style="display:none;">
            <h3>💳 Thanh toán</h3>
            <div id="ta-payment-info" style="margin-top:15px;">
                <p style="color:#888;">Đang tạo đơn...</p>
            </div>
        </div>

        <?php if (!empty($history)): ?>
        <div class="profile-card">
            <h3>📋 Lịch sử giao dịch</h3>
            <div style="overflow-x:auto;margin-top:15px;">
                <table style="width:100%;border-collapse:collapse;">
                    <thead>
                        <tr style="border-bottom:1px solid #2a2a4e;">
                            <th style="padding:10px;text-align:left;color:#888;font-size:12px;">Thời gian</th>
                            <th style="padding:10px;text-align:center;color:#888;font-size:12px;">Loại</th>
                            <th style="padding:10px;text-align:center;color:#888;font-size:12px;">Số lượng</th>
                            <th style="padding:10px;text-align:left;color:#888;font-size:12px;">Ghi chú</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_reverse($history) as $h): ?>
                        <tr style="border-bottom:1px solid #2a2a4e;">
                            <td style="padding:10px;color:#888;font-size:13px;"><?php echo $h['time']; ?></td>
                            <td style="padding:10px;text-align:center;">
                                <?php if ($h['type'] === 'deposit'): ?><span style="color:#2ecc71;">+ Nạp</span>
                                <?php elseif ($h['type'] === 'purchase'): ?><span style="color:#f0c040;">- Mua</span>
                                <?php elseif ($h['type'] === 'withdrawal'): ?><span style="color:#e74c3c;">- Rút</span>
                                <?php elseif ($h['type'] === 'withdrawal_request'): ?><span style="color:#e67e22;">⏳ Rút</span>
                                <?php elseif ($h['type'] === 'earn'): ?><span style="color:#3498db;">+ Thu nhập</span>
                                <?php else: ?><span><?php echo $h['type']; ?></span><?php endif; ?>
                            </td>
                            <td style="padding:10px;text-align:center;font-weight:700;">
                                <?php if (in_array($h['type'], ['deposit', 'earn'])): ?>+<?php else: ?>-<?php endif; ?>💎<?php echo number_format(abs($h['amount'])); ?>
                            </td>
                            <td style="padding:10px;color:#888;font-size:13px;"><?php echo $h['note']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

    <?php endif; ?>
</div>

<style>
.pkg-card:hover { border-color: #f0c040 !important; transform: translateY(-2px); }
.pkg-card.selected { border-color: #2ecc71 !important; background: #0d2e1a !important; }
#ta-payment-section { border: 1px solid #3498db; }
</style>

<script>
// Ensure ta_ajax is defined
var ajaxurl = typeof ta_ajax !== 'undefined' ? ta_ajax.ajax_url : '<?php echo admin_url('admin-ajax.php'); ?>';

jQuery(function($) {
    // Buy package
    $('.ta-buy-pkg').on('click', function() {
        var $card = $(this).closest('.pkg-card');
        var pkgId = $card.data('pkg-id');

        $card.addClass('selected').siblings().removeClass('selected');
        $('#ta-payment-section').show();
        $('#ta-payment-info').html('<p style="color:#888;">Đang tạo đơn...</p>');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: { action: 'create_lt_order', package_id: pkgId },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    var data = res.data;
                    $('#ta-payment-info').html(
                        '<div style="background:#0f0f1a;border-radius:8px;padding:20px;">' +
                            '<p><strong>Mã đơn:</strong> <span style="color:#f0c040;">' + data.order_code + '</span></p>' +
                            '<p><strong>Gói:</strong> 💎' + number_format(data.lt) + ' Linh Thạch</p>' +
                            '<p><strong>Số tiền:</strong> ' + number_format(data.vnd) + '₫</p>' +
                            '<hr style="border-color:#2a2a4e;margin:15px 0;">' +
                            '<p style="color:#f39c12;">🧪 Chế độ test — nhấn "Xác nhận thanh toán" để nhận LT ngay.</p>' +
                            '<button class="btn btn-primary ta-confirm-pay" data-order-id="' + data.order_id + '" style="margin-top:10px;">✅ Xác nhận thanh toán</button>' +
                            '<div id="ta-pay-status" style="margin-top:15px;"></div>' +
                        '</div>'
                    );
                } else {
                    $('#ta-payment-info').html('<p style="color:#e74c3c;">❌ ' + res.data + '</p>');
                }
            },
            error: function(xhr, status, err) {
                $('#ta-payment-info').html('<p style="color:#e74c3c;">❌ Lỗi: ' + err + '</p>' +
                    '<p style="color:#888;font-size:12px;margin-top:8px;">Phản hồi: ' + (xhr.responseText || 'trống') + '</p>');
            }
        });
    });

    // Confirm payment (test mode)
    $('#ta-payment-info').on('click', '.ta-confirm-pay', function() {
        var $btn = $(this).prop('disabled', true).text('Đang xử lý...');
        var orderId = $btn.data('order-id');
        var $status = $('#ta-pay-status');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: { action: 'confirm_lt_payment', order_id: orderId },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    ta_toast('🎉 ' + res.data.message, 'success');
                    $('#ta-balance').text(number_format(res.data.new_balance));
                    $status.html('<p style="color:#2ecc71;">✅ ' + res.data.message + '</p>');
                    $btn.remove();
                } else {
                    ta_toast(res.data, 'error');
                    $status.html('<p style="color:#e74c3c;">❌ ' + res.data + '</p>');
                    $btn.prop('disabled', false).text('✅ Xác nhận thanh toán lại');
                }
            },
            error: function(xhr, status, err) {
                ta_toast('Lỗi kết nối: ' + err, 'error');
                $status.html('<p style="color:#e74c3c;">❌ Lỗi: ' + err + '</p>');
                $btn.prop('disabled', false).text('✅ Thử lại');
            }
        });
    });
});
</script>

<?php get_footer(); ?>
