<?php
/* Update/index.php

CW1 Restful Currency Convertor
!---------------------------------------!
Module Code: UFCFX3-15-3
Module Leader: Prakash Chatterjee
Date: 05/12/2019 
!---------------------------------------!


By Dawid Koleczko 17024154
*/

    require('../config.php');
    include '../functions.php';
    error_reporting(0);

    //set the deafult error format to XML     
    $defaultFormat = "xml";
   

    //GET the Action and Currency from URL
    $cur = htmlspecialchars($_GET["cur"]);
    
    $action = htmlspecialchars($_GET["action"]);

    //if trying to update the base rate throw 2400 error
    if ($cur == baseRate){
        displayErrorMessage("2400",defaultFormat);
        die();
    } 
    
    //set deafult timezone 
    date_default_timezone_set("Europe/London");

    $date = time();
    $filename ="../rates.xml";
    
    //check if there is such a currency  

    $xml = simplexml_load_file("../rates.xml");

    $findRate = $xml->xpath("//currency[code='" . $cur . "']/rate");
    
    if (($cur == null) || (!is_string($cur))  ){

     
        displayErrorMessage("2100",$defaultFormat);
        die();
    }

    if ($action == "del"){

   
        deleteCurrency ($cur,$action);

    
    }else if ($action == "put"){

        putCurrency ($cur);
     
    }else if($action == "post") {
   
        postCurrency($cur);
    }else{

        displayErrorMessage("2000",$defaultFormat);
        die();
    }


?>        