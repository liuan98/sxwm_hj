<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\plugins\pintuan\forms\mall;

use app\core\CsvExport;
use app\forms\mall\export\BaseExport;
use app\models\Order;

class OrderExport extends \app\forms\mall\export\OrderExport
{
    public function getFileName()
    {
        if ($this->send_type == 1) {
            $name = '拼团-自提订单';
        } elseif ($this->send_type == 2) {
            $name = '拼团-同城配送';
        } else {
            $name = '拼团-订单列表';
        }
        $fileName = $name . date('YmdHis');

        return $fileName;
    }
}
