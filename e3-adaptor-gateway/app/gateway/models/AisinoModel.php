<?php
/*
* File name
* 
* @author xy.wu
* @since 2020/3/27 14:37
*/

namespace gateway\app\gateway\models;

use gateway\app\CommonModel;
use gateway\boot\CommonTool;

class AisinoModel extends CommonModel
{
    public function execute($request = array())
    {
        $header = getallheaders();
        $this->header = &$header;
        $this->request = &$request;

        //记录日志
        $this->logFileName = $logFileName = 'aisino';
        $msg = "Request: " . var_export($request, true) . "\tHeader: " . var_export($header, true);
        CommonTool::debugLog($msg, $logFileName);
        if (isset($request['__show*debug__']) && $request['__show*debug__'] == 1) {
            print_r("<hr/>Request=");
            print_r($request);
            print_r("<hr/>Header=");
            print_r($header);
        }

        if (!isset($header['Marketplace-Type']) || empty($header['Marketplace-Type'])) {
//            $return = array('status' => 'api-invalid-parameter', 'message' => 'Missing Required Header Marketplace-Type', 'data' => array());
//            return $return;
            $header['Marketplace-Type'] = 'tmall';
        }
        $maketplaceType = strtolower(trim($header['Marketplace-Type']));
        if (!in_array($maketplaceType, array('tmall', 'jd'))) {
            $return = array('status' => 'api-invalid-parameter', 'message' => 'Not Allowed Marketplace-Type[' . trim($header['Marketplace-Type']) . '] Calls', 'data' => array());
            return $return;
        }

        if ((!isset($header['Source']) || strtolower($header['Source']) != 'aisino') && (!isset($header['Method']) || empty($header['Method']) || $header['Method'] != 'aisino.sh.fpkj')) {
            $return = array('status' => 'api-invalid-parameter', 'message' => 'Missing Required Header Method1', 'data' => array());
            return $return;
        }
        if (!isset($header['Method']) || empty($header['Method'])) {
            //aisino.sh.fpkj
            $header['Method'] = 'aisino.sh.push.invoice';
        }

        //获取xml格式数据
        if (isset($request['data']) && !empty(isset($request['data']))) {
            $content = $request['data'];
        } else {
            $content = file_get_contents('php://input');
        }

        $msg = "Request: " . var_export($request, true) . "\tHeader: " . var_export($header, true) . "\tContent: " . var_export($content, true);
        CommonTool::debugLog($msg, $logFileName);

        if ($header['Method'] == 'aisino.sh.fpkj') {
            $return = $this->aisino_sh_fpkj($content);
        } else if ($header['Method'] == 'aisino.sh.push.invoice') {
            $return = $this->aisino_sh_push_invoice($content);
        } else {
            $return = array('status' => 'api-invalid-parameter', 'message' => 'Not Allowed Header Method', 'data' => array());
        }

        $msg = __METHOD__ . " Response: " . var_export($return, true);
        CommonTool::debugLog($msg, $logFileName);
        if (isset($request['__show*debug__']) && $request['__show*debug__'] == 1) {
            print_r("<hr/>Response=");
            print_r($return);
        }

        //aisino http状态吗都返回200
        $return['code'] = 200;
        return $return;
    }

    public function aisino_sh_fpkj(&$content)
    {
        //校验签名
        if ($this->checkAdaptorAuthorization($content) === false) {
            $return = array('status' => 'api-invalid-parameter-authentication', 'message' => 'Authorization Error', 'data' => array());
            return $return;
        }

        GB()->setState('aisinoapi');
        $logFileName = 'aisino';

        //通过环境变量获取配置，获取不到再从配置文件获取
        $url = CommonTool::loadEnv("aisino.url");
//        if (substr($this->appToken['url'], -1) != '/') {
//            $this->appToken['url'] .= '/';
//        }
        if (isset($this->request['__show*debug__']) && $this->request['__show*debug__'] == 1) {
            print_r("<hr/>content=");
            print_r($content);
        }

        $msg = "aisino_sh_fpkj content: " . var_export($content, true);
        CommonTool::debugLog($msg, $logFileName);

        $json = json_decode($content, true);
        $msg = "aisino_sh_fpkj content json: " . var_export($json, true);
        CommonTool::debugLog($msg, $logFileName);

        if (isset($this->request['__show*debug__']) && $this->request['__show*debug__'] == 1) {
            print_r("<hr/>json=");
            print_r($json);
        }
        if (empty($json) || !isset($json['orders']) || !is_array($json['orders']) || count($json['orders']) < 1) {
            $return = array('status' => 'api-invalid-parameter', 'message' => 'Content Json Error', 'data' => array());
            return $return;
        }

        $content = $this->get_aisino_json($json);
        $content = strtoupper($content);

        $msg = "aisino_sh_fpkj content encrypt: " . var_export($content, true);
        CommonTool::debugLog($msg, $logFileName);
        if (isset($this->request['__show*debug__']) && $this->request['__show*debug__'] == 1) {
            print_r("<hr/>content=");
            print_r($content);
        }

        //header
        $post_header = array(
//            "Content-type: application/x-www-form-urlencoded",
        );
        $post_data = array();
        $post_data['HEADER'] = array(
            "CONSUMER_SYSCODE" => CommonTool::loadEnv('aisino.consumer_syscode'),
            "SIGN_PWD" => CommonTool::loadEnv('aisino.sign_pwd'),
            "DTSEND" => date('YmdHis'),
        );
        $post_data['HEADER'] = json_encode($post_data['HEADER']);
        $post_data['DATA'] = $content;

        //发送请求
        $msg = "Post Url: " . $url . "\tPost Header: " . var_export($post_header, true) . "\tPost Data: " . var_export($post_data, true);
        CommonTool::debugLog($msg, $logFileName);
        if (isset($this->request['__show*debug__']) && $this->request['__show*debug__'] == 1) {
            print_r("<hr/>Post Url=");
            print_r($url);
            print_r("<hr/>Post Header=");
            print_r($post_header);
            print_r("<hr/>Post Data=");
            print_r($post_data);
        }

        $content = "HEADER=" . urlencode($post_data['HEADER']) . "&DATA=" . urlencode($post_data['DATA']);
        $msg = "Post Url: " . $url . "\tPost Content: " . $content;
        CommonTool::debugLog($msg, $logFileName);
        $result = CommonTool::curl($url, $content, $post_header, true);

        $msg = "Post Url: " . $url . "\tResponse: " . var_export($result, true);
        CommonTool::debugLog($msg, $logFileName);
        if (isset($this->request['__show*debug__']) && $this->request['__show*debug__'] == 1) {
            print_r("<hr/>Response=");
            print_r($result);
        }

        $return = array('status' => 'api-success', 'code' => 200, 'message' => '', 'data' => array());
        if ($result['status'] == -1) {
            $return['data']['ResponseCode'] = 'FAIL';
            $return['data']['ResponseMessage'] = 'Call Aisino Failed:' . $result['message'];
        } else if ($result['status'] == 1 && !isset($result['data']) || empty($result['data'])) {
            $return['data']['ResponseCode'] = 'FAIL';
            $return['data']['ResponseMessage'] = 'Call Aisino Failed: Unknown Error';
        } else {
            $json = json_decode($result['data'], true);
            if ($json['status'] == '0000') {
                $return['data']['ResponseCode'] = 'SUCCESS';
                $return['data']['ResponseMessage'] = "Call Aisino Success: Status[{$json['status']}];Message[{$json['message']}]";
            } else {
                $return['data']['ResponseCode'] = 'FAIL';
                $return['data']['ResponseMessage'] = "Call Aisino Failed: Status[{$json['status']}];Message[{$json['message']}]";
            }
        }
//        $return['data'] = json_encode($return['data']);

        $msg = "Post Url: " . $url . "\tResult: " . var_export($return, true);
        CommonTool::debugLog($msg, $logFileName);

        return $return;
    }

    public function aisino_sh_push_invoice($content)
    {
        $logFileName = 'aisino';
        $content = $this->decrypt($content);

        $msg = "aisino_sh_push_invoice: " . "\tContent Decrypt: " . var_export($content, true);
        CommonTool::debugLog($msg, $logFileName);

        $json = json_decode($content, true);
        $msg = "aisino_sh_push_invoice: " . "\tContent Json Decode: " . var_export($json, true);
        CommonTool::debugLog($msg, $logFileName);

        if (isset($this->request['__show*debug__']) && $this->request['__show*debug__'] == 1) {
            print_r("<hr/>Content Decrypt=");
            print_r($content);
            print_r("<hr/>Content Json Decode=");
            print_r($json);
        }

        if (!is_array($json) || empty($json) || !isset($json['invoice_no']) || !isset($json['invoice_items']) || !is_array($json['invoice_items'])) {
            $return = array('status' => 'api-invalid-parameter', 'message' => 'Illegal Parameter', 'data' => array());
            return $return;
        }

        //接口兼容，两位小数
        $json['invoice_amount'] = sprintf("%1\$.2f", round($json['invoice_amount'], 2));
        $json['sum_price'] = sprintf("%1\$.2f", round($json['sum_price'], 2));
        $json['sum_tax'] = sprintf("%1\$.2f", round($json['sum_tax'], 2));

        foreach ((array)$json['invoice_items'] as $key=>$item) {
            $json['invoice_items'][$key]['tax'] = sprintf("%1\$.2f", round($item['tax'], 2));
            $json['invoice_items'][$key]['price'] = sprintf("%1\$.2f", round($item['price'], 2));
            $json['invoice_items'][$key]['sum_price'] = sprintf("%1\$.2f", round($item['sum_price'], 2));
            $json['invoice_items'][$key]['amount'] = sprintf("%1\$.2f", round($item['amount'], 2));
        }

        $data = array();
        $data['method'] = 'e3plus.oms.einvoice.detail.upload';
        $data['timestamp'] = date('Y-m-d H:i:s');
        $data['platform_tid'] = $json['platform_tid'];
        $data['data'] = $json;

        $omnihubModel = new OmnihubModel();
        $json = GB()->formatJson($data);
        $omnihubModel->setHeader($this->header);
        return $omnihubModel->send($json);
    }

    /**
     * {"apply":{"memo":"我是备注","status":1,"sum_tax":"10.00","sum_price":"90.00","gmt_create":"2018-09-18 21:09:10","payer_bank":"浙商银行","payer_name":"张三","payer_phone":"010-1234567","extend_props":"{}","invoice_kind":0,"invoice_type":"blue","platform_tid":"246371000001","business_type":0,"invoice_items":{"invoice_item":[{"tax":"10.00","unit":"台","price":"100.00","amount":"100.00","quantity":"1","row_type":"0","tax_rate":"0.00","item_name":"电视机","sum_price":"90.00","biz_order_id":"198738479342342","specification":"X100","is_post_fee_row":true}]},"payer_address":"浙江省杭州市余杭区","platform_code":"TM","invoice_amount":"100.00","trigger_status":"buyer_payed","gmt_modified_str":"2018-09-18 21:09:12","payer_bankaccount":"62003449373273384","payer_register_no":"9132209873843221"},"orders":[{"yf":"0.00","sjr":"王大大","qdbz":"0","sjdh":"18855356353","sjdz":"新港中路397号海珠区广东省广州市GD510000CN","phone":"18855356353","Detail":[{"num":"1","hsbz":"1","item":"1","spbm":"Tmall Football","spec":"Ultraboost Shoes 中性跑步鞋","unit":"PIECE","zxbm":"","fphxz":"0","lslbs":"","price":"599.00","skucd":"660","taxamt":"","yhzcbs":"","taxrate":"13.00","zzstsgl":"","goodsname":"Ultraboost Shoes 中性跑步鞋"},{"num":"1","hsbz":"1","item":"2","spbm":"Tmall Football","spec":"Ultraboost Shoes 中性跑步鞋","unit":"PIECE","zxbm":"","fphxz":"0","lslbs":"","price":"599.00","skucd":"670","taxamt":"","yhzcbs":"","taxrate":"13.00","zzstsgl":"","goodsname":"Ultraboost Shoes 中性跑步鞋"}],"kptype":"1","qdxmmc":"","taxnum":"0123456789","OrderNo":"ADWECOM61763200060","account":"1202026219900061029","address":"新港中路397号海珠区广东省广州市GD510000CN","message":["ShipmentNo=A2408701060-1,Status=Delivered,DeliveryScanDate=2020-05-28"],"StoreNum":["9524","9524"],"buyername":"王大大","dataSoure":"ecom","lshStatus":"0","telephone":"18855356353","lshorderno":"","DiscountAmt":"0.00","invoiceLine":"p","invoicedate":"2020-05-28T13:09:47+00:00","ReceivableAmt":"1198.00"}]}
     */
    private function get_aisino_json($json) {
        $encryptData = [];
        foreach ((array)$json['orders'] as $order) {
            $data = array();
            $data['orderno'] = $order['OrderNo'];															//是   订单号   订单号
            $data['lshorderno'] = $order['lshorderno'];															//N  原订单号  退货单号/换货单号
            $data['lshStatus'] = $order['lshStatus'];															//是
//            $data['dataSoure'] = $order['dataSoure'];															//是  数据来源如（TM）
            $data['dataSoure'] = 'baison';															//是 数据来源默认使用baison
//            $data['IsUpdatedBuyer'] = isset($order['IsUpdatedBuyer']) ? $order['IsUpdatedBuyer'] : 0;	 //是  订单状态  订单状态：0：新订单，1：更新购方信息
            $data['IsUpdatedBuyer'] = isset($json['updated']) ? $json['updated'] : 0;	 //是  订单状态  订单状态：0：新订单，1：更新购方信息
            $data['storenum'] = implode(',', $order['StoreNum']);															//否   门店必填
            $data['kptype'] = $order['kptype'];															//是   开票类型:1,正票;2,红票   航行自行写入
//            $data['message'] = implode(';', $order['message']);														//否   备注   航行自行写入，目前内容是：订单号
            $data['message'] = '';														//否   备注   航行自行写入，目前内容是：订单号
            $data['invoiceLine'] = $order['invoiceLine'];															//   发票种类，p 电子增值税普通发票，c 增值税普通发票(纸票)，s 增值税专用发票
//        $data['invoicedate'] = $order['invoicedate'];															//是   开票日期  订单日期
            //航信反馈这个值要是时间，但是omnihub给的是[2020-05-28T13:09:47+00:00]
            $data['invoicedate'] = substr($order['invoicedate'], 0, 10);
            $data['buyername'] = $json['apply'][0]['payer_name'] ?? '';															//是   购方名称
            $data['taxnum'] = $json['apply'][0]['payer_register_no'] ?? '';															//否   购方税号
            $data['telephone'] = $json['apply'][0]['payer_phone'] ?? '';															//否   购方电话
            $data['address'] = $json['apply'][0]['payer_address'] ?? '';															//否   购方地址
            $data['account'] = ($json['apply'][0]['payer_bank'] ?? '') . ($json['apply'][0]['payer_bankaccount'] ?? '');															//否   购方银行账号
            $data['phone'] = $order['phone'];															//是   购方手机(开票成功会短信提醒购方)
            if ($data['kptype'] == 2 && isset($order['yfpdm'])) {
                $data['yfpdm'] = $order['yfpdm'];                                                            //否   对应蓝票发票代码   航信自行写入
            }
            if ($data['kptype'] == 2 && isset($order['yfphm'])) {
                $data['yfphm'] = $order['yfphm'];                                                            //否   对应蓝票发票号码   航信自行写入
            }
            $data['sjr'] = $order['sjr'];															//否     收件人
            $data['sjdh'] = $order['sjdh'];															//否     收件电话
            $data['sjdz'] = $order['sjdz'];															//否     收件地址
            $data['dkbz'] = isset($order['dkbz']) ? $order['dkbz'] : 0;					//否   代开标志:0 非代开;1 代开。代开蓝票备注文案要求包含：代开企业税号:***,代开企业名称:***；代开红票备注文案要求：对应正数发票代码:*** 号码:***代开企业税号 :*** 代开企业名称:***。   默认为：0
            $data['ReceivableAmt'] = $order['ReceivableAmt'];															//否
            $data['DiscountAmt'] = $order['DiscountAmt'];															//否
            $data['yf'] = $order['yf'];															//否     运费
//        $data['sjr'] = $order['sjr'];															//否     收件人
//        $data['sjdh'] = $order['sjdh'];															//否     收件电话
//        $data['sjdz'] = $order['sjdz'];															//否     收件地址
//        $data['tsfs'] = $order['tsfs'];															//否   推送方式 :-1, 不推送;0,邮箱;1,手机(默认);2,邮箱、手机   默认为：1
//        $data['email'] = $order['email'];															//否   推送邮箱（tsfs 为 0 或 2 时，此项为必填）
            if (isset($order['qdbz'])) {
                $data['qdbz'] = $order['qdbz'];                                                            //否   清单标志:0,根据项目名称数，自动产生清单;1,将项目信息打印至清单   航信自行写入
            }
            if (isset($order['qdxmmc'])) {
                $data['qdxmmc'] = $order['qdxmmc'];                                                            //否   清单项目名称:打印清单时对应发票票面项目名称，注意：税总要求清单项目名称为（详见销货清单）   航信自行写入
            }
//        $data['clerk'] = $order['clerk'];															//是   开票员   航信自行写入
//        $data['payee'] = $order['payee'];															//否   收款人   航信自行写入
//        $data['checker'] = $order['checker'];															//否   复核人   航信自行写入

            foreach ($order['Detail'] as $key=>$value) {
                $items = array();
                $items['item'] = $value['item'];															//是
                $items['goodsname'] = $value['goodsname'];															//是   商品名称，
                $items['unit'] = $value['unit'];															//否
                $items['num'] = $value['num'];															//否   数量；数量、单价必须都不填，或者都必填，不可只填一个；当数量、单价都不填时，不含税金额、税额、含税金额都必填
                $items['spec'] = $value['spec'];															//否   规格型号
                $items['spbm'] = $value['spbm'];															//是   商品编码   根据商品编码查询商品税收分类编码和开票名称
                $items['skucd'] = $value['skucd'];															//是  skucd  skucd
                if (isset($value['zsbm'])) {
                    $items['zsbm'] = $value['zsbm'];                                                            //否   自行编码   articleno
                } else {
                    $items['zsbm'] = $value['skucd'];
                }
                $items['fphxz'] = $value['fphxz'];															//是   发票行性质 :0, 正常行;1,折扣行;2,被折扣行   默认为：0
                $items['yhzcbs'] = $value['yhzcbs'];															//否   优惠政策标识:0,不使用;1,使用   默认为：0
                if (isset($value['zzstsgl'])) {
                    $items['zzstsgl'] = $value['zzstsgl'];                                                            //否   增值税特殊管理，如：即征即退、免税、简易征收 等   航行自行写入
                }
                if (isset($value['lslbs'])) {
                    $items['lslbs'] = $value['lslbs'];                                                            //否   零税率标识:空,非零税率 ;1, 免税 ;2,不征税;3,普通零税率   航行自行写入
                }
                $items['hsbz'] = $value['hsbz'];															//是   单价含税标志，0:不含税,1:含税   默认为：1
                if (isset($value['taxrate'])) {
                    $items['taxrate'] = $value['taxrate'];                                                            //是   税率   航信自行写入
                }
                $items['price'] = $value['price'];															//否   单价；数量、单价必须都不填，或者都必填，不可只填一个；当数量、单价都不填时，不含税金额、税额、含税金额都必填   含税单价
//            $items['tax'] = $value['tax'];															//否   税额，[不含税金额] * [税率] = [税额]；税额允许误差为 0.06  航行自行计算
//            $items['taxfreeamt'] = $value['taxfreeamt'];															//否   不含税金额   航行自行计算
//            $items['taxamt'] = $value['taxamt'];															//否   含税金额，[不含税金额] + [税额] = [含税金额]，   航行自行计算
//            $items['kce'] = $value['kce'];															//否   扣除额，小数点后两位。差额征收的发票目前只支持一行明细。不含税差额 = 不含税金额 - 扣除额；税额 = 不含税差额* 税率。   航行自行写入

                $data['Detail'][] = $items;
                $data['request_id'] = GB()->getRequestId();
            }

            $encryptData[] = $data;
        }

        $msg = "get_aisino_json: " . var_export($encryptData, true) . "\t Data Json:" . json_encode($encryptData, JSON_UNESCAPED_UNICODE);
        CommonTool::debugLog($msg, $this->logFileName);

        $data = $this->encrypt(json_encode($encryptData));

        return $data;
    }

    private function encrypt($input, $key = '', $iv = '', $crypt_type = 'hex') {
        $key = CommonTool::loadEnv('aisino.tdes_key');
        $iv = CommonTool::loadEnv('aisino.tdes_iv');
        if ($crypt_type == 'hex') {
            return bin2hex(openssl_encrypt($input, 'des-ede3-cbc', $key, OPENSSL_RAW_DATA, $iv));
        } else {
            return base64_encode(openssl_encrypt($input, 'des-ede3-cbc', $key, OPENSSL_RAW_DATA, $iv));
        }
    }

    private function decrypt($crypt, $key = '', $iv = '', $crypt_type = 'hex') {
        $key = CommonTool::loadEnv('aisino.tdes_key');
        $iv = CommonTool::loadEnv('aisino.tdes_iv');
        if ($crypt_type == 'hex') {
            return openssl_decrypt(hex2bin($crypt), 'des-ede3-cbc', $key, OPENSSL_RAW_DATA, $iv);
        } else {
            return openssl_decrypt(base64_decode($crypt), 'des-ede3-cbc', $key, OPENSSL_RAW_DATA, $iv);
        }
    }

}