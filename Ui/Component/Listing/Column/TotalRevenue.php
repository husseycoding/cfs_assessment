<?php
declare(strict_types=1);

namespace CFS\Assessment\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use CFS\Assessment\Helper\CustomerTotalRevenue;
use Magento\Ui\Component\Listing\Columns\Column as CoreColumn;
use Magento\Directory\Model\Currency;

/**
 * Class to generate the total customer revenue column data for the order grid
 */
class TotalRevenue extends CoreColumn
{
    /**
     * @param Currency $currency
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        protected Currency $currency,
        protected CustomerTotalRevenue $customerTotalRevenue,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct(
            $context,
            $uiComponentFactory,
            $components,
            $data
        );
    }

    /**
     * Populate the grid data source with the total revenue column values
     *
     * @param array $dataSource The grid data source
     * 
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as $key => $item) {
                if (!empty($item['customer_id'])) {
                    $currencyCode = $item['base_currency_code'] ?? null;
                    $baseCurrency = $this->currency->load($currencyCode);
                    $customerId = (int) $item['customer_id'];
                    $revenue = $this->customerTotalRevenue->getRevenue($customerId);
                    $dataSource['data']['items'][$key]['total_revenue'] = $baseCurrency->format($revenue, [], false);
                }
            }
        }

        return $dataSource;
    }
}
