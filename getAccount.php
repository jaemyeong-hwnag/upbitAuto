<?php
$serverIP = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : "127.0.0.1"; // console실행 처이
if($serverIP != "193.123.253.106" && $serverIP != "114.206.48.234" && $serverIP != "127.0.0.1") {
    exit();
}

include_once  dirname(__FILE__)."/database/DBData.php";
include_once  dirname(__FILE__)."/upbit/UpitData.php";

include_once  dirname(__FILE__)."/database/DBConnect.php";
include_once  dirname(__FILE__)."/upbit/Upbit.php";

include_once  dirname(__FILE__)."/Messenger.php";

$upbit = new Upbit;
$dbconnect = new DBConnect;
$messenger = new Messenger;

$upbitAccount = $upbit->getAccount();

$message = "";
$increase = "";
$revenueTotal = 0;
if($upbitAccount["result"] == true) {
    $accountData = $upbitAccount["data"];

    foreach ($accountData as $key => $value) {
        $currency = $value["currency"];
        $markets = $value["unit_currency"] . "-" . $value["currency"];
        $balance = $value["balance"];
		$avg_buy_price = $value["avg_buy_price"];

        $ticker = $upbit->getTicker($markets);
        if($ticker["result"] === true){
            $query = "
                SELECT korean_name 
                FROM market
                WHERE market = '" . $markets . "'
            ";
            $marketInfo = $dbconnect->getSelectOneRow($query);
            if($marketInfo != null) {
                $trade_price = $ticker["data"][0]["trade_price"];

                $tradeTotlaKRW = $balance * $trade_price;
                $buyTotlaKRW = $balance * $avg_buy_price;

                $message .= $marketInfo["korean_name"] . "\n";
                $message .= "수량 : " . $balance . " " . $currency . "\n";
                $message .= "매수가 : " . $avg_buy_price . "\n";
                $message .= "종가 : " . $trade_price . "\n";
                $message .= "총계 : " . number_format($buyTotlaKRW) . "\n\n";
                
                $increaseValue = $trade_price * 100.0 / $avg_buy_price - 100;
                $revenueValue = $tradeTotlaKRW - $buyTotlaKRW;
                $revenueTotal = $revenueTotal + $revenueValue;

                $increase .= $marketInfo["korean_name"] . " : " . number_format($revenueValue) . "(" . $increaseValue . "%) \n";
            }
        }
        
    }
	$message .= "\n\n" . $increase;
} else {
    $message = "오류 : " . date("Y-m-d h:m:s");
}

$message .= "\n 총 수익 : " . number_format(round($revenueTotal));


$btc = $upbit->getTicker("KRW-BTC");

$message .= "\n 상한 : " . $btc["data"][0]["trade_price"];
$message .= "\n 비트코인 현재가 : " . $btc["data"][0]["trade_price"];
//$messenger->sendMessage($message);
?>