<?php
/**
 * Created by PhpStorm.
 * User: Amit Thakur
 * Date: 15/8/18
 * Time: 5:54 PM
 */

namespace Amitshree\TextLocalSms\Observer;


use Amitshree\TextLocalSms\Helper\Data;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CustomerRegistrationSuccess implements ObserverInterface
{

    public function __construct(
        Data $helper,
        CustomerRepositoryInterface $customerRepository
    )
    {
        $this->helper = $helper;
        $this->customerRepository = $customerRepository;
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

        $customerId = $observer->getEvent()->getCustomer()->getId();
        $customer = $this->customerRepository->getById($customerId);
        $firstName  = $customer->getFirstname();
        $mobileNo = $customer->getCustomAttribute('mobile_no')->getValue();
       if ($mobileNo) {
           $message = $this->helper->getRegistrationMessage($firstName);
           $this->helper->sendSms($message, $mobileNo);
       }
    }
}