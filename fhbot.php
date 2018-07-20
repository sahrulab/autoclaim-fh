<?php
/**
 * Created by PhpStorm.
 * User: aziz
 * Date: 29/03/18
 * Time: 10.35
 */

    date_default_timezone_set("Asia/Jakarta");
    error_reporting(0);
    set_time_limit(0);
    ini_set("max_execution_time","-1");
    ini_set("memory_limit","-1");
    ini_set("output_buffering",0);
    ini_set("request_order","GP");
    ini_set("variables_order","EGPCS");

    defined('BTC')                 OR define('BTC','1ESfG399xm5mJEddZGsk7yNSeuysFhnfe1');
    defined('BCH')                 OR define('BCH','1zaTh5qfMKgP2H8iHQLsTqVHZGvkrz2kW');
    defined('ETH')                 OR define('ETH','0xde4ec63120cdcbdff88fea82400b2812baf97d6b');
    defined('LTC')                 OR define('LTC','LPT4VjJpShTWsihuzsMrFY2MESoT32NtFF');
    defined('BLK')                 OR define('BLK','B9a5pXt7JJucTPhiF1w7qu5LCHMUSbENZZ');
    defined('BTX')                 OR define('BTX','1FZMbPihh8dezkYhggVNBUZ2p5fGCHt4CB');
    defined('DASH')                OR define('DASH','XgTJQD6qLLu1th17L9MhGYqAYjitMUrg2H');
    defined('DOGE')                OR define('DOGE','DGS3F8PpEEaT4LfgDYLHc5YznhCfqhR92h');
    defined('PPC')                 OR define('PPC','PBfoW11ZBkfohfN8aUdwnDLig6fnXz9rrf');
    defined('POT')                 OR define('POT','PLeoqusaCv2BuYHjscFWEo3o5u4iHaYE66');
    defined('XPM')                 OR define('XPM','AYsfRE5bzuQWjEwrxcPejA29kzJZ4EhDM2');
    defined('CODE')                OR define('CODE','&r=');
    defined('CODEC')               OR define('CODEC','&rc=');

    echo "-------------------------------------------------\n";
    echo "Welcome to AutoClaim Bot\n";
    startClaim:
    echo "-------------------------------------------------\n";
    echo "1. Start claim\n";
    echo "2. Add key\n";
    echo "-------------------------------------------------\n";
    echo "Enter your command: ";
    $cmd = trim(fgets(STDIN));
    if($cmd == 1){
        $myUrl = getUrl();
        $tag = array();
        $myTrimmedUrl = array();
        $index = 0;
        foreach($myUrl as $url) {
            $trimmedUrl = explode("||", trim($url));
            $newUrl = constructURL($trimmedUrl[0]);
            $myTrimmedUrl[$index] = array();
            $myTrimmedUrl[$index]["waitTime"] = (string)$trimmedUrl[1];
            $myTrimmedUrl[$index]["url"] = $newUrl;
            $tag[$index] = $myTrimmedUrl[$index]["url"];
            $index++;
        }
        $myGroupedUrl = groupUrl($myTrimmedUrl);
        if($myGroupedUrl != "empty"){
            $balance = initBalance();
            printBalance($balance);
            $seconds = 0;
            $increment = 1;
            while(1){
                $seconds = $seconds + $increment;
                $curls = array();
                $mh = curl_multi_init();
                foreach ($myGroupedUrl as $group) {
                    $start = 0;
                    if ($seconds % $group['waitTime'] == 0) {
                        foreach ($group['url'] as $url) {
                            $curls[$start] = curl_init($url);
                            curl_setopt($curls[$start], CURLOPT_RETURNTRANSFER, 1);
                            curl_setopt($curls[$start], CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;
                               MSIE 7.0;
                                     Windows NT 6.1;
                                 Win64;
                                     x64;
                                 Trident/4.0;
                                 Mozilla/4.0 (compatible;
                                 MSIE 6.0;
                                 Windows NT 5.1;
                                 SV1) ;
                                 NeoDownloader Embedded Web Browser from: http://bsalsa.com/;
                                 .NET CLR 2.0.50727;
                                 SLCC2;
                                 .NET CLR 3.5.30729;
                                 .NET CLR 3.0.30729)");
                            curl_setopt($curls[$start], CURLOPT_COOKIEJAR, "cookie");
                            curl_setopt($curls[$start], CURLOPT_COOKIEFILE, "cookie");
                            curl_multi_add_handle($mh, $curls[$start]);
                            $start++;
                        }
                    }
                }

                $running = 0;
                do {
                    curl_multi_exec($mh, $running);
                } while ($running > 0);

                for($i = 0; $i < count($curls); $i++){
                    $info = curl_getinfo($curls[$i]);
                    $resultSatoshi = explode("<div class=\"alert alert-success\">",curl_multi_getcontent($curls[$i]));
                    $resultCoin = explode("satoshi",$resultSatoshi[1]);
                    if(trim($resultCoin[0]) == ""){
                        $resultCoin = explode("doge",$resultSatoshi[1]);
                    }
                    $satoshi = trim($resultCoin[0]);
                    $currency = getCurrency($info['url']);
                    $key = arr_contains($info['url'], $tag);
                    if($satoshi){
                        echo "(" . $key . ") " . $currency. " +".$satoshi.", ";
                        sleep(1);
                        $balance[$currency] = $balance[$currency] + (int)$satoshi;
                    }
                    curl_multi_remove_handle($mh, $curls[$i]);
                    curl_close($curls[$i]);
                }
                if($seconds == 1800){
                    $seconds = 0;
                    printBalance($balance);
                }
                sleep($increment);
            }
        }
    }
    elseif ($cmd == 2){
        createKey();
        goto startClaim;
    }
    else{
        echo "Try again\n";
        goto startClaim;
    }

    function arr_contains($str, array $arr){
        $key = 0;
        foreach($arr as $a) {
            $key++;
            if (strpos($a, $str) !== false) return $key;
        }
        return 0;
    }

    function initBalance(){
        $balance["BTC"] = 0;
        $balance["BCH"] = 0;
        $balance["ETH"] = 0;
        $balance["LTC"] = 0;
        $balance["BLK"] = 0;
        $balance["BTX"] = 0;
        $balance["DASH"] = 0;
        $balance["DOGE"] = 0;
        $balance["PPC"] = 0;
        $balance["POT"] = 0;
        $balance["XPM"] = 0;

        return $balance;
    }

    function printBalance($balance){
        echo "\n-------------------------------------------------\n";
        echo "Total Income until now\n";
        echo "Last update: " . date("Y-m-d H:i:s"). "\n";
        echo "-------------------------------------------------\n";
        echo sprintf("BTC  : %15d\n", $balance["BTC"]);
        echo sprintf("BCH  : %15d\n", $balance["BCH"]);
        echo sprintf("ETH  : %15d\n", $balance["ETH"]);
        echo sprintf("LTC  : %15d\n", $balance["LTC"]);
        echo sprintf("BLK  : %15d\n", $balance["BLK"]);
        echo sprintf("BTX  : %15d\n", $balance["BTX"]);
        echo sprintf("DASH : %15d\n", $balance["DASH"]);
        echo sprintf("DOGE : %15d\n", $balance["DOGE"]);
        echo sprintf("PPC  : %15d\n", $balance["PPC"]);
        echo sprintf("POT  : %15d\n", $balance["POT"]);
        echo sprintf("XPM  : %15d\n", $balance["XPM"]);
        echo "-------------------------------------------------\n";
    }

    function getUrl(){
        $fileName = "allkey.txt";
        $keyFile = fopen($fileName, "r");
        if ($keyFile) {
            $url = array();
            $index = 0;
            echo "Scanning key file...\n";
            while (($row = fgets($keyFile)) !== false) {
                $key = trim($row);
                if($key != ""){
                    $url[$index] = $key;
                    $index++;
                }
            }
            fclose($keyFile);
            echo "Key found : " . $index;
            if(empty($url)){
                echo "\n-------------------------------------------------\nFile empty.\n";
                createKey();
            }
            else{
                return $url;
            }
        }
        else {
            echo "\n-------------------------------------------------\nFile not found.\n";
            createKey();
        }
    }

    function createKey(){
        echo "Do you want to enter your key now? (Y/N): ";
        $confirm = trim(fgets(STDIN));
        if($confirm == "Y" || $confirm == "y"){
            $keyCollection = array();
            $index = 0;
            $my_file = 'allkey.txt';
            if(!file_exists($my_file)){
                $handle = fopen($my_file, 'w');
                fclose($handle);
            }
            do{
                echo "Enter url: ";
                $u = trim(fgets(STDIN));
                echo "Enter wait time (seconds): ";
                $t = trim(fgets(STDIN));
                $generatedKey = $u . "||" . $t;
                $keyCollection[$index++] = $generatedKey;
                echo "Do you want to add more key? (Y/N): ";
                $confirm = trim(fgets(STDIN));
            }while($confirm == "Y" || $confirm == "y");
            $handle = fopen($my_file, 'a');
            foreach ($keyCollection as $kc){
                fwrite($handle, $kc);
                fwrite($handle, "\n");
            }
        }
    }

    function groupUrl($groupedUrl){
        if(!empty($groupedUrl)){
            $tmp = array();

            foreach($groupedUrl as $arg) {
                $tmp[$arg['waitTime']][] = $arg['url'];
            }

            $output = array();
            foreach($tmp as $waitTime => $url) {
                $output[] = array(
                    'waitTime' => $waitTime,
                    'url' => $url
                );
            }
            return $output;
        }
        else{
            return "empty";
        }
    }

    function constructURL($url){
        if(!empty($url)){
            $newUrl = generateUrl($url);
            return $newUrl;
        }
        else{
            return $url;
        }
    }

    function generateUrl($url){
        if(strpos($url,'BTC')){
            return $url.CODE.BTC.CODEC.'BTC';
        }
        else if(strpos($url,'BCH')){
            return $url.CODE.BCH.CODEC.'BCH';
        }
        else if(strpos($url,'ETH')){
            return $url.CODE.ETH.CODEC.'ETH';
        }
        else if(strpos($url,'LTC')){
            return $url.CODE.LTC.CODEC.'LTC';
        }
        else if(strpos($url,'BLK')){
            return $url.CODE.BLK.CODEC.'BLK';
        }
        else if(strpos($url,'BTX')){
            return $url.CODE.BTX.CODEC.'BTX';
        }
        else if(strpos($url,'DASH')){
            return $url.CODE.DASH.CODEC.'DASH';
        }
        else if(strpos($url,'DOGE')){
            return $url.CODE.DOGE.CODEC.'DOGE';
        }
        else if(strpos($url,'PPC')){
            return $url.CODE.PPC.CODEC.'PPC';
        }
        else if(strpos($url,'POT')){
            return $url.CODE.POT.CODEC.'POT';
        }
        else if(strpos($url,'XPM')){
            return $url.CODE.XPM.CODEC.'XPM';
        }
    }

    function getCurrency($url){
        if (strpos($url, 'BTC') !== false) {
            return 'BTC';
        } else if (strpos($url, 'BCH') !== false) {
            return 'BCH';
        } else if (strpos($url, 'ETH') !== false) {
            return 'ETH';
        } else if (strpos($url, 'BLK') !== false) {
            return 'BLK';
        } else if (strpos($url, 'BTX') !== false) {
            return 'BTX';
        } else if (strpos($url, 'DASH') !== false) {
            return 'DASH';
        } else if (strpos($url, 'DOGE') !== false) {
            return 'DOGE';
        } else if (strpos($url, 'LTC') !== false) {
            return 'LTC';
        } else if (strpos($url, 'PPC') !== false) {
            return 'PPC';
        } else if (strpos($url, 'XPM') !== false) {
            return 'XPM';
        } else if (strpos($url, 'POT') !== false) {
            return 'POT';
        }
        return '$';
    }
?>