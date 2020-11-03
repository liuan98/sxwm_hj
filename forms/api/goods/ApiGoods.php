<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/11/5
 * Time: 17:07
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */

namespace app\forms\api\goods;


use app\forms\common\goods\CommonGoodsMember;
use app\forms\common\video\Video;
use app\forms\permission\role\AdminRole;
use app\forms\permission\role\SuperAdminRole;
use app\models\Goods;
use app\models\GoodsAttr;
use app\models\Mall;
use app\models\Model;
use app\models\User;
use yii\helpers\ArrayHelper;

/**
 * Class BaseApiGoods
 * @package app\forms\api\goods
 * @property Goods $goods
 * @property Mall $mall
 */
class ApiGoods extends Model
{
    private static $instance;
    public $goods;
    public $mall;
    public $isSales = 1;

    public static function getCommon($mall = null)
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }
        if (!$mall) {
            $mall = \Yii::$app->mall;
        }
        self::$instance->mall = $mall;
        return self::$instance;
    }

    private function defaultData()
    {
        return [
            'id' => '商品id',
            'goods_warehouse_id' => '商品库id',
            'name' => '商品名称',
            'cover_pic' => '商品缩略图',
            'original_price' => '商品原价',
            'unit' => '商品单位',
            'page_url' => '商品跳转路径',
            'is_negotiable' => '是否价格面议',
            'is_level' => '是否享受会员',
            'level_price' => '会员价',
            'price' => '售价',
            'price_content' => '售价文字版',
            'is_sales' => '是否显示销量',
            'sales' => '销量',
        ];
    }

    public function getDetail()
    {
        $isNegotiable = $this->getNegotiable();
        $isSales = $this->getIsSales();
        try {
            $attrGroups = \Yii::$app->serializer->decode($this->goods->attr_groups);
        } catch (\Exception $exception) {
            $attrGroups = [];
        }

        $goodsStock = array_sum(array_column($this->goods->attr, 'stock')) ?? 0;
        $data = [
            'id' => $this->goods->id,
            'goods_warehouse_id' => $this->goods->goods_warehouse_id,
            'mch_id' => $this->goods->mch_id,
            'sign' => $this->goods->sign,
            'name' => $this->goods->name,
            'cover_pic' => $this->goods->coverPic,
            'video_url' => Video::getUrl(trim($this->goods->videoUrl)),
            'original_price' => $this->goods->originalPrice,
            'unit' => $this->goods->unit,
            'page_url' => $this->goods->pageUrl,
            'is_negotiable' => $isNegotiable,
            'is_level' => $this->goods->is_level,
            'level_price' => $this->getGoodsMember(),
            'price' => $this->goods->price,
            'price_content' => $this->getPriceContent($isNegotiable),
            'is_sales' => $isSales,
            'sales' => $this->getSales($isSales, $this->goods->unit),
            'attr_groups' => $attrGroups,
            'attr' => $this->setAttr($this->goods->attr),
            'goods_stock' => $goodsStock,
            'goods_num' => $goodsStock,
        ];
        $data = array_merge($data, $this->getPlugin());
        return $data;
    }

    /**
     * @return int
     * 获取是否价格面议
     */
    protected function getNegotiable()
    {
        $data = 0;
        if ($this->goods->sign == '') {
            $mallGoods = $this->goods->mallGoods;
            $data = $mallGoods->is_negotiable;
        }
        return $data;
    }

    /**
     * @return string
     * 获取会员价
     */
    protected function getGoodsMember()
    {
        return CommonGoodsMember::getCommon()->getGoodsMemberPrice($this->goods);
    }

    /**
     * @param int $isNegotiable
     * @return string
     * 获取售价文字版
     */
    protected function getPriceContent($isNegotiable)
    {
        if ($isNegotiable == 1) {
            $priceContent = '价格面议';
        } elseif ($this->goods->price > 0) {
            $priceContent = 'FCFA/' . $this->goods->price;
        } else {
            $priceContent = '免费';
        }
        return $priceContent;
    }

    /**
     * @return int|mixed
     * 获取是否显示销量
     */
    protected function getIsSales()
    {
        try {
            $setting = \Yii::$app->mall->getMallSetting(['is_show_sales_num']);
            $isSales = $setting['is_show_sales_num'];
        } catch (\Exception $exception) {
            $isSales = 1;
        }
        return $isSales;
    }

    /**
     * @param int $isSales
     * @param string $unit
     * @return string
     * 获取销量
     */
    protected function getSales($isSales, $unit = '件')
    {
        $sales = '';
        if ($this->isSales == 1 && $isSales == 1) {
            $sales = $this->goods->virtual_sales + $this->goods->getSales();
            $length = strlen($sales);

            if ($length > 8) { //亿单位
                $sales = substr_replace(substr($sales, 0, -7), '.', -1, 0) . "亿";
            } elseif ($length > 4) { //万单位
                $sales = substr_replace(substr($sales, 0, -3), '.', -1, 0) . "w";
            }
            $sales = sprintf("已售%s%s", $sales, $unit);
        }
        return $sales;
    }

    /**
     * @return array
     * 获取插件中额外的信息
     */
    protected function getPlugin()
    {
        $list = [];
        try {
            try {
                $pluginList = \Yii::$app->role->getMallRole()->getPluginList();
            } catch (\Exception $exception) {
                /* @var User $user */
                $user = $this->mall->user;
                $config = [
                    'userIdentity' => $user->identity,
                    'user' => $user,
                    'mall' => $this->mall
                ];
                if ($user->identity->is_super_admin == 1) {
                    $parent = new SuperAdminRole($config);
                } elseif ($user->identity->is_admin == 1) {
                    $parent = new AdminRole($config);
                } else {
                    throw new \Exception('错误的账户');
                }
                $pluginList = $parent->getMallRole()->getPluginList();
            }
            foreach ($pluginList as $plugin) {
                $list = array_merge($list, $plugin->getGoodsExtra($this->goods));
            }
        } catch (\Exception $exception) {
        }
        return $list;
    }

    /**
     * 处理规格数据
     * @param null $attr
     * @return array
     * @throws \Exception
     */
    public function setAttr($attr = null)
    {
        if (!$this->goods) {
            throw new \Exception('请先设置商品对象');
        }
        if (!$attr) {
            $attr = $this->goods->attr;
        }
        $newAttr = [];
        $attrGroup = \Yii::$app->serializer->decode($this->goods->attr_groups);
        $attrList = $this->goods->resetAttr($attrGroup);
        /* @var GoodsAttr[] $attr */
        foreach ($attr as $key => $item) {
            $newItem = ArrayHelper::toArray($item);
            $newItem['attr_list'] = $attrList[$item['sign_id']];
            $newAttr[] = $newItem;
        }
        return $newAttr;
    }
}
