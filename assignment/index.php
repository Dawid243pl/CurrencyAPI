<?php
/* index.php

CW1 Restful Currency Convertor
!---------------------------------------!
Module Code: UFCFX3-15-3
Module Leader: Prakash Chatterjee
Date: 05/12/2019 
!---------------------------------------!


By Dawid Koleczko 17024154
*/
require('config.php');
include 'functions.php';

//disable deafult errors
error_reporting(0);

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
        
    //otherwise throw error 1100
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

   
    $xml_file_name = "rates.xml";
    
    date_default_timezone_set("Europe/London");
    
    $date = time();
    
    $countryFileXml = simplexml_load_file("country.xml");
    
    $rateFileXml = simplexml_load_file("rates.xml");
    
    $supported_rates = array();
    
   
    //check if the currency is a decimal
    if (is_decimal($amnt)) {
       
        displayErrorMessage("1300", $format);
        exit();
        
    }

   
    //check if rate file is there if it is load it otherwise create it
    if ($rateFileXml === FALSE) {
      
        //check if the currency we want to convert is in our currency list otherwise 1200 erorr
        if ( (in_array($to, startingCurrencies)) && (in_array($from, startingCurrencies)) ) {
            
            //make the file
            makeFile(startingCurrencies,$format,$to,$from,$amnt);
        } else {
            displayErrorMessage("1200", $format);
            exit();
        }

    } else {
        //read file
        //Make a new support rates array by getting all the currenices exluding the deleted currencies
        $getSupportedRates = $rateFileXml->xpath('//code[not(@display)]/ancestor::currency');
       
        //print("<pre>".print_r($getSupportedRates,true)."</pre>");

        
        foreach ($getSupportedRates as $supportedRate) {
            
            $rateBack = (string) $supportedRate->code;
            
            array_push($supported_rates, $rateBack);
        }

     
        //check if To and From is valid otherwise 1200 error
        if ( (in_array($to, $supported_rates)) && (in_array($from, $supported_rates)) ) {
            
            
            $currencyArray = array();
            
            
            $checkTime = $rateFileXml->xpath('/holder/@time');
            
            
            $dateStamp = (string) $checkTime[0]->time;
            
        
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