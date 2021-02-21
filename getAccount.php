<?php
include_once  dirname(__FILE__)."/database/DBData.php";
include_once  dirname(__FILE__)."/upbit/UpitData.php";

include_once  dirname(__FILE__)."/database/DBConnect.php";
include_once  dirname(__FILE__)."/upbit/Upbit.php";

include_once  dirname(__FILE__)."/SlackWebhooks.php";

$upbit = new Upbit;
$dbconnect = new DBConnect;
$slack = new SlackWebhooks;

$serverIP = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : "127.0.0.1"; // console실행 처이
if($serverIP != "193.123.253.106" && $serverIP != "114.206.48.234" && $serverIP != "127.0.0.1") {
    exit();
}

$upbitAccount = $upbit->getAccount();

$message = "";

if($upbitAccount["result"] == true) {
    $accountData = $upbitAccount["data"];

    foreach ($accountData as $key => $value) {
        $currency = $value["currency"];
        $markets = $value["unit_currency"] . "-" . $value["currency"];
        $balance = $value["balance"];

        $ticker = $upbit->getTicker($markets);
        if($ticker["result"] === true){
            $query = "
                SELECT korean_name 
                FROM market
                WHERE market = '" . $markets . "'
            ";
            $marketInfo = $dbconnect->getSelectOneRow($query);
            $trade_price = $ticker["data"][0]["trade_price"];

            $totlaKRW = $balance * $trade_price;

            $message .= $marketInfo["korean_name"] . "\n";
            $message .= "수량 : " . $balance . " " . $currency . "\n";
            $message .= "시가 : " . $trade_price . "\n";
            $message .= "총계 : " . number_format($totlaKRW) . "\n\n";
        }
    }
} else {
    $message = "오류 : " . date("Y-m-d h:m:s");
}

$sendUrl = "https://hooks.slack.com/services/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";
$slack->sendMessage($message, $sendUrl);
?>