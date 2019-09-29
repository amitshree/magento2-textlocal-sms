<?php
/**
 * Created by PhpStorm.
 * User: Amit Thakur
 * Date: 15/8/18
 * Time: 6:08 PM
 */

namespace Amitshree\TextLocalSms\Observer;


use Amitshree\TextLocalSms\Helper\Data;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Session;

class OrderSuccess implements ObserverInterface
{


    public function __construct(
        Session $session,
        Data $helper
    )
    {
        $this->session = $session;
        $this->helper = $helper;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $isModuleEnabled = $this->helper->isModuleEnabled();
        if (!$isModuleEnabled) {
            return $this;
        }

        $orderId = $observer->getEvent()->getOrderIds()[0];
        $customer = $this->session->getCustomer();
        $firstName = $customer->getFirstname();
        $mobileNo = $customer->getMobileNo();
        $message = $this->helper->getOrderMessage($firstName, $orderId);
        $this->helper->sendSms($message, $mobileNo);
    }
}