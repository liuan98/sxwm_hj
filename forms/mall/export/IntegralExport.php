<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\forms\mall\export;

use app\core\CsvExport;
use app\forms\common\CommonMallMember;
use app\models\Order;
use app\models\UserCard;
use app\models\UserCoupon;

class IntegralExport extends BaseExport
{
    public function fieldsList()
    {
        return [
            [
                'key' => 'platform',
                'value' => '所属平台',
            ],
            [
                'key' => 'user_id',
                'value' => '用户ID',
            ],
            [
                'key' => 'nickname',
                'value' => '用户昵称',
            ],
            [
                'key' => 'mobile',
                'value' => '用户手机号',
            ],
            [
                'key' => 'integral',
                'value' => '充值积分',
            ],
            [
                'key' => 'created_at',
                'value' => '充值时间',
            ],
            [
                'key' => 'desc',
                'value' => '说明',
            ],
        ];
    }

    public function export($query)
    {
        $list = $query->with(['user.userInfo'])->asArray()->all();

        $this->transform($list);
        $this->getFields();
        $dataList = $this->getDataList();

        $fileName = '积分记录' . date('YmdHis');
        (new CsvExport())->export($dataList, $this->fieldsNameList, $fileName);
    }

    protected function transform($list)
    {
        $newList = [];
        $number = 1;
        foreach ($list as $item) {
            $arr = [];
            $arr['number'] = $number++;
            $arr['platform'] = $this->getPlatform($item['user']['userInfo']['platform']);
            $arr['user_id'] = $item['user']['id'];
            $arr['nickname'] = $item['user']['nickname'];
            $arr['mobile'] = $item['user']['mobile'];
            $arr['integral'] = (float)$item['integral'];
            $arr['created_at'] = $this->getDateTime($item['created_at']);
            $arr['desc'] = $item['desc'];
            $newList[] = $arr;
        }

        $this->dataList = $newList;
    }
}
