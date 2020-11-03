<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/11/14
 * Time: 16:14
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */

namespace app\core\cloud;


class CloudTemplate extends CloudBase
{
    /**
     * @param array $args 查询参数
     * @return array ['list'=>[],'pagination'=>[]]
     * @throws CloudException
     */
    public function getList($args = [])
    {
        return $this->httpGet('/mall/template/index', $args);
    }

    /**
     * @param $args
     * @return mixed
     * @throws CloudException
     */
    public function getDetail($args)
    {
        return $this->httpGet('/mall/template/detail', $args);
    }

    /**
     * @param $id
     * @return mixed
     * @throws CloudException
     */
    public function createOrder($id)
    {
        return $this->httpPost('/mall/template/create-order', [], [
            'id' => $id,
        ]);
    }

    /**
     * @param $id
     * @return mixed
     * @throws CloudException
     */
    public function orderDetail($id)
    {
        return $this->httpGet('/mall/template/order-detail', [
            'id' => $id
        ]);
    }

    /**
     * @param $id
     * @return mixed
     * @throws CloudException
     */
    public function install($id)
    {
        return $this->httpGet('/mall/template/package', [
            'id' => $id
        ]);
    }

    public function allId($params)
    {
        return $this->httpGet('/mall/template/all-id', $params);
    }
}
