<?php
defined('YII_ENV') or exit('Access Denied');
Yii::$app->loadViewComponent('app-setting');
?>
<style>
    .form-body {
        padding: 20px 50% 20px 20px;
        background-color: #fff;
        margin-bottom: 20px;
        min-width: 1000px;
    }

    .form-button {
        margin: 0;
    }

    .form-button .el-form-item__content {
        margin-left: 0!important;
    }

    .button-item {
        padding: 9px 25px;
    }

    .red {
        color: #ff4544
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>刮刮卡抽奖设置</span>
        </div>
        <div class="form-body">
            <el-form v-loading="loading" :model="form" label-width="120px" ref="form" size="small" :rules="FormRules">
                <el-form-item label="中奖概率" prop="probability">
                    <el-input size="small" type="number" v-model="form.probability" autocomplete="off">
                        <template slot="prepend">万分之</template>
                    </el-input>
                </el-form-item>
                <el-form-item label="抽奖时间" prop="time">
                    <el-date-picker v-model="form.time" unlink-panels type="datetimerange" size="small" value-format="yyyy-MM-dd HH:mm:ss" range-separator="至" start-placeholder="开始日期" end-placeholder="结束日期"></el-date-picker>
                    </el-date-picker>
                </el-form-item>
                <el-form-item label="抽奖规则" prop="type">
                    <el-radio v-model="form.type" :label="1">一天{{ form.oppty }}次</el-radio>
                    <el-radio v-model="form.type" :label="2">一人{{ form.oppty }}次</el-radio>
                </el-form-item>
                <el-form-item label="抽奖次数" prop="oppty">
                    <el-input size="small" type="number" v-model="form.oppty" autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item v-if="false" label="小程序标题" prop="title">
                    <el-input size="small" v-model="form.title" autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item label="消耗积分" prop="deplete_integral_num">
                    <el-input size="small" v-model="form.deplete_integral_num" autocomplete="off"></el-input>
                </el-form-item>
                <app-setting v-model="form" :is_share="false" :is_territorial_limitation="false"></app-setting>
                <el-form-item label="规则说明" prop="rule">
                    <el-input rows="15" type="textarea" v-model="form.rule" autocomplete="off"></el-input>
                </el-form-item>
            </el-form>
        </div>
        <el-button type="primary" class="button-item" size="small" :loading=btnLoading @click="onSubmit">提交</el-button>
    </el-card>
</div>
<style>
.el-textarea__inner {
    padding: 0 15px;
}
</style>
<script>
const app = new Vue({
    el: '#app',
    data() {
        return {
            loading: false,
            time: null,
            btnLoading: false,
            form: {
                is_sms: 0,
                is_mail: 0,
                is_print: 0,
                payment_type: ['online_pay'],
                send_type: ['express', 'offline']
            },
            FormRules: {
                probability: [
                    { required: true, message: '概率不能为空', trigger: 'blur' },
                ],
                oppty: [
                    { required: true, message: '抽奖次数不能为空', trigger: 'blur' },
                ],
                type: [
                    { required: true },
                ],
                time: [
                    { required: true, message: '抽奖时间不能为空', trigger: 'blur' },
                ],
            },
        };
    },

    methods: {
        onSubmit() {
            this.$refs.form.validate((valid) => {
                if (valid) {
                    this.form.start_at = this.form.time[0];
                    this.form.end_at = this.form.time[1];

                    this.btnLoading = true;
                    let para = Object.assign(this.form);
                    request({
                        params: {
                            r: 'plugin/scratch/mall/setting',
                        },
                        data: para,
                        method: 'post'
                    }).then(e => {
                        this.btnLoading = false;
                        if (e.data.code === 0) {
                            this.$message({
                              message: e.data.msg,
                              type: 'success'
                            });
                        } else {
                            this.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        this.btnLoading = false;
                        this.$message.error(e.data.msg);
                    });
                }
            });
        },
        getList() {
            this.loading = true;
            request({
                params: {
                    r: 'plugin/scratch/mall/setting',
                },
            }).then(e => {
                console.log(e);
                this.loading = false;
                if (e.data.code === 0) {
                    if (!e.data.data) {
                        return ;
                    }
                    let time = [];
                    time.unshift(e.data.data.start_at);
                    time.push(e.data.data.end_at);
                    e.data.data.time = time;
                    this.form = e.data.data;
                } else {
                    this.$message.error(e.data.msg);
                }
            }).catch(e => {
                this.loading = false;
            });
        },
    },
    mounted: function() {
        this.getList();
    }
})
</script>
