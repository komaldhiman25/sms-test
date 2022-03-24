<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class SmsApiController extends Controller
{
    /**
     * To get the messages sent by number
     */
    public function getMessages($number, $apiKey, $secretKey){   
        $method = 'GET';   
        $params = json_encode([
            "destination" => $number
        ], JSON_FORCE_OBJECT);
        //Generating header for this number and method
        $header = SmsApiController::generateHeader( $apiKey, $secretKey, $method);
        //Sending request to get message
        $response_content = SmsApiController::sendRequest($method, $params, $header);
            
        return $response_content;
        
    }

    /**
     * To send the message to a number
     */
    public function sendMessages($number, $message, $apiKey, $secretKey){
        $method = 'POST';
        //Setting Parameters to pass in the body
        $params = json_encode([
            "destination" => $number,
            "message" => $message
        ], JSON_FORCE_OBJECT);
        //Generating header
        $header = SmsApiController::generateHeader( $apiKey, $secretKey, $method);
        //Sending request to get message
        $response_content = SmsApiController::sendRequest($method, $params, $header);
        return $response_content;
        
    }

    /**
     * Generating header using MXT Rest API keys
     */
    private static function generateHeader( $apiKey, $secretKey, $method){
        $timestamp = time();
        $nonce = floor(random_int(1000000, 9999999));
        $string = array($timestamp, $nonce, $method, "/v2/sms", "api.smsglobal.com", 443, '');
        $string = sprintf("%s\n", implode("\n", $string));
        $hash = hash_hmac('sha256', $string, $secretKey, true);
        $hash = base64_encode($hash);
        $header = 'MAC id="%s", ts="%s", nonce="%s", mac="%s"';
        $header = sprintf($header, $apiKey, $timestamp, $nonce, $hash);
        return $header;
    }
    
    /**
     * Sending request to send sms
     */
    private static function sendRequest($method, $params, $header){
        $client = new Client();
        try {
            $response = $client->request($method, 'https://api.smsglobal.com/v2/sms', [
                'body' => $params,
                'headers' => [
                    'Authorization' => $header,
                    'content-type' => 'application/json'
                ]
            ]);
            
            $response_content = $response->getBody()->getContents();
            if($response->getStatusCode()==200){
                return $response_content;
            }
        } catch (\Throwable $th) {
            //If issue in sending request
            return "Something went wrong. Please try after sometime.";
        }
        
        return;
    }
}
