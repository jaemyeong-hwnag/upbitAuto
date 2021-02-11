<?php

class Upbit extends UpitData
{
    public function getInfo() {
        $return = array();
        $return['result'] = false;
        $return['data'] = array();

        $url = "https://api.upbit.com/v1/market/all?isDetails=false";

        $ch = curl_init(); //curl 초기화
        curl_setopt($ch, CURLOPT_URL, $url); //URL 지정하기
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //요청 결과를 문자열로 반환 
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1); //connection timeout 1초 
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //원격 서버의 인증서가 유효한지 검사 안함
        
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
}

?>