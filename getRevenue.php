<?php
$serverIP = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : "127.0.0.1"; // console실행 처이
if($serverIP != "193.123.253.106" && $serverIP != "114.206.48.234" && $serverIP != "127.0.0.1") {
    exit();
}
ini_set("display_errors", 1);
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
$message = "";

$increase = "";
$revenueTotal = 0;
$assetsTotal = 0;

if($upbitAccount["result"] == true) {
    $accountData = $upbitAccount["data"];

    foreach ($accountData as $key => $value) {
        $currency = $value["currency"];
        $markets = $value["unit_currency"] . "-" . $value["currency"];
        $balance = $value["balance"];
		$avg_buy_price = $value["avg_buy_price"];

        $query = "
        SELECT korean_name 
        FROM market
        WHERE market = '" . $markets . "'
        ";
        $marketInfo = $dbconnect->getSelectOneRow($query);
        if(isset($marketInfo["korean_name"])) {
            $ticker = $upbit->getTicker($markets);
            if($ticker["result"] === true){
                $trade_price = $ticker["data"][0]["trade_price"];

                $tradeTotlaKRW = $balance * $trade_price;
                $buyTotlaKRW = $balance * $avg_buy_price;

                $increaseValue = $trade_price * 100.0 / $avg_buy_price - 100;
                
                if($tradeTotlaKRW > 5000) {
                    if($increaseValue < -7) {
                        $revenueValue = $tradeTotlaKRW - $buyTotlaKRW;
                        $revenueTotal = $revenueTotal + $revenueValue;
                        $assetsTotal = $assetsTotal + $buyTotlaKRW;

                        $warningMessage .= $marketInfo["korean_name"] . " : " . number_format($revenueValue) . "(" . $increaseValue . "%) \n";
                    }
                    if($increaseValue > 10) {
                        $revenueValue = $tradeTotlaKRW - $buyTotlaKRW;
                        $revenueTotal = $revenueTotal + $revenueValue;
                        $assetsTotal = $assetsTotal + $buyTotlaKRW;

                        $revenueMessage .= $marketInfo["korean_name"] . " : " . number_format($revenueValue) . "(" . $increaseValue . "%) \n";
                    }
                } else if($markets == "KRW-BTC") {
					$revenueValue = $tradeTotlaKRW - $buyTotlaKRW;
					$revenueTotal = $revenueTotal + $revenueValue;
					$assetsTotal = $assetsTotal + $buyTotlaKRW;

					$message .= $marketInfo["korean_name"] . " : " . number_format($revenueValue) . "(" . $increaseValue . "%) \n";
				}
            
            }
        }
    }
} else {
    $message .= "오류 : " . date("Y-m-d h:m:s");
}

if($warningMessage != null) {
    $warningMessage = "\n경고\n" . $warningMessage;
}

if($revenueMessage != null) {
    $revenueMessage = "\n수익\n" . $revenueMessage;
}

$total = round($assetsTotal - 1509078);
$message .= $warningMessage . $revenueMessage;

$btc = $upbit->getTicker("KRW-BTC");

$message .= "\n\n 비트코인";
$message .= "\n 상한 : 56,000,000~(54,000,000~51,000,000)";
$message .= "\n 현재가 : " . number_format($btc["data"][0]["trade_price"]);

if($message != null) {
    $message .= "\n 총 수익 : " . number_format(round($revenueTotal));

    $sendUrl = "https://hooks.slack.com/services/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";
    $slack->sendMessage($message, $sendUrl);
}
?>