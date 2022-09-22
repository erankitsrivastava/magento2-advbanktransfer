<?php

namespace Tws\Advancebanktransfer\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;


class Validate extends Action
{
    const genuine_token = "ASDFGHJKTEWXC";
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;
    /**
     * Validate constructor.
     * @param Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultjson
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultjson
    ) {
        $this->resultJsonFactory = $resultjson;
        parent::__construct($context);
    }
    public function execute()
    {
       
        $_POST['reference_number'] = "014908842684";
        $_POST['token'] ="ASDFGHJKTEWXC";
        $_POST['amount'] = "123";
        if(isset($_POST['reference_number']) && isset($_POST['amount']) && isset($_POST['token'])){
            $reference_number = trim(strip_tags($_POST['reference_number']));
            $amount = trim(strip_tags($_POST['amount']));
            $token = trim(strip_tags($_POST['token']));

            if(!empty($reference_number) && !empty($amount) && !empty($token) && $this->sent_token_genuine($token)){
                //DO YOUR PROCESSING
                if($this->process_transaction_details($reference_number, $amount, $token)){
                    exit("OK");
                }
            }
        }

        exit("false");
    }
    public function process_transaction_details($reference_number, $amount, $token){
        if(!empty($reference_number) && !empty($amount)&& !empty($token)){

            $upitransactions = $this->_objectManager->create("Tws\Advancebanktransfer\Model\Upitransactions");
            $upitransactions->load($reference_number, "transaction_id");
            if(empty($upitransactions->getData())){
                $upitransactions = $this->_objectManager->create("Tws\Advancebanktransfer\Model\Upitransactions");
                $upitransactions->setData("transaction_id", $reference_number);
                $upitransactions->setData("amount", $amount);
                $upitransactions->setData("token", $token);
                $upitransactions->setData("module-catalog", new \DateTime('now'));
                $upitransactions->save();
                file_put_contents("transaction-details.txt", file_get_contents("transaction-details.txt").json_encode($_POST).'========='.date("h:i:sa"));
                return true;
            }
        }
        return false;
    }

    //FUNCTION TO CHECK IF TOKEN IS GENUINE
    public function sent_token_genuine($sent_token){
        return ($sent_token == self::genuine_token);
    }
}
