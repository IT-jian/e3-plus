<?php


namespace App\Http\Controllers\Admin;


use App\Services\DotenvEditor;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Encryption\Encrypter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Env;

class EnvController extends Controller
{
    protected $env;

    public function __construct(DotenvEditor $env)
    {
        $this->env = $env;
        if ('local' != config('app.env')) {
            throw new AuthorizationException('当前环境变量 APP_ENV 不允许编辑');
        }
    }

    public function availableFields()
    {
        return [
            'APP_ENV'                        => [
                'label' => '应用环境',
                'desc'  => '可取值：local,test,simulation,production',
            ],
            'DB_HOST'                        => [
                'label' => '数据库地址',
            ],
            'DB_PORT'                        => [
                'label' => '数据库端口',
            ],
            'DB_DATABASE'                    => [
                'label' => '数据库名称',
            ],
            'DB_USERNAME'                    => [
                'label' => '数据库用户',
            ],
            'DB_PASSWORD'                    => [
                'label' => '数据库密码',
            ],

            'DB_RDS_HOST'     => [
                'label' => 'RDS数据库地址',
            ],
            'DB_RDS_PORT'     => [
                'label' => 'RDS数据库端口',
            ],
            'DB_RDS_DATABASE' => [
                'label' => 'RDS数据库名称',
            ],
            'DB_RDS_USERNAME' => [
                'label' => 'RDS数据库用户',
            ],
            'DB_RDS_PASSWORD' => [
                'label' => 'RDS数据库密码',
            ],

            'REDIS_HOST' => [
                'label' => 'redis地址',
            ],
            'REDIS_PASSWORD'                 => [
                'label' => 'redis密码',
                'desc'  => '无密码，默认为 null'
            ],
            'REDIS_PORT'                     => [
                'label' => 'redis端口',
            ],
            'VUE_CLIENT_ID'         => [
                'label' => '前端授权ID',
                'desc'  => '默认 2'
            ],
            'VUE_CLIENT_SECRET'     => [
                'label' => '前端授权SECRET',
                'desc'  => '执行 passport:install 生成'
            ],
            'ADIDAS_HUB_URL'        => [
                'label' => '百胜HUB地址'
            ],
            'ADIDAS_HUB_PUSH_STOP'  => [
                'label' => 'Adaptor 停止数据推送',
                'desc'  => '0: 关闭 1: 开启，开启停止推送任务',
            ],
            'ADIDAS_HUB_APP_KEY'    => [
                'label' => '百胜HUB APP_ID'
            ],
            'ADIDAS_HUB_SIMULATION' => [
                'label' => '百胜HUB开启模拟请求',
                'desc'  => '0: 关闭 1: 开启，开启不请求omnihub直接模拟返回'
            ]
        ];
    }

    public function index(Request $request)
    {
        if ('local' != config('app.env')) {
            throw new AuthorizationException('当前环境变量 APP_ENV 不允许编辑');
        }

        $fieldMap = [];
        $variables = $this->env->getContent();
        foreach ($this->availableFields() as $key => $availableField) {
            $availableField['value'] = $variables[$key] ?? '';
            $fieldMap[$key] = $availableField;
        }

        return $this->respond($fieldMap);
    }

    /**
     * Updates the given entry from your .env.
     *
     * @param Request $request request
     *
     * @return void
     */
    public function store(Request $request)
    {
        $form = $request->only(array_keys($this->availableFields()));
        $key = Env::getVariables()->get('APP_KEY');
        if (empty($key)) { // 生成 APP_KEY
            $form['APP_KEY'] = 'base64:' . base64_encode(Encrypter::generateKey(''));
        }
        $this->env->changeEnv(
            $form
        );
        foreach ($form as $key => $value) {
            Env::getVariables()->set($key, $value);
        }
        Env::getVariables()->get('APP_ENV');

        return $this->setStatusCode(Response::HTTP_CREATED)->respond([]);
    }
}