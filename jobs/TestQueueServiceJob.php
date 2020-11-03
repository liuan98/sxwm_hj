<?php
/**
 * Queue服务配置测试，请勿删除
 */

namespace app\jobs;


use yii\queue\JobInterface;
use yii\queue\Queue;

class TestQueueServiceJob implements JobInterface
{

    /**
     * @param Queue $queue which pushed and is handling the job
     * @return void|mixed result of the job execution
     */
    public function execute($queue)
    {
    }
}
