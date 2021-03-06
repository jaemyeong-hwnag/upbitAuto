<?php
exit;
    include_once  dirname(__FILE__)."/database/DBData.php";
    include_once  dirname(__FILE__)."/upbit/UpitData.php";

    include_once  dirname(__FILE__)."/database/DBConnect.php";
    include_once  dirname(__FILE__)."/upbit/Upbit.php";

    $dbconnect = new DBConnect;
    $upbit = new Upbit;

    $serverIP = $_SERVER['REMOTE_ADDR'];
    if($serverIP != "193.123.253.106" && $serverIP != "114.206.48.234") {
        exit();
    }

    $upbitInfo = $upbit->getInfo();
    
    $upbitInfoData = $upbitInfo['data'];
    $upbitInfoResult = $upbitInfo['result'];
    $upbitTemp = null;

    unset($upbitInfo);

    if($upbitInfoResult == true) {
        foreach ($upbitInfoData as $key => $value) {
            $upbitTemp = explode('-' , $value['market']);
            $value['order_type'] = $upbitTemp[0];
            
            $dbconnect->insertDB("market", $value);
        }

        unset($upbitTemp);
        unset($key);
        unset($value);
    }
    
?>