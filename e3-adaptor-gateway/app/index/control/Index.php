<?php

namespace gateway\app\index\control;

class Index
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
        $response = array('status' => 1, 'message' => 'Welcome to Gateway');
    }
}


