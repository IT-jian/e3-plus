<?php


namespace App\Services\HubApi\Adidas\Taobao\Api;

use App\Models\Sys\Shop;
use App\Models\SysStdTrade;
use App\Services\HubApi\BaseApi;
use App\Services\Platform\Exceptions\PlatformClientSideException;
use App\Services\Platform\Exceptions\PlatformServerSideException;
use App\Services\Platform\Taobao\Client\Top\Request\AlibabaEinvoiceDetailUploadRequest;
use App\Services\Platform\Taobao\Client\Top\TopClient;

/**
 * 发票回传
 *
 * Class EinvoiceDetailUploadHubApi
 * @package App\Services\HubApi\Adidas\Taobao\Api
 */
class AlibabaEinvoiceDetailUploadHubApi extends BaseApi
{
    protected $notNullFields = [
        "invoice_type",
        "invoice_items",
        "invoice_no",
        "invoice_code",
        "payee_register_no",
        "invoice_amount",
        "invoice_date",
        "platform_tid",
        "payer_name",
        "invoice_kind",
        "business_type",
        "sum_price",
        "sum_tax",
        "platform_code",
        "invoice_file_url",
    ];

    public function check()
    {
        $data = $this->data['data'] ?? [];
        foreach ($this->getNotNullFields() as $fieldName) {
            if (!isset($data[$fieldName]) || self::checkEmpty($data[$fieldName])) {
                throw new PlatformClientSideException("adaptor-check-error:Missing Required Arguments:" .$fieldName , 422);
            }
        }

        return true;
    }

    public function proxy()
    {
        $request = new AlibabaEinvoiceDetailUploadRequest();
        $input = $this->data['data'];
        $request->setInvoiceType($input['invoice_type']);
        $request->setInvoiceItems(json_encode($input['invoice_items']));
        if (isset($input['normal_invoice_no'])) {
            $request->setNormalInvoiceNo($input['normal_invoice_no']);
        }
        $request->setInvoiceNo($input['invoice_no']);
        $request->setInvoiceCode($input['invoice_code']);
        if (isset($input['normal_invoice_code'])) {
            $request->setNormalInvoiceCode($input['normal_invoice_code']);
        }
        $request->setPayeeRegisterNo($input['payee_register_no']);
        $request->setInvoiceAmount($input['invoice_amount']);
        $request->setInvoiceDate($input['invoice_date']);
        $request->setPlatformTid($input['platform_tid']);
        $request->setPayerName($input['payer_name']);
        $request->setInvoiceKind($input['invoice_kind']);
        $request->setBusinessType($input['business_type']);
        if (isset($input['checksum'])) {
            $request->setChecksum($input['checksum']);
        }
        $request->setSumPrice($input['sum_price']);
        $request->setSumTax($input['sum_tax']);
        $request->setPlatformCode($input['platform_code']);
        if (isset($input['payer_register_no'])) {
            $request->setPayerRegisterNo($input['payer_register_no']);
        }
        if (isset($input['payer_phone'])) {
            $request->setPayerPhone($input['payer_phone']);
        }
        if (isset($input['payer_address'])) {
            $request->setPayerAddress($input['payer_address']);
        }
        if (isset($input['payer_bankaccount'])) {
            $request->setPayerBankaccount($input['payer_bankaccount']);
        }
        if (isset($input['receiver'])) {
            $request->setReceiver($input['receiver']);
        }
        if (isset($input['receiver_email'])) {
            $request->setReceiverEmail($input['receiver_email']);
        }
        if (isset($input['receiver_phone'])) {
            $request->setReceiverPhone($input['receiver_phone']);
        }
        if (isset($input['logistic_code'])) {
            $request->setLogisticCode($input['logistic_code']);
        }
        if (isset($input['logistic_name'])) {
            $request->setLogisticName($input['logistic_name']);
        }
        if (isset($input['need_logistic'])) {
            $request->setNeedLogistic($input['need_logistic']);
        }

        $fileName = md5($input['platform_tid'] . $input['invoice_no'] . $input['invoice_code']) . '.pdf';
        $filePath = app()->storagePath($fileName);
        file_put_contents($filePath, file_get_contents($input['invoice_file_url']));
        if (isset($input['invoice_file_url'])) {
            $request->setInvoiceFileData('@' . $filePath);
        }
        $trade = SysStdTrade::where('tid', $input['platform_tid'])->firstOrFail(['shop_code']);
        $shop = Shop::getShopByCode($trade['shop_code']);
        $topClient = new TopClient();
        $bestUrl = 'https://eco.taobao.com/router/rest';
        $result = $topClient->shop($shop)->execute($request, $shop['access_token'], $bestUrl);
        \Illuminate\Support\Facades\File::delete($filePath);
        if (isset($result['error_response']) && !empty($result['error_response'])) {
            throw (new PlatformServerSideException())->setResponseBody($result['error_response']);
        }
        return $this->responseSimple($result);
    }

    public function isSuccess($response)
    {
        return data_get($response, 'alibaba_einvoice_detail_upload_response.is_success', false);
    }
}
