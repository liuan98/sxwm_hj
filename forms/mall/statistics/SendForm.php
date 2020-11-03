<?php


namespace app\forms\mall\statistics;


use app\core\response\ApiCode;
use app\forms\mall\export\SendStatisticsExport;
use app\models\Coupon;
use app\models\Model;
use app\models\UserCard;
use app\models\UserCoupon;
use app\models\UserInfo;

class SendForm extends Model
{
    public $name;
    public $order;
    public $type = 'coupon';//默认查询优惠券

    public $date_start;
    public $date_end;

    public $page;
    public $limit;

    public $flag;
    public $fields;

    public $platform;


    public function rules()
    {
        return [
            [['flag', 'type', 'platform'], 'string'],
            [['page', 'limit'], 'integer'],
            [['page',], 'default', 'value' => 1],
            [['name', 'order'], 'string'],
            [['date_start', 'date_end', 'fields'], 'trim'],
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        if ($this->type == 'card') {
            $query = $this->card_where();
        } else {
            $query = $this->coupon_where();
        }

        if ($this->flag == "EXPORT") {
            $new_query = clone $query;
            $this->export($new_query);
            return false;
        }

        $all_data = $query->asArray()->one();
        $all_data = [
            'all_num' => !empty($all_data['all_num']) ? $all_data['all_num'] : 0,
            'unuse_num' => !empty($all_data['unuse_num']) ? $all_data['unuse_num'] : 0,
            'use_num' => !empty($all_data['use_num']) ? $all_data['use_num'] : 0,
            'end_num' => !empty($all_data['end_num']) ? $all_data['end_num'] : 0,
        ];

        $list = $query->groupBy('`date`,name')
            ->page($pagination)
            ->asArray()
            ->all();


        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'pagination' => $pagination,
                'all_data' => $all_data,
                'list' => $list,
            ]
        ];
    }

    protected function coupon_where()
    {
        $query = UserCoupon::find()->alias('uc')
            ->select("DATE_FORMAT(uc.`created_at`,'%Y-%m-%d') AS `date`,c.`name`,
                                COUNT(uc.`created_at`) AS all_num,
                                SUM(CASE uc.`is_use` WHEN 0 THEN 1 ELSE 0 END) AS `unuse_num`,
                                SUM(CASE uc.`is_use` WHEN 1 THEN 1 ELSE 0 END) AS `use_num`,
                                SUM(CASE WHEN uc.`end_time`< NOW() AND uc.`is_use` = 0 THEN 1 ELSE 0 END) AS `end_num`")
            ->leftJoin(['c' => Coupon::tableName()], 'c.id = uc.coupon_id')
            ->leftJoin(['i' => UserInfo::tableName()], 'i.user_id = uc.user_id')
            ->where(['uc.mall_id' => \Yii::$app->mall->id, 'uc.is_delete' => 0]);

        if ($this->name) {
            $query->andWhere(['like', 'c.name', $this->name]);
        }
        //时间查询
        if ($this->date_start) {
            $query->andWhere(['>=', 'uc.created_at', $this->date_start . ' 00:00:00']);
        }

        if ($this->date_end) {
            $query->andWhere(['<=', 'uc.created_at', $this->date_end . ' 23:59:59']);
        }
        //平台标识查询
        if ($this->platform) {
            $query->andWhere(['i.platform' => $this->platform]);
        }
        $query->orderBy(!empty($this->order) ? $this->order : '`date` desc,uc.`created_at` desc');

        return $query;
    }

    protected function card_where()
    {
        $query = UserCard::find()->alias('uc')
            ->select("DATE_FORMAT(uc.`created_at`,'%Y-%m-%d') AS `date`,uc.`name`,
                                COUNT(uc.`created_at`) AS all_num,
                                SUM(CASE uc.`is_use` WHEN 0 THEN 1 ELSE 0 END) AS `unuse_num`,
                                SUM(CASE uc.`is_use` WHEN 1 THEN 1 ELSE 0 END) AS `use_num`,
                                SUM(CASE WHEN uc.`end_time`< NOW() AND uc.`is_use` = 0 THEN 1 ELSE 0 END) AS `end_num`")
            ->leftJoin(['i' => UserInfo::tableName()], 'i.user_id = uc.user_id')
            ->where(['uc.mall_id' => \Yii::$app->mall->id, 'uc.is_delete' => 0]);

        if ($this->name) {
            $query->andWhere(['like', 'uc.name', $this->name]);
        }
        //时间查询
        if ($this->date_start) {
            $query->andWhere(['>=', 'uc.created_at', $this->date_start . ' 00:00:00']);
        }

        if ($this->date_end) {
            $query->andWhere(['<=', 'uc.created_at', $this->date_end . ' 23:59:59']);
        }
        //平台标识查询
        if ($this->platform) {
            $query->andWhere(['i.platform' => $this->platform]);
        }
        $query->orderBy(!empty($this->order) ? $this->order : '`date` desc,uc.`created_at` desc');

        return $query;
    }


    protected function export($query)
    {
        $exp = new SendStatisticsExport();
        $exp->fieldsKeyList = $this->fields;
        $exp->type = $this->type;
        $exp->export($query);
    }
}