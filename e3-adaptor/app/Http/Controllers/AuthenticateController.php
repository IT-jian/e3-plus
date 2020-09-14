<?php


namespace App\Http\Controllers;

use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Validator;

class AuthenticateController extends Controller
{
    protected $errorMessage = '账号或密码错误!';

    /**
     * Handle a login request to the application.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        // 校验登录信息
        $validator = $this->validateLogin($request->input());
        if ($validator->fails()) {
            $request->request->add([
                                       'errors' => $validator->errors()->toArray(),
                                       'code'   => 422,
                                   ]);

            // return $this->errorBadRequest($validator);
            return $this->sendFailedLoginResponse($request);
        }
        // 账号和密码
        $credentials = $this->credentials($request);
        if (!$this->checkUser($credentials)) {
            return $this->setStatusCode(401)->failed(trans('auth.failed'));
        }
        $data = [
            'username' => $credentials[$this->username()],
            'password' => $credentials['password'],
        ];

        return $this->access('password', $data);
    }

    /**
     * [validateLogin 验证登录信息]
     * @param array $data [description]
     * @return [type]       [description]
     */
    protected function validateLogin(array $data)
    {
        return Validator::make($data, [
            $this->username() => 'required',
            'password'        => 'required',
        ], [
                                   'required' => ':attribute 为必填项',
                               ], [
                                   $this->username() => '账号',
                                   'password'        => '密码',
                               ]);
    }

    /**
     * [username description]
     * @return [type] [description]
     */
    public function username()
    {
        return 'email';
    }

    /**
     * Get the failed login response instance.
     *
     * @return \Illuminate\Http\Response
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        $code = $request['code'];
        $msg = $request['errors'];

        return $this->setStatusCode($code)->failed($msg);
    }

    /**
     * Get the needed authorization credentials from the request.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        return $request->only($this->username(), 'password');
    }

    /**
     * Check the given user credentials.
     *
     * @return boolean
     */
    protected function checkUser($credentials)
    {
        $user = User::where([$this->username() => $credentials[$this->username()]])->first();
        if (is_null($user)) {
            $this->errorMessage = trans('passwords.user');

            return false;
        }
        if (Hash::check($credentials['password'], $user->password)) {
            return true;
        }

        return false;
    }

    /**
     * Send request to the laravel passport.
     *
     * @param string $grantType
     * @param array $data
     * @return \Illuminate\Http\Response
     */
    private function access($grantType, array $data = [])
    {
        try {
            // 合并需要发送的数据
            $data = array_merge([
                                    'client_id'     => env('VUE_CLIENT_ID'),
                                    'client_secret' => env('VUE_CLIENT_SECRET'),
                                    'grant_type'    => $grantType,
                                ], $data);

            $http = new Client();
            //$http->setDefaultOption(['verify'=>false]);
            $guzzleResponse = $http->post(env('APP_URL') . '/oauth/token', [
                'form_params' => $data,
            ]);
        } catch (\GuzzleHttp\Exception\BadResponseException $e) {
            Log::debug('access ---' . $e->getMessage());

            return $this->setStatusCode(400)->failed('/oauth/token请求出错!');
        }
        $resp = json_decode($guzzleResponse->getBody());
        $response = [
            'token_type'    => $resp->token_type,
            'expires_in'    => $resp->expires_in,
            'access_token'  => $resp->access_token,
            'refresh_token' => $resp->refresh_token,
        ];
        $data = [
            'data'   => $response,
            'code'   => $guzzleResponse->getStatusCode(),
            'status' => 'success',
        ];

        return $this->success($response);
    }

    /**
     * Handle a refresh token request.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function refreshToken(Request $request)
    {
        return $this->access('refresh_token', [
            'refresh_token' => $request->input('refresh_token'),
        ]);
    }

    public function logout(Request $request)
    {
        if (\Auth::check()) {

            $this->user()->token()->revoke();

        }

        return $this->message('退出登录成功');

    }

    public function client(Request $request)
    {
        $this->validate($request, [
            'client_id' => 'required',
            'client_secret'   => 'required',
        ]);
        try {
            // 合并需要发送的数据
            $data = [
                'client_id'     => $request->input('client_id', ''),
                'client_secret' => $request->input('client_secret'),
                'grant_type'    => 'client_credentials',
                'scope'         => '*',
            ];

            $http = new Client();
            $guzzleResponse = $http->post(env('APP_URL') . '/oauth/token', [
                'form_params' => $data,
            ]);
        } catch (\GuzzleHttp\Exception\BadResponseException $e) {
            Log::debug('access ---' . $e->getMessage());

            return $this->setStatusCode(400)->failed('/oauth/token请求出错!');
        }
        $resp = json_decode($guzzleResponse->getBody());
        Log::debug('client response', [$resp]);
        $response = [
            'token_type'    => $resp->token_type,
            'expires_in'    => $resp->expires_in,
            'access_token'  => $resp->access_token,
        ];

        return $this->success($response);
    }
}
