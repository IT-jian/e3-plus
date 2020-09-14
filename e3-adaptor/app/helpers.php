<?php

use App\Models\SysStdTrade;

/**
 * 生成 8位字符串
 *
 * @param $a
 * @return string
 *
 * @author linqihai
 * @since 2019/12/17 15:38
 */
function num16to32($a){
    for ($a = md5( $a, true ),
         $s = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ',
         $d = '',
         $f = 0;
         $f < 8;
         $g = ord( $a[ $f ] ),
         $d .= $s[ ( $g ^ ord( $a[ $f + 8 ] ) ) - $g & 0x1F ],
         $f++
    );
    return $d;
}

/**
 * 字符串包含有全角字符
 * @param $str
 * @return false|int
 *
 * @author linqihai
 * @since 2020/2/17 14:30
 */
function containSbc($str)
{
    // @todo 中文符号会被识别
    return preg_match('/[\x{3000}\x{ff01}-\x{ff5f}]/u', $str);
}

function cutoverTrade($tid, $platform)
{
    if ($liveDate = config('hubclient.cutover_date', '')) {
        // 指定表中有数据，则为加强版报文
        /*$exist = \App\Models\CutoverTrade::where('tid', $tid)->exists();
        if ($exist) {
            return true;
        }*/
        // 查询订单
        $where = [
            'tid' => $tid,
            'platform' => $platform
        ];
        $trade = SysStdTrade::where($where)->first(['pay_time']);
        // 加强版报文
        if (isset($trade['pay_time']) && !empty($trade['pay_time']) && (strtotime($trade['pay_time']) < strtotime($liveDate))) {
            return true;
        }
    }

    return false;
}
