<?php
class Messenger
{
	/**
	 * 슬랙 메시지 보내기
	 * 
	 * @author 황재명
	 * @param stirng $message 보낼 메시지 내용
     * @param string|null $url 사용할 슬ㄹ개 url 없으면 아래 $url 값으로 고정
	 * @return null
	 */
    public function slackSend($message, $url=null) {
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

    /**
	 * 카카오 메시지 보내기
	 * 
	 * @author 황재명
	 * @param  stirng $message 보낼 메시지 내용
	 * @return null
	 */
    public function kakaoSend($message="테스트") {
        $url = "https://kapi.kakao.com/v2/api/talk/memo/default/send"; // 카카오 url
        
        $access_token = "";

        $messageArray = array(); // 전송할 정보 셋팅 배열
        $messageArray["object_type"] = "text"; // 타입 설정
        $messageArray["text"] = $message; // 메시지 내용
        $messageArray["link"] = array(); // 메시지 내용

        $messageJson = json_encode($messageArray); // json 형태로 변경
        
        $ch = curl_init(); //curl 초기화
        curl_setopt($ch, CURLOPT_URL, $url); //URL 지정하기
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $access_token)); // 헤더설정
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //요청 결과를 문자열로 반환 
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1000); //connection timeout 1초 
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); //원격 서버의 인증서가 유효한지 검사 안함
        curl_setopt($ch, CURLOPT_POSTFIELDS, $messageJson);       //POST data
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type:application/json",
        ));
        
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
}
?>