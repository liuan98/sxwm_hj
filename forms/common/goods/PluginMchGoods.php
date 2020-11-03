<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\forms\common\goods;


use app\core\response\ApiCode;
use app\forms\common\mptemplate\MpTplMsgSend;
use app\models\Goods;
use app\models\Model;
use app\plugins\mch\models\MchGoods;

class PluginMchGoods extends Model
{
    public $goods_id;
    public $mch_id;

    public function rules()
    {
        return [
            [['goods_id', 'mch_id'], 'required'],
            [['goods_id', 'mch_id'], 'integer'],
        ];
    }

    /**
     * 多商户申请上架
     * @return array
     */
    public function applyStatus()
    {
        try {
            $mchGoods = MchGoods::findOne([
                'goods_id' => $this->goods_id,
                'mch_id' => $this->mch_id,
                'mall_id' => \Yii::$app->mall->id
            ]);
            if (!$mchGoods) {
                throw new \Exception('商品不存在');
            }
            $mchGoods->status = 1;
            $mchGoods->remark = '申请上架';
            $res = $mchGoods->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($mchGoods));
            }
            $this->sendMpTplMsg();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '申请成功'
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }

    /**
     * 发给管理员公众号消息
     */
    private function sendMpTplMsg()
    {
        try {
            $goods = Goods::findOne([
                'mall_id' => \Yii::$app->mall->id,
                'id' => $this->goods_id
            ]);
            $tplMsg = new MpTplMsgSend();
            $tplMsg->method = 'mchGoodApplyTpl';
            $tplMsg->params = [
                'goods' => $goods->goodsWarehouse->name
            ];
            $tplMsg->sendTemplate(new MpTplMsgSend());
        } catch (\Exception $exception) {
            \Yii::error('公众号模板消息发送: ' . $exception->getMessage());
        }
    }
}