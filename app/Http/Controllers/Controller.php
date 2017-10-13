<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use Helpers;

use App\Models\AuthenticationModel;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    private $unauthPaths = [
        'users/register' => TRUE,
        'users/login' => TRUE,
        'users/password-reset' => TRUE,
        'users/password-recovery-by-email' => TRUE,
        'questions/get' => TRUE,
        'user-questions/get' => TRUE,
        'user-questions/verify' => TRUE,
    ];

    protected $userID;
    protected $construct;

    /*
    ****************************************************************************
    */

    public function __construct($request)
    {
        $this->userID = $this->construct = NULL;

        $parsed = explode('/', $request->getPathInfo());

        if (count($parsed) == 3 && $parsed[1] == 'api' && $parsed[2] == 'user') {
            return;
        }

        if (empty($parsed[3]) || empty($parsed[4])) {
            return $this->construct = [
                'error' => [400 => 'bad_request'],
            ];
        }

        $class = $parsed[3];
        $method = $parsed[4];

        if (! isset($this->unauthPaths[$class . '/' . $method])) {
            // some requests may not need prior authorization
            $header = $request->header();

            if (empty($header['token']) || empty($header['id'])) {
                return $this->construct = [
                    'error' => [400 => 'bad_request'],
                ];
            }

            $authenticationModel = new AuthenticationModel();

            $token = reset($header['token']);
            $id = reset($header['id']);

            $response = $authenticationModel->getAuthUser($token);

            $body = json_decode($response->getContent(), TRUE);

            if (Helpers::getDefault($body['data']['id']) != $body['data']['id']) {
                return $this->construct = [
                    'error' => [403 => 'invalid_token'],
                ];
            }

            $this->userID = $id;
        }
    }

    /*
    ****************************************************************************
    */

    protected function makeResponse($code, $message, $exception=NULL)
    {
        if ($code >= 300) {
            $this->logError($message, $exception);
        }

        return response()->json([
            'message' => $message,
        ], $code);
    }

    /*
    ****************************************************************************
    */

    protected function logError($message, $exception=NULL)
    {
        $now = new \DateTime();

        $errLog = '../../logs/error.log';
        $backTrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $logMsg = $exception ? $exception->getMessage() : $message;

        file_put_contents($errLog, $now->format('Y-m-d H:i:s') . PHP_EOL, FILE_APPEND);
        file_put_contents($errLog, print_r($backTrace, TRUE), FILE_APPEND);
        file_put_contents($errLog, 'Error Message: ' . print_r($logMsg, TRUE), FILE_APPEND);
        file_put_contents($errLog, str_repeat(PHP_EOL, 3), FILE_APPEND);
    }

    /*
    ****************************************************************************
    */

    protected function constructErrorResponse()
    {
        $error = $this->construct['error'];

        $code = key($error);

        $this->makeResponse($code, $error[$code]);
    }

    /*
    ****************************************************************************
    */

}