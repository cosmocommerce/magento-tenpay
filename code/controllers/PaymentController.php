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
 * @package 	CosmoCommerce_CosmoTenpay
 * @copyright	Copyright (c) 2009 CosmoCommerce,LLC. (http://www.cosmocommerce.com)
 * @contact :
 * T: +86-021-66346672
 * L: Shanghai,China
 * M:sales@cosmocommerce.com
 */
class CosmoCommerce_CosmoTenpay_PaymentController extends Mage_Core_Controller_Front_Action
{
    /**
     * Order instance
     */
    protected $_order;

    /**
     *  Get order
     *
     *  @param    none
     *  @return	  Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        if ($this->_order == null)
        {
            $session = Mage::getSingleton('checkout/session');
            $this->_order = Mage::getModel('sales/order');
            $this->_order->loadByIncrementId($session->getLastRealOrderId());
        }
        return $this->_order;
    }

    /**
     * When a customer chooses CosmoTenpay on Checkout/Payment page
     *
     */
    public function redirectAction()
    {
        $session = Mage::getSingleton('checkout/session');
        $session->setCosmoTenpayPaymentQuoteId($session->getQuoteId());

        $order = $this->getOrder();

        if (!$order->getId())
        {
            $this->norouteAction();
            return;
        }

        $order->addStatusToHistory(
        $order->getStatus(),
        Mage::helper('cosmotenpay')->__('Customer was redirected to CosmoTenpay')
        );
        $order->save();

        $this->getResponse()
        ->setBody($this->getLayout()
        ->createBlock('cosmotenpay/redirect')
        ->setOrder($order)
        ->toHtml());

        $session->unsQuoteId();
    }
    public function notifyAction()
    {
        if ($this->getRequest()->isPost())
        {
            $postData = $this->getRequest()->getPost();
            $method = 'post';


        } else if ($this->getRequest()->isGet())
        {
            $postData = $this->getRequest()->getQuery();
            $method = 'get';

        } else
        {
            return;
        }
        Mage::log($postData);
		$cosmotenpay = Mage::getModel('cosmotenpay/payment');
		
		$partner=$cosmotenpay->getConfigData('partner_id');
		$security_code=$cosmotenpay->getConfigData('security_code');
		$sign_type='MD5';
		$mysign="";
		$_input_charset='utf-8';
		
		
		$signPars = "";
		ksort($postData);
		foreach($postData as $k => $v) {
			if("sign" != $k && "" != $v) {
				$signPars .= $k . "=" . $v . "&";
			}
		}
		$signPars .= "key=" . $security_code;
		
		$sign = strtolower(md5($signPars));
		
		if ( $sign == strtolower($postData["sign"]))  {
			
			
			if($postData['trade_state'] == 0) {   
				$order = Mage::getModel('sales/order');
				$order->loadByIncrementId($postData['out_trade_no']);
				//$order->setCosmoTenpayTradeno($postData['trade_no']);
				$order->setStatus(Mage_Sales_Model_Order::STATE_PROCESSING);
				// $order->sendNewOrderEmail();
				$order->addStatusToHistory(
				$cosmotenpay->getConfigData('order_status_payment_accepted'),
				Mage::helper('cosmotenpay')->__('付款成功。'));
				try{
					$order->save();
					echo "success";
					 exit();
				} catch(Exception $e){
					
				}

			} 
			if($postData['trade_state'] == 1) {                   //交易创建
				
				$order = Mage::getModel('sales/order');
				$order->loadByIncrementId($postData['out_trade_no']);
				//$order->setCosmoTenpayTradeno($postData['trade_no']);
				// $order->sendNewOrderEmail();
				$order->addStatusToHistory(
				$order->getStatus(),
				Mage::helper('cosmotenpay')->__('交易创建.'));
				try{
					$order->save();
					echo "success";
					 exit();
				} catch(Exception $e){
					
				}
			}
			if($postData['trade_state'] == 2) {                   //收获地址填写完毕
				
				$order = Mage::getModel('sales/order');
				$order->loadByIncrementId($postData['out_trade_no']);
				//$order->setCosmoTenpayTradeno($postData['trade_no']);
				// $order->sendNewOrderEmail();
				$order->addStatusToHistory(
				$order->getStatus(),
				Mage::helper('cosmotenpay')->__('收获地址填写完毕.'));
				try{
					$order->save();
					echo "success";
					 exit();
				} catch(Exception $e){
					
				}
			}
			
			if($postData['trade_state'] == 4) {                   //卖家发货成功
				
				$order = Mage::getModel('sales/order');
				$order->loadByIncrementId($postData['out_trade_no']);
				//$order->setCosmoTenpayTradeno($postData['trade_no']);
				// $order->sendNewOrderEmail();
				$order->addStatusToHistory(
				$order->getStatus(),
				Mage::helper('cosmotenpay')->__('卖家发货成功.'));
				try{
					$order->save();
					echo "success";
					 exit();
				} catch(Exception $e){
					
				}
			}
			if($postData['trade_state'] == 5) {                   //买家收货确认，交易成功
				
				$order = Mage::getModel('sales/order');
				$order->loadByIncrementId($postData['out_trade_no']);
				//$order->setCosmoTenpayTradeno($postData['trade_no']);
				// $order->sendNewOrderEmail();
				$order->addStatusToHistory(
				$order->getStatus(),
				Mage::helper('cosmotenpay')->__('买家收货确认，交易成功.'));
				try{
					$order->save();
					echo "success";
					 exit();
				} catch(Exception $e){
					
				}
			}
			if($postData['trade_state'] == 6) {                   //交易关闭，未完成超时关闭
				
				$order = Mage::getModel('sales/order');
				$order->loadByIncrementId($postData['out_trade_no']);
				//$order->setCosmoTenpayTradeno($postData['trade_no']);
				// $order->sendNewOrderEmail();
				$order->addStatusToHistory(
				$order->getStatus(),
				Mage::helper('cosmotenpay')->__('交易关闭，未完成超时关闭.'));
				try{
					$order->save();
					echo "success";
					 exit();
				} catch(Exception $e){
					
				}
			}
			if($postData['trade_state'] == 7) {                   //修改交易价格成功
				
				$order = Mage::getModel('sales/order');
				$order->loadByIncrementId($postData['out_trade_no']);
				//$order->setCosmoTenpayTradeno($postData['trade_no']);
				// $order->sendNewOrderEmail();
				$order->addStatusToHistory(
				$order->getStatus(),
				Mage::helper('cosmotenpay')->__('修改交易价格成功.'));
				try{
					$order->save();
					echo "success";
					 exit();
				} catch(Exception $e){
					
				}
			}
			if($postData['trade_state'] == 8) {      //买家发起退款
				
				$order = Mage::getModel('sales/order');
				$order->loadByIncrementId($postData['out_trade_no']);
				//$order->setCosmoTenpayTradeno($postData['trade_no']);
				// $order->sendNewOrderEmail();
				$order->addStatusToHistory(
				$order->getStatus(),
				Mage::helper('cosmotenpay')->__('修改交易价格成功.'));
				try{
					$order->save();
					echo "success";
					 exit();
				} catch(Exception $e){
					
				}
			}
			if($postData['trade_state'] == 9) {    //退款成功
			
				$order = Mage::getModel('sales/order');
				$order->loadByIncrementId($postData['out_trade_no']);
				//$order->setCosmoTenpayTradeno($postData['trade_no']);
				// $order->sendNewOrderEmail();
				$order->addStatusToHistory(
				$order->getStatus(),
				Mage::helper('cosmotenpay')->__('退款成功.'));
				try{
					$order->save();
					echo "success";
					 exit();
				} catch(Exception $e){
				}

			}
			if($postData['trade_state'] == 10) {    //退款关闭
			
				$order = Mage::getModel('sales/order');
				$order->loadByIncrementId($postData['out_trade_no']);
				//$order->setCosmoTenpayTradeno($postData['trade_no']);
				// $order->sendNewOrderEmail();
				$order->addStatusToHistory(
				$order->getStatus(),
				Mage::helper('cosmotenpay')->__('退款关闭.'));
				try{
					$order->save();
					echo "success";
					 exit();
				} catch(Exception $e){
				}

			}
				echo "fail";
				Mage::log("x");
			 exit();

		} else {
			echo "fail";
			 exit();
		}
    }

	public function get_verify($url,$time_out = "60") {
		$urlarr     = parse_url($url);
		$errno      = "";
		$errstr     = "";
		$transports = "";
		if($urlarr["scheme"] == "https") {
			$transports = "ssl://";
			$urlarr["port"] = "443";
		} else {
			$transports = "tcp://";
			$urlarr["port"] = "80";
		}
		$fp=@fsockopen($transports . $urlarr['host'],$urlarr['port'],$errno,$errstr,$time_out);
		if(!$fp) {
			die("ERROR: $errno - $errstr<br />\n");
		} else {
			fputs($fp, "POST ".$urlarr["path"]." HTTP/1.1\r\n");
			fputs($fp, "Host: ".$urlarr["host"]."\r\n");
			fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
			fputs($fp, "Content-length: ".strlen($urlarr["query"])."\r\n");
			fputs($fp, "Connection: close\r\n\r\n");
			fputs($fp, $urlarr["query"] . "\r\n\r\n");
			while(!feof($fp)) {
				$info[]=@fgets($fp, 1024);
			}
			fclose($fp);
			$info = implode(",",$info);
			$arg="";
			while (list ($key, $val) = each ($_POST)) {
				$arg.=$key."=".$val."&";
			}

		return $info;
		}

	}
  
     /**
     *  Save invoice for order
     *
     *  @param    Mage_Sales_Model_Order $order
     *  @return	  boolean Can save invoice or not
     */
    protected function saveInvoice(Mage_Sales_Model_Order $order)
    {
        if ($order->canInvoice())
        {
            $convertor = Mage::getModel('sales/convert_order');
            $invoice = $convertor->toInvoice($order);
            foreach ($order->getAllItems() as $orderItem)
            {
                if (!$orderItem->getQtyToInvoice())
                {
                    continue ;
                }
                $item = $convertor->itemToInvoiceItem($orderItem);
                $item->setQty($orderItem->getQtyToInvoice());
                $invoice->addItem($item);
            }
            $invoice->collectTotals();
            $invoice->register()->capture();
            Mage::getModel('core/resource_transaction')
            ->addObject($invoice)
            ->addObject($invoice->getOrder())
            ->save();
            return true;
        }

        return false;
    }

    /**
     *  Success payment page
     *
     *  @param    none
     *  @return	  void
     */
    public function successAction()
    {
        $session = Mage::getSingleton('checkout/session');
        $session->setQuoteId($session->getCosmoTenpayPaymentQuoteId());
        $session->unsCosmoTenpayPaymentQuoteId();

        $order = $this->getOrder();

        if (!$order->getId())
        {
            $this->norouteAction();
            return;
        }

        $order->addStatusToHistory(
        $order->getStatus(),
        Mage::helper('cosmotenpay')->__('Customer successfully returned from CosmoTenpay')
        );

        $order->save();

        $this->_redirect('checkout/onepage/success');
    }

    /**
     *  Failure payment page
     *
     *  @param    none
     *  @return	  void
     */
    public function errorAction()
    {
        $session = Mage::getSingleton('checkout/session');
        $errorMsg = Mage::helper('cosmotenpay')->__(' There was an error occurred during paying process.');

        $order = $this->getOrder();

        if (!$order->getId())
        {
            $this->norouteAction();
            return;
        }
        if ($order instanceof Mage_Sales_Model_Order && $order->getId())
        {
            $order->addStatusToHistory(
            Mage_Sales_Model_Order::STATE_CANCELED,//$order->getStatus(),
            Mage::helper('cosmotenpay')->__('Customer returned from CosmoTenpay.').$errorMsg
            );

            $order->save();
        }

        $this->loadLayout();
        $this->renderLayout();
        Mage::getSingleton('checkout/session')->unsLastRealOrderId();
    }
	
	
    
    
}
