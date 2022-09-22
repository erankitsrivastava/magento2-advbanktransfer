<?php

namespace Tws\Advancebanktransfer\Model\ResourceModel;

use Magento\Framework\Filesystem\DirectoryList;

class Upitransactions extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected function _construct()
    {
        $this->_init('tws_upi_transactions', 'id');
    }

}
