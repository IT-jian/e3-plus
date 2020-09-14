<?php


namespace App\Services\Hub;


final class HubRequestEnum
{
    const TRADE_CREATE = 'tradeCreate';
    const TRADE_CANCEL = 'tradeCancel';
    const STEP_TRADE_CREATE = 'stepTradeCreate';
    const STEP_TRADE_PAID = 'stepTradePaid';
    const STEP_TRADE_CANCEL = 'stepTradeCancel';
    const TRADE_ADDRESS_MODIFY = 'tradeAddressModify';
    const REFUND_RETURN_LOGISTIC_MODIFY = 'refundReturnLogisticModify';
    const REFUND_RETURN_CREATE = 'refundReturnCreate';
    const REFUND_RETURN_CREATE_EXTEND = 'refundReturnCreateExtend';
    const REFUND_RETURN_CANCEL = 'refundReturnCancel';
    const REFUND_CREATE = 'refundCreate';
    const REFUND_CANCEL = 'refundCancel';
    const EXCHANGE_RETURN_LOGISTIC_MODIFY = 'exchangeReturnLogisticModify';
    const EXCHANGE_CREATE = 'exchangeCreate';
    const EXCHANGE_CREATE_EXTEND = 'exchangeCreateExtend';
    const EXCHANGE_CANCEL = 'exchangeCancel';
    const SKU_INVENTORY_UPDATE_ACK = 'skuInventoryUpdateAck';
}
