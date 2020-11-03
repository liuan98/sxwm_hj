<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/5/27
 * Time: 10:38
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */

namespace app\plugins\bargain\forms\common;


use app\forms\common\CommonOption;
use app\forms\common\CommonOptionP;
use app\forms\common\version\Compatible;
use app\helpers\PluginHelper;
use app\models\Mall;
use app\models\Model;
use app\plugins\bargain\models\Code;

/**
 * Class CommonSetting
 * @package app\plugins\bargain\forms\common
 * @property Mall $mall
 */
class CommonSetting extends Model
{
    const SETTING = 'bargain_setting';
    public $mall;
    public static function getCommon($mall = null)
    {
        $form = new self();
        if (!$mall) {
            $mall = \Yii::$app->mall;
        }
        $form->mall = $mall;
        return $form;
    }

    public function getDefault()
    {
        return [
            'is_share' => Code::CLOSED, // 是否开启分销
            'is_sms' => Code::CLOSED, // 是否开启短信提醒
            'is_mail' => Code::CLOSED, // 是否开启邮件提醒
            'is_print' => Code::CLOSED, // 是否开启打印
            'payment_type' => ['online_pay'], // 支付方式
            'send_type' => ['express', 'offline'], // 发货方式
            'rule' => '', // 活动规则
            'title' => '', // 活动标题
            'goods_poster' => self::getPosterDefault(),
        ];
    }

    public function getList()
    {
        $list = CommonOption::get(self::SETTING, $this->mall->id, 'plugin', $this->getDefault());
        foreach ($list as $key => &$item) {
            if (is_numeric($item)) {
                $item = floatval($item);
            }
            if ($key == 'send_type') {
                $item = Compatible::getInstance()->sendType($item);
            }
        }
        unset($item);

        $list['goods_poster'] = $list['goods_poster'] ?? self::getPosterDefault();
        return $list;
    }

    public static function getPosterDefault()
    {
        if (!isset(\Yii::$app->request->hostInfo)) {
            return [];
        }

        $iconBaseUrl = PluginHelper::getPluginBaseAssetsUrl('bargain') . '/img/';
        return [
            'bg_pic' => [
                'url' => \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/statics/img/mall/poster_bg.png',
            ],
            'pic' => [
                'is_show' => '1',
                'width' => 345,
                'height' => 345,
                'top' => 100,
                'left' => 15,
                'file_type' => 'image',
            ],
            'head' => [
                'is_show' => '1',
                'size' => 34,
                'top' => 44,
                'left' => 15,
                'file_type' => 'image',
            ],
            'nickname' => [
                'is_show' => '1',
                'font' => 13.5,
                'top' => 57,
                'left' => 64,
                'text' => '小明',
                'color' => '#5b85cf',
                'file_type' => 'text',
            ],

            'nickname_share' => [
                'is_show' => '0',
                'font' => 13.5,
                'top' => 57,
                'left' => 0,
                'text' => '分享给你一个商品',
                'color' => '#353535',
                'file_type' => 'text',
            ],

            'poster_bg' => [
                'is_show' => '1',
                'width' => 345,
                'height' => 60,
                'top' => 385,
                'left' => 15,
                'file_path' => $iconBaseUrl . 'bargain-hb-good.png',
                'file_type' => 'image',
            ],

            'time_str' => [
                'is_show' => '1',
                'font' => 13.5,
                'top' => 422,
                'left' => 200,
                'text' => '01.24 12:00',
                'color' => '#ffffff',
                'file_type' => 'text',
            ],

            'qr_code' => [
                'is_show' => '1',
                'size' => 80,
                'top' => 528,
                'left' => 268,
                'type' => '2',
                'file_path' => '',
                'file_type' => 'image',
            ],

            'name' => [
                'is_show' => '1',
                'font' => 19,
                'top' => 467,
                'left' => 15,
                'color' => '#353535',
                'file_type' => 'text',
            ],

            'desc' => [
                'is_show' => '1',
                'font' => 16,
                'top' => 581,
                'left' => 15,
                'width' => 375,
                'text' => '长按识别小程序码',
                'color' => '#999999',
                'file_type' => 'text',
            ],

            'price' => [
                'is_show' => '1',
                'font' => 20,
                'top' => 554,
                'left' => 15,
                'color' => '#ff5c5c',
                'text' => '最低￥10',
                'file_type' => 'text',
            ],
        ];
    }
}
