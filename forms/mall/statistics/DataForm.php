<?php


namespace app\forms\mall\statistics;


use app\core\response\ApiCode;
use app\forms\api\admin\CashForm;
use app\forms\api\admin\ReviewForm;
use app\forms\mall\export\DataStatisticsExport;
use app\forms\mall\order\OrderForm;
use app\forms\mall\order\OrderRefundListForm;
use app\models\Goods;
use app\models\MallSetting;
use app\models\Model;
use app\models\Order;
use app\models\OrderDetail;
use app\models\OrderRefund;
use app\models\ShareSetting;
use app\models\Store;
use app\models\User;
use app\models\UserInfo;
use app\plugins\mch\models\Mch;

class DataForm extends Model
{
    public $date_start;
    public $date_end;
    public $mch_id;

    public $name;

    public $goods_order;
    public $user_order;
    public $sign;

    public $status;

    public $page;
    public $limit;

    public $flag;
    public $fields;
    public $is_mch_role; // 当前登录是否是商户

    public $mch_per = false;//多商户权限

    public $platform;

    public $map = [
        'miaosha' => '秒杀',
        'pintuan' => '拼团',
        'booking' => '预约',
        'integral_mall' => '积分',
        'bargain' => '砍价',
    ];

    public function rules()
    {
        return [
            [['mch_id'], 'integer'],
            [['flag', 'goods_order', 'user_order', 'name', 'sign', 'platform'], 'string'],
            [['page', 'limit'], 'integer'],
            [['page',], 'default', 'value' => 1],
            [['status',], 'default', 'value' => -1],
            [['date_start', 'date_end', 'fields'], 'trim'],
//            [['mch_per',], 'default', 'value' => false],
        ];
    }

    public function validate($attributeNames = null, $clearErrors = true)
    {
        if (\Yii::$app->role->name == 'mch') {
            $this->mch_id = \Yii::$app->mchId;
            $this->is_mch_role = true;
        }
        $permission_arr = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);//直接取商城所属账户权限，对应绑定管理员账户方法修改只给于app_admin权限
        if (!is_array($permission_arr) && $permission_arr) {
            $this->mch_per = true;
        } else {
            foreach ($permission_arr as $value) {
                if ($value == 'mch') {
                    $this->mch_per = true;
                    break;
                }
            }
        }
        return parent::validate($attributeNames, $clearErrors);
    }

    public function search($type = 0)
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $all_data = [];
        $mch_list = [];
        $goods_top_list = [];
        $user_top_list = [];
        $table_list = [];
        $table_data = [
            'user_num' => 0,
            'order_num' => 0,
            'total_pay_price' => 0,
            'goods_num' => 0
        ];

        if ($type == 1 || $type == 0) {
            //商品排行
            $goods_query = $this->goods_where();
            $goods_query->select("g.`goods_warehouse_id`,COALESCE(SUM(od.`total_price`),0) AS `total_price`,COALESCE(SUM(od.`num`),0) AS `num`")
                ->groupBy('g.goods_warehouse_id');

            if ($this->flag == "EXPORT") {
                $new_query = clone $goods_query;
                $this->export($new_query, $type);
                return false;
            }

            $goods_top_list = $goods_query
                ->limit(10)
                ->asArray()
                ->all();
            $goods_top_list = Order::getGoods_name($goods_top_list);

        }

        if ($type == 2 || $type == 0) {
            //用户排行
            $users_query = $this->users_where();
            $users_query->select("o.user_id,COALESCE(SUM(od.`total_price`),0) AS `total_price`,COALESCE(SUM(od.`num`),0) AS `num`,`i`.`platform`")
                ->groupBy('user_id');


            if ($this->flag == "EXPORT") {
                $new_query = clone $users_query;
                $this->export($new_query, $type);
                return false;
            }

            $user_top_list = $users_query
                ->limit(10)
                ->asArray()
                ->all();
            foreach ($user_top_list as $key => $v) {
                $user_top_list[$key]['nickname'] = $v['user']['nickname'];
                $user_top_list[$key]['avatar'] = $v['user']['userInfo']['avatar'];
                unset($user_top_list[$key]['user']);
            }
        }

        if ($type == 0) {
            //图表
            $table_query = $this->table_where();
            $table_list = $table_query->select("DATE_FORMAT(`o`.`created_at`, '%H') AS `time`,COUNT(DISTINCT `o`.`user_id`) AS `user_num`,
  COUNT(DATE_FORMAT(`o`.`created_at`, '%Y-%m-%d')) AS `order_num`,SUM(`o`.`total_pay_price`) AS `total_pay_price`,SUM(`d`.`num`) AS `goods_num`")
                ->groupBy('time')
                ->orderBy('time')
                ->asArray()
                ->all();

            foreach ($table_list as $value) {
                $table_data['user_num'] += $value['user_num'];
                $table_data['order_num'] += $value['order_num'];
                $table_data['total_pay_price'] = bcadd($table_data['total_pay_price'], $value['total_pay_price'], 2);
                $table_data['goods_num'] += $value['goods_num'];
            }

            //添加的商品数量
            $goods_query = Goods::find()->where(['mall_id' => \Yii::$app->mall->id])
                ->andWhere(['>=', 'created_at', date("Y-m-d", strtotime("-1 day")) . ' 00:00:00'])
                ->andWhere(['<=', 'created_at', date("Y-m-d", strtotime("-1 day")) . ' 23:59:59']);
            $table_data['goods_num'] = $goods_query->count() ?? 0;


            $table_list = $this->hour_24($table_list);
            //店铺列表
            $mch_list = [];
            if ($this->mch_per) {
                $list = \Yii::$app->plugin->getList();
                foreach ($list as $value) {
                    if ($value['display_name'] == '多商户') {
                        $mch_query = $this->mch_where();
                        $mch_list = $mch_query->select('m.id,s.name')
                            ->asArray()
                            ->all();
                        break;
                    }
                }
            }


            //数据总览-用户-订单……
            $all_data = $this->get_all_data();

        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'all_data' => $all_data,
                'mch_list' => $mch_list,
                'goods_top_list' => $goods_top_list,
                'user_top_list' => $user_top_list,
                'table_list' => $table_list,
                'table_data' => $table_data,
                'is_mch_role' => $this->is_mch_role
            ]
        ];
    }

    public function all_search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $all_data = $this->get_all_data();
        //插件分类订单list
        $plugins_list = Order::find()->select('sign')->where(['and', ['in', 'sign', ['booking', 'integral_mall', 'miaosha', 'pintuan', 'bargain']], 'mall_id' => \Yii::$app->mall->id,])->groupBy('sign')->asArray()->all();

        foreach ($plugins_list as $key => $value) {
            $plugins_list[$key]['name'] = $this->map[$value['sign']];
        }

        //小程序管理端分销商，多商户，订单评论开关状态
        $admin_info = [
            'share' => 0,
            'mch' => 0,
            'comment' => 0,
            'bonus' => 0,
            'stock' => 0
        ];
        $share_info = ShareSetting::findOne(['mall_id' => \Yii::$app->mall->id, 'key' => 'level', 'is_delete' => 0]);
        if (!empty($share_info) && $share_info['value'] >= 1) {
            $admin_info['share'] = 1;
        }
        $mall_info = MallSetting::findOne(['mall_id' => \Yii::$app->mall->id, 'key' => 'is_comment', 'is_delete' => 0]);
        if (!empty($mall_info) && $mall_info['value'] == 1) {
            $admin_info['comment'] = 1;
        }
        $list = \Yii::$app->plugin->getList();
        foreach ($list as $value) {
            if ($this->mch_per) {
                if ($value['display_name'] == '多商户') {
                    $admin_info['mch'] = 1;
                }
            }
            if ($value['display_name'] == '团队分红') {
                $admin_info['bonus'] = 1;
            }
            if ($value['display_name'] == '股东分红') {
                $admin_info['stock'] = 1;
            }
        }

        $permissions = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);
        $isScanCodePay = 0;
        if (in_array('scan_code_pay', $permissions)) {
            $isScanCodePay = 1;
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'plugins_list' => $plugins_list,
                'all_data' => $all_data,
                'new_msg_num' => $this->new_msg_num(),//首页新消息提醒数量
                'admin_info' => $admin_info,
                'is_scan_code_pay' => $isScanCodePay
            ]
        ];
    }

    protected function get_all_data()
    {
        $user_query = UserInfo::find()->alias('i')
            ->leftJoin(['u' => User::tableName()], 'u.id = i.user_id');
        //平台标识查询
        if ($this->platform) {
            $user_query->andWhere(['i.platform' => $this->platform]);
        }
        $data_arr['user_count'] = $user_query->andWhere(['u.mch_id' => 0, 'u.is_delete' => 0, 'i.is_delete' => 0, 'u.mall_id' => \Yii::$app->mall->id,])
            ->count();//用户数
        //以下随时间查询改变
        $order_query = Order::find()->alias('o')->where(['o.is_recycle' => 0, 'o.is_delete' => 0, 'o.mall_id' => \Yii::$app->mall->id,])
            ->leftJoin(['i' => UserInfo::tableName()], 'i.user_id = o.user_id')
            ->andWhere(['not', ['o.cancel_status' => 1]])->andWhere(['o.mch_id' => 0]);

        $good_query = Goods::find()->alias('g')->where(['g.mall_id' => \Yii::$app->mall->id,]);
        //时间查询
        if ($this->date_start) {
            $order_query->andWhere(['>=', 'o.created_at', $this->date_start . ' 00:00:00']);
            $good_query->andWhere(['>=', 'g.created_at', $this->date_start . ' 00:00:00']);
        }

        if ($this->date_end) {
            $order_query->andWhere(['<=', 'o.created_at', $this->date_end . ' 23:59:59']);
            $good_query->andWhere(['<=', 'g.created_at', $this->date_end . ' 23:59:59']);
        }

        //插件分类查询
        if ($this->sign) {
            $order_query->andWhere(['o.sign' => $this->sign]);
        }

        if ($this->mch_id) {
            $order_query->andWhere(['o.mch_id' => $this->mch_id]);
            $good_query->andWhere(['g.mch_id' => $this->mch_id]);
        }
        //平台标识查询
        if ($this->platform) {
            $order_query->andWhere(['i.platform' => $this->platform]);
        }
        $data_arr['goods_num'] = $good_query->count();

        $all_query = clone $order_query;
        $data_arr['order_num'] = $all_query->count();
        $pay_query = clone $order_query;
        $data_arr['pay_num'] = $pay_query->andWhere(['or', ['o.is_pay' => 1], ['o.pay_type' => 2]])->count();

        $price_query = clone $order_query;
        $data_arr['pay_price'] = $price_query->andWhere(['or', ['o.is_pay' => 1], ['o.pay_type' => 2]])->sum('o.total_pay_price');
        $data_arr['pay_price'] = $data_arr['pay_price'] ?? '0';

        $wait_query = clone $order_query;
        $data_arr['wait_send_num'] = $wait_query->andWhere(['is_send' => 0])
            ->andWhere(['or', ['o.is_pay' => 1], ['o.pay_type' => 2]])
            ->andWhere(['o.cancel_status' => 0, 'o.sale_status' => 0])
            ->count();

        $wait_pay_query = clone $order_query;
        $data_arr['wait_pay_num'] = $wait_pay_query->andWhere(['is_send' => 0, 'is_pay' => 0])->count();

        $pro_query = clone $order_query;
        $data_arr['pro_order'] = $pro_query->leftJoin(['or' => OrderRefund::tableName()], 'or.order_id = o.id')
            ->andWhere(['or', ['or.status' => 1], ['or.status' => 2]])->andWhere(['or.is_confirm' => 0])->count();

        return $data_arr;
    }

    public function table_search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $query = $this->table_where(true);
        //昨天的数据需要按小时分组，默认取昨天数据
        if (empty($this->date_start) || $this->date_start == $this->date_end) {
            $query->select("DATE_FORMAT(`o`.`created_at`, '%H') AS `time`,COUNT(DISTINCT `o`.`user_id`) AS `user_num`,
  COUNT(DATE_FORMAT(`o`.`created_at`, '%Y-%m-%d')) AS `order_num`,SUM(`o`.`total_pay_price`) AS `total_pay_price`,SUM(`d`.`num`) AS `goods_num`");
        } else {
            $query->select("DATE_FORMAT(`o`.`created_at`, '%Y-%m-%d') AS `time`,COUNT(DISTINCT `o`.`user_id`) AS `user_num`,
  COUNT(DATE_FORMAT(`o`.`created_at`, '%Y-%m-%d')) AS `order_num`,SUM(`o`.`total_pay_price`) AS `total_pay_price`,SUM(`d`.`num`) AS `goods_num`");
        }
        $list = $query->groupBy('time')
            ->orderBy('time')
            ->asArray()
            ->all();
        $table_data = [
            'user_num' => 0,
            'order_num' => 0,
            'total_pay_price' => 0,
            'goods_num' => 0
        ];
        foreach ($list as $value) {
            $table_data['user_num'] += $value['user_num'];
            $table_data['order_num'] += $value['order_num'];
            $table_data['total_pay_price'] = bcadd($table_data['total_pay_price'], $value['total_pay_price'], 2);
            $table_data['goods_num'] += $value['goods_num'];
        }
        

        if (empty($this->date_start) || $this->date_start == $this->date_end) {
            $list = $this->hour_24($list);
        } else {
            $day = floor((strtotime($this->date_end) - strtotime($this->date_start)) / 86400);
            $list = $this->day_data($list, $day);
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
                'table_data' => $table_data
            ]
        ];
    }

    public function mch_search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $mch_query = $this->mch_where();
        $mch_list = $mch_query->select('m.id,s.name')
            ->asArray()
            ->all();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'mch_list' => $mch_list,
            ]
        ];
    }

    protected function table_where($bool = false)
    {
        $orderQuery = OrderDetail::find()->alias('od')->where(['is_delete' => 0])
            ->select(['od.order_id', 'SUM(`od`.`num`) num'])->groupBy('od.order_id');
        $query = Order::find()->alias('o')
            ->where(['o.is_recycle' => 0, 'o.is_pay' => 1, 'o.mall_id' => \Yii::$app->mall->id,])
            ->andWhere(['o.is_delete' => 0])->andWhere(['not', ['o.cancel_status' => 1]])
            ->leftJoin(['d' => $orderQuery], 'd.order_id = o.id')
            ->leftJoin(['i' => UserInfo::tableName()], 'i.user_id = o.user_id');

        //店铺查询
        if ($this->mch_id) {
            $query->andWhere(['o.mch_id' => $this->mch_id]);
        }
        //插件查询
        if ($this->sign) {
            $query->andWhere(['o.sign' => $this->sign]);
        }
        //时间查询，默认查询昨天
        if ($this->date_start && $bool) {
            $query->andWhere(['>=', 'o.created_at', $this->date_start . ' 00:00:00']);
        } else {
            $query->andWhere(['>=', 'o.created_at', date("Y-m-d", strtotime("-1 day")) . ' 00:00:00']);
        }
        if ($this->date_end && $bool) {
            $query->andWhere(['<=', 'o.created_at', $this->date_end . ' 23:59:59']);
        } else {
            $query->andWhere(['<=', 'o.created_at', date("Y-m-d", strtotime("-1 day")) . ' 23:59:59']);
        }
        //平台标识查询
        if ($this->platform) {
            $query->andWhere(['i.platform' => $this->platform]);
        }
        return $query;
    }

    protected function goods_where()
    {
        $query = Order::find()->alias('o')
            ->where(['g.mall_id' => \Yii::$app->mall->id, 'o.is_recycle' => 0, 'o.is_delete' => 0])->andWhere(['not', ['o.cancel_status' => 1]])
            ->leftJoin(['od' => OrderDetail::tableName()], 'od.order_id = o.id and od.is_refund = 0')//过滤退款
            ->leftJoin(['g' => Goods::tableName()], 'g.id = od.goods_id');

        //店铺查询
        if ($this->mch_id) {
            $query->andWhere(['g.mch_id' => $this->mch_id]);
        }

        //时间查询
        if ($this->date_start) {
            $query->andWhere(['>=', 'od.created_at', $this->date_start . ' 00:00:00']);
        }

        if ($this->date_end) {
            $query->andWhere(['<=', 'od.created_at', $this->date_end . ' 23:59:59']);
        }

        //排序
        $query->orderBy((!empty($this->goods_order) ? $this->goods_order : 'total_price DESC') . ',g.goods_warehouse_id');

        return $query;
    }

    protected function users_where()
    {
        $query = Order::find()->alias('o')
            ->where(['o.mall_id' => \Yii::$app->mall->id, 'o.is_recycle' => 0, 'o.is_delete' => 0, 'is_pay' => 1])->andWhere(['not', ['o.cancel_status' => 1]])
            ->rightJoin(['od' => OrderDetail::tableName()], 'od.order_id = o.id')
            ->with('user.userInfo')
//            ->leftJoin(['u' => User::tableName()], 'o.user_id = u.id')
            ->leftJoin(['i' => UserInfo::tableName()], 'i.user_id = o.user_id')
            ->andWhere(['od.is_delete' => 0]);

        //店铺查询
        if ($this->mch_id) {
            $query->andWhere(['o.mch_id' => $this->mch_id]);
        }

        //时间查询
        if ($this->date_start) {
            $query->andWhere(['>=', 'od.created_at', $this->date_start . ' 00:00:00']);
        }

        if ($this->date_end) {
            $query->andWhere(['<=', 'od.created_at', $this->date_end . ' 23:59:59']);
        }
        //平台标识查询
        if ($this->platform) {
            $query->andWhere(['i.platform' => $this->platform]);
        }

        //排序
        $query->orderBy((!empty($this->user_order) ? $this->user_order : 'total_price DESC') . ',o.user_id');

        return $query;
    }

    protected function mch_where()
    {
        $query = Mch::find()->alias('m')->where(['m.is_delete' => 0, 'm.mall_id' => \Yii::$app->mall->id,])
            ->leftJoin(['s' => Store::tableName()], 's.mch_id = m.id')
            ->andWhere(['m.review_status' => 1])->keyword($this->is_mch_role, ['mch_id' => $this->mch_id])
            ->orderBy('s.name');

        //店铺模糊查询
        if ($this->name) {
            $query->andWhere(['like', 's.name', $this->name]);
        }

        return $query;
    }

    protected function export($query, $type)
    {
        $exp = new DataStatisticsExport();
        $exp->type = $type;
        $exp->export($query);
    }

    protected function hour_24($list)
    {
        for ($i = 0; $i < 24; $i++) {
            $bool = false;
            foreach ($list as $item) {
                if ($i == intval($item['time'])) {
                    $bool = true;
                    $arr[$i]['created_at'] = $item['time'];
                    $arr[$i]['user_num'] = $item['user_num'];
                    $arr[$i]['order_num'] = $item['order_num'];
                    $arr[$i]['total_pay_price'] = $item['total_pay_price'];
                    $arr[$i]['goods_num'] = $item['goods_num'];
                }
            }
            if (!$bool) {
                $arr[$i]['created_at'] = $i;
                $arr[$i]['user_num'] = '0';
                $arr[$i]['order_num'] = '0';
                $arr[$i]['total_pay_price'] = '0.00';
                $arr[$i]['goods_num'] = '0';
            }
        }

        return $arr;
    }

    protected function day_data($list, $day)
    {
        for ($i = 0; $i < $day; $i++) {
            $date = date('Y-m-d', strtotime("-$i day"));
            $bool = false;
            foreach ($list as $item) {
                if ($date == $item['time']) {
                    $bool = true;
                    $arr[$i]['created_at'] = $item['time'];
                    $arr[$i]['user_num'] = $item['user_num'];
                    $arr[$i]['order_num'] = $item['order_num'];
                    $arr[$i]['total_pay_price'] = $item['total_pay_price'];
                    $arr[$i]['goods_num'] = $item['goods_num'];
                }
            }
            if (!$bool) {
                $arr[$i]['created_at'] = $date;
                $arr[$i]['user_num'] = '0';
                $arr[$i]['order_num'] = '0';
                $arr[$i]['total_pay_price'] = '0.00';
                $arr[$i]['goods_num'] = '0';
            }
        }
        return !empty($arr) ? array_reverse($arr) : [];
    }

    protected function new_msg_num()
    {
        $num_arr = $this->new_order_msg();
        $cash = new CashForm(['mch_per' => $this->mch_per]);
        $cash_num = $cash->getCount();
        $review = new ReviewForm(['mch_per' => $this->mch_per]);
        $review_num = $review->getCount();
        $data = [
            'order_num' => $num_arr['all_num'] ? $num_arr['all_num'] : 0,//订单提醒
            'cash_num' => !empty($cash_num) ? $cash_num : 0,
            'review_num' => !empty($review_num) ? $review_num : 0,
        ];

        return $data;
    }

    protected function new_order_msg()
    {
        $order = new OrderForm();
        $order->status = 8;//下单提醒数量
        $put_num = $order->search_num();
        $order->status = 4;//退款提醒数量
        $cancel_num = $order->search_num();
        $order_refund = new OrderRefundListForm();
        $order_refund->status = 0;
        $refund_num = $order_refund->search_num();

        return [
            'put_num' => $put_num ? $put_num : 0,
            'cancel_num' => $cancel_num ? $cancel_num : 0,
            'refund_num' => $refund_num ? $refund_num : 0,
            'all_num' => ($put_num + $cancel_num + $refund_num) ? ($put_num + $cancel_num + $refund_num) : 0
        ];
    }
}
