<?php

namespace App\Models;

use GuzzleHttp\Client;

use Illuminate\Database\Eloquent\Model;

class AuthenticationModel extends Model
{
    private $client;
    private $api;

    /*
    ****************************************************************************
    */

    public function __construct()
    {
        $this->client = new Client();
        $this->api = env('API_AUTHENTICATION');
    }

    /*
    ****************************************************************************
    */

    public function verifyToken($token, $id)
    {
        try {
            $response = $this->client->get($this->api . 'user?token=' . $token);

            if ($response->getStatusCode() != 200) {
                return FALSE;
            }

            $result = json_decode($response->getBody(), TRUE);

            return empty($result['data']['id']) ? FALSE :
                    $result['data']['id'] == $id;
        } catch (\Exception $exception) {
            return FALSE;
        }
    }

    /*
    ****************************************************************************
    */

    public function register($type, $data)
    {
        try {
            $url = $this->api . 'auth/register/' . $type;

            $response = $this->client->post($url, [
                'form_params' => $data
            ]);

            if ($response->getStatusCode() != 200) {
                return FALSE;
            }

            $result = json_decode($response->getBody(), TRUE);

            return $result;
        } catch (\Exception $exception) {
            return FALSE;
        }
    }

    /*
    ****************************************************************************
    */

}
