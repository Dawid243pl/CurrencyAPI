<?php
    require('../config.php');
    include '../functions.php';
    error_reporting(0);

    
    $defaultFormat = "xml";
    
    //error_reporting(0);
    $cur = htmlspecialchars($_GET["cur"]);
    
    $action = htmlspecialchars($_GET["action"]);

    if ($cur == baseRate){
        displayErrorMessage("2400",defaultFormat);
        die();
    } 
    
    date_default_timezone_set("Europe/London");

    $date = time();
    $filename ="../rates.xml";
    //echo $action."<br>".$cur;
    //de activate the current rate do not delete ie add an attribute        
   /*
    $checkCurrency = simplexml_load_file("../rates.xml");
    
    $checkCurr = $checkCurrency->xpath("//CcyNtry[Ccy='" . $cur . "']");
*/
    $xml = simplexml_load_file("../rates.xml");

    $findRate = $xml->xpath("//currency[code='" . $cur . "']/rate");
    
    if (($cur == null) || (!is_string($cur))  ){

        //echo "Error 2100 Currency code in wrong format or is missing";
     
        displayErrorMessage("2100",$defaultFormat);
        die();
    }

    /*
    if (empty($findRate)){

        //echo "Error 2100 Currency code in wrong format or is missing";
     
        displayErrorMessage("2200",$defaultFormat);
        die();
    }
*/



    //2200 chec if 


    if ($action == "del"){

   
        deleteCurrency ($cur,$action);

    //add new rate or update current
    }else if ($action == "put"){

        putCurrency ($cur);
    //update a current rate    
    }else if($action == "post") {
   
        postCurrency($cur);
    }else{
        displayErrorMessage("2000",$defaultFormat);
        die();
    }


?>        