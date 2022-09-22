<?php

namespace Tws\Advancebanktransfer\Cron;

class Cron
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $_objectManager
    )
    {
        $this->_objectManager = $_objectManager;
    }

    public function createinvoice()
    {
        /*
         * method : purchaseorder
         * amount_ordered : amount
         * po_number : transaction_id
         *
         * */
        $resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tws_upi_transactions = $resource->getTableName('tws_upi_transactions'); //gives table name with prefix
        $sales_order_payment = $resource->getTableName('sales_order_payment'); //gives table name with prefix

        //Select Data from table
        $sql = "SELECT $tws_upi_transactions.id,
                       $tws_upi_transactions.transaction_id, 
                       $tws_upi_transactions.amount, 
                       $sales_order_payment.parent_id as order_id,
                       $sales_order_payment.amount_ordered,
                       $tws_upi_transactions.amount
                    FROM $tws_upi_transactions
                    RIGHT JOIN $sales_order_payment
                         ON $sales_order_payment.po_number= $tws_upi_transactions.transaction_id
                   WHERE $sales_order_payment.method= 'purchaseorder'
                          AND $sales_order_payment.amount_ordered = $tws_upi_transactions.amount";

        $data = $connection->fetchAll($sql); // gives associated array, table fields as key in array.
        foreach ($data as $item){
           try{
               if((float)$item['amount'] == (float)$item['amount_ordered']){
                   /*create invoice*/
                   $transaction_id = $item['transaction_id'];
                   $comment = $this->_objectManager->create('Magento\Sales\Api\Data\InvoiceCommentCreationInterface');
                   $comment->setComment("Invoice for ordr elated to transaction $transaction_id id has created");
                   $comment->setIsVisibleOnFront(0);
                   $invoiceinterface = $this->_objectManager->create('Magento\Sales\Api\InvoiceOrderInterface');
                   $invoiceinterface->execute(
                       $item['order_id'],
                       false,
                       [],
                       true,
                       true,
                       $comment
                   );
                   $upitransactions = $this->_objectManager->create("Tws\Advancebanktransfer\Model\Upitransactions");
                   $upitransactions->load($item['transaction_id'], "transaction_id");
                   $upitransactions->delete();
               }else{
                   /*add error message that the amount is not same*/
                   $transAmount = $item['amount'];
                   $amount_ordered = $item['amount_ordered'];
                   $order = $this->_objectManager->create('\Magento\Sales\Model\Order')->load($item['order_id']);
                   $order->addStatusHistoryComment(
                       "Unable to create automatic invoice the ordered amount and the transaction amount is different. ordered amount is $amount_ordered and the transaction amount is $transAmount"
                   );
                   $order->save();
               }
           }catch (\Exception $e){
               /*add error message that the amount is not same*/
               $order = $this->_objectManager->create('\Magento\Sales\Model\Order')->load($item['order_id']);
               $order->addStatusHistoryComment(
                   "Something happen wrong, unable to create automatic invoice. Mangwale Admin please create the invoice manually"
               );
               $order->save();
           }
        }
    }
}
