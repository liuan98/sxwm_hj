# 订单说明

## 订单中的价格字段说明

###所有订单

- total_price: 所有订单实际需支付的金额总和（含运费）。

###商户订单

- total_price: 所有商品及运费需支付的金额总和（商品优惠后）。
- total_goods_original_price: 所有商品的金额总和（商品优惠前）。
- total_goods_price: 所有商品的金额总和（商品优惠后）。
- express_price: 运费。

###订单内的商品

- total_original_price: 商品原价（优惠前）。
- total_price: 需支付的商品总价（优惠后）。

## 订单价格计算说明

```
单个商户订单金额相关：
  商品金额 =
     数量×单价/

     （会员价1、会员折扣2，二选一）

     优惠券（使用条件都按原价基准计算）

     积分抵扣（使用条件都按原价基准计算，实际扣除的积分按实际抵扣的金额计算）。

  会员折扣/
  会员价/
  积分抵扣/n个积分抵扣n/m元
  优惠券/满减|折扣，一个订单只能用一个优惠券，


  运费/
    /二者满足一个条件即可包邮
    单品满件包邮/购买满n件商品包邮。
    单品满额包邮/购买满金额n包邮。
    如果设置了单品包邮规则，那单品的包邮必须满足单品的规则该单品才可以包邮。

    包邮计算的金额以商品原价计算

    多个单品设置了单品包邮/
      /单品运费与全局无关
      /不设置单品包邮的按全局的包邮规则计算（按整个订单金额计算）
    都没设置单品包邮/
      /按全局包邮规则计算
      
    多个商品一起下单，有单品设置了包邮规则
      /全局包邮规则是否还需要将这个单独设置了包邮的单品（金额、件数）加入计算？ 加入。
      /多个单品包邮没达到包邮条件，运费是否是叠加起来？ 叠加。
      /单品包邮的计算金额是单品的金额×单品数量（不同规格的同一商品一起算）。   


    运费按件计算/不同地区不同运费。
    运费按重量计算/不同地区不同运费。


---------
插件相关优惠/
  秒杀价格/
  团购价格/
  团长价格/

-----------
下单限制/
  区域限制/只允许指定的区域下单，针对商城自身。

  起送限制/指定区域 单个商户订单满足n元才可下单，多个商户一起下单有不满足条件的不允许下单。
  起送金额按订单原价计算（不含运费）；


多个订单一起提交只要有一个不能下单则多个订单都不能下单。

```

## 订单流程开放

**订单流程开放可参考小程序端示例/pages/test-order-submit/test-order-submit**

$form = new OrderSubmitForm();

### 订单标识

- $form->setSign(string); // 设置订单的标识，主要用于区分插件

### 下单功能开关

- $form->setEnableMemberPrice(bool); // 是否开启会员价会员折扣功能

- $form->setEnableCoupon(bool); // 是否开启优惠券功能

- $form->setEnableIntegral(bool); // 是否开启积分功能

- $form->setEnableOrderForm(bool); // 是否开启自定义表单功能

### 若插件需要定制订单数据则需要继承OrderSubmitForm并重写自己需要修改的方法，下面列举支持重写的方法:

0. **订单商品信息**

   - 方法定义:
    ```
    OrderSubmitForm::getGoodsListData($goodsList);
    ```
    
   - 参数示例:
   ```
   // 表单提交form_data['list'][n]['goods_list']的数据
   [
       0 => [
           'id' => 9
           'goods_attr_id' => 349
           'num' => 1
           'cart_id' => 0
       ]
   ]
   ```
   
   - 返回示例:
    ```
    [
        0 => [
            'id' => 9
            'name' => '【组合运费测试】重量'
            'num' => 1
            'forehead_integral' => 0
            'forehead_integral_type' => 1
            'accumulative' => 0
            'pieces' => '0'
            'forehead' => '0'
            'freight_id' => 8
            'unit_price' => '5.00'
            'total_original_price' => '5.00'
            'total_price' => '5.00'
            'goods_attr' => [
                'id' => '349'
                'goods_id' => 9
                'sign_id' => '3:5'
                'stock' => '9997'
                'price' => '40'
                'no' => ''
                'weight' => 0
                'pic_url' => ''
                'share_commission_first' => '50.00'
                'share_commission_second' => '30.00'
                'share_commission_third' => '10.00'
                'is_delete' => 0
            ]
            'attr_list' => [
                0 => [
                    'attr_group_name' => '颜色'
                    'attr_group_id' => 1
                    'attr_id' => 3
                    'attr_name' => '黑色'
                ]
                1 => [
                    'attr_group_name' => '尺码'
                    'attr_group_id' => 2
                    'attr_id' => 5
                    'attr_name' => 'L'
                ]
            ]
            'discounts' => []
            'member_discount' => '0.00'
        ]
    ]
    ```

0. **商户列表信息**

   - 方法定义:
    ```
    OrderSubmitForm::getMchListData($formMchList);
    ```
    
   - 参数示例:
   ```
   // 表单提交form_data['list']的数据
   [
       0 => [
           'mch_id' => 0
           'goods_list' => [
               0 => [
                   'id' => 9
                   'goods_attr_id' => 349
                   'num' => 1
                   'cart_id' => 0
               ]
           ]
           'use_integral' => 0
           'user_coupon_id' => 0
       ]
   ]
   ```
   
   - 返回示例
   ```
   [
       0 => [
           'mch' => [
               'id' => 0
               'name' => '禾匠商城🎁'
           ]
           'goods_list' => [], // getGoodsListData返回的结果
           'form_data' => [
               'mch_id' => 0
               'goods_list' => [
                   0 => [
                       'id' => 9
                       'goods_attr_id' => 349
                       'num' => 1
                       'cart_id' => 0
                   ]
               ]
               'use_integral' => 0
               'user_coupon_id' => 0
           ]
       ]
   ]
   ```
   
0. **商品库存更新**

   - 方法定义:
    ```
    OrderSubmitForm::subGoodsNum($goodsAttr, $subNum, $goodsItem);
    ```
    
   - 参数示例:
   ```
   // $goodsAttr: 当前商品的app\models\GoodsAttr对象
   
   // $subNum: 扣除的商品数量
   
   // $goodsItem: OrderSubmitForm::getGoodsListData() 返回的列表的一项
   ```
   
   - 返回示例
   ```
   // 库存更新成功返回true，库存不足或更新失败抛出异常。
   ```

0. **小程序端**

   小程序端`/pages/order-submit/order-submit`页面也支持一些参数以实现订单的定制化:
   
   - preview_url: 表单预览的接口（需urlEncode）。
   
   - submit_url: 表单提交的接口（需urlEncode）。
   