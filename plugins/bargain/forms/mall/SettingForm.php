<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/6
 * Time: 15:21
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */

namespace app\plugins\bargain\forms\mall;


use app\core\response\ApiCode;
use app\forms\common\CommonOption;
use app\forms\common\CommonOptionP;
use app\models\Mall;
use app\models\Model;
use app\plugins\bargain\forms\common\CommonSetting;

/**
 * @property Mall $mall
 */
class SettingForm extends Model
{
    protected $mall;

    public $is_share;
    public $is_sms;
    public $is_print;
    public $is_mail;
    public $payment_type;
    public $send_type;
    public $title;
    public $rule;
    public $goods_poster;

    public function rules()
    {
        return [
            [['is_share', 'is_sms', 'is_mail', 'is_print'], 'integer'],
            [['title', 'rule', 'goods_poster'], 'trim'],
            [['title', 'rule'], 'string'],
            [['payment_type', 'send_type'], 'safe']
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $default = CommonSetting::getCommon()->getDefault();
        if (!$this->payment_type || empty($this->payment_type)) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '请选择支付方式'
            ];
        }
        foreach ($default as $key => $value) {
            if (!$this->$key) {
                $this->$key = $value;
            }
        }
        $this->goods_poster = (new CommonOptionP())->saveEnd($this->goods_poster);
        CommonOption::set(CommonSetting::SETTING, $this->attributes, $this->mall->id, 'plugin');

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '保存成功'
        ];
    }

    public function setMall($mall)
    {
        $this->mall = $mall;
    }

    public function getList()
    {
        $list = CommonSetting::getCommon()->getList();
        $list['goods_poster'] = (new CommonOptionP())->poster($list['goods_poster']);
        foreach ($list as &$item) {
            if (is_numeric($item)) {
                $item = floatval($item);
            }
        }
        unset($item);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'list' => $list
            ]
        ];
    }
}
