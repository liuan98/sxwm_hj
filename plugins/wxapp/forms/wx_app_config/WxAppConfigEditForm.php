<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\plugins\wxapp\forms\wx_app_config;


use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\wxapp\models\WxappConfig;
use luweiss\Wechat\Wechat;
use luweiss\Wechat\WechatException;
use luweiss\Wechat\WechatPay;

class WxAppConfigEditForm extends Model
{
    public $appid;
    public $appsecret;
    public $cert_pem;
    public $key;
    public $key_pem;
    public $mchid;
    public $id;

    public function rules()
    {
        return [
            [['appid', 'appsecret', 'key', 'mchid'], 'required'],
            [['appid', 'appsecret', 'key_pem', 'cert_pem',], 'string'],
            [['key', 'mchid'], 'string', 'max' => 32],
            [['id'], 'integer']
        ];
    }

    public function attributeLabels()
    {
        return [
            'appid' => '小程序AppId',
            'appsecret' => '小程序appSecret',
            'key' => '微信支付Api密钥',
            'mchid' => '微信支付商户号',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $config = [
            'appId' => $this->appid,
            'mchId' => $this->mchid,
            'key' => $this->key,
        ];
        if ($this->cert_pem && $this->key_pem) {
            $pemDir = \Yii::$app->runtimePath . '/pem';
            make_dir($pemDir);
            $certPemFile = $pemDir . '/' . md5($this->cert_pem);
            if (!file_exists($certPemFile)) {
                file_put_contents($certPemFile, $this->cert_pem);
            }
            $keyPemFile = $pemDir . '/' . md5($this->key_pem);
            if (!file_exists($keyPemFile)) {
                file_put_contents($keyPemFile, $this->key_pem);
            }
            $config['certPemFile'] = $certPemFile;
            $config['keyPemFile'] = $keyPemFile;
        }
        // 检测参数是否有效
        $wechatPay = new WechatPay($config);
        try {
            if (!$this->appid) {
                throw new \Exception('小程序AppId有误');
            }
            if (!$this->appsecret) {
                throw new \Exception('小程序appSecret有误');
            }
            $wechat = new Wechat([
                'appId' => $this->appid,
                'appSecret' => $this->appsecret,
            ]);
            $wechat->getAccessToken(true);
        } catch (WechatException $e) {
            if ($e->getRaw()['errcode'] == '40013') {
                $message = '小程序AppId有误(' . $e->getRaw()['errmsg'] . ')';
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => $message,
                ];
            }
            if ($e->getRaw()['errcode'] == '40125') {
                $message = '小程序appSecret有误(' . $e->getRaw()['errmsg'] . ')';
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => $message,
                ];
            }
        }

        try {
            if ($this->mchid || $this->key) {
                $wechatPay->orderQuery(['out_trade_no' => '88888888']);
            }
        } catch (WechatException $e) {
            if ($e->getRaw()['return_code'] != 'SUCCESS') {
                $message = '微信支付商户号 或 微信支付Api密钥有误(' . $e->getRaw()['return_msg'] . ')';
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => $message,
                ];
            }
        }


        try {
            if ($this->id) {
                $wxAppConfig = WxappConfig::findOne(['mall_id' => \Yii::$app->mall->id, 'id' => $this->id]);

                if (!$wxAppConfig) {
                    return [
                        'code' => ApiCode::CODE_ERROR,
                        'msg' => '数据异常,该条数据不存在',
                    ];
                }
            } else {
                $wxAppConfig = new WxappConfig();
            }

            $wxAppConfig->mall_id = \Yii::$app->mall->id;
            $wxAppConfig->appid = $this->appid;
            $wxAppConfig->appsecret = $this->appsecret;
            $wxAppConfig->key = $this->key;
            $wxAppConfig->mchid = $this->mchid;
            $wxAppConfig->key_pem = $this->key_pem;
            $wxAppConfig->cert_pem = $this->cert_pem;
            $res = $wxAppConfig->save();

            if ($res) {
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '保存成功',
                ];
            }

            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '保存失败',
            ];


        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }
}
