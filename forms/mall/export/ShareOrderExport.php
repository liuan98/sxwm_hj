<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\forms\mall\export;

use app\models\Order;
use app\models\ShareOrder;
use app\models\User;
use yii\db\Query;

class ShareOrderExport extends BaseExport
{

    public function fieldsList()
    {
        return [
            [
                'key' => 'platform',
                'value' => '所属平台',
            ],
            [
                'key' => 'order_no',
                'value' => '订单号',
            ],
            [
                'key' => 'nickname',
                'value' => '下单用户',
            ],
            [
                'key' => 'goods_name',
                'value' => '商品名',
            ],
            [
                'key' => 'attr',
                'value' => '规格',
            ],
            [
                'key' => 'goods_num',
                'value' => '数量',
            ],
            [
                'key' => 'goods_no',
                'value' => '货号',
            ],
            [
                'key' => 'name',
                'value' => '收件人',
            ],
            [
                'key' => 'mobile',
                'value' => '收件人电话',
            ],
            [
                'key' => 'address',
                'value' => '收件人地址',
            ],
            [
                'key' => 'total_price',
                'value' => '总金额',
            ],
            [
                'key' => 'total_pay_price',
                'value' => '实际付款',
            ],
            [
                'key' => 'created_at',
                'value' => '下单时间',
            ],
            [
                'key' => 'order_status',
                'value' => '订单状态',
            ],
            [
                'key' => 'pay_type',
                'value' => '支付方式',
            ],
            [
                'key' => 'is_pay',
                'value' => '支付状态',
            ],
            [
                'key' => 'pay_time',
                'value' => '支付时间',
            ],
            [
                'key' => 'remark',
                'value' => '备注/表单',
            ],
            [
                'key' => 'words',
                'value' => '买家留言',
            ],
            [
                'key' => 'rebate',
                'value' => '自购返利',
            ],
            [
                'key' => 'first_user',
                'value' => '一级分销商',
            ],
            [
                'key' => 'second_user',
                'value' => '二级分销商',
            ],
            [
                'key' => 'third_user',
                'value' => '三级分销商',
            ],
            [
                'key' => 'first',
                'value' => '一级佣金',
            ],
            [
                'key' => 'second',
                'value' => '二级佣金',
            ],
            [
                'key' => 'third',
                'value' => '三级佣金',
            ],
        ];
    }

    public function export($query)
    {
        $list = $query->with(['user.userInfo', 'shareOrder', 'detail.share'])->orderBy(['created_at' => SORT_DESC])->all();
        $this->transform($list);
        $this->getFields();
        $dataList = $this->getDataList();

        $fileName = '分销订单列表' . date('YmdHis');
        (new CsvExport())->export($dataList, $this->fieldsNameList, $fileName);
    }

    protected function transform($list)
    {
        $newList = [];
        // false 不拆分订单、true 根据商品数量拆分订单
        $sign = false;
        foreach ($this->fieldsKeyList as $item) {
            if (in_array($item, ['goods_name', 'attr', 'goods_num', 'goods_no'])) {
                $sign = true;
                break;
            }
        }

        $parentId = [];
        /** @var Order $item */
        foreach ($list as $item) {
            if (!in_array($item->shareOrder[0]->first_parent_id, $parentId)) {
                $parentId[] = $item->shareOrder[0]->first_parent_id;
            }
            if (!in_array($item->shareOrder[0]->second_parent_id, $parentId)) {
                $parentId[] = $item->shareOrder[0]->second_parent_id;
            }
            if (!in_array($item->shareOrder[0]->third_parent_id, $parentId)) {
                $parentId[] = $item->shareOrder[0]->third_parent_id;
            }
        }
        /* @var User[] $parent */
        $parent = User::find()->where(['id' => $parentId])->with('share')->all();

        $order = new Order();
        $number = 1;
        foreach ($list as $key => $item) {
            $arr = [];
            $arr['number'] = $number++;
            $arr['platform'] = $this->getPlatform($item->user->userInfo->platform);
            $arr['order_no'] = $item->order_no;
            $arr['nickname'] = $item->user->nickname;
            $arr['name'] = $item->name;
            $arr['mobile'] = $item->mobile;
            $arr['address'] = $item->address;
            $arr['created_at'] = $item->created_at;
            $arr['order_status'] = $order->orderStatusText($item);
            $arr['pay_type'] = $order->getPayTypeText($item->pay_type);
            $arr['is_pay'] = $item->is_pay == 1 ? "已付款" : "未付款";
            $arr['pay_time'] = $this->getDateTime($item->pay_time);
            $arr['words'] = $item->words;

            $orderForm = json_decode($item['order_form'], true);
            if ($orderForm) {
                $arr['remark'] = $orderForm;
            } else {
                $arr['remark'] = $item['remark'];
            }

            if ($sign) {
                foreach ($item->detail as $detailItem) {
                    $arr['number'] = $number++;
                    $newArr = [];
                    $newArr['goods_name'] = $detailItem->goods->goodsWarehouse->name;
                    $newArr['goods_num'] = intval($detailItem->num);
                    $newArr['cost_price'] = (float)$detailItem->goods->goodsWarehouse->cost_price;
                    // 规格详情
                    $goodsInfo = \Yii::$app->serializer->decode($detailItem->goods_info);
                    $attr = '';
                    if (isset($goodsInfo['attr_list']) && is_array($goodsInfo['attr_list'])) {
                        foreach ($goodsInfo['attr_list'] as $attrItem) {
                            $attr .= $attrItem['attr_group_name'] . ':' . $attrItem['attr_name'] . ',';
                        }
                    }
                    $newArr['attr'] = $attr;
                    $newArr['goods_no'] = isset($goodsInfo['goods_attr']['no']) ? $goodsInfo['goods_attr']['no'] : '';
                    $newArr['total_price'] = (float)$detailItem->total_original_price;
                    $newArr['total_pay_price'] = (float)$detailItem->total_price;
                    $newArr = $this->getShareData($newArr, $detailItem->share, $parent);

                    $newList[] = array_merge($arr, $newArr);
                }
            } else {
                $arr['number'] = $number++;
                $arr['total_price'] = (float)$item->total_price;
                $arr['total_pay_price'] = (float)$item->total_pay_price;

                $arr = $this->getShareData($arr, $item->shareOrder[0], $parent);
                $newList[] = $arr;
            }
        }
        $this->dataList = $newList;
    }

    private function getShareData($arr, $share, $parent)
    {
        if ($share['user_id'] == $share['first_parent_id']) {
            $arr['rebate'] = (float)$share['first_price'];

            $arr['first'] = (float)$share['second_price'];
            $arr['second'] = (float)$share['third_price'];
            $arr['third'] = 0;

            foreach ($parent as $user) {
                if ($user->id == $share['second_parent_id']) {
                    $arr['first_user'] = '昵称：' . $user->nickname . '，姓名：' . $user->share->name . '，手机号：' . $user->share->mobile;
                }
                if ($user->id == $share['third_parent_id']) {
                    $arr['second_user'] = '昵称：' . $user->nickname . '，姓名：' . $user->share->name . '，手机号：' . $user->share->mobile;
                }
            }

        } else {
            $arr['first'] = (float)$share['first_price'];
            $arr['second'] = (float)$share['second_price'];
            $arr['third'] = (float)$share['third_price'];
            foreach ($parent as $user) {
                if ($user->id == $share['first_parent_id']) {
                    $arr['first_user'] = '昵称：' . $user->nickname . '，姓名：' . $user->share->name . '，手机号：' . $user->share->mobile;
                }
                if ($user->id == $share['second_parent_id']) {
                    $arr['second_user'] = '昵称：' . $user->nickname . '，姓名：' . $user->share->name . '，手机号：' . $user->share->mobile;
                }
                if ($user->id == $share['third_parent_id']) {
                    $arr['third_user'] = '昵称：' . $user->nickname . '，姓名：' . $user->share->name . '，手机号：' . $user->share->mobile;
                }
            }
        }

        return $arr;
    }
}
