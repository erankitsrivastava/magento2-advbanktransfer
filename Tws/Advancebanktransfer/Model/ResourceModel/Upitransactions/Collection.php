<?php

namespace Tws\Advancebanktransfer\Model\ResourceModel\Upitransactions;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'id';

    protected function _construct()
    {
        $this->_init(
            'Tws\Advancebanktransfer\Model\Upitransactions',
            'Tws\Advancebanktransfer\Model\ResourceModel\Upitransactions'
        );
    }
}
