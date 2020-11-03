<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/12
 * Time: 13:31
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */

namespace app\handlers\orderHandler;


use app\events\OrderEvent;
use app\forms\common\prints\Exceptions\PrintException;
use app\forms\common\prints\PrintOrder;
use app\forms\common\share\AddShareOrder;
use app\models\Mall;
use app\models\Model;
use app\forms\OrderConfig;

/**
 * @property OrderEvent $event
 * @property Mall $mall
 * @property OrderConfig $orderConfig
 */
abstract class BaseOrderHandler extends Model
{
    public $event;
    public $mall;
    public $orderConfig;

    /**
     * @return mixed
     * 事件处理
     */
    abstract public function handle();

    /**
     * @return $this
     */
    public function setMall()
    {
        try {
            $this->mall = \Yii::$app->mall;
        } catch (\Exception $exception) {
            $mall = Mall::findOne(['id' => $this->event->order->mall_id]);
            \Yii::$app->setMall($mall);
            $this->mall = \Yii::$app->mall;
        }
        return $this;
    }

    /**
     * @param string $orderType submit|pay|confirm 打印方式
     * @return $this
     * 小票打印
     */
    protected function receiptPrint($orderType)
    {
        try {
            if ($this->orderConfig->is_print != 1) {
                throw new \Exception($this->event->order->sign . '未开启小票打印');
            }
            $printer = new PrintOrder();
            $printer->print($this->event->order, $this->event->order->id, $orderType);
        } catch (PrintException $exception) {
            \Yii::error('小票打印机打印:' . $exception->getMessage());
        } catch (\Exception $exception) {
            \Yii::error('小票打印机打印:' . $exception->getMessage());
        }
        return $this;
    }

    protected function addShareOrder()
    {
        try {
            (new AddShareOrder())->save($this->event->order);
        } catch (\Exception $exception) {
            \Yii::error('分销佣金记录失败：' . $exception->getMessage());
            \Yii::error($exception);
        }
        return $this;
    }

    public function setMchId()
    {
        \Yii::$app->setMchId($this->event->order->mch_id);
        return $this;
    }
}
