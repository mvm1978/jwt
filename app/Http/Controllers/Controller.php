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
        'GET' => [
            'questions' => TRUE,
            'user-questions' => TRUE,
        ],
        'POST' => [
            'users/register' => TRUE,
            'users/login' => TRUE,
        ],
        'PUT' => [
            'users/password' => TRUE, // reset password by question
        ],
        'PATCH' => [
            'users/password' => TRUE, // reset password by email
            'user-questions' => TRUE, // verify question and update password
        ]
    ];

    protected $userID;
    protected $token;
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

        $method = $request->method();
        $resourse = $parsed[3];

        $resourse .= isset($parsed[4]) ? '/' . $parsed[4] : NULL;

        if (empty($parsed[3])) {
            return $this->construct = [
                'error' => [400 => 'bad_request'],
            ];
        }

        if (! isset($this->unauthPaths[$method][$resourse])) {
            // some requests may not need prior authorization
            $header = $request->header();

            if (empty($header['token']) || empty($header['id'])) {
                return $this->construct = [
                    'error' => [400 => 'bad_request'],
                ];
            }

            $token = reset($header['token']);
            $id = reset($header['id']);

            if (! $token || ! $id) {
                return $this->construct = [
                    'error' => [401 => 'invalid_token'],
                ];
            }

            $authenticationModel = new AuthenticationModel();

            $response = $authenticationModel->getAuthUser($token);

            $body = json_decode($response->getContent(), TRUE);

            if (Helpers::getDefault($body['data']['id']) != $id) {
                return $this->construct = [
                    'error' => [403 => 'invalid_token'],
                ];
            }

            $this->userID = $id;
            $this->token = $token;
        }
    }

    /*
    ****************************************************************************
    */

    protected function makeResponse($code, $message)
    {
        return response()->json([
            'message' => $message,
        ], $code);
    }

    /*
    ****************************************************************************
    */

    protected function constructErrorResponse()
    {
        $error = $this->construct['error'];

        $code = key($error);

        return $this->makeResponse($code, $error[$code]);
    }

    /*
    ****************************************************************************
    */

}