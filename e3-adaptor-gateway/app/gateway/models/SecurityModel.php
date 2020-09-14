<?php
/*
 * File name
 * 
 * @author xy.wu
 * @since 2020/3/30 15:01
 */

namespace gateway\app\gateway\models;

use gateway\app\gateway\models\security\JingdongSecurityModel;
use gateway\app\gateway\models\security\TaobaoSecurityModel;

class SecurityModel {

    /**
     * Method encrypt
     * 调用加密
     *
     * @param $data
     * @param string $platform
     * @return array
     * @author xy.wu
     * @since 2020/3/30 16:56
     */
    public static function encrypt($data, $platform = 'tmall')
    {
        if ($platform == 'jd') {
            return JingdongSecurityModel::encrypt($data);
        } else {
            return TaobaoSecurityModel::encrypt($data);
        }
    }

    /**
     * Method decrypt
     * 调用解密
     *
     * @param $data
     * @param string $platform
     * @return array
     * @author xy.wu
     * @since 2020/3/30 16:56
     */
    public static function decrypt($data, $platform = 'tmall')
    {
        if ($platform == 'jd') {
            return JingdongSecurityModel::decrypt($data);
        } else {
            return TaobaoSecurityModel::decrypt($data);
        }
    }
}