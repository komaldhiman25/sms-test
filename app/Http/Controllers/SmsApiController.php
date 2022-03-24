<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;


class SmsApiController extends Controller
{
    public function getMessages($number, $apiKey, $secretKey){
        //Setting Header   
        $method = 'GET';   
        
        $params = json_encode([
            "destination" => $number
            
        ], JSON_FORCE_OBJECT);
        $header = SmsApiController::generateHeader( $apiKey, $secretKey, $method);
        
        $response_content = SmsApiController::sendRequest($method, $params, $header);
        //$response_content = $response->getBody()->getContents();
            
        // if ($response->getStatus() == 200) {
        //     echo $response->getBody();
        // }
        // else {
        //     echo 'Unexpected HTTP status: ' . $response->getStatus();
        //     //$response->getReasonPhrase();
        // }
            
        return $response_content;
        
    }

    public function sendMessages($number, $message, $apiKey, $secretKey){
        $method = 'POST';
        //Setting Parameters to pass in the body
        $params = json_encode([
            "destination" => $number,
            "message" => $message
        ], JSON_FORCE_OBJECT);
        //Generating header
        $header = SmsApiController::generateHeader( $apiKey, $secretKey, $method);
        $response_content = SmsApiController::sendRequest($method, $params, $header);
        return $response_content;
        
    }

    public static function generateHeader( $apiKey, $secretKey, $method){
        // $apiKey = '246acd60ada9b008e0f3bf937232c103';
        // $secretKey = 'ec97a08655792e9086f28495257a6bbf';
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
    
    public static function sendRequest($method, $params, $header){
        $client = new Client();
        $response = $client->request($method, 'https://api.smsglobal.com/v2/sms', [
            'body' => $params,
            'headers' => [
                'Authorization' => $header,
                'content-type' => 'application/json'
            ]
        ]);
        
        $response_content = $response->getBody()->getContents();
        return $response_content;
    }
}
