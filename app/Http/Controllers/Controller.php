<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

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
        'user-questions/get/{username}' => TRUE,
        'user-questions/verify' => TRUE,
    ];

    protected $userID = NULL;
    protected $construct;

    /*
    ****************************************************************************
    */

    public function __construct($request)
    {
        $this->userID = NULL;

        $parsed = explode('/', $request->getPathInfo());

        if (count($parsed) == 3 && $parsed[1] == 'api' && $parsed[2] == 'user') {
            return;
        }

        if (empty($parsed[3]) || empty($parsed[4])) {
            return [
                'error' => [400 => 'bad_request'],
            ];
        }

        $class = $parsed[3];
        $method = $parsed[4];

        if (! isset($this->unauthPaths[$class . '/' . $method])) {
            // some requests may not need prior authorization
            $header = $request->header();

            if (empty($header['token']) || empty($header['id'])) {
                return [
                    'error' => [400 => 'bad_request'],
                ];
            }

            $authenticationModel = new AuthenticationModel();

            $token = reset($header['token']);
            $id = reset($header['id']);

            $response = $authenticationModel->getAuthUser($token);

            $body = json_decode($response->getContent(), TRUE);

            if (empty($body['data']['id']) || $id != $body['data']['id']) {
                return [
                    'error' => [403 => 'invalid_token'],
                ];
            }

            $this->userID = $id;
        }
    }

    /*
    ****************************************************************************
    */

    protected function constructErrorResponse()
    {
        $error = $this->construct['error'];

        $code = key($error);

        return response()->json([
            'error' => $error[$code],
        ], $code);
    }

    /*
    ****************************************************************************
    */

}