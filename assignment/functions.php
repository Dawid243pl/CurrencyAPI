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


    if (strpos($_SERVER['REQUEST_URI'], "update") !== false){

        $errorCodes=simplexml_load_file("../error.xml");
    }else{

        $errorCodes=simplexml_load_file("error.xml");

    }

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

//getting the API
function getAPI($supported_rates){

$newArray =array();    
$url = "http://data.fixer.io/api/latest?access_key=cbc73bcd8ffa149c344ba19ef687fa31";

$contents = file_get_contents($url);

$ratez=json_decode($contents);

$rate = $ratez->rates;

$gbp = $rate->GBP;


//get the rates from the API and put them in the curency array
foreach ($rate as $key=> $item) {
    
    for ($i =0; $i < sizeof($supported_rates );$i++){

        if ($supported_rates[$i] == $key){

            //echo $key . ' : ';
            //echo $item / $gbp. '<br>';

            $tempCurrency = array();

            array_push($tempCurrency,$key,$item / $gbp);
            
            array_push($newArray,$tempCurrency);
        


            }
    }

}
return $newArray;
}

//loading the rates file to display it
function loadRateFile($rateFileXml,$to,$from,$amnt){
    $toResponse=$rateFileXml->xpath("//currency[code='" . $to . "'][not(@display)]");


    $atTo= (string) $toResponse[0]->time;
    $codeTo= (string) $toResponse[0]->code;
    $currTo= (string) $toResponse[0]->currencyName;
    $locTo= (string) $toResponse[0]->location;
    $rateTo= (string) $toResponse[0]->rate;

    $fromResponse=$rateFileXml->xpath("//currency[code='" . $from . "'][not(@display)]");


    $atFrom= (string) $fromResponse[0]->time;
    $codeFrom= (string) $fromResponse[0]->code;
    $currFrom= (string) $fromResponse[0]->currencyName;
    $locFrom= (string) $fromResponse[0]->location;
    $rateFrom= (string) $fromResponse[0]->rate;



    $conversion = ($rateTo / $rateFrom);

    $conversion2 = ($rateTo / $rateFrom) * $amnt;


    $dom2 = new DOMDocument("1.0");

    $root2 = $dom2->createElement('root');

    $dom2->appendChild($root2);

    $mainNode = $dom2->createElement("conv");

    $mainNode->appendChild($dom2->createElement("at",$atTo)); 
    $mainNode->appendChild($dom2->createElement("rate",$conversion));

    $fromElement = $dom2->createElement("from");
    $fromElement->appendChild($dom2->createElement("code",$codeFrom)); 
    $fromElement->appendChild($dom2->createElement("curr",$currFrom)); 
    $fromElement->appendChild($dom2->createElement("loc",$locFrom)); 
    $fromElement->appendChild($dom2->createElement("amnt",$amnt)); 
    $mainNode->appendChild($fromElement);

    $toElement = $dom2->createElement("to");
    $toElement->appendChild($dom2->createElement("code",$codeTo)); 
    $toElement->appendChild($dom2->createElement("curr",$currTo)); 
    $toElement->appendChild($dom2->createElement("loc",$locTo)); 
    $toElement->appendChild($dom2->createElement("amnt",$conversion2)); 
    $mainNode->appendChild($toElement);


    $root2->appendChild($mainNode);

    $test2 = $dom2->saveXML();
    return $test2;
}


function compareToCountryXML($currencyArray,$countryFileXml,$date){

    $res2=$countryFileXml->xpath('//CcyNtry');

        $string = "";

        //loop from my currency array and look in the country xml to get the currency name and location and put them into the array
        for ($i =0; $i < sizeof($currencyArray);$i++){

            foreach ($res2 as $aviable) {

                if ($currencyArray[$i][0] == $aviable->Ccy){

                    
                    if (!isset($currencyArray[$i][2]) )
                    {
                        $CurrencyName= (string) $aviable->CcyNm;

                        //echo "pushing this date".date('d F Y H:i',$date,"<br>"); 

                        array_push($currencyArray[$i],$CurrencyName,date('d F Y H:i',$date));
                    }
                    
                    if (!isset($currencyArray[$i][4]) )
                    {
                        $location= (string) $aviable->CtryNm;

                        $string = $string.$location.",";

                    
                    }
                }


            }   
            
            array_push($currencyArray[$i],substr($string, 0, -1));
            $string = "";
        } 

        return $currencyArray;
}


function createRateFile($currencyArray,$xml_file_name){
       //create the rateV1File
       $dom = new DOMDocument("1.0");

       $root = $dom->createElement('holder');

       $dom->appendChild($root);


       for ($z =0;$z < sizeof($currencyArray);$z++){
       
           $itemNode = $dom->createElement("currency");
           

           $itemNode->appendChild($dom->createElement("code",$currencyArray[$z][0])); 
           $itemNode->appendChild($dom->createElement("rate",$currencyArray[$z][1]));
           $itemNode->appendChild($dom->createElement("currencyName",$currencyArray[$z][2]));
           $itemNode->appendChild($dom->createElement("time",$currencyArray[$z][3]));
           $itemNode->appendChild($dom->createElement("location",$currencyArray[$z][4]));
       

           $root->appendChild($itemNode);

   }

    //print("<pre>".print_r($currencyArray,true)."</pre>");

    //save as rates v1
    $test = $dom->saveXML();
    $dom->save($xml_file_name);

    return $test;

}

function deleteRate($cur,$date,$action,$defaultFormat){
    $dom = new DomDocument();
    $dom->load('../rateV1.xml');
    $xp = new DomXPath($dom);
    $res = $xp->query("//currency[code='" . $cur . "']");

    
    if ($res->length>0){
       // print("<pre>".print_r($res->item(0) ,true)."</pre>");
        $res->item(0)->setAttribute('display','none');
        $dom->save('../rateV1.xml');


        $doc = new DOMDocument('1.0', "UTF-8");

        $action = $doc->createElement('action');

        $domAttribute = $doc->createAttribute('type');

        // Value for the created attribute
        $domAttribute->value = "del";

        // Don't forget to append it to the element
        $action->appendChild($domAttribute);

        $at = $doc->createElement("at", date('d F Y H:i',$date));
        $action->appendChild($at);

        $newRate = $doc->createElement("curr",$cur);
        $action->appendChild($newRate);
    

        $doc->appendChild($action);

        header('Content-Type: text/xml');
        print $doc->saveXML();
    
    }else{
        displayErrorMessage("2200",$defaultFormat);
        die();
    }
}

?>