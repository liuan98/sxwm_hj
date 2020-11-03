<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: jack_guo
 */

namespace app\plugins\gift\forms\mall;

use app\core\response\ApiCode;
use app\forms\common\CommonOptionP;
use app\plugins\gift\forms\common\CommonGift;
use app\plugins\gift\forms\common\CommonOption;
use app\plugins\gift\models\GiftSetting;
use app\models\Model;
use yii\helpers\ArrayHelper;

class GiftSettingForm extends Model
{
    public $title;
    public $type;
    public $auto_refund;
    public $auto_remind;
    public $bless_word;
    public $ask_gift;

    public $is_share;
    public $is_sms;
    public $is_mail;
    public $is_print;
    public $payment_type;
    public $send_type;
    public $poster;

    public $background;
    public $theme;
    public $explain;


    public function rules()
    {
        return [
            [['auto_refund', 'auto_remind', 'is_share', 'is_sms', 'is_mail', 'is_print'], 'integer'],
            [['poster'], 'trim'],
            [['payment_type', 'type', 'theme', 'background', 'send_type'], 'safe'],
            [['title', 'bless_word', 'ask_gift', 'explain'], 'string']
        ];
    }

    public function attributeLabels()
    {
        return [
            'title' => '标题',
            'type' => '玩法',
            'auto_refund' => '自动退款时间',
            'auto_remind' => '未领提醒时间',
            'bless_word' => '送礼祝福语',
            'ask_gift' => '求礼物话术',
            'is_share' => '是否开启分销',
            'is_sms' => '是否开启短信通知',
            'is_mail' => '是否开启邮件通知',
            'is_print' => '是否开启订单打印',
            'payment_type' => '支付方式',
            'poster' => '自定义海报',
            'background_pic' => '背景图',
            'theme' => '主题'
        ];
    }

    public function getList()
    {
        $setting = CommonGift::getSetting();
        $setting['poster'] = (new CommonOptionP())->poster($setting['poster'], CommonOption::getPosterDefault());
        $setting['default']['poster'] = (new CommonOptionP())->poster($setting['default']['poster'], CommonOption::getPosterDefault());
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => $setting
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        };

        try {
            $model = GiftSetting::findOne([
                'mall_id' => \Yii::$app->mall->id,
            ]);
            if (!$model) {
                $model = new GiftSetting();
            }
            if (!$this->type || empty($this->type)) {
                throw new \Exception('请选择玩法');
            }
            if (!$this->payment_type || empty($this->payment_type)) {
                throw new \Exception('请选择支付方式');
            }
            if (!$this->send_type || empty($this->send_type)) {
                throw new \Exception('请选择发货方式');
            }
            if ($this->auto_refund <= 0 && $this->auto_refund != -1) {
                throw new \Exception('自动退款天数不能为0');
            }
            if ($this->auto_remind == 0 && $this->auto_remind != -1) {
                throw new \Exception('送礼未成功提醒天数不能为0');
            }
            $this->background['left'] = floatval($this->background['left']);
            $this->background['top'] = floatval($this->background['top']);

            $model->type = \Yii::$app->serializer->encode($this->type);
            $model->title = $this->title;
            $model->auto_refund = $this->auto_refund;
            $model->auto_remind = $this->auto_remind;
            $model->bless_word = $this->bless_word;
            $model->ask_gift = $this->ask_gift;
            $model->is_share = $this->is_share;
            $model->is_sms = $this->is_sms;
            $model->is_mail = $this->is_mail;
            $model->is_print = $this->is_print;
            $model->payment_type = \Yii::$app->serializer->encode($this->payment_type);
            $model->mall_id = \Yii::$app->mall->id;
            $model->poster = \Yii::$app->serializer->encode((new CommonOptionP())->saveEnd($this->poster));
            $model->background = \Yii::$app->serializer->encode($this->background);
            $model->theme = \Yii::$app->serializer->encode($this->theme);
            $model->send_type = \Yii::$app->serializer->encode($this->send_type);
            $model->explain = $this->explain;
            if (!$model->save()) {
                throw new \Exception($this->getErrorMsg($model));
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }
}
