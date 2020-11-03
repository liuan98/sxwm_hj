<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\plugins\diy\forms\common;


use app\forms\common\goods\CommonGoodsList;
use app\forms\common\goods\CommonGoodsMember;
use app\models\Goods;
use app\models\Model;

class DiyGoodsForm extends Model
{
    public function getGoodsIds($data)
    {
        $goodsIds = [];
        // 显示分类
        if ($data['showCat']) {
            foreach ($data['catList'] as $cItem) {
                // 自定义商品
                if ($cItem['staticGoods']) {
                    foreach ($cItem['goodsList'] as $item) {
                        $goodsIds[] = $item['id'];
                    }
                }
            }
        }

        // 不显示分类 自定义商品
        if (!$data['showCat']) {
            foreach ($data['list'] as $item) {
                $goodsIds[] = $item['id'];
            }
        }

        return $goodsIds;
    }

    public function getCats($data, $goodsCats = [])
    {
        // TODO 可以重新查询分类、同步分类名称
        if ($data['showCat']) {
            foreach ($data['catList'] as $cItem) {
                // 默认商品
                if (!$cItem['staticGoods']) {
                    $sign = true;
                    foreach ($goodsCats as $cKey => $cat) {
                        if ($cat['id'] == $cItem['id']) {
                            $sign = false;
                            if ($cat['goodsNum'] < $cItem['goodsNum']) {
                                $goodsCats[$cKey]['goodsNum'] = $cItem['goodsNum'];
                            }
                        }
                    }
                    if ($sign) {
                        $arr['id'] = $cItem['id'];
                        $arr['goodsNum'] = $cItem['goodsNum'];
                        $goodsCats[] = $arr;
                    }
                }
            }
        }
        return $goodsCats;
    }

    public function getGoodsById($goodsIds)
    {
        $form = new CommonGoodsList();
        $form->goods_id = $goodsIds;
        $form->sign = ['mch', ''];
        $form->relations = ['goodsWarehouse', 'mallGoods'];
        $form->status = 1;
        $form->limit = count($goodsIds);
        $newList = $form->getList();

        return $newList;
    }

    public function getGoodsByCat($goodsCats)
    {
        foreach ($goodsCats as &$cat) {
            $form = new CommonGoodsList();
            $form->cat_id = $cat['id'];
            $form->sign = ['mch', ''];
            $form->relations = ['goodsWarehouse', 'mallGoods'];
            $form->limit = $cat['goodsNum'];
            $form->status = 1;
            $newList = $form->getList();
            $cat['goodsList'] = $newList;
        }
        unset($cat);

        return $goodsCats;
    }


    public function getNewGoods($data, $diyGoods, $diyCatsGoods)
    {
        if ($data['showCat']) {
            foreach ($data['catList'] as &$cItem) {
                if ($cItem['staticGoods']) {
                    // 自定义商品
                    $cItem['goodsList'] = $this->setGoodsList($cItem['goodsList'], $diyGoods);
                } else {
                    // 默认商品
                    foreach ($diyCatsGoods as $diyCatsGood) {
                        // 相同分类
                        if ($diyCatsGood['id'] == $cItem['id']) {
                            $cItem['goodsList'] = array_slice($diyCatsGood['goodsList'], 0, $cItem['goodsNum']);
                            break;
                        }
                    }
                }
            }
            unset($cItem);
        } else {
            // 不显示分类
            $data['list'] = $this->setGoodsList($data['list'], $diyGoods);
        }

        return $data;
    }

    private function setGoodsList($goodsList, $diyGoods)
    {
        $newArr = [];
        foreach ($goodsList as $item) {
            foreach ($diyGoods as $dItem) {
                if ($item['id'] == $dItem['id']) {
                    $newArr[] = $dItem;
                    break;
                }
            }
        }

        return $newArr;
    }
}
