<?php

namespace gateway\app\ftp\control;

use FtpClient\FtpClient;

class Ftp
{

    /**
     * index方法，用于默认入口
     *
     * @param array $request
     * @param array $response
     * @param array $app
     */
    public function index(&$request = array(), &$response = array(), &$app = array())
    {
        $response = array('status' => 1, 'message' => 'OK');
    }

    public function upload(&$request = array(), &$response = array(), &$app = array())
    {
        try {
            $ftp = new FtpClient();
            $ftpConfig = GB()->loadConfig('ftp');

            //连接ftp
            $ftp->connect($ftpConfig['host'], true, 21, 6);
            $ftp->login($ftpConfig['user'], $ftpConfig['password']);

            //设置被动模式
            $ftp->set_option(FTP_USEPASVADDRESS,false);
            $ftp->pasv(true);

            //默认文件目录
            if (isset($request['dir']) && !empty($request['dir'])) {
                $dir = ROOT_PATH . '/runtime/file/' . $request['dir'];
                $ftpDir = $request['dir'];
            } else {
                $dir = ROOT_PATH . '/runtime/file';
                $ftpDir = '.';
            }

            if (isset($request['file']) && !empty($request['file'])) {
                //根据文件列表上传
                $file_list = explode(',', trim($request['file']));
                foreach ((array)$file_list as $file_name) {
                    //true 成功
                    //false 失败
                    $put = $ftp->put($ftpDir . '/' . $file_name, $dir . '/' . $file_name, FTP_BINARY);
//                    $ftp->rename('./20880319030944560156_20191004.csv', './20880319030944560156_20191004-rename.csv');
                }

            } else {
                //上传当前目录下所有文件
                $ftp->putAll($dir, $ftpDir);
            }

            $response = array('status' => 1, 'message' => 'Success', 'data' => array());
            if (isset($request['scan']) && $request['scan'] == 1) {
                //获取ftp当前目录所有文件
                $response['scan'] = $ftp->scanDir($ftpDir, true);
            }
        } catch (\Exception $e) {
            $response = array('status' => -1, 'message' => $e->getMessage(), 'data' => array());
        }

        return $response;
    }
}


