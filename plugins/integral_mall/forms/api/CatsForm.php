<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\plugins\integral_mall\forms\api;

use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\integral_mall\models\IntegralMallCats;

class CatsForm extends Model
{
    public $page;

    public function rules()
    {
        return [
            [['page'], 'safe'],
            [['page'], 'default', "value" => 1]
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            if (!$this->validate()) {
                return $this->getErrorResponse();
            }
            $query = IntegralMallCats::find()->alias('b')->where([
                'b.is_delete' => 0,
                'b.mall_id' => \Yii::$app->mall->id,
            ])->joinWith(['cats c' => function ($query) {
                $query->where([
                    'c.mall_id' => \Yii::$app->mall->id,
                    'c.is_delete' => 0
                ]);
            }]);

            $list = $query->orderBy('sort ASC, b.id DESC')->asArray()->all();
            $cats = array_map(function ($item) {
                return $item['cats'];
            }, $list);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $cats,
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }
}
