<?php


namespace App\Services\Platform\Taobao\Client\Top\Request;


use App\Services\Platform\Taobao\Client\Top\TopRequest;
/**
 * 上传发票数据给阿里发票平台
 *
 * Class EinvoiceDetailUploadRequest
 * @package App\Services\Platform\Taobao\Client\Top\Request
 *
 * @method $this setInvoiceType($invoiceType)  必须 发票类型，blue=蓝票，red=红票 blue
 * @method $this setInvoiceItems($invoiceItems)  [] 必须 发票明细
 * @method $this setNormalInvoiceNo($normalInvoiceNo)  可选 原蓝票发票号码，红票是必填 00004348
 * @method $this setInvoiceNo($invoiceNo)  必须 发票号码 00004349
 * @method $this setInvoiceCode($invoiceCode)  必须 发票代码 111100000001
 * @method $this setNormalInvoiceCode($normalInvoiceCode)  可选 原蓝票发票代码，红票是必填 111100000000
 * @method $this setPayeeRegisterNo($payeeRegisterNo)  必须 销售方纳税人识别号（税号） 20150201321123
 * @method $this setInvoiceAmount($invoiceAmount)  必须 开票金额 100.00
 * @method $this setInvoiceDate($invoiceDate)  必须 开票日期 2018-04-01
 * @method $this setPlatformTid($platformTid)  必须 订单号 1000001557272964
 * @method $this setPayerName($payerName)  必须 购买方抬头 张三
 * @method $this setInvoiceKind($invoiceKind)  必须 发票种类，0=电子发票,1=纸质发票, 2=专票 0
 * @method $this setBusinessType($businessType)  必须 抬头类型，0=个人，1=企业 0
 * @method $this setChecksum($checksum)  可选 发票校验码 2384798473873
 * @method $this setInvoiceFileData($invoiceFileData)  [] 可选 发票数据，电子发票上传PDF 11
 * @method $this setSumPrice($sumPrice)  必须 不含税金额（合计） 100.00
 * @method $this setSumTax($sumTax)  必须 税额（合计） 0.00
 * @method $this setPlatformCode($platformCode)  必须 电商平台代码。TB=淘宝 、TM=天猫 、JD=京东、DD=当当、PP=拍拍、 YX=易讯、EBAY=ebay、QQ=QQ 网购、AMAZON=亚马逊、SN=苏宁 、GM=国美、WPH=唯品会、JM= 聚美、LF=乐蜂、MGJ=蘑菇街、JS= 聚尚、PX=拍鞋、YT=银泰、YHD=1 号店、VANCL=凡客、YL=邮乐、YG =优购、1688=阿里巴巴、POS=POS 门店、OTHER=其他, (只传英文编码) TM
 * @method $this setPayerRegisterNo($payerRegisterNo)  可选 购买方税号，企业抬头和专票时必填 20150201321124
 * @method $this setPayerPhone($payerPhone)  可选 购买方电话，专票必填 0571-1938334
 * @method $this setPayerAddress($payerAddress)  可选 购买方地址，专票必填 杭州市余杭区文一西路969号
 * @method $this setPayerBankaccount($payerBankaccount)  可选 购买方银行及账号，专票必填 招商银行 8372349
 * @method $this setReceiver($receiver)  可选 收票人姓名，纸票必填 李四
 * @method $this setReceiverEmail($receiverEmail)  可选 收票人邮箱，仅电子发票会发送邮箱
 * @method $this setReceiverPhone($receiverPhone)  可选 收票人电话，纸票必填 16678127635
 * @method $this setLogisticCode($logisticCode)  可选 快递单号 19827234762
 * @method $this setLogisticName($logisticName)  可选 快递公司 天天快递
 * @method $this setNeedLogistic($needLogistic)  可选 是否需要物流，true：logistic_code 和logistic_name必填，false： logistic_code和logistic_name不用填 true
 */
class EinvoiceDetailUploadRequest extends TopRequest
{
    public $requireHttps = true;

    protected $apiName = 'alibaba.einvoice.detail.upload';

    protected $commaSeparatedParams = [
    ];
    protected $paramKeys = [
        'invoiceType',
        'invoiceItems',
        'normalInvoiceNo',
        'invoiceNo',
        'invoiceCode',
        'normalInvoiceCode',
        'payeeRegisterNo',
        'invoiceAmount',
        'invoiceDate',
        'platformTid',
        'payerName',
        'invoiceKind',
        'businessType',
        'checksum',
        'invoiceFileData',
        'sumPrice',
        'sumTax',
        'platformCode',
        'payerRegisterNo',
        'payerPhone',
        'payerAddress',
        'payerBankaccount',
        'receiver',
        'receiverEmail',
        'receiverPhone',
        'logisticCode',
        'logisticName',
        'needLogistic',
    ];

    public function setContentTypeMultipart()
    {
        $this->contentType = 'multipart';
    }

    public function getMultiPartData($data)
    {
        $multiPart = [];
        foreach ($data as $k => $v) {
            if ("invoice_file_data" != $k)//判断是不是文件上传
            {
                $multiPart[] = [
                    'name'     => $k,
                    'contents' => $v,
                ];
            }
        }
        unset($k, $v);
        if (isset($data['invoice_file_data'])) {

            $multiPart[] = [
                'name'     => 'invoice_file_data',
                'contents' => fopen($data['invoice_file_data'], 'r'), // 读取文件
            ];
        }

        return $multiPart;
    }


    public function check()
    {
        // RequestCheckUtil::checkNotNull($this->invoiceType, "invoice_type");
        // RequestCheckUtil::checkNotNull($this->invoiceItems, "invoice_items");
        // RequestCheckUtil::checkNotNull($this->invoiceNo, "invoice_no");
        // RequestCheckUtil::checkNotNull($this->invoiceCode, "invoice_code");
        // RequestCheckUtil::checkNotNull($this->payeeRegisterNo, "payee_register_no");
        // RequestCheckUtil::checkNotNull($this->invoiceAmount, "invoice_amount");
        // RequestCheckUtil::checkNotNull($this->invoiceDate, "invoice_date");
        // RequestCheckUtil::checkNotNull($this->platformTid, "platform_tid");
        // RequestCheckUtil::checkNotNull($this->payerName, "payer_name");
        // RequestCheckUtil::checkNotNull($this->invoiceKind, "invoice_kind");
        // RequestCheckUtil::checkNotNull($this->businessType, "business_type");
        // RequestCheckUtil::checkNotNull($this->sumPrice, "sum_price");
        // RequestCheckUtil::checkNotNull($this->sumTax, "sum_tax");
        // RequestCheckUtil::checkNotNull($this->platformCode, "platform_code");
    }

    public function getApiMethodName()
    {
        return $this->apiName;
    }

    public function getApiParas()
    {
        return $this->data;
    }

    public function putOtherTextParam($key, $value)
    {
        // TODO: Implement putOtherTextParam() method.
    }
}
