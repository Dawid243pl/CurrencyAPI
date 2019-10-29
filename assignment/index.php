<?php


include 'functions.php';
error_reporting(0);


$keyArray = array();
foreach ($_GET as $key => $value) {
    
    array_push($keyArray, $key);
    
}
for ($z = 0; $z < sizeof($keyArray); $z++) {
    
    
    if ($keyArray[$z] == "from") {
        $from = htmlspecialchars($_GET["from"]);
        
    } else if ($keyArray[$z] == "to") {
        
        $to = htmlspecialchars($_GET["to"]);
    } else if ($keyArray[$z] == "amnt") {
        
        $amnt = htmlspecialchars($_GET["amnt"]);
    } else if ($keyArray[$z] == "format") {
        
        $format = htmlspecialchars($_GET["format"]);
    } else {
        displayErrorMessage("1100", $format);
        exit();
    }
    
}

if ((isset($_GET['from'])) && (isset($_GET['to'])) && (isset($_GET['amnt'])) && (isset($_GET['format']))) {
    
    
    $xml_file_name = "rateV1.xml";
    
    
    date_default_timezone_set("Europe/London");
    
    $date = time();
    
    $countryFileXml = simplexml_load_file("country.xml");
    
    $rateFileXml = simplexml_load_file("rateV1.xml");
    
    $supported_rates = array();
    
    if ($rateFileXml === FALSE) {
        $supported_rates = array(
            "AUD",
            "BRL",
            "CAD",
            "CHF",
            "CNY",
            "DKK",
            "EUR",
            "GBP",
            "HKD",
            "HUF",
            "INR",
            "JPY",
            "MXN",
            "MYR",
            "NOK",
            "NZD",
            "PHP",
            "RUB",
            "SEK",
            "SGD",
            "THB",
            "TRY",
            "USD",
            "ZAR"
        );
        
        $checkFrom = false;
        $checkTo   = false;
        
        
        
        if (in_array($from, $supported_rates)) {
            $checkFrom = true;
        } else {
            $checkFrom = false;
        }
        
        if (in_array($to, $supported_rates)) {
            $checkTo = true;
        } else {
            $checkTo = false;
        }
        
        
        if (($checkFrom == true) && ($checkTo == true)) {
            
            $currencyArray = array();
              
            $currencyArray = getAPI($supported_rates);
            
            $currencyArray = compareToCountryXML($currencyArray, $countryFileXml, $date);
            
            $test = createRateFile($currencyArray, $xml_file_name);
            
            $loadNewFile = simplexml_load_string($test);
            
            $test2 = loadRateFile($loadNewFile, $to, $from, $amnt);
        
            if (!is_decimal($amnt)) {
                
                //echo "Currency must be a decimal number error 1300";
                displayErrorMessage("1300", $format);
                exit();
            } else {
                
                displayFormat($format, $test2);
            }
            
        } else {
            displayErrorMessage("1200", $format);
            exit();
        }

    } else {
        $getSupportedRates = $rateFileXml->xpath('//currency[not(@display)]');
        //$supported_rates =array("AUD","BRL","CAD","CHF","CNY","DKK","EUR","GBP","HKD","HUF","INR","JPY","MXN","MYR","NOK","NZD","PHP","RUB","SEK","SGD","THB","TRY","USD","ZAR");
        
        foreach ($getSupportedRates as $supportedRate) {
            
            $rateBack = (string) $supportedRate->code;
            
            array_push($supported_rates, $rateBack);
        }
    }
    
    
    $checkFrom = false;
    $checkTo   = false;
    
    
    
    if (in_array($from, $supported_rates)) {
        $checkFrom = true;
    } else {
        $checkFrom = false;
    }
    
    if (in_array($to, $supported_rates)) {
        $checkTo = true;
    } else {
        $checkTo = false;
    }
    
    
    if (($checkFrom == true) && ($checkTo == true)) {
        
        $currencyArray = array();
        
        
        $checkTime = $rateFileXml->xpath('//currency[1]');
        
        
        $dateStamp = (string) $checkTime[0]->time;
        
        // if( strtotime($dateStamp) <= strtotime("-2 hours") ){
        
        if (strtotime($dateStamp) <= strtotime("-2 hours")) {
            
            $currencyArray = getAPI($supported_rates);
            
            $currencyArray = compareToCountryXML($currencyArray, $countryFileXml, $date);
            
            $test = createRateFile($currencyArray, $xml_file_name);
            
            $loadNewFile = simplexml_load_string($test);
            
            $test2 = loadRateFile($loadNewFile, $to, $from, $amnt);
        } else {
            
            //load the file
            $test2 = loadRateFile($rateFileXml, $to, $from, $amnt);
        }
        
        
        
        /*!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!*/
        
        
        if (!is_decimal($amnt)) {
            
            //echo "Currency must be a decimal number error 1300";
            displayErrorMessage("1300", $format);
            exit();
        } else {
            
            displayFormat($format, $test2);
        }
        
    } else {
        displayErrorMessage("1200", $format);
        exit();
    }
    
    
} else {
    displayErrorMessage("1000", $format);
    exit();
}








?>