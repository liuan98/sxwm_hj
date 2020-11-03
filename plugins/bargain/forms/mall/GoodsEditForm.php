<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/11
 * Time: 14:59
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */

namespace app\plugins\bargain\forms\mall;


use app\core\response\ApiCode;
use app\forms\mall\goods\BaseGoodsEdit;
use app\models\Mall;
use app\plugins\bargain\events\BargainGoodsEvent;
use app\plugins\bargain\forms\common\goods\CommonBargainGoods;
use app\plugins\bargain\handlers\HandlerRegister;
use app\plugins\bargain\models\BargainGoods;
use app\plugins\bargain\Plugin;
use yii\db\Exception;

/**
 * @property Mall $mall;
 * @property BargainGoods $bargain;
 */
class GoodsEditForm extends BaseGoodsEdit
{
    public $mall;
    public $min_price;
    public $active_date;
    public $bargain_time;
    public $bargain_people;
    public $bargain_human;
    public $bargain_first_min_price;
    public $bargain_first_max_price;
    public $bargain_second_min_price;
    public $bargain_second_max_price;
    public $type;
    public $stock;
    public $bargain;

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['min_price', 'bargain_time', 'bargain_first_min_price', 'bargain_first_max_price',
                'type', 'bargain_second_min_price', 'bargain_second_max_price', 'bargain_people',
                'bargain_human', 'stock'], 'required'],
            [['min_price', 'bargain_time', 'bargain_first_min_price', 'bargain_first_max_price',
                'bargain_second_min_price', 'bargain_second_max_price'], 'number', 'min' => 0],
            [['bargain_people', 'bargain_human', 'stock'], 'integer', 'min' => 0],
            [['active_date'], function ($attr, $params) {
                if (!is_array($this->$attr)) {
                    $this->addError('活动时间错误');
                }
                foreach ($this->$attr as $item) {
                    if (!$item) {
                        $this->addError('请填写活动时间');
                    }
                }
            }]
        ]);
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributes(), [
            'min_price' => '最低价',
            'bargain_time' => '砍价时间',
            'bargain_first_min_price' => '前面砍价最低值',
            'bargain_first_max_price' => '前面砍价最高值',
            'bargain_second_min_price' => '剩余砍价最低值',
            'bargain_second_max_price' => '剩余砍价最高值',
            'bargain_people' => '参与人数',
            'bargain_human' => '砍价前面人数',
            'type' => '是否允许中途下单',
        ]);
    }


    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        if (!$this->mall) {
            $this->mall = \Yii::$app->mall;
        }
        $t = \Yii::$app->db->beginTransaction();
        try {
            $this->is_level = 0;
            $this->is_level_alone = 0;
            $this->attr_setting_type = 0;
            $this->goods_num = $this->stock;
            $this->setGoods();

            $this->setAttr();
            $this->setGoodsService();
            $this->setCard();
            $model = $this->bargain();
            $t->commit();
            \Yii::$app->trigger(HandlerRegister::BARGAIN_TIMER, new BargainGoodsEvent([
                'bargainGoods' => $model
            ]));
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } catch (Exception $exception) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        } catch (\Exception $exception) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    protected function setGoodsSign()
    {
        return (new Plugin())->getName();
    }

    /**
     * @return BargainGoods|array|\yii\db\ActiveRecord|null
     * @throws Exception
     */
    private function bargain()
    {
        $model = CommonBargainGoods::getCommonGoods($this->mall)->getGoods($this->goods->id);
        if (!$model) {
            $model = new BargainGoods();
            $model->goods_id = $this->goods->id;
            $model->mall_id = $this->mall->id;
            $model->is_delete = 0;
        }
        if ($this->min_price >= $this->price) {
            throw new Exception('砍价最低价必须小于商品售价');
        }
        $model->min_price = $this->min_price;
        if (!$this->active_date) {
            throw new Exception('活动时间不能为空');
        }
        if (!is_array($this->active_date)) {
            throw new Exception('活动时间错误');
        }
        $activeDate = $this->active_date;
        $model->begin_time = mysql_timestamp(strtotime($activeDate[0]));
        $model->end_time = mysql_timestamp(strtotime($activeDate[1]));
        $model->time = $this->bargain_time;
        $model->type = $this->type;
        $model->stock = $this->stock;
        $model->status_data = \Yii::$app->serializer->encode([
            'people' => $this->bargain_people,
            'human' => $this->bargain_human,
            'first_min_price' => $this->bargain_first_min_price,
            'first_max_price' => $this->bargain_first_max_price,
            'second_min_price' => $this->bargain_second_min_price,
            'second_max_price' => $this->bargain_second_max_price,
        ]);
        if (!$model->save()) {
            throw new Exception($this->getErrorMsg($model));
        }
        return $model;
    }
}
