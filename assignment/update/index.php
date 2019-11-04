<?php
    include '../functions.php';
    //error_reporting(0);

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

        deleteRate($cur,$date,$action,$defaultFormat);
      
    //put in a new rate 
    /*
    1 step open the country grab infrom from there
    2 step past the infro to rate v1
    3 step update this info?
    */
    }else if ($action == "put"){


        $xml = simplexml_load_file("../country.xml");
    

        $obj = $xml->xpath("//CcyNtry[Ccy='" . $cur . "']");

        //print("<pre>".print_r($obj,true)."</pre>");

        $string="";

        $currencyName = (string)  $obj[0]->CcyNm;

        $newCurrencyArray = getCurrencyArray($cur,$currencyName,$date,$obj,$string);
       
        $newCurrencyArray = getAPI2($newCurrencyArray,$cur,$date);
  
        $xml = simplexml_load_file("../rateV1.xml");
    

        $obj = $xml->xpath("//currency[code='" . $cur . "']");

        //print("<pre>".print_r($obj,true)."</pre>");
        if (empty($obj)){
        //echo "Currency does not exist new currency added";
            
        addNewCurr($newCurrencyArray,$filename);
        
        displayFile($newCurrencyArray);

        
        }else{
            //then update it
            //displayFile($newCurrencyArray);
            $url = "http://data.fixer.io/api/latest?access_key=cbc73bcd8ffa149c344ba19ef687fa31";

            $contents = file_get_contents($url);
    
            $ratez=json_decode($contents);
    
            $rate = $ratez->rates;
    
            $gbp = $rate->GBP;
    
            $newRate = array();
    
            foreach ($rate as $key=> $item) {
    
                if ($cur == $key){
    
                    array_push($newRate,$cur,$item / $gbp);
    
                }
    
            }     
      
            
            printPost($newRate,$date,$cur,$filename);
    
           
        }
                


    //update a current rate    
    }else if($action == "post") {
        
        $url = "http://data.fixer.io/api/latest?access_key=cbc73bcd8ffa149c344ba19ef687fa31";

        $contents = file_get_contents($url);

        $ratez=json_decode($contents);

        $rate = $ratez->rates;

        $gbp = $rate->GBP;

        $newRate = array();

        foreach ($rate as $key=> $item) {

            if ($cur == $key){

                array_push($newRate,$cur,$item / $gbp);

            }

        }     
  
        
        printPost($newRate,$date,$cur,$filename);

    }else{
        displayErrorMessage("2000",$defaultFormat);
        die();
    }


?>        