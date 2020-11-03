<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/11
 * Time: 14:59
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */

namespace app\plugins\booking\forms\mall;

use app\core\response\ApiCode;
use app\forms\mall\goods\BaseGoodsEdit;
use app\plugins\booking\forms\common\CommonBookingGoods;
use app\plugins\booking\models\BookingGoods;
use app\plugins\booking\models\BookingStore;
use app\plugins\booking\Plugin;

/**
 * @property
 * @property
 */
class GoodsEditForm extends BaseGoodsEdit
{
    public $id;
    public $form_data;
    public $store;

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['form_data', 'store'], 'trim'],
            [['form_data'], 'default', 'value' => ''],
            [['id'], 'integer'],
        ]);
    }

    public function attributes()
    {
        return array_merge(parent::attributes(), [
            'form_data' => '自定义表单',
            'price' => '商品售价',
        ]);
    }


    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $t = \Yii::$app->db->beginTransaction();
        try {
            $this->checkData();
            $this->setGoods();
            $this->setAttr();
            $this->setGoodsService();
            $this->setCard();
            $this->booking();
            $this->store();
            $this->setListener();
            $t->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } catch (\Exception $exception) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        };
    }

    protected function setGoodsSign()
    {
        return (new Plugin())->getName();
    }

    private function booking()
    {
        $model = CommonBookingGoods::getGoods($this->goods->id);
        if (!$model) {
            $model = new BookingGoods();
            $model->goods_id = $this->goods->id;
            $model->mall_id = \Yii::$app->mall->id;
            $model->is_delete = 0;
        }
        $model->form_data = json_encode($this->form_data);
        if (!$model->save()) {
            throw new \Exception($this->getErrorMsg($model));
        }
        return $model;
    }

    private function store()
    {
        BookingStore::updateAll(['is_delete' => 1,'deleted_at' => date('Y-m-d H:i:s')], [
            'is_delete' => 0,
            'mall_id' => \Yii::$app->mall->id,
            'goods_id' => $this->goods->id,
        ]);
        foreach ($this->store as $item) {
            $form = new BookingStore();
            $form->goods_id = $this->goods->id;
            $form->store_id = $item['id'];
            $form->mall_id = \Yii::$app->mall->id;
            $form->is_delete = 0;
            $form->save();
        };
    }

    // 检测数据
    private function checkData()
    {
        if (!$this->form_data) {
            return;
        }
        foreach ($this->form_data as $item) {
            if (!$item['name']) {
                throw new \Exception('自定义表单名称尚未填写');
            }
            if (isset($item['list'])) {
                foreach ($item['list'] as $item2) {
                    if (!$item2['label']) {
                        throw new \Exception('自定义表单选项尚未填写');
                    }
                }
            }
        }
    }
}
