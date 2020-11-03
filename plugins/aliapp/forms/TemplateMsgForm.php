<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\plugins\aliapp\forms;

use app\core\response\ApiCode;
use app\models\Mall;
use app\models\Model;
use app\plugins\aliapp\Plugin;

/**
 * @property Mall $mall
 */
class TemplateMsgForm extends Model
{
    public $mall;
    public $user_id;
    public $tpl_id;

    public function getDetail()
    {
        $plugin = \Yii::$app->plugin->getCurrentPlugin();
        if (method_exists($plugin, 'getTemplateList')) {
            $list = $plugin->getTemplateList();
        } else {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => "当前插件{$plugin->getDisplayName()}不支持"
            ];
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $this->getList($list),
            ]
        ];
    }

    public function getList($list)
    {
        $iconUrlPrefix = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl .
            '/statics/img/mall/tplmsg/aliapp/';

        $default = [
            [
                'name' => '商城模板消息',
                'key' => 'store',
                'list' => [
                    [
                        'name' => '订单进度提醒(模板编号: AT0115 )',
                        'order_pay_tpl' => '',
                        'tpl_name' => 'order_pay_tpl',
                        'img_url' => $iconUrlPrefix . 'order_pay_tpl.png'
                    ],
                    [
                        'name' => '订单取消通知(模板编号: AT0027 )',
                        'order_cancel_tpl' => '',
                        'tpl_name' => 'order_cancel_tpl',
                        'img_url' => $iconUrlPrefix . 'order_cancel_tpl.png'
                    ],
                    [
                        'name' => '订单发货提醒(模板编号: AT0011 )',
                        'order_send_tpl' => '',
                        'tpl_name' => 'order_send_tpl',
                        'img_url' => $iconUrlPrefix . 'order_send_tpl.png'
                    ],
                    [
                        'name' => '退款已被受理通知(模板编号: AT0123 )',
                        'order_refund_tpl' => '',
                        'tpl_name' => 'order_refund_tpl',
                        'img_url' => $iconUrlPrefix . 'order_refund_tpl.png'
                    ],
                    [
                        'name' => '审核结果通知(模板编号: AT0044 )',
                        'audit_result_tpl' => '',
                        'tpl_name' => 'audit_result_tpl',
                        'img_url' => $iconUrlPrefix . 'mch-tpl-1.png'
                    ],
                ]
            ],
            [
                'name' => '分销模板消息',
                'key' => 'share',
                'list' => [
                    [
                        'name' => '提现成功(模板编号: AT0112 )',
                        'withdraw_success_tpl' => '',
                        'tpl_name' => 'withdraw_success_tpl',
                        'img_url' => $iconUrlPrefix . 'withdraw_success_tpl.png'
                    ],
                    [
                        'name' => '提现失败(模板编号: AT0112 )',
                        'withdraw_error_tpl' => '',
                        'tpl_name' => 'withdraw_error_tpl',
                        'img_url' => $iconUrlPrefix . 'withdraw_error_tpl.png'
                    ],
                    [
                        'name' => '温馨提示(模板编号: AT0005 )',
                        'remove_identity_tpl' => '',
                        'tpl_name' => 'remove_identity_tpl',
                        'img_url' => $iconUrlPrefix . 'remove_identity_tpl.png'
                    ],
                ]
            ],
        ];

        if (!$list) {
            return $default;
        }

        $newList = [];
        foreach ($list as $item) {
            $newList[$item['tpl_name']] = $item['tpl_id'];
        }

        foreach ($default as $k => $item) {
            foreach ($item['list'] as $k2 => $item2) {
                if (isset($newList[$item2['tpl_name']])) {
                    $default[$k]['list'][$k2][$item2['tpl_name']] = $newList[$item2['tpl_name']];
                }
            }
        }

        return $default;
    }
}
