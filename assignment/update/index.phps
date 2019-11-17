<?php
    require('../config.php');
    include '../functions.php';
    error_reporting(0);

    
    $defaultFormat = "xml";
    
    //error_reporting(0);
    $cur = htmlspecialchars($_GET["cur"]);
    
    $action = htmlspecialchars($_GET["action"]);

    date_default_timezone_set("Europe/London");

    $date = time();
    $filename ="../rateV1.xml";
    //echo $action."<br>".$cur;
    //de activate the current rate do not delete ie add an attribute        

   
    $checkCurrency = simplexml_load_file("../country.xml");
    
    $checkCurr = $checkCurrency->xpath("//CcyNtry[Ccy='" . $cur . "']");

    
    if (empty($checkCurr)){

        //echo "Error 2100 Currency code in wrong format or is missing";
     
        displayErrorMessage("2100",$defaultFormat);
        die();
    }

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