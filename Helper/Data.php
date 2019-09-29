<?php
/**
 * Created by PhpStorm.
 * User: Amit Thakur
 * Date: 15/8/18
 * Time: 6:19 PM
 */
namespace Amitshree\TextLocalSms\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Twilio\Rest\Client;

class Data extends AbstractHelper {

    const XML_PATH_TEXT_LOCAL = 'textlocal/';
    const XML_REGISTER_SUCCESS_MESSAGE = 'registration_message';
    const XML_ORDER_SUCESS_MESSAGE = 'order_success_message';
    const XML_MODULE_ENABLED = 'enable';
    const TEST_MODE_ENABLED = 'test_mode_enabled';

    public function __construct(
        Context $context,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->logger = $logger;
        parent::__construct($context);
    }


    public function getConfigValues($field, $storeId) {

        return $this->scopeConfig->getValue(
            $field, ScopeInterface::SCOPE_STORE, $storeId
        );
    }
    public function getGeneralConfig($code, $storeId = null) {
        return $this->getConfigValues(self::XML_PATH_TEXT_LOCAL . 'general/' . $code, $storeId);
    }

    public function getRegistrationMessage($firstName) {
        $messageTemplate = $this->getGeneralConfig(self::XML_REGISTER_SUCCESS_MESSAGE);
        if(preg_match('/\{\{([a-z0-9_]+)\}\}/', $messageTemplate, $matches)) {
            $message = str_replace('{{' . $matches[1] . '}}', $firstName, $messageTemplate);
        };
        return $message;
    }

        public function getOrderMessage($firstName, $orderId) {
        $messageTemplate = $this->getGeneralConfig(self::XML_ORDER_SUCESS_MESSAGE);
        $message = str_replace('{{first_name}}', $firstName, $messageTemplate);
        $message = str_replace('{{order_id}}', $orderId, $message);
        return $message;
    }

    public function isModuleEnabled() {
        return $this->getGeneralConfig(self::XML_MODULE_ENABLED);
    }

    public function isTestModeEnabled() {
        return $this->getGeneralConfig(self::TEST_MODE_ENABLED);
    }

    public function sendSms($message, $mobileNo) {
        $apiKey = $this->getGeneralConfig('api_key');
        $senderName = $this->getGeneralConfig('sender_name');
        $testModeEnabled = $this->isTestModeEnabled();

        // Account details
        $apiKey = urlencode($apiKey);

        // Message details
        $numbers = array($mobileNo);

        $message = rawurlencode($message);
        $numbers = implode(',', $numbers);

        // Prepare data for POST request
        $data = array('apikey' => $apiKey, 'numbers' => $numbers, "message" => $message);

        if (!$testModeEnabled) {
            $sender = urlencode($senderName);
            $data['sender'] = $sender;
        }
        if ($testModeEnabled) {
            $sender = urlencode('TXTLCL');
            $data['sender'] = $sender;
            $data['test'] = true;
        }

        // Send the POST request with cURL
        $ch = curl_init('https://api.textlocal.in/send/');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
    }
}