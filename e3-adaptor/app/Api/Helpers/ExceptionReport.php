<?php


namespace App\Api\Helpers;


use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Queue\MaxAttemptsExceededException;
use Illuminate\Validation\ValidationException;
use League\OAuth2\Server\Exception\OAuthServerException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class ExceptionReport
{
    use ApiResponse;

    /**
     * @var Exception
     */
    public $exception;
    /**
     * @var Request
     */
    public $request;

    /**
     * @var
     */
    protected $report;

    /**
     * @var array
     */
    public $doReport = [];

    /**
     * ExceptionReport constructor.
     * @param Request $request
     * @param Exception $exception
     */
    function __construct(Request $request, Exception $exception)
    {
        $this->request = $request;
        $this->exception = $exception;
        $this->doReport = $this->doReportList();
    }

    public function doReportList()
    {
        return [
            AuthenticationException::class       => [trans('exceptions.authentication_exception'), 401],
            OAuthServerException::class          => [trans('exceptions.oauth_server_exception'), 401],
            ModelNotFoundException::class        => [trans('exceptions.model_not_found_exception'), 404],
            RelationNotFoundException::class     => [trans('exceptions.relation_not_found_exception'), 404],
            AuthorizationException::class        => [trans('exceptions.authorization_exception'), 403],
            ValidationException::class           => [],
            UnauthorizedHttpException::class     => [trans('exceptions.validation_exception'), 422],
            NotFoundHttpException::class         => [trans('exceptions.unauthorized_http_exception'), 404],
            MethodNotAllowedHttpException::class => [trans('exceptions.not_found_http_exception'), 405],
            MaxAttemptsExceededException::class  => [trans('exceptions.max_attempts_exceeded_exception'), 405],
        ];
    }

    public function register($className,callable $callback){

        $this->doReport[$className] = $callback;
    }

    /**
     * @return bool
     */
    public function shouldReturn(){

        if (! ($this->request->wantsJson() || $this->request->ajax())){
            return false;
        }

        foreach (array_keys($this->doReport) as $report){

            if ($this->exception instanceof $report){

                $this->report = $report;
                return true;
            }
        }

        return false;
    }

    /**
     * @param Exception $e
     * @return static
     */
    public static function make(Request $request, Exception $e){

        return new static($request,$e);
    }

    /**
     * @return mixed
     */
    public function report(){

        if ($this->exception instanceof ValidationException){
            return $this->failed($this->exception->errors(), 422);
        }
        // 淘宝异常
        // if ($this->exception instanceof TaobaoTopServerSideException) {
        // }
        // $message = !empty($this->report) ? $this->doReport[$this->report] : [$this->exception->getMessage(), 501];
        $message = $this->doReport[$this->report];

        return $this->failed($message[0],$message[1]);

    }

}