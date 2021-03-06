<?php
/**
 * ALIPAY API: zoloz.authentication.customer.ftoken.query request
 *
 * @author auto create
 *
 * @since 1.0, 2019-01-07 20:51:15
 */

namespace Alipay\Request;

class ZolozAuthenticationCustomerFtokenQueryRequest extends AbstractAlipayRequest
{
    /**
     * 人脸ftoken查询消费接口
     **/
    private $bizContent;

    public function setBizContent($bizContent)
    {
        $this->bizContent = $bizContent;
        $this->apiParams['biz_content'] = $bizContent;
    }

    public function getBizContent()
    {
        return $this->bizContent;
    }
}
