<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/27
 * Time: 9:40
 */

namespace app\forms\common;


class AppImg
{
    public static function search()
    {
        $url = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/statics/img/app';
        return [
            'common' => [
                'address_bottom' => $url . '/common/address-bottom.png',
                'payment_alipay' => $url . '/common/payment-alipay.png',
                'payment_balance' => $url . '/common/payment-balance.png',
                'payment_huodao' => $url . '/common/payment-huodao.png',
                'payment_wechat' => $url . '/common/payment-wechat.png'
            ],
            'mall' => [
                'close' => $url . '/mall/icon-close.png',
                'order' => [
                    'status_bar' => $url . '/mall/order/status-bar.png'
                ],
                'balance' => $url . '/mall/icon-balance.png',
                'huodao' => $url . '/mall/icon-huodao.png',
                'online' => $url . '/mall/icon-online.png',
                'coupon_enable_bg' => $url . '/mall/img-coupon-enable-bg.png',
                'coupon_disable_bg' => $url . '/mall/img-coupon-disable-bg.png',
                'order_pay_success' => $url . '/mall/img-order-pay-success.png',
                'order_pay_result_coupon' => $url . '/mall/img-order-pay-result-coupon.png',
                'icon_wechat' => $url . '/mall/icon-wechatapp.png',
                'icon_alipay' => $url . '/mall/icon-alipay.png',
                'icon_ttapp' => $url . '/mall/icon-ttapp.png',
                'disabled' => $url . '/mall/img-disabled.png',
                'binding' => $url . '/mall/binding_pic.png',
                'list_out' => $url . '/mall/list-out.png',
                'book_out' => $url . '/mall/book-out.png',
                'rate_out' => $url . '/mall/rate-out.png',
                'one_out' => $url . '/mall/one-out.png',
                'plugins_out' => $url . '/mall/plugins-out.png',
                'balance_recharge' => $url . '/mall/icon-balance-recharge-bg.png',
                'loading' => $url . '/mall/loading.gif',
            ],
            'share' => [
                'apply' => $url . '/share/img-share-apply.png',
                'status' => $url . '/share/img-share-status.png',
                'poster_load' => $url . '/share/loading.gif',
                'sharebg' => $url . '/share/sharebg.png',
                'level_btn' => $url . '/share/level-btn.png',
                'dialog_success' => $url . '/share/dialog-success.png',
                'dialog_error' => $url . '/share/dialog-error.png',
                'no_level_bg' => $url . '/share/no-level-bg.png',
            ],
            'foot' => [
                'total_bg' => $url . '/footprint/total.png',
                'buy_bg' => $url . '/footprint/buy.png',
                'coupon_bg' => $url . '/footprint/coupon.png',
                'day_bg' => $url . '/footprint/day.png',
                'high_bg' => $url . '/footprint/high.png',
                'member_bg' => $url . '/footprint/member.png',
                'open_bg' => $url . '/footprint/open.png',
                'total' => $url . '/footprint/total.gif',
                'buy' => $url . '/footprint/buy.gif',
                'coupon_icon' => $url . '/footprint/coupon-icon.png',
                'index' => $url . '/footprint/index.png',
                'member_icon' => $url . '/footprint/member-icon.png',
                'rate_icon' => $url . '/footprint/rate-icon.png',
                'day_icon' => $url . '/footprint/day-icon.png',
                'high_icon' => $url . '/footprint/high-icon.png',
                'hycyj' => $url . '/footprint/hycyj.ttf',
            ],
            'coupon' => [
                'get_coupon_bg' => $url . '/coupon/img-get-coupon-bg.png',
                'get_coupon_item_bg' => $url . '/coupon/img-get-coupon-item-bg.png',
                'get_coupon_title' => $url . '/coupon/img-get-coupon-title.png',
                'get_coupon_share' => $url . '/coupon/img-get-coupon-share.png',
                'get_coupon_receive' => $url . '/coupon/img-get-coupon-receive.png',
                'coupon_disabled' => $url . '/coupon/icon-coupon-disabled.png',
                'coupon_enabled' => $url . '/coupon/icon-coupon-enabled.png',
                'discount_coupon' => $url . '/coupon/discount-coupon.png',
                'discount_receive' => $url . '/coupon/discount-receive.png',
            ],
            'member' => [
                'bg' => $url . '/member/BG.png',
                'card' => $url . '/member/card-1.png',
                'coupon' => $url . '/member/coupon.png',
                'goods' => $url . '/member/goods.png',
                'coupon_index' => $url . '/member/icon-coupon-index.png',
                'more' => $url . '/member/more.png',
                'up' => $url . '/member/up.png',
                'banner' => $url . '/member/banner.png',
                'card_bottom' => $url . '/member/card-bottom.png',
                'icon_user_level' => $url . '/member/icon-user-level.png',
                'member_left' => $url . '/member/member-left.png',
                'member_right' => $url . '/member/member-right.png',
                'one' => $url . '/member/one.png',
                'two' => $url . '/member/two.png',
            ],
            'bonus' => [
                'banner' => $url . '/bonus/banner.png',
                'right' => $url . '/bonus/right.png',
                'wait' => $url . '/bonus/wait.png',
                'progress' => $url . '/bonus/progress.png',
                'member' => $url . '/bonus/member.png',
                'order' => $url . '/bonus/order.png',
                'success' => $url . '/bonus/success.png',
            ],
            'app_admin' => [
                'bg' => $url . '/app_admin/bg.png',
                'cash' => $url . '/app_admin/cash.png',
                'comment' => $url . '/app_admin/comment.png',
                'msg' => $url . '/app_admin/msg.png',
                'user' => $url . '/app_admin/user.png',
                'order' => $url . '/app_admin/order.png',
                'detail_bg' => $url . '/app_admin/detail-bg.png',
                'no_order' => $url . '/app_admin/no-order.png',
                'no_comment' => $url . '/app_admin/no-comment.png',
                'no_goods' => $url . '/app_admin/no-goods.png',
                'no_apply' => $url . '/app_admin/no-apply.png',
                'no_message' => $url . '/app_admin/no-message.png',
                'no_user' => $url . '/app_admin/no-user.png'
            ],
            'clerk' => [
                'detail' => $url . '/clerk/detail.png',
                'qr' => $url . '/clerk/qr.png',
                'order' => $url . '/clerk/order.png',
                'card' => $url . '/clerk/card.png',
            ],
            'mch' => [
                'detail_bg' => $url . '/mch/detail-bg.png',
                'no_order' => $url . '/mch/no-order.png',
                'no_comment' => $url . '/mch/no-comment.png',
                'no_goods' => $url . '/mch/no-goods.png',
                'no_apply' => $url . '/mch/no-apply.png',
                'no_message' => $url . '/mch/no-message.png',
                'no_user' => $url . '/mch/no-user.png'
            ],
            'vip_card' => [
                'logo' => $url . '/vip_card/logo.png',
                'icon1' => $url . '/vip_card/icon1.png',
                'icon2' => $url . '/vip_card/icon2.png',
                'icon3' => $url . '/vip_card/icon3.png',
                'icon4' => $url . '/vip_card/icon4.png',
                'icon5' => $url . '/vip_card/icon5.png',
                'icon6' => $url . '/vip_card/icon6.png',
                'icon7' => $url . '/vip_card/icon7.png',
                'balance' => $url . '/vip_card/balance.png',
                'card' => $url . '/vip_card/card.png',
                'coupon' => $url . '/vip_card/coupon.png',
                'integral' => $url . '/vip_card/integral.png',
                'left' => $url . '/vip_card/left.png',
                'right' => $url . '/vip_card/right.png',
                'shipping' => $url . '/vip_card/free-shipping.png',
                'off' => $url . '/vip_card/off.png',
                'card_bottom' => $url . '/vip_card/card-bottom.png',
                'buy_bg' => $url . '/vip_card/buy_bg.png',
                'default_card' => $url . '/vip_card/default-card.png',
            ],
            'stock' => [
                'index' => $url . '/stock/index.png',
                'success' => $url . '/stock/success.png',
                'bonus' => $url . '/stock/bonus.png',
                'banner' => $url . '/stock/banner.png',
                'foot' => $url . '/stock/foot.png',
                'get' => $url . '/stock/get.png',
                'max' => $url . '/stock/max.png',
                'update' => $url . '/stock/update.png'
            ]
        ];
    }
}
