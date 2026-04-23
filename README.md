# CFS Assessment

The CFS assessment module for Magento 2.

## Description

This module adds lifetime revenue column to the admin order grid which displays the total order value for each customer.

## Features

- Displays an additional column on the order grid populated with the total revenue value by customer
- Revenue value considers all orders for a customer, even across multiple, thus the value shows in base currency
- Revenue will only be shown for registered customers
- The revenue value will sum all 'complete' orders for a customer indicating the order has been paid for and fully shipped
- No revenue value will be shown for guest checkout orders

## Requirements

- Magento 2.4.6 or higher
- PHP 8.2 or higher

## Installation

### Via Composer (Recommended)

```bash
composer require husseycoding/cfs_assessment
bin/magento module:enable CFS_Assessment
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static-content:deploy
bin/magento cache:flush
```

### Manual Installation

1. Download the extension
2. Extract to `app/code/CFS/Assessment`
3. Run the following commands:

```bash
bin/magento module:enable CFS_Assessment
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static-content:deploy
bin/magento cache:flush
```

## Configuration

No specific configuration required, but you can choose to show/hide and drag the additional column as usual using the standard order grid configuration

## Usage

When viewing the admin order grid, the additional column should automatically be displayed

## Technical build notes

This  displays base currency instead of purchase currency due to the possibility of different currencies per store

This populates the grid on a per row basis, it does not alter the order grid collection. This was done for performance reasonms, with the trade off being that you cannot order or filter by the columns

I considered altering the main MySQL query adding a GROUP BY and SUM to calculate the total order revenue, but this has drawbacks

We really need to use a correlated subquery to properly determine the correct data in this way, but this will have an unacceptable performance impact

The scope of the task is just to add a column to the order grid, so we don't want to impact the processing of order collections universally, just add logic to the grid

Another approach is to add an extension attribute into a new table for the collection, but this also has drawbacks

It introduces the need to calculate and update this value every time an order is placed, thus there is a performance overhead

If it's done inline on order placement this will affect the customer experience

If it's done asynchronously using a consumer queue or cron job then the population of this value won't be in real time which could be confusing and lead to inaccuracies

Both of these approaches introduce a performance overhead which really isn't needed just to achieve the narrow spec, and these types of approach could well introduce measurable performance issues at scale

The primary issue with adding this column to the order grid, is that you are introducing a many to many data relationship, with the possibility of having multiple orders in the grid which relate to the same customer, and needing to join and sum them against multiple orders to determine the revenue

Overall, the approach taken is better for the given, narrow spec, as we are only ever dealing with a limited collection size for the grid, so the solution is scalable regardless of how many orders there are

The trade off however is that with this approach you are not able to sort or filter by the column, but considering a solution at scale, this approach seems like the most acceptable solution. If being able to sort and filter by total revenue is key, I would actually suggest either:
- A new UI component grid specific to total customer revenue, backed up by it's own DB data source
- Adding this column to the customer grid instead where it would be possible to perform a join against the order collection with a GROUP BY and SUM to get the correct revenue total, giving a one to many, rather than many to many data relationship

Additionally part of the spec was to consider future similar applications, for instance average order value. With the approach used, it would be straightforward to either add a new column in similar fashion for average order value, or even update the column added here if that was preferable

This checks for existence of a customer against the order and shows no revenue for guest orders

This only considers orders with complete status indicating payment has been received and the order has been fully shipped

## Support

For support, please contact hussey@husseycoding.co.uk

## License

This extension is licensed under the [Open Software License (OSL) version 3.0](LICENSE.txt)
