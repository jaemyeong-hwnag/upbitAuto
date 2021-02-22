<?php
$serverIP = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : "127.0.0.1"; // console실행 처이
if($serverIP != "193.123.253.106" && $serverIP != "114.206.48.234" && $serverIP != "127.0.0.1") {
    exit();
}

include_once  dirname(__FILE__)."/database/DBData.php";
include_once  dirname(__FILE__)."/upbit/UpitData.php";

include_once  dirname(__FILE__)."/database/DBConnect.php";
include_once  dirname(__FILE__)."/upbit/Upbit.php";

include_once  dirname(__FILE__)."/SlackWebhooks.php";

$upbit = new Upbit;
$dbconnect = new DBConnect;
$slack = new SlackWebhooks;

$upbitAccount = $upbit->getAccount();

$warningMessage = "";
$revenueMessage = "";
$increase = "";
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
            $trade_price = $ticker["data"][0]["trade_price"];

            $tradeTotlaKRW = $balance * $trade_price;
            $buyTotlaKRW = $balance * $avg_buy_price;

			$increaseValue = $trade_price * 100.0 / $avg_buy_price - 100;
            $revenueValue = $tradeTotlaKRW - $buyTotlaKRW;

            if($revenueValue < -5000 || $increaseValue < -10) {
			    $warningMessage .= $marketInfo["korean_name"] . " : " . number_format($revenueValue) . "(" . $increaseValue . "%) \n";
            }
            if($revenueValue > 5000 || $increaseValue > 10) {
                $revenueMessage .= $marketInfo["korean_name"] . " : " . number_format($revenueValue) . "(" . $increaseValue . "%) \n";
            }
        }
    }
} else {
    $message = "오류 : " . date("Y-m-d h:m:s");
}

if($warningMessage != null) {
    $warningMessage = "\n경고\n" . $warningMessage;
}

if($revenueMessage != null) {
    $revenueMessage = "\n수익\n" . $revenueMessage;
}
$message = $warningMessage . $revenueMessage;

if($message != null) {
    $sendUrl = "https://hooks.slack.com/services/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";
    $slack->sendMessage($message, $sendUrl);
}
?>