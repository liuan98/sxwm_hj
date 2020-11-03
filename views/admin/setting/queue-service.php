<?php
/**
 * @copyright ©2018 Lu Wei
 * @author Lu Wei
 * @link http://www.luweiss.com/
 * Created by IntelliJ IDEA
 * Date Time: 2019/6/15 15:00
 */

?>
<style>
    .code-block {
        background: #e8efee;
        border-left: 2px solid #d2d2d2;
        margin: 10px 0;
        padding: 10px 10px;
        white-space: pre-line;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never">
        <div slot>队列服务</div>
        <div>
            <ol>
                <li>
                    <?php
                    $queueFile = Yii::$app->basePath . '/queue.sh';
                    $command = 'chmod a+x ' . $queueFile . ' && ' . $queueFile;
                    ?>
                    <h4>启动服务</h4>
                    <div>Linux使用SSH远程登录服务器，运行命令：</div>
                    <pre class="code-block"><?= $command ?></pre>
                </li>
                <li>
                    <h4>测试服务</h4>
                    <el-button style="margin-bottom: 10px" @click="createTestQueue" :loading="testLoading">开始测试
                    </el-button>
                    <div style="color: #909399">测试过程最多可能需要两分钟的时间。</div>
                </li>
            </ol>
        </div>
    </el-card>
</div>
<script>
    new Vue({
        el: '#app',
        data() {
            return {
                testLoading: false,
                testCount: 0,
                maxTestCount: 60,
            };
        },
        created() {
        },
        methods: {
            createTestQueue() {
                this.testLoading = true;
                this.$request({
                    params: {
                        r: 'admin/setting/queue-service',
                        action: 'create',
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        this.testQueue(e.data.data.id);
                    } else {
                        this.$alert(e.data.msg);
                    }
                });
            },
            testQueue(id) {
                if (this.testCount >= this.maxTestCount) {
                    this.testLoading = false;
                    this.testCount = 0;
                    this.$alert('队列服务测试失败，请检查服务是否正常运行。');
                    return;
                }
                this.testCount++;
                this.$request({
                    params: {
                        r: 'admin/setting/queue-service',
                        action: 'test',
                        id: id,
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        if (e.data.data.done) {
                            this.testLoading = false;
                            this.testCount = 0;
                            this.$alert('队列服务测试通过，服务已正常运行。');
                        } else {
                            setTimeout(() => {
                                this.testQueue(id);
                            }, 1000);
                        }
                    }
                });
            },
        },
    });
</script>