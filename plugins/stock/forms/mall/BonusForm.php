<?php
/**
 * Created by zjhj_mall_v4
 * User: jack_guo
 * Date: 2019/7/5
 * Email: <657268722@qq.com>
 */

namespace app\plugins\stock\forms\mall;


use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\stock\jobs\StockBonusJob;
use app\plugins\stock\models\StockBonusLog;
use app\plugins\stock\models\StockCashLog;
use app\plugins\stock\models\StockOrder;
use app\plugins\stock\models\StockSetting;
use app\plugins\stock\models\StockUser;
use app\plugins\stock\models\StockUserInfo;

class BonusForm extends Model
{
    public $type;
    public $is_save;//分红

    //测试数据
//    public $mall_id = 195;
//    public $first_day = '2019-12-15 00:00:00';
//    public $last_day = '2019-12-22 00:00:00';

    public function rules()
    {
        return [
            [['type', 'is_save'], 'integer'],
        ];
    }

    //查询分红总数据
    public function search_data()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $setting = StockSetting::getList(\Yii::$app->mall->id);
            if ($setting['is_stock'] == 0) {
                throw new \Exception('股东分红未开启');
            }
            //是否有可分红订单，1有，0无
            $is_bonus = 1;
            /* @var StockOrder $first_data */
            $first_data = StockOrder::find()->where(['is_bonus' => 0, 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id])->orderBy('created_at')->one();
            if (empty($first_data)) {
                $is_bonus = 0;
            }
            $year = date('Y', strtotime(@$first_data->created_at)) ?? '';
            $mouth = date('m', strtotime(@$first_data->created_at)) ?? '';
            $day = date('d', strtotime(@$first_data->created_at)) ?? '';
            $week = '';

            $first_day = '';
            $last_day = '';
            if ($this->type == 1) {
                if ($day >= 1 && $day <= 7) {
                    $week = '第一周';
                    $first_day = $year . '-' . $mouth . '-01 00:00:00';
                    $last_day = $year . '-' . $mouth . '-07 23:59:59';
                } else if ($day >= 8 && $day <= 14) {
                    $week = '第二周';
                    $first_day = $year . '-' . $mouth . '-08 00:00:00';
                    $last_day = $year . '-' . $mouth . '-14 23:59:59';
                } else if ($day >= 15 && $day <= 21) {
                    $week = '第三周';
                    $first_day = $year . '-' . $mouth . '-15 00:00:00';
                    $last_day = $year . '-' . $mouth . '-21 23:59:59';
                } else if ($day >= 22 && $day <= 31) {
                    $week = '第四周';
                    $first_day = $year . '-' . $mouth . '-22 00:00:00';
                    $last_day = date('Y-m-t 23:59:59', strtotime(@$first_data->created_at));//月末日期时间
                }
            } elseif ($this->type == 2) {
                $first_day = date('Y-m-01 00:00:00', strtotime(@$first_data->created_at));//月头日期时间
                $last_day = date('Y-m-t 23:59:59', strtotime(@$first_data->created_at));//月末日期时间
            } else {
                throw new \Exception('结算周期参数错误');
            }
            if (strtotime($last_day) >= time()) {
                $is_bonus = 0;
            }
            //分红操作
            if ($this->is_save) {
                $data = [
                    'mall_id' => \Yii::$app->mall->id,
                    'first_day' => $first_day,
                    'last_day' => $last_day,
                    'type' => $this->type
                ];
                $queue_id = \Yii::$app->queue->delay(0)->push(new StockBonusJob($data));
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '处理请求已发送',
                    'queue_id' => $queue_id
                ];
            } else {
                $time_data = StockOrder::find()->select(['sum(total_pay_price) as total_pay_price', 'count(order_id) as order_num'])
                    ->where(['is_bonus' => 0, 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id])
                    ->andWhere(['>=', 'created_at', $first_day])
                    ->andWhere(['<=', 'created_at', $last_day])
                    ->orderBy('created_at')
                    ->asArray()
                    ->one();
                $stock_rate = $setting['stock_rate'];
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'data' => [
                        'is_bonus' => $is_bonus,
                        'year' => $year . '年',
                        'mouth' => $mouth . '月',
                        'week' => $week,
                        'first_day' => substr($first_day, 0, 10),
                        'last_day' => substr($last_day, 0, 10),
                        'order_num' => $time_data['order_num'],
                        'total_pay_price' => $time_data['total_pay_price'],
                        'bonus_price' => bcmul($time_data['total_pay_price'], $stock_rate / 100),
                        'stock_rate' => (string)$stock_rate,
                        'stock_num' => StockUser::find()->where(['status' => 1, 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id])->count() ?? '0',
                    ]
                ];
            }
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'line' => $exception->getLine()
            ];
        }
    }

    //测试分红队列方法
//    public function bonus()
//    {
//        \Yii::error($this->first_day . '-' . $this->last_day . '的股东分红订单队列开始：');
//        $t = \Yii::$app->db->beginTransaction();
//        try {
//            $setting = StockSetting::getList($this->mall_id);
//            if ($setting['is_stock'] == 0) {
//                throw new \Exception('股东分红未开启');
//            }
//            if ($setting['stock_rate'] <= 0) {
//                throw new \Exception('分红比例小于0，不分红');
//            }
//            //可以分红的金额，订单数
//            $time_data = StockOrder::find()->select(['sum(total_pay_price) as total_pay_price', 'count(order_id) as order_num'])
//                ->where(['is_bonus' => 0, 'is_delete' => 0, 'mall_id' => $this->mall_id])
//                ->andWhere(['>=', 'created_at', $this->first_day])
//                ->andWhere(['<=', 'created_at', $this->last_day])
//                ->orderBy('created_at')
//                ->asArray()
//                ->one();
//            if (empty($time_data)) {
//                throw new \Exception('订单已被分红');
//            }
//
//            //股东计算
//            $user_list = StockUser::find()->where(['is_delete' => 0, 'status' => 1, 'mall_id' => $this->mall_id])->with('level')->asArray()->all();
//            if (empty($user_list)) {
//                throw new \Exception('股东人数小于0，不分红');
//            }
//            $level_arr = [];//记录等级，分红比例，人数，对应用户ids
//            foreach ($user_list as $item) {
//                //第一个
//                if (empty($level_arr)) {
//                    $level_arr[] = [
//                        'level_id' => $item['level']['id'],
//                        'name' => $item['level']['name'],
//                        'bonus_rate' => $item['level']['bonus_rate'],
//                        'num' => 1,
//                        'user' => [
//                            $item['user_id']
//                        ]
//                    ];
//                } else {
//                    //第二个后每个对比是否存在同一个等级的记录
//                    $is_have = false;
//                    foreach ($level_arr as &$value) {
//                        if ($value['level_id'] === $item['level']['id']) {
//                            array_push($value['user'], $item['user_id']);
//                            $value['num']++;
//                            $is_have = true;//已存在
//                        }
//                    }
//                    //不存在，则新增
//                    if (!$is_have) {
//                        $level_arr[] = [
//                            'level_id' => $item['level']['id'],
//                            'name' => $item['level']['name'],
//                            'bonus_rate' => $item['level']['bonus_rate'],
//                            'num' => 1,
//                            'user' => [
//                                $item['user_id']
//                            ]
//                        ];
//                    }
//                }
//            }
//            //计算每个股东分红
//            bcscale(6);//为了更精确
//            $all_rate = 0;
//            foreach ($level_arr as &$a) {
//                $num = 0;
//                foreach ($a['user'] as $b) {
//                    $num++;
//                }
//                $all_rate = bcadd($all_rate, bcmul($a['bonus_rate'] / 100, $num));//记录每个等级分红比例  10%*2+20%*5+30%*10
//                $a['bonus_price'] = bcmul(bcmul($time_data['total_pay_price'], $setting['stock_rate'] / 100), $a['bonus_rate'] / 100);//第一次记录  10%*100元
//            }
////            var_dump($level_arr, $all_rate);die;
////等级1每个股东可得：10%*100元/（10%*2+20%*5+30%*10）=2.38元
////
////等级2每个股东可得： 20%*100元/（10%*2+20%*5+30%*10）=4.76元
////
////等级3每个股东可得： 30%*100元/（10%*2+20%*5+30%*10）=7.14元
//            //记录分红
//            $bonus_model = new StockBonusLog();
//            $bonus_model->mall_id = $this->mall_id;
//            $bonus_model->bonus_type = $this->type;
//            $bonus_model->bonus_price = bcmul($time_data['total_pay_price'], $setting['stock_rate'] / 100);
//            $bonus_model->bonus_rate = $setting['stock_rate'];
//            $bonus_model->order_num = $time_data['order_num'];
//            $bonus_model->stock_num = count($user_list) ?? 0;
//            $bonus_model->start_time = $this->first_day;
//            $bonus_model->end_time = $this->last_day;
//            if (!$bonus_model->save()) {
//                throw new \Exception($bonus_model->errors[0]);
//            }
//            //记录每个股东分红
//            foreach ($level_arr as $c) {
//                foreach ($c['user'] as $d) {
//                    bcscale(2);//四舍五入，不至于最后分红大于总分红
//                    $price = bcdiv($c['bonus_price'], $all_rate) ?? 0;
//                    //流水记录
//                    $cash_log = new StockCashLog();
//                    $cash_log->mall_id = $this->mall_id;
//                    $cash_log->user_id = $d;
//                    $cash_log->type = 1;
//                    $cash_log->price = $price;
//                    $cash_log->desc = '股东分红';
//                    $cash_log->level_id = $c['level_id'];
//                    $cash_log->level_name = $c['name'];
//                    $cash_log->order_num = $time_data['order_num'];
//                    $cash_log->bonus_rate = $c['bonus_rate'];
//                    $cash_log->bonus_id = $bonus_model->id;
//                    if (!$cash_log->save()) {
//                        throw new \Exception($cash_log->errors[0]);
//                    }
//                    //总金额记录
//                    if ($price > 0) {
//                        if (StockUserInfo::updateAllCounters(['all_bonus' => $price, 'total_bonus' => $price], ['user_id' => $d]) <= 0) {
//                            throw new \Exception('股东分红金额更新失败');
//                        }
//                    }
//                }
//            }
//
//            //分红后订单状态更新
//            if (StockOrder::updateAll(['is_bonus' => 1, 'bonus_time' => mysql_timestamp(), 'bonus_id' => $bonus_model->id],
//                    ['and', ['>=', 'created_at', $this->first_day], ['<=', 'created_at', $this->last_day], ['mall_id' => $this->mall_id]]) <= 0) {
//                throw new \Exception('分红订单状态更新失败');
//            }
//
//            $t->commit();
//        } catch (\Exception $exception) {
//            $t->rollBack();
//            \Yii::error('股东分红队列：');
//            \Yii::error($exception->getMessage());
//        }
//    }
}