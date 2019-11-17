<?php

require('config.php');
include 'functions.php';
error_reporting(0);


//decimal?

//check if each GET matches the parameters we want
foreach ($_GET as $key => $value) {
       
if ( (($key == "from") && in_array($key, params)) ){
    $from = htmlspecialchars($_GET["from"]);

   
}else if ( (($key == "to") && in_array($key, params)) ){
    $to = htmlspecialchars($_GET["to"]);
    

}else if ( (($key == "amnt") && in_array($key, params)) ){
    
    $amnt = htmlspecialchars($_GET["amnt"]);


}else if ( (($key == "format") && in_array($key, params)) ){
    $format = htmlspecialchars($_GET["format"]);
    
//otherwise thorw error that paramter not recognised
}else {

    $format = $_GET["format"];

    displayErrorMessage("1100", $format);
        exit();

}

}


//Check if the params we want are actually there otherwise thorw paramter missing
if ((isset($_GET['from'])) && (isset($_GET['to'])) && (isset($_GET['amnt'])) ) {
    
    if (!isset($_GET['format'])) {

        $format = "xml";

    }   


    $xml_file_name = "rateV1.xml";
    
  
    date_default_timezone_set("Europe/London");
    
    $date = time();
    
    $countryFileXml = simplexml_load_file("country.xml");
    
    $rateFileXml = simplexml_load_file("rateV1.xml");
    
    $supported_rates = array();
    
   
    //check if the currency is a decimal
    if (is_decimal($amnt)) {
        //echo "Currency must be a decimal number error 1300";
        displayErrorMessage("1300", $format);
        exit();
        
    }

   
    //check if rate file is there if it is load it otherwise create it
    if ($rateFileXml === FALSE) {
      
        //check if the currency we want to convert is in our currency list    
        if ( (in_array($to, startingCurrencies)) && (in_array($from, startingCurrencies)) ) {
            
            makeFile(startingCurrencies,$format,$to,$from,$amnt);
        } else {
            displayErrorMessage("1200", $format);
            exit();
        }

    } else {
        //read file
        //Make a new supporte rates array by getting all the currenices exluding the deleted currencies
        $getSupportedRates = $rateFileXml->xpath('//code[not(@display)]/ancestor::currency');
       
        //print("<pre>".print_r($getSupportedRates,true)."</pre>");

        foreach ($getSupportedRates as $supportedRate) {
            
            $rateBack = (string) $supportedRate->code;
            
            array_push($supported_rates, $rateBack);
        }

     
        
        if ( (in_array($to, $supported_rates)) && (in_array($from, $supported_rates)) ) {
            
            
            $currencyArray = array();
            
            
            $checkTime = $rateFileXml->xpath('/holder/@time');
            
            
            $dateStamp = (string) $checkTime[0]->time;
            
            // if( strtotime($dateStamp) <= strtotime("-2 hours") ){
            //if time is bigger than 2 hours since last update grab new rates otherwise load old rates
            if ( strtotime($dateStamp) <= strtotime("-2 hours")) {
                
                makeFile($supported_rates,$format,$to,$from,$amnt);
            } else {
                
                //load the file
                $test2 = loadRateFile($rateFileXml, $to, $from, $amnt);
                displayFormat($format, $test2);
            }
                            
            
        } else {
            displayErrorMessage("1200", $format);
            exit();
        }
        
    }
    
} else {
    displayErrorMessage("1000", $format);
    exit();
}








?>