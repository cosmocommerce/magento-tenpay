<?php	 	 	 	 	 	 	 	 
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category	CosmoCommerce
 * @package 	CosmoCommerce_Tenpay
 * @copyright	Copyright (c) 2009 CosmoCommerce,LLC. (http://www.cosmocommerce.com)
 * @contact :
 * T: +86-021-66346672
 * L: Shanghai,China
 * M:sales@cosmocommerce.com
 */
class CosmoCommerce_CosmoTenpay_Model_Payment extends Mage_Payment_Model_Method_Abstract
{
    protected $_code  = 'cosmotenpay_payment';
    protected $_formBlockType = 'cosmotenpay/form';

    // CosmoTenpay return codes of payment
    const RETURN_CODE_ACCEPTED      = 'Success';
    const RETURN_CODE_TEST_ACCEPTED = 'Success';
    const RETURN_CODE_ERROR         = 'Fail';

    // Payment configuration
    protected $_isGateway               = false;
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = false;
    protected $_canRefund               = false;
    protected $_canVoid                 = false;
    protected $_canUseInternal          = false;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = false;

    // Order instance
    protected $_order = null;

    /**
     *  Returns Target URL
     *
     *  @return	  string Target URL
     */
    public function getCosmoTenpayUrl()
    {
		$model = Mage::getModel('cosmotenpay/payment');
		$sandbox=$model->getConfigData('sandbox'); 
        
        if($sandbox){
            $url = 'https://sandbox.tenpay.com/api/gateway/pay.htm';
            $url = 'https://gw.tenpay.com/gateway/pay.htm';
        }else{
            $url = 'https://gw.tenpay.com/gateway/pay.htm';
        }
        return $url;
    }

    /**
     *  Return back URL
     *
     *  @return	  string URL
     */
	protected function getReturnURL()
	{
		return Mage::getUrl('checkout/onepage/success', array('_secure' => true));
	}

	/**
	 *  Return URL for CosmoTenpay success response
	 *
	 *  @return	  string URL
	 */
	protected function getSuccessURL()
	{
		return Mage::getUrl('checkout/onepage/success', array('_secure' => true));
	}

    /**
     *  Return URL for CosmoTenpay failure response
     *
     *  @return	  string URL
     */
    protected function getErrorURL()
    {
        return Mage::getUrl('cosmotenpay/payment/error', array('_secure' => true));
    }

	/**
	 *  Return URL for CosmoTenpay notify response
	 *
	 *  @return	  string URL
	 */
	protected function getNotifyURL()
	{
		return Mage::getUrl('cosmotenpay/payment/notify', array('_secure' => true));
	}

    /**
     * Capture payment
     *
     * @param   Varien_Object $orderPayment
     * @return  Mage_Payment_Model_Abstract
     */
    public function capture(Varien_Object $payment, $amount)
    {
        $payment->setStatus(self::STATUS_APPROVED)
            ->setLastTransId($this->getTransactionId());

        return $this;
    }

    /**
     *  Form block description
     *
     *  @return	 object
     */
    public function createFormBlock($name)
    {
        $block = $this->getLayout()->createBlock('cosmotenpay/form_payment', $name);
        $block->setMethod($this->_code);
        $block->setPayment($this->getPayment());

        return $block;
    }

    /**
     *  Return Order Place Redirect URL
     *
     *  @return	  string Order Redirect URL
     */
    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('cosmotenpay/payment/redirect');
    }

    /**
     *  Return Standard Checkout Form Fields for request to CosmoTenpay
     *
     *  @return	  array Array of hidden form fields
     */
    public function getStandardCheckoutFormFields()
    {
        $session = Mage::getSingleton('checkout/session');
        
        $order = $this->getOrder();
        if (!($order instanceof Mage_Sales_Model_Order)) {
            Mage::throwException($this->_getHelper()->__('Cannot retrieve order object'));
        }
		
		
		$_merchantAcctId=$this->getConfigData('partner_id'); 
		$_key=$this->getConfigData('security_code'); 
		$_orderId=$order->getRealOrderId();
		$_orderAmount=sprintf('%.2f', $order->getBaseGrandTotal())*100;  
		//$_bankId=$this->getConfigData('bank_id');
		
		 
		
		$_payType=$this->getConfigData('pay_type');
		
		$_payerName=$order->getCustomerName();
		$_payerContact=$order->getCustomerEmail() ;

		 
        $parameter=array();
        
        $parameter['partner']=$_merchantAcctId;
        $parameter['out_trade_no']=$_orderId;
        $parameter['total_fee']=$_orderAmount;
        
        $parameter['return_url']=$this->getNotifyURL();
        $parameter['notify_url']=$this->getNotifyURL();
        $parameter['body']=$_orderId;
        $parameter['bank_type']='DEFAULT'; //银行类型，默认为财付通
        //用户ip
        $parameter['spbill_create_ip']=$_SERVER['REMOTE_ADDR'];//客户端IP
        $parameter['fee_type']="1";//币种
        $parameter['subject']=$_orderId;//商品名称，（中介交易时必填）
        
        //系统可选参数
        $parameter['sign_type']='MD5';  //签名方式，默认为MD5，可选RSA
        $parameter['service_version']='1.0'; //接口版本号
        $parameter['input_charset']='utf-8'; //字符集
        $parameter['sign_key_index']='1'; //密钥序号

        //业务可选参数
        $parameter['attach']='';  //附件数据，原样返回就可以了
        $parameter['product_fee']=''; //商品费用
        $parameter['transport_fee']='0'; //物流费用
        $parameter['time_start']=date("YmdHis"); //订单生成时间
        $parameter['time_expire']=''; //订单失效时间
        $parameter['buyer_id']=''; //买方财付通帐号
        $parameter['goods_tag']=''; //商品标记
        $parameter['trade_mode']=$_payType; //交易模式（1.即时到帐模式，2.中介担保模式，3.后台选择（卖家进入支付中心列表选择））
        $parameter['transport_desc']=''; //物流说明
        $parameter['trans_type']='1'; //交易类型
        $parameter['agentid']=''; //平台ID
        $parameter['agent_type']=''; //代理模式（0.无代理，1.表示卡易售模式，2.表示网店模式）
        $parameter['seller_id']=''; //卖家的商户号

        
            
       
		$signPars = "";
		ksort($parameter);
		foreach($parameter as $k => $v) {
			if("" != $v && "sign" != $k) {
				$signPars .= $k . "=" . $v . "&";
			}
		}
		$signPars .= "key=" . $_key;
		$sign = strtolower(md5($signPars));
		
        $parameter['sign']=$sign;
        
        
        return $parameter;
    }

	
	//功能函数。将变量值不为空的参数组成字符串
	public function appendParam($returnStr,$paramId,$paramValue){

		if($returnStr!=""){
			
				if($paramValue!=""){
					
					$returnStr.="&".$paramId."=".$paramValue;
				}
			
		}else{
		
			If($paramValue!=""){
				$returnStr=$paramId."=".$paramValue;
			}
		}
		
		return $returnStr;
	}
	//功能函数。将变量值不为空的参数组成字符串。结束	
	
	/**
	 * Return authorized languages by CosmoTenpay
	 *
	 * @param	none
	 * @return	array
	 */
	protected function _getAuthorizedLanguages()
	{
		$languages = array();
		
        foreach (Mage::getConfig()->getNode('global/payment/cosmotenpay_payment/languages')->asArray() as $data) 
		{
			$languages[$data['code']] = $data['name'];
		}
		
		return $languages;
	}
	
	/**
	 * Return language code to send to CosmoTenpay
	 *
	 * @param	none
	 * @return	String
	 */
	protected function _getLanguageCode()
	{
		// Store language
		$language = strtoupper(substr(Mage::getStoreConfig('general/locale/code'), 0, 2));

		// Authorized Languages
		$authorized_languages = $this->_getAuthorizedLanguages();

		if (count($authorized_languages) === 1) 
		{
			$codes = array_keys($authorized_languages);
			return $codes[0];
		}
		
		if (array_key_exists($language, $authorized_languages)) 
		{
			return $language;
		}
		
		// By default we use language selected in store admin
		return $this->getConfigData('language');
	}



    /**
     *  Output failure response and stop the script
     *
     *  @param    none
     *  @return	  void
     */
    public function generateErrorResponse()
    {
        die($this->getErrorResponse());
    }

    /**
     *  Return response for CosmoTenpay success payment
     *
     *  @param    none
     *  @return	  string Success response string
     */
    public function getSuccessResponse()
    {
        $response = array(
            'Pragma: no-cache',
            'Content-type : text/plain',
            'Version: 1',
            'OK'
        );
        return implode("\n", $response) . "\n";
    }

    /**
     *  Return response for CosmoTenpay failure payment
     *
     *  @param    none
     *  @return	  string Failure response string
     */
    public function getErrorResponse()
    {
        $response = array(
            'Pragma: no-cache',
            'Content-type : text/plain',
            'Version: 1',
            'Document falsifie'
        );
        return implode("\n", $response) . "\n";
    }

}