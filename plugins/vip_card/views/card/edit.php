<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/8/28
 * Time: 14:38
 */
defined('YII_ENV') or exit('Access Denied');
Yii::$app->loadViewComponent('app-dialog-select');
?>

<style>
    .form-body {
        padding: 20px;
        background-color: #fff;
        margin-bottom: 20px;
        padding-right: 50%;
        min-width: 1200px;
    }

    .input-item {
        width: 250px;
        margin: 0 0 20px;
    }

    .input-item .el-input__inner {
        border-right: 0;
    }

    .input-item .el-input__inner:hover {
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }

    .input-item .el-input__inner:focus {
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }

    .input-item .el-input-group__append {
        background-color: #fff;
        border-left: 0;
        width: 10%;
        padding: 0;
    }

    .input-item .el-input-group__append .el-button {
        padding: 0;
    }

    .input-item .el-input-group__append .el-button {
        margin: 0;
    }

    .app-goods-cat-list {
        border: 1px solid #E8EAEE;
        border-radius: 5px;
        margin-top: -5px;
        padding: 10px;
    }

    .choose-right .el-form-item__content>div {
        margin-bottom: 10px;
    }

    .dialog-goods {
        max-height: 530px;
        overflow: auto;
    }

    .dialog-goods .el-button {
        padding: 0;
    }

    .dialog-goods .el-table__row td {
        padding: 4px;
    }

    .el-table::before {
        height: 0;
    }

    .detail {
        border-top: 1px solid #e2e2e2;
        padding-top: 20px;
    }

    .button-item {
        margin-bottom: 12px;
        padding: 9px 25px;
    }
</style>
<div id="app" v-cloak>
    <el-card v-loading="loading" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <el-breadcrumb v-if="id" separator="/">
                <el-breadcrumb-item><span style="color: #409EFF;cursor: pointer" @click="$navigate({r:'plugin/vip_card/mall/card/index'})">会员卡管理</span></el-breadcrumb-item>
                <el-breadcrumb-item v-if="id > 0">编辑会员卡</el-breadcrumb-item>
                <el-breadcrumb-item v-else>添加会员卡</el-breadcrumb-item>
            </el-breadcrumb>
            <el-breadcrumb v-else separator="/">
                <el-breadcrumb-item><span style="color: #409EFF;cursor: pointer" @click="$navigate({r:'plugin/vip_card/mall/card/index'})">会员卡管理</span></el-breadcrumb-item>
                <el-breadcrumb-item v-if="edit">编辑超级会员卡</el-breadcrumb-item>
                <el-breadcrumb-item v-else>添加超级会员卡</el-breadcrumb-item>
            </el-breadcrumb>
        </div>

        <div class="form-body">
            <el-form :model="card" size="small" label-width="130px" ref="form" :rules="rules">
                <el-form-item label="超级会员卡名称" prop="name">
                    <el-input placeholder="最多输入7个字符" :disabled="have_id" style="width: 420px" v-model="card.name"></el-input>
                </el-form-item>
                <el-form-item v-if="!id" label="卡片样式" prop="cover">
                    <div style="position: relative">
                        <app-attachment :multiple="false" :max="1" @selected="cover">
                            <el-tooltip class="item" effect="dark" content="建议尺寸:750*360" placement="top">
                                <el-button size="mini">选择文件</el-button>
                            </el-tooltip>
                        </app-attachment>
                        <div style="margin-top: 10px;position: relative">
                            <app-image width="100px"
                                       height="100px"
                                       mode="aspectFill"
                                       :src="card.cover">
                            </app-image>
                        </div>
                    </div>
                </el-form-item>

                <el-form-item class="choose-right" label="会员权益" >
                    <div>
                        <el-checkbox :disabled="have_id" v-model="card.is_free_delivery" true-label="1" false-label="0">自营商品包邮</el-checkbox>
                    </div>
                    <div>
                        <span style="margin-right: 10px">
                            <span>会员折扣</span>
                            <el-tooltip effect="dark" content="请输入0.1~10之间的数字"
                                    placement="top">
                                <i class="el-icon-info"></i>
                            </el-tooltip>
                        </span>
                        <el-input style="width: 45%" :disabled="have_id" v-model="card.discount" type="number" >
                            <template slot="append">折</template>
                        </el-input>
                    </div>
                    <div>
                        <el-checkbox @change="notAll(1,$event)" :disabled="have_id" v-model="is_cats" style="margin-right: 30px">折扣指定分类</el-checkbox>
                        <el-tag disable-transitions v-show="is_cats" :closable="!id" @close="destroyCat(item.value,index)" style="margin-right: 5px" v-for="(item,index) in card.type_info_detail.cats" :key="item.value" type="warning">
                            {{item.label}}
                        </el-tag>
                        <el-button :disabled="have_id" @click="dialogVisible = true" type="text" size="small">选择分类</el-button>
                    </div>
                    <div>
                        <el-checkbox @change="notAll(2,$event)" :disabled="have_id" v-model="is_goods" style="margin-right: 30px">折扣指定商品</el-checkbox>
                        <el-button v-show="is_goods" :disabled="have_id" @click="toLook" v-if="card.type_info_detail.goods.length > 0" style="margin-right: 15px;" size="small">查看</el-button>
                        <div style="display: inline-block">
                            <app-dialog-select ref="goodsList" :multiple="true"
                                               @selected="selectGoodsWarehouse">
                                <el-button :disabled="have_id" type="text" size="small">选择商品</el-button>
                            </app-dialog-select>
                        </div>
                    </div>
                    <div>
                        <el-checkbox @change="getAll" :disabled="have_id" v-model="card.type_info_detail.all">自营商品商品折扣</el-checkbox>
                    </div>
                </el-form-item>
            </el-form>
            <!-- 子卡 -->
            <el-form :model="detail" v-if="id" class="detail" size="small" :rules="DetailRule" label-width="120px" ref="detail">
                <el-form-item label="子卡标题" prop="name">
                    <el-input maxlength='6' style="width: 420px" placeholder="最多输入6个字符" v-model="detail.name"></el-input>
                </el-form-item>
                <el-form-item label="有效时长" prop="expire_day">
                    <el-input type="number" style="width: 420px" v-model="detail.expire_day">
                        <template slot="prepend">购买起</template>
                        <template slot="append">天内有效</template>
                    </el-input>
                </el-form-item>
                <el-form-item label="价格" prop="price">
                    <el-input type="number" style="width: 420px" v-model="detail.price">
                        <template slot="append">元</template>
                    </el-input>
                </el-form-item>
                <el-form-item label="库存" prop="num">
                    <el-input type="number" style="width: 420px" v-model="detail.num"></el-input>
                </el-form-item>
                <el-form-item class="choose-right" label="赠送" >
                    <div>
                        <el-checkbox v-model="send_integral_num">
                            <div>
                                <span style="margin-right: 10px">赠送积分</span>
                                <el-input :disabled="!send_integral_num" v-model="detail.send_integral_num" type="number" >
                                    <template slot="append">积分</template>
                                </el-input>
                            </div>
                        </el-checkbox>
                    </div>
                    <div>
                        <el-checkbox v-model="send_balance">
                            <div>
                                <span style="margin-right: 10px">赠送余额</span>
                                <el-input :disabled="!send_balance" v-model="detail.send_balance" type="number" >
                                    <template slot="append">元</template>
                                </el-input>
                            </div>
                        </el-checkbox>
                    </div>
                    <div>
                        <el-checkbox @change="noCoupon" style="margin-right: 10px" v-model="is_coupon">
                            <span>赠送优惠券</span>
                        </el-checkbox>
                        <el-tag style="margin:5px"
                                v-for="(tag,i) in detail.coupons"
                                :key="i"
                                closable
                                @close="couponClose(detail,i,tag)">
                            {{tag.send_num}}张 | {{tag.name}}
                        </el-tag>
                        <el-button class="button-new-tag" size="small" @click="couponVisible = true">新增优惠券</el-button>
                    </div>
                    <div>
                        <el-checkbox @change="noCards" style="margin-right: 10px" v-model="is_cards">
                            <span>赠送卡劵</span>
                        </el-checkbox>
                        <el-tag style="margin:5px"
                                v-for="(tag,i) in detail.cards"
                                :key="i"
                                closable
                                @close="cardClose(detail,i,tag)">
                            {{tag.send_num}}张 | {{tag.name}}
                        </el-tag>
                        <el-button class="button-new-tag" size="small" @click="cardsVisible = true">新增卡劵</el-button>
                    </div>
                </el-form-item>
                <el-form-item label="使用说明" prop="title">
                    <el-input maxlength="8" style="width: 420px" placeholder="标题（最多输入8个字符）" v-model="detail.title"></el-input>
                </el-form-item>
                <el-form-item label="内容" prop="content">
                    <el-input type="textarea" show-word-limit maxlength="100" :rows="4" style="width: 420px" v-model="detail.content"></el-input>
                </el-form-item>
            </el-form>
        </div>
        <el-button :loading="btnLoading" class="button-item" v-if="id" type="primary" @click="storeDetail('detail')" size="small">保存</el-button>
        <el-button :loading="btnLoading" class="button-item" v-else type="primary" @click="store('form')" size="small">保存</el-button>
    </el-card>
    <el-dialog title="选择分类" width="1100px" :visible.sync="dialogVisible">
        <el-row v-loading="dialogLoading" :gutter="20" style="margin-top: -30px;">
            <template v-if="options.length > 0">
                <el-col :span="8">
                    <h3>一级分类</h3>
                    <div class="app-goods-cat-list">
                        <el-checkbox-group v-for="(option,index) in options"
                                           v-model="cats"
                                           :key="option.value"
                                           @change="selectCats(option,1)"
                                           size="mini">
                            <el-checkbox :label="option.value" size="mini">{{option.label}}</el-checkbox>
                        </el-checkbox-group>
                    </div>
                </el-col>
                <el-col :span="8" v-if="children.length > 0">
                    <h3>二级分类</h3>
                    <div class="app-goods-cat-list">
                        <el-checkbox-group v-for="(option,index) in children"
                                           v-model="cats"
                                           :key="option.value"
                                           @change="selectCats(option,2)"
                                           size="mini">
                            <el-checkbox :label="option.value" size="mini">{{option.label}}</el-checkbox>
                        </el-checkbox-group>
                    </div>
                </el-col>
                <el-col :span="8" v-if="third.length > 0">
                    <h3>三级分类</h3>
                    <div class="app-goods-cat-list">
                        <el-checkbox-group v-for="(option,index) in third"
                                           v-model="cats"
                                           :key="option.value"
                                           @change="selectCats(option,3)"
                                           size="mini">
                            <el-checkbox :label="option.value" size="mini">{{option.label}}</el-checkbox>
                        </el-checkbox-group>
                    </div>
                </el-col>
            </template>
            <template v-else>
                <div flex="main:center" style="align-items: center;margin-top: 20px;">
                    <span>无系统分类</span>
                    <el-button style="display: inline-block;margin-left: 10px" flex="main:center" type="primary"
                               size="small"
                               @click="$navigate({r: 'mall/cat/edit'})">
                        请先添加商品分类
                    </el-button>
                </div>
            </template>
        </el-row>
        <span slot="footer" class="dialog-footer">
            <el-button size='small' @click="catDialogCancel">取 消</el-button>
            <el-button size='small' type="primary" @click="beSelectCats">确 定</el-button>
        </span>
    </el-dialog>
    <el-dialog title="查看商品" :visible.sync="lookGoods">
        <el-input size="small" v-model="keyword" placeholder="根据名称搜索">
            <template slot="append">
                <el-button slot="append" @click="getGoods">搜索</el-button>
            </template>
        </el-input>
        <el-table :data="goods" class="dialog-goods" @selection-change="handleSelectionChange">
            <el-table-column align="center" type="selection" width="60px" label="ID" props="id"></el-table-column>
            <el-table-column property="id" label="ID" width="120"></el-table-column>
            <el-table-column property="name" label="商品名称"></el-table-column>
            <el-table-column label="操作" width="150">
                <template slot-scope="scope">
                    <el-button circle size="mini" type="text" @click="destroyGoods(scope.row, scope.$index)">
                        <el-tooltip class="item" effect="dark" content="删除" placement="top">
                            <img src="statics/img/mall/del.png" alt="">
                        </el-tooltip>
                    </el-button>
                </template>
            </el-table-column>
        </el-table>
        <div style="margin-top: 10px;">
            <el-button type="primary" size='small' @click="destroyMore">批量删除</el-button>
        </div>
        <span slot="footer" class="dialog-footer">
            <el-button size='small' @click="GoodsDialogCancel">取 消</el-button>
            <el-button size='small' type="primary" @click="beSelectGoods">确 定</el-button>
        </span>
    </el-dialog>
    <el-dialog title="新增优惠券" :visible.sync="couponVisible" width="30%" append-to-body>
        <div class="input-item">
            <el-input @keyup.enter.native="couponSearch" size="small" placeholder="请输入优惠券名称搜索"
                      v-model="couponKeyword"
                      clearable @clear='couponSearch'>
                <el-button slot="append" icon="el-icon-search" @click="couponSearch"></el-button>
            </el-input>
        </div>

        <div v-loading="couponLoading" style="padding-bottom:10px">
            <div v-for="coupon in coupons" style="margin-top:15px">
                <el-checkbox v-model="coupon.send_status">{{coupon.name}}</el-checkbox>
                <el-input-number :step="1" step-strictly style="float:right" size="mini" :min="1"
                                 v-model="coupon.send_num"
                ></el-input-number>
            </div>
        </div>
        <div style="text-align: right;margin: 20px 0;">
            <el-pagination v-if="couponPagination"
                           @current-change="couponPageChange"
                           background
                           layout="prev, pager, next"
                           :page-size="couponPagination.pageSize"
                           :total="couponPagination.total_count"></el-pagination>
        </div>
        <div slot="footer" class="dialog-footer">
            <el-button size="small" @click="couponVisible = false">取消</el-button>
            <el-button size="small" type="primary" @click.native="senderBatchCouponSubmit">添加</el-button>
        </div>
    </el-dialog>
    <el-dialog title="新增卡劵" :visible.sync="cardsVisible" width="30%" append-to-body>
        <div class="input-item">
            <el-input @keyup.enter.native="cardsSearch" size="small" placeholder="请输入卡劵名称搜索"
                      v-model="cardsKeyword"
                      clearable @clear='cardsSearch'>
                <el-button slot="append" icon="el-icon-search" @click="cardsSearch"></el-button>
            </el-input>
        </div>
        <div v-loading="cardsLoading" style="padding-bottom:10px">
            <div v-for="card in cardsList" style="margin-top:15px">
                <el-checkbox v-model="card.send_status">{{card.name}}</el-checkbox>
                <el-input-number :step="1" step-strictly style="float:right" size="mini" :min="1"
                                 v-model="card.send_num"
                ></el-input-number>
            </div>
        </div>
        </el-form>
        <div style="text-align: right;margin: 20px 0;">
            <el-pagination v-if="cardsPagination"
                           @current-change="cardsPageChange"
                           background
                           layout="prev, pager, next"
                           :page-size="cardsPagination.pageSize"
                           :total="cardsPagination.total_count"></el-pagination>
        </div>
        <div slot="footer" class="dialog-footer">
            <el-button size="small" @click="cardsVisible = false">取消</el-button>
            <el-button size="small" type="primary" @click.native="senderBatchCardSubmit">提交
            </el-button>
        </div>
    </el-dialog>
</div>


<script>
    const app = new Vue({
        el: '#app',
        data() {
            let validatePass = (rule, value, callback) => {
                if (this.detail.expire_day == '' || this.detail.expire_day == undefined) {
                    callback('请输入有效时长');
                }if (+this.detail.expire_day > 2000) {
                    callback('有效时长最大支持2000');
                } else {
                    callback();
                }
            };
            return {
                have_id: false,
                /* 卡劵 */
                cardsList: [],
                cardsVisible: false,
                cardsKeyword: '',
                cardsPage: 1,
                cardsPagination: null,
                cardsLoading: false,
                cardsPageCount: 0,

                /* 优惠券 */
                coupons: [],
                couponList: [],
                couponVisible: false,
                couponKeyword: '',
                couponPage: 1,
                couponPagination: null,
                couponLoading: false,
                couponPageCount: 0,

                is_goods: false,
                is_cats: false,
                is_coupon: false,
                is_cards: false,
                id: null,
                send_integral_num: false,
                send_balance: false,
                edit: false,
                keyword: '',
                dialogVisible: false,
                dialogLoading: false,
                loading: false,
                lookGoods: false,
                btnLoading : false,
                options: [],// 商品分类列表
                goods: [],
                cats: [],
                children: [],
                third: [],
                multipleSelection: [],
                list: [],
                detail: {
                    cards: [],
                    coupons: []
                },
                card: {
                    is_discount:0,
                    discount:'',
                    cover:'',
                    type_info: {
                        goods: [],
                        cats: [],
                        all: false
                    },
                    type_info_detail: {
                        goods: [],
                        cats: [],
                        all: false
                    }
                },
                rules: {
                    name: [
                        { required: true, message: '请输入超级会员卡名称', trigger: 'blur' },
                        { max: 7, message: '最多输入7个字符', trigger: 'blur' }
                    ],
                    cover: [
                        { required: true, message: '请选择卡片样式', trigger: 'blur' }
                    ]
                },
                DetailRule: {
                    name: [
                        { required: true, message: '请输入子卡标题', trigger: 'blur' }
                    ],
                    expire_day: [
                        { required: true, validator: validatePass, trigger: 'blur' }
                    ],
                    price: [
                        { required: true, message: '请输入价格', trigger: 'blur' }
                    ],
                    num: [
                        { required: true, message: '请输入库存', trigger: 'blur' }
                    ],
                    title: [
                        { required: true, message: '请输入使用说明', trigger: 'blur' }
                    ],
                    content: [
                        { required: true, message: '请输入内容', trigger: 'blur' }
                    ],
                }
            };
        },

        methods: {
            couponClose(row, index, res) {
                row.coupons.splice(index, 1);
                if(row.coupons.length == 0) {
                    this.is_coupon = false;
                }
                this.coupons.forEach(v => {
                    if(v.id == res.coupon_id) {
                        console.log(v)
                        v.send_num = 1;
                        v.send_status = false;
                    }
                })
            },
            cardClose(row, index, res) {
                row.cards.splice(index, 1);
                if(row.cards.length == 0) {
                    this.is_cards = false;
                }
                this.cardsList.forEach(v => {
                    if(v.id == res.card_id) {
                        v.send_num = 1;
                        v.send_status = false;
                    }
                })
            },

            noCards(e) {
                if(e) {
                    this.is_cards = this.detail.cards.length > 0 ? true: false
                }else {
                    this.detail.cards = [];
                    this.cardsList.forEach(v => {
                        v.send_num = 1;
                        v.send_status = false;
                    })
                }
            },

            noCoupon(e) {
                if(e) {
                    this.is_coupon = this.detail.coupons.length > 0 ? true: false
                }else {
                    this.detail.coupons = [];
                    this.coupons.forEach(v => {
                        v.send_num = 1;
                        v.send_status = false;
                    })
                }
            },

            getAll(e) {
                if(e) {
                    this.is_goods = false;
                    this.is_cats = false;
                }
            },

            notAll(e,res) {
                if(this.card.type_info_detail.cats.length != 0 || this.card.type_info_detail.goods.length != 0)  {
                    this.card.type_info_detail.all = false;
                }
                if(this.card.type_info_detail.goods.length == 0) {
                    this.is_goods = false;
                }
                if(this.card.type_info_detail.cats.length == 0) {
                    this.is_cats = false;
                }
                if(res) {
                    if(e == 1 && this.card.type_info_detail.cats.length == 0) {
                        this.dialogVisible = true;
                    }
                    if(e == 2 && this.card.type_info_detail.goods.length == 0) {
                        this.$refs.goodsList.click();
                    }
                }
            },

            selectGoodsWarehouse(e) {
                let that = this;
                let goods = that.card.type_info_detail.goods;
                e.forEach(function(row,index){
                    goods.push({
                        id: row.goodsWarehouse.id,
                        name:row.name
                    })
                    if(index == e.length - 1) {
                        const obj = {}
                        const newObjArr = []
                        for(let i = 0; i < goods.length; i++){
                            if(!obj[goods[i].name]){
                              newObjArr.push(goods[i]);
                              obj[goods[i].name] = true
                            }
                        }
                        that.card.type_info_detail.goods = newObjArr;
                        that.goods = newObjArr;
                        if(that.goods.length > 0) {
                            that.is_goods = true;
                            that.card.type_info_detail.all = false;
                        }
                    }
                })
            },

            getGoods() {
                let that = this;
                if(that.keyword) {
                    that.goods = [];
                    that.list.forEach(function(res,index){
                        console.log(this.list)
                        if(res.name.indexOf(that.keyword) > -1) {
                            that.goods.push(res)
                        }
                    })
                }else {
                    this.goods = JSON.parse(JSON.stringify(this.list));
                }
            },

            handleSelectionChange(val) {
                this.multipleSelection = val;
            },

            destroyGoods(row,index) {
                this.goods.splice(index, 1);
                this.list = JSON.parse(JSON.stringify(this.goods));
            },

            GoodsDialogCancel() {
                this.lookGoods = false;
            },

            destroyMore() {
                let that = this;
                that.multipleSelection.forEach(function(row){
                    that.goods.forEach(function(res,index){
                        if(res.id == row.id) {
                            that.goods.splice(index,1)
                            that.list.splice(index,1)
                        }
                    })
                })
            },

            beSelectGoods() {
                this.lookGoods = false;
                this.card.type_info_detail.goods = this.goods;
                if(this.goods.length > 0) {
                    this.is_goods = true;
                }else {
                    this.is_goods = false;
                }
                this.card.type_info_detail.all = false;
            },

            toLook() {
                this.lookGoods = true;
                this.goods = JSON.parse(JSON.stringify(this.card.type_info_detail.goods));
                this.list = JSON.parse(JSON.stringify(this.goods));
            },

            catDialogCancel() {
                let that = this;
                that.dialogVisible = false;
                that.cats = [];
                if(that.card.type_info_detail.cats.length > 0) {
                    that.card.type_info_detail.cats.forEach(function(row){
                        that.cats.push(row.value);
                    })
                }
            },
            // 获取商品分类
            getCats() {
                let self = this;
                self.dialogLoading = true;
                request({
                    params: {
                        r: 'mall/cat/options',
                        mch_id: 0
                    },
                    method: 'get',
                    data: {}
                }).then(e => {
                    self.dialogLoading = false;
                    let children = [];
                    let third = [];
                    if (e.data.code == 0) {
                        self.options = e.data.data.list;
                        self.options.forEach(function (item, index) {
                            if(self.cats.indexOf(item.value) > -1 && item.children) {
                                children = children.concat(item.children)
                            }
                            if(item.children) {
                                item.children.forEach(function (item1, index1) {
                                    if(self.cats.indexOf(item1.value) > -1) {
                                        children.push(item1)
                                    }
                                })
                            }
                            if(index == self.options.length -1) {
                                self.children = Array.from(new Set(children));
                                self.children.forEach(function (item2, index2) {
                                    if(self.cats.indexOf(item2.value) > -1 && item2.children) {
                                        third = third.concat(item2.children)
                                    }
                                    if(item2.children) {
                                        item2.children.forEach(function (item3, index3) {
                                            if(self.cats.indexOf(item3.value) > -1) {
                                                third.push(item3)
                                            }
                                        })
                                    }
                                    if(index2 == self.children.length -1) {
                                        self.third = Array.from(new Set(third));
                                    }
                                })
                            }
                        })
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            // 选择分类
            selectCats(e, type) {
                let self = this;
                let third = [];
                let children = [];
                self.options.forEach(function (item, index) {
                    if(self.cats.indexOf(item.value) > -1 && item.children) {
                        children = children.concat(item.children)
                    }
                    if(item.children) {
                        item.children.forEach(function (item1, index1) {
                            if(self.cats.indexOf(item1.value) > -1) {
                                children.push(item1)
                            }
                        })
                    }
                    if(index == self.options.length -1) {
                        self.children = Array.from(new Set(children));
                    }
                })
                self.children.forEach(function (item2, index2) {
                    if(self.cats.indexOf(item2.value) > -1 && item2.children) {
                        third = third.concat(item2.children)
                    }
                    if(item2.children) {
                        item2.children.forEach(function (item3, index3) {
                            if(self.cats.indexOf(item3.value) > -1) {
                                third.push(item3)
                            }
                        })
                    }
                    if(index2 == self.children.length -1) {
                        self.third = Array.from(new Set(third));
                    }
                })
            },
            // 确认选择分类
            beSelectCats() {
                let self = this;
                self.card.type_info_detail.cats = [];
                if(self.cats.length > 0) {
                    self.is_cats = true;
                }
                self.options.forEach(function (item, index) {
                    self.cats.forEach(function (item2, index2) {
                        if (item.value == item2) {
                            self.card.type_info_detail.cats.push({
                                label: item.label,
                                value: item.value
                            })
                        }
                    });
                })
                self.children.forEach(function (item, index) {
                    self.cats.forEach(function (item2, index2) {
                        if (item.value == item2) {
                            self.card.type_info_detail.cats.push({
                                label: item.label,
                                value: item.value
                            });
                        }
                    });
                })
                self.third.forEach(function (item, index) {
                    self.cats.forEach(function (item2, index2) {
                        if (item.value == item2) {
                            self.card.type_info_detail.cats.push({
                                label: item.label,
                                value: item.value
                            });
                        }
                    });
                })
                self.dialogVisible = false;
                self.card.type_info_detail.all = false;
            },
            destroyCat(value,index) {
                let self = this;
                console.log(value,index)
                self.cats.splice(self.card.type_info_detail.cats.indexOf(value),1)
                self.card.type_info_detail.cats.splice(index, 1)
                if(self.card.type_info_detail.cats.length == 0) {
                    self.is_cats = false;
                }
            },
            getCard() {
                let that = this;
                that.loading = true;
                request({
                    params: {
                        r: 'plugin/vip_card/mall/card/index'
                    },
                    method: 'get',
                }).then(e => {
                    that.loading = false;
                    if (e.data.code == 0) {
                        if (e.data.data != null) {
                            that.card = e.data.data;
                            that.edit = true;
                            that.cats = e.data.data.type_info.cats;
                            if(e.data.data.type_info.cats.length > 0) {
                                this.is_cats = true;
                            }
                            if(e.data.data.type_info.goods.length > 0) {
                                this.is_goods = true;
                            }
                            that.getCats();
                        }
                    } else {
                        that.$message.error(e.data.msg);
                    }
                });
            },
            store(formName) {
                let that = this;
                that.$refs[formName].validate((valid) => {
                    if (valid) {
                        if(that.card.discount < 0.1 || that.card.discount > 9.99) {
                            that.$message.error('会员折扣输入有误');
                            return false
                        }
                        if(!that.is_cats && !that.is_goods && !that.card.type_info_detail.all) {
                            that.$message.error('请选择折扣对象');
                            return false
                        }
                        that.btnLoading = true;
                        that.card.type_info.cats = [];
                        that.card.type_info.goods = [];
                        if(!that.card.type_info_detail.all) {
                            that.card.type_info.all = false;
                            if(that.card.type_info_detail.cats.length > 0) {
                                that.card.type_info_detail.cats.forEach(function(row){
                                    that.card.type_info.cats.push(row.value.toString())
                                })
                            }
                            if(that.card.type_info_detail.goods.length > 0) {
                                that.card.type_info_detail.goods.forEach(function(row){
                                    that.card.type_info.goods.push(row.id)
                                })
                            }
                        }else {
                            that.card.type_info.all = true;
                        }
                        let card = JSON.parse(JSON.stringify(that.card));
                        if(!that.is_goods) {
                            card.type_info.goods = [];
                        }
                        if(!that.is_cats) {
                            card.type_info.cats = [];
                        }
                        card.is_discount = 1;
                        card.type_info = JSON.stringify(card.type_info);
                        delete card.type_info_detail
                        request({
                            params: {
                                r: 'plugin/vip_card/mall/card/edit'
                            },
                            method: 'post',
                            data: card
                        }).then(e => {
                            that.btnLoading = false;
                            if (e.data.code == 0) {
                                that.$message.success(e.data.msg);
                                setTimeout(v=>{
                                    window.history.go(-1)
                                },1000)
                            } else {
                                that.$message.error(e.data.msg);
                            }
                        });
                    }
                })
            },
            storeDetail(formName) {
                let that = this;
                this.$refs[formName].validate((valid) => {
                    if (valid) {
                        this.btnLoading = true;
                        if(!this.send_integral_num || !this.send_integral_num) {
                            this.detail.send_integral_num = 0
                        }
                        if(!this.send_balance || !this.detail.send_balance) {
                            this.detail.send_balance = 0
                        }
                        let detail = JSON.parse(JSON.stringify(this.detail));
                        detail.cards = JSON.stringify(detail.cards);
                        detail.coupons = JSON.stringify(detail.coupons);
                        detail.vip_id = that.card.id;
                        request({
                            params: {
                                r: 'plugin/vip_card/mall/card/edit-detail'
                            },
                            method: 'post',
                            data: detail
                        }).then(e => {
                            this.btnLoading = false;
                            if (e.data.code == 0) {
                                this.$message.success(e.data.msg);
                                setTimeout(v=>{
                                    window.history.go(-1)
                                },1000)
                            } else {
                                this.$message.error(e.data.msg);
                            }
                        });
                    }
                })
            },
            cover(e) {
                this.card.cover = e[0].url;
                this.cover = e[0].url;
            },
            /* 优惠券 */
            checkCoupon() {
                if (this.coupons && this.detail.coupons) {
                    this.coupons.forEach(v => {
                        this.detail.coupons.map(v1 => {
                            if (v.id === v1.coupon_id) {
                                v.send_num = v1.send_num;
                                v.send_status = true;
                            }
                        })
                    })
                }
            },
            handleCoupon() {
                this.checkCoupon();
                this.couponVisible = true;
            },
            couponSearch() {
                this.couponPage = 1;
                this.getCoupon(() => {
                    this.checkCoupon();
                });
            },
            couponPageChange(currentPage) {
                this.couponPage = currentPage;
                this.getCoupon(() => {
                    this.checkCoupon();
                });
            },
            getCoupon(args = () => {
            }) {
                let self = this;
                self.couponLoading = true;
                request({
                    params: {
                        r: 'plugin/vip_card/mall/card/coupons',
                        keyword: this.couponKeyword
                    },
                    method: 'get',
                }).then(e => {
                    self.couponLoading = false;
                    if (e.data.code == 0) {
                        let couponList = e.data.data.coupons;
                        couponList.forEach(v => {
                            v.send_num = 1;
                            v.send_status = false;
                        })
                        self.coupons = couponList;
                        args();
                        self.couponPagination = e.data.data.pagination;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            senderBatchCouponSubmit() {
                let list = [];
                let sentry = true;
                this.coupons.forEach(v => {
                    if (v.send_num > 0 && v.send_status) {
                        sentry = true;
                        for (let i in this.detail.coupons) {
                            if (this.detail.coupons[i].coupon_id == v.id) {
                                sentry = false;
                                this.detail.coupons[i].send_num = v.send_num;
                            }
                        }
                        if (sentry) {
                            list.push({
                                id: 0,
                                coupon_id: v.id,
                                send_num: v.send_num,
                                name: v.name,
                            })
                        }
                    }
                })
                this.detail.coupons = this.detail.coupons.concat(list);
                if(this.detail.coupons.length > 0) {
                    this.is_coupon = true;
                }
                this.couponVisible = false;
            },
            // 卡券
            senderBatchCardSubmit() {
                let list = [];
                let sentry = true;
                this.cardsList.forEach(v => {
                    if (v.send_num > 0 && v.send_status) {
                        sentry = true;
                        for (let i in this.detail.cards) {
                            if (this.detail.cards[i].card_id == v.id) {
                                sentry = false;
                                this.detail.cards[i].send_num = v.send_num;
                            }
                        }
                        if (sentry) {
                            list.push({
                                id: 0,
                                card_id: v.id,
                                send_num: v.send_num,
                                name: v.name,
                            })
                        }
                    }
                })
                this.detail.cards = this.detail.cards.concat(list);
                if(this.detail.cards.length > 0) {
                    this.is_cards = true;
                }
                this.cardsVisible = false;
            },
            checkCoupon() {
                if (this.coupons && this.detail.coupons) {
                    this.coupons.forEach(v => {
                        this.detail.coupons.map(v1 => {
                            if (v.id === v1.coupon_id) {
                                v.send_num = v1.send_num;
                                v.send_status = true;
                            }
                        })
                    })
                }
            },
            handleCoupon() {
                this.checkCoupon();
                this.couponVisible = true;
            },
            couponSearch() {
                this.couponPage = 1;
                this.getCoupon(() => {
                    this.checkCoupon();
                });
            },
            couponPageChange(currentPage) {
                this.couponPage = currentPage;
                this.getCoupon(() => {
                    this.checkCoupon();
                });
            },
            senderCouponSubmit() {
                let list = [];
                let sentry = true;
                this.coupons.forEach(v => {
                    if (v.send_num > 0 && v.send_status) {
                        sentry = true;
                        for (let i in this.detail.coupons) {
                            if (this.detail.coupons[i].coupon_id == v.id) {
                                sentry = false;
                                this.detail.coupons[i].send_num = v.send_num;
                            }
                        }
                        if (sentry) {
                            list.push({
                                id: 0,
                                coupon_id: v.id,
                                send_num: v.send_num,
                                name: v.name,
                            })
                        }
                    }
                })
                this.detail.coupons = this.detail.coupons.concat(list);
                this.couponVisible = false;
            },
            /* 卡劵 */
            checkCard() {

                if (this.cardsList && this.detail.cards) {
                    this.cardsList.forEach(v => {
                        this.detail.cards.map(v1 => {
                            if (v.id === v1.card_id) {
                                v.send_num = v1.send_num;
                                v.send_status = true;
                            }
                        })
                    })
                }
            },

            handleCard() {
                this.checkCard();
                this.cardsVisible = true;
            },
            cardsSearch() {
                this.cardsPage = 1;
                this.getCards(() => {
                    this.checkCard();
                });
            },
            cardsPageChange(currentPage) {
                this.cardsPage = currentPage;
                this.getCards(() => {
                    this.checkCard();
                });
            },
            getCards(args = () => {
            }) {
                let self = this;
                self.cardsLoading = true;
                request({
                    params: {
                        r: 'plugin/vip_card/mall/card/cards',
                        keyword: self.cardsKeyword
                    },
                    method: 'get',
                }).then(e => {
                    self.cardsLoading = false;
                    if (e.data.code == 0) {
                        let cardsList = e.data.data.cards;
                        cardsList.forEach(v => {
                            v.send_num = 1;
                            v.send_status = false;
                        })
                        self.cardsList = cardsList;
                        args();
                        self.cardsPagination = e.data.data.pagination;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            getDetail() {
                let that = this;
                that.loading = true;
                request({
                    params: {
                        r: 'plugin/vip_card/mall/card/edit-detail',
                        id: that.id
                    },
                    method: 'get',
                }).then(e => {
                    that.loading = false;
                    if (e.data.code == 0) {
                        that.detail = e.data.data;
                        that.send_balance = that.detail.send_balance > 0 ? true: false;
                        that.send_integral_num = that.detail.send_integral_num > 0 ? true: false;
                        that.is_coupon = that.detail.coupons.length > 0 ? true: false;
                        that.is_cards = that.detail.cards.length > 0 ? true: false;
                    } else {
                        that.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    that.loading = false;
                    console.log(e);
                });
            },
        }, 
        created() {
            this.getCard();
            this.id = getQuery('id');
            if(getQuery('id')) {
                this.have_id = true;
                this.getCoupon();
                this.getCards();
                if(getQuery('id') > 0) {
                    this.getDetail();
                }
            }else {
                this.getCats();
            }
        },
    })
</script>