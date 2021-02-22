<?php
class SlackWebhooks
{
    public function sendMessage($message, $url=null) {
        $url = $url == null ? "https://hooks.slack.com/services/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" : $url;
        $messageArray = array("text" => $message);
        $messageJson = json_encode($messageArray);
        
        $ch = curl_init(); //curl 초기화
        curl_setopt($ch, CURLOPT_URL, $url); //URL 지정하기
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //요청 결과를 문자열로 반환 
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1000); //connection timeout 1초 
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); //원격 서버의 인증서가 유효한지 검사 안함
        curl_setopt($ch, CURLOPT_POSTFIELDS, $messageJson);       //POST data
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type:application/json",
        ));
        
        $response = curl_exec($ch);
        curl_close($ch);
	}
}
?>