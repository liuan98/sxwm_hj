<?php

namespace app\plugins\miaosha\jobs;

use app\plugins\miaosha\forms\mall\GoodsEditForm;
use yii\base\Component;
use yii\queue\JobInterface;

class MiaoshaActivityJob extends Component implements JobInterface
{
    public $open_date;
    public $open_time;
    /** @var  GoodsEditForm $miaoshaGoods */
    public $miaoshaGoods;
    public $mall;
    public $user;

    public function execute($queue)
    {
        \Yii::$app->setMall($this->mall);
        \Yii::$app->user->setIdentity($this->user);
        $this->miaoshaGoods->executeSave();
    }
}
