<?php
/**
 * @copyright (c)天幕网络
 * @author Lu Wei
 * @link http://www.67930603.top/
 * Created by IntelliJ IDEA
 * Date Time: 2019/1/4 18:23:00
 */


namespace app\core\cloud;


class CloudException extends \Exception
{
    public $raw;

    public function __construct($message = '', $code = 0, $previous = null, $raw)
    {
        $this->raw = $raw;
        parent::__construct($message, $code, $previous);
    }
}
