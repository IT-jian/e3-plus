<?php


namespace App\Services\Adaptor;


final class AdaptorTypeEnum
{
    const TRADE = 'trade';
    const REFUND = 'refund';
    const EXCHANGE = 'exchange';
    const ITEM = 'item';
    const SKU = 'sku';
    const TRADE_BATCH = 'tradeBatch';
    const REFUND_BATCH = 'refundBatch';
    const EXCHANGE_BATCH = 'exchangeBatch';
    const ITEM_BATCH = 'itemBatch';
    const STEP_TRADE = 'stepTrade';
    const STEP_TRADE_BATCH = 'stepTradeBatch';
    const COMMENTS = 'comments';
    const JD_ORDER_SPLIT_AMOUNT = 'orderSplitAmount';
    const REFUND_UPDATE = 'refundUpdate';
    const REFUND_APPLY = 'refundApply';
}
