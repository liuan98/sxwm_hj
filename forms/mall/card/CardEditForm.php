<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\forms\mall\card;


use app\core\response\ApiCode;
use app\models\GoodsCards;
use app\models\Model;

class CardEditForm extends Model
{
    public $name;
    public $pic_url;
    public $description;
    public $id;
    public $expire_type;
    public $time;
    public $expire_day;
    public $total_count;

    public function rules()
    {
        return [
            [['name', 'pic_url', 'description', 'expire_day', 'time', 'expire_type'], 'required'],
            [['pic_url', 'name', 'description'], 'string'],
            [['id', 'expire_type', 'expire_day', 'total_count'], 'integer'],
            [['total_count'], 'default', 'value' => -1]
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            if ($this->id) {
                $card = GoodsCards::findOne(['mall_id' => \Yii::$app->mall->id, 'id' => $this->id]);

                if (!$card) {
                    return [
                        'code' => ApiCode::CODE_ERROR,
                        'msg' => '数据异常,该条数据不存在',
                    ];
                }
            } else {
                $card = new GoodsCards();
            }

            $card->name = $this->name;
            $card->mall_id = \Yii::$app->mall->id;
            $card->expire_type = $this->expire_type;
            $card->expire_day = $this->expire_day;
            $card->begin_time = $this->time[0];
            $card->end_time = $this->time[1];
            $card->pic_url = $this->pic_url;
            $card->description = $this->description;
            $card->total_count = $this->total_count;
            $res = $card->save();

            if (!$res) {
                throw new \Exception($this->getErrorMsg($card));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }
}
