<?php 

//check if decimal
function is_decimal( $val )
{

    if(strpos($val,".") !== false){
        
        return true;
    }else{
        return false;
    }
}

//parse error messages
function displayErrorMessage($errCd,$format)
{
    $errorCodes=simplexml_load_file("error.xml");

    $err=$errorCodes->xpath("//error[code='".$errCd."']");
    
    if (empty($err)){

        $errorCode = "??";
        $message = "Error Unknown";
    }else{
        $errorCode = (string)  $err[0]->code;
        $message = (string) $err[0]->msg;
    }

    $doc = new DOMDocument('1.0', "UTF-8");

    $action = $doc->createElement('conv');

    $er = $doc->createElement("error");

    $cd = $doc->createElement("code",$errorCode);
    $er->appendChild($cd);

    $ms = $doc->createElement("msg",$message);
    $er->appendChild($ms);
  

    $action->appendChild($er);
    $doc->appendChild($action);

    $savedMessage = $doc->saveXML();
   
    header('Content-Type:text/'.$format.'');

    if ($format == "xml"){
        
        header('Content-Type:text/xml');
        echo $savedMessage;
    }
    else if ($format == "json"){
        $xml = simplexml_load_string($savedMessage);
        $json = json_encode($xml);
        header ("Content-Type: application/json");
        echo $json;
    }else{
        header('Content-Type:text/xml');
        echo $savedMessage;
    }

    //print $doc->saveXML();

    //return $doc;
}

///Get all paramters and add them to an array and check if they exist
function getParameters($parameter){

    $keyArray= array();
    $ParsedKeyArray =array();

    foreach($parameter as $key=>$value){
    
        array_push($keyArray,$key);

    }
    //check each key otherwise throw in error
    for($z =0;$z<sizeof($keyArray);$z++){


        if ($keyArray[$z] == "from"){

            array_push($ParsedKeyArray,$keyArray[$z]);
            $from = htmlspecialchars($_GET["from"]);

        }else if ($keyArray[$z] == "to"){

            array_push($ParsedKeyArray,$keyArray[$z]);
            $from = htmlspecialchars($_GET["to"]);

        }else if ($keyArray[$z] == "amnt"){

            array_push($ParsedKeyArray,$keyArray[$z]);
            $from = htmlspecialchars($_GET["amnt"]);

        }else if ($keyArray[$z] == "format"){

            array_push($ParsedKeyArray,$keyArray[$z]);
            $from = htmlspecialchars($_GET["format"]);
        }else {
            //echo "ERROR 1100 Unrecognised parameter";
            $resultMsg = displayErrorMessage("1100",$format);    
            exit();
            
        }     

    }

    return $ParsedKeyArray;
}

//check if parameters are set
function check_if_set($paramsArray){
    

    if ( (isset($_GET[$paramsArray[0]])) && (isset($_GET[$paramsArray[1]])) && (isset($_GET[$paramsArray[2]])) && (isset($_GET[$paramsArray[3]]))  )  {
        
      return true;
    
    }else{
        $resultMsg = displayErrorMessage("1000",$_GET[$paramsArray[3]]);    
        exit();
        return false;
    }

}





?>