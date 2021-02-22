<?php
ini_set('display_errors', '1');
include_once dirname(__FILE__)."/../thirdPart/php-jwt-master/src/JWT.php";

use Firebase\JWT\JWT;

class Upbit extends UpitData
{
    private $server_url = "https://api.upbit.com";

    public function getInfo() {
        $return = array();
        $return['result'] = false;
        $return['data'] = array();

        $url = "https://api.upbit.com/v1/market/all?isDetails=false";

        $ch = curl_init(); //curl 초기화
        curl_setopt($ch, CURLOPT_URL, $url); //URL 지정하기
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //요청 결과를 문자열로 반환 
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1); //connection timeout 1초 
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); //원격 서버의 인증서가 유효한지 검사 안함
        
        $response = curl_exec($ch);
        curl_close($ch);

        if($response == false){
            $return['result'] = false;
        } else {
            $return['result'] = true;
            $return['data'] = json_decode($response, true);
        }

        return $return;
    }

    public function getAccount() {
        // 변수 설정
        $access_key = $this->AccessKey; // 키
        $secret_key = $this->SecretKey; // 키

        // 리턴값 기본 설정
        $return = array();
        $return['result'] = false;
        $return['data'] = array();

        $params = array();


        // 데이터 설정
        $payload = array();
        $payload["access_key"] = $access_key;
        $payload["nonce"] = $this->gen_uuid();
        $payload["query_hash"] = "";
        $payload["query_hash_alg"] = "SHA512";

        $jwt_token = JWT::encode($payload, $secret_key, "HS256");
        $authorize_token = "Bearer " . $jwt_token;

        $authorize_token_array = array();
        $authorize_token_array["Authorization"] = $authorize_token;

        $url = "https://api.upbit.com/v1/accounts";

        $opts = array(
            'http'=>array(
              'method'=>"GET",
              'header'=>"Accept-language: en\r\n" .
                        "Cookie: foo=bar\r\n" .
                        "User-agent: BROWSER-DESCRIPTION-HERE\r\n" . 
                        "Authorization: " . $authorize_token . "\r\n"
            )
        );

        $context = stream_context_create($opts);
        
        // Open the file using the HTTP headers set above
        $response = file_get_contents($url, false, $context);

        if($response == false){
            $return['result'] = false;
        } else {
            $return['result'] = true;
            $return['data'] = json_decode($response, true);
        }

        return $return;
    }

    public function getTicker($markets) {
        // 변수 설정
        $access_key = $this->AccessKey; // 키
        $secret_key = $this->SecretKey; // 키

        // 리턴값 기본 설정
        $return = array();
        $return['result'] = false;
        $return['data'] = array();

        $url = "https://api.upbit.com/v1/ticker?markets=" . $markets;
        $opts = array(
            'http'=>array(
              'method'=>"GET",
              'header'=>"Accept-language: en\r\n" .
                        "Cookie: foo=bar\r\n" .
                        "User-agent: BROWSER-DESCRIPTION-HERE\r\n"
            )
        );
        
        $context = stream_context_create($opts);
        
        // Open the file using the HTTP headers set above
        $response = file_get_contents($url, false, $context);
        
        if($response == false){
            $return['result'] = false;
        } else {
            $return['result'] = true;
            $return['data'] = json_decode($response, true);
        }
        
        return $return;
    }

    public function gen_uuid() {
        return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
    
            // 16 bits for "time_mid"
            mt_rand( 0, 0xffff ),
    
            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand( 0, 0x0fff ) | 0x4000,
    
            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand( 0, 0x3fff ) | 0x8000,
    
            // 48 bits for "node"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
        );
    }
}

?>