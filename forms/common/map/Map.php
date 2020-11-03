<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/9/25
 * Time: 10:35
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */

namespace app\forms\common\map;


use app\helpers\CurlHelper;
use app\models\CityDeliverySetting;
use app\models\Mall;
use yii\base\BaseObject;

/**
 * Class Amap
 * @package app\forms\common\map
 * @property Mall $mall;
 * @property array $origins
 * @property array $destination
 * @property array range
 * 高德地图web服务api
 */
class Map extends BaseObject
{
    public static $instance;
    public $mall;
    public $destination;

    private $key;
    private $origins;
    private $range;

    public static function getInstance($mall = null)
    {
        // 暂时只有同城配送使用
        $instance = new self();
        if (!$mall) {
            $instance->mall = \Yii::$app->mall;
        }
        $instance->setConfig();
        return $instance;
    }

    /**
     * 获取同城配送--高德web_key
     */
    public function setConfig()
    {
        /* @var CityDeliverySetting[] $config */
        $config = CityDeliverySetting::find()->select('key,value')
            ->where(['mall_id' => $this->mall->id, 'is_delete' => 0])
            ->all();
        foreach ($config as $item) {
            if ($item->key == 'web_key') {
                $this->key = json_decode($item->value, true);
            }
            if ($item->key == 'address') {
                $address = json_decode($item->value, true);
                $this->origins = [
                    'lng' => $address['longitude'],
                    'lat' => $address['latitude'],
                ];
            }
            if ($item->key == 'range') {
                $this->range = json_decode($item->value, true);
            }
        }
    }

    /**
     * @throws \Exception
     * 高德驾车规划
     */
    public function distance()
    {
        $url = 'https://restapi.amap.com/v3/direction/driving';
        if (!$this->key) {
            throw new \Exception('请先配置高德地图');
        }
        if (!$this->origins || empty($this->origins)
            || !isset($this->origins['lng']) || !isset($this->origins['lat'])) {
            throw new \Exception('商家地址未配置');
        }
        if (!$this->destination || empty($this->destination)
            || !isset($this->destination['lng']) || !isset($this->destination['lat'])) {
            throw new \Exception('用户定位地址未选择');
        }
        if (!is_point_in_polygon($this->destination, $this->range)) {
            throw new \Exception('用户定位地址不在配送范围内');
        }
        $params = [
            'key' => $this->key,
            'origin' => price_format($this->origins['lng'], 'string', 6) .
                ',' . price_format($this->origins['lat'], 'string', 6),
            'destination' => price_format($this->destination['lng'], 'string', 6) .
                ',' . price_format($this->destination['lat'], 'string', 6),
            'strategy' => 2,
        ];
        $res = CurlHelper::getInstance()->httpGet($url, $params);
        if ($res['status'] == 0) {
            throw new \Exception(\Yii::$app->serializer->encode($res));
        }
        $distance = $res['route']['paths'][0]['distance'];
        return ceil($distance / 1000);
    }
}
