<?php
declare(strict_types=1);

namespace CFS\Assessment\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Framework\DB\Select;

/**
 * Helper to get the total revenue value for a given customer
 */
class CustomerTotalRevenue extends AbstractHelper
{
    /**
     * Array to store revenue values by customer ID that we have already
     * calculated
     * 
     * We store this for cases where we encounter the same customer for multiple
     * orders
     *
     * @var array
     */
    protected array $revenueByCustomerId = [];
    
    /**
     * @param CollectionFactory $orderCollectionFactory
     * @param Context $context
     */
    public function __construct(
        protected CollectionFactory $orderCollectionFactory,
        Context $context
    ) {
        parent::__construct(
            $context
        );
    }
    
    /**
     * Method to get a customers total order revenue by customer ID
     * 
     * Here we use the collection object rather than repository getList() method
     * as we need to perform MySQL SUM and GROUP BY operations
     *
     * @param int $customerId
     *
     * @return float
     */
    public function getRevenue(
        int $customerId
    ): float {
        if (isset($this->revenueByCustomerId[$customerId])) {
            return $this->revenueByCustomerId[$customerId];
        }
        
        $customerOrders = $this->orderCollectionFactory->create($customerId);
        $customerOrders->getSelect()
            ->reset(Select::COLUMNS)
            ->columns([
                'base_grand_total',
                'total_revenue' => new \Zend_Db_Expr('SUM(main_table.base_grand_total)')
            ])
            ->where('main_table.status = ?', 'complete')
            ->group('main_table.customer_id');
        
        $result = $customerOrders->getData();
        $this->revenueByCustomerId[$customerId] = 0;
        if (!empty($result[0]['total_revenue'])) {
            $this->revenueByCustomerId[$customerId] = (float) $result[0]['total_revenue'];
        }
        
        return $this->revenueByCustomerId[$customerId];
    }
}
