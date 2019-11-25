<?php


function makeFile ($startingCurrencies,$format,$to,$from,$amnt){

//get The API    
$newArray =array();    

$xml_file_name = ratesF;

$loadrate = simplexml_load_file(ratesF);

$loadCtry = simplexml_load_file(countryF);

date_default_timezone_set("Europe/London");

$date = time();


$contents = file_get_contents(apiKey);

$ratez=json_decode($contents);

$rate = $ratez->rates;

$gbp = $rate->GBP;


//get the rates from the API and put them in the curency array
foreach ($rate as $key=> $item) {
    
    for ($i =0; $i < sizeof($startingCurrencies );$i++){

        if ($startingCurrencies[$i] == $key){

            $tempCurrency = array();

            array_push($tempCurrency,$key,number_format(($item / $gbp),2));
            
            array_push($newArray,$tempCurrency);
        


            }
    }

}

//Compare the rates array to the country array to get all the other information 

$res2=$loadCtry->xpath('//CcyNtry');

$string = "";

//loop from my currency array and look in the country xml to get the currency name and location and put them into the array
for ($i =0; $i < sizeof($newArray);$i++){

    foreach ($res2 as $aviable) {

        if ($newArray[$i][0] == $aviable->Ccy){

            
            if (!isset($newArray[$i][2]) )
            {
                $CurrencyName= (string) $aviable->CcyNm;

                //echo "pushing this date".date('d F Y H:i',$date,"<br>"); 

                array_push($newArray[$i],$CurrencyName);
            }
            
            if (!isset($newArray[$i][3]) )
            {
                $location= (string) $aviable->CtryNm;

             
                
                //$string = str_replace ("Iphone","iPhone", $string);
               
                $string = $string.$location.", ";
                

                $string = ucwords(ucwords(strtolower($string), ","));
                $string = str_replace(" And "," and ", $string);
                $string = str_replace(" Of "," of ", $string);
                $string = str_replace(" Da "," da ", $string);
                $string = str_replace(" The "," the ", $string);
            }
        }


    }   
    
    array_push($newArray[$i],substr($string, 0, -2));
    $string = "";
}



 //create the rateV1File

 $dom = new DOMDocument("1.0");

 $root = $dom->createElement('holder');

 $dom->appendChild($root);

 $domAttribute = $dom->createAttribute('time');

 // Value for the created attribute
 $domAttribute->value = date('d M Y H:i',$date);

 // Don't forget to append it to the element
 $root->appendChild($domAttribute);
 
 for ($z =0;$z < sizeof($newArray);$z++){
 
     $itemNode = $dom->createElement("currency");
      
      
     $itemNode->appendChild($dom->createElement("code",$newArray[$z][0])); 
     $itemNode->appendChild($dom->createElement("rate",$newArray[$z][1]));
     $itemNode->appendChild($dom->createElement("currencyName",$newArray[$z][2]));
     $itemNode->appendChild($dom->createElement("location",$newArray[$z][3]));
 

     $root->appendChild($itemNode);

}

//save as rates v1
$test = $dom->saveXML();
//if there is a rates file already rename it and create a new rateV!
if (file_exists($xml_file_name)) {
  
  $newFName = str_replace(".xml", "", $xml_file_name);

  $renamed= rename($xml_file_name, $newFName."-".$date.".xml");
  
  $dom->save($xml_file_name);
}else {
  //save the brand new file
  $dom->save($xml_file_name);
}

$loadNewFile = simplexml_load_string($test);

$getTime=$loadNewFile->xpath("/holder/@time");

    $gotTime= (string) $getTime[0]->time;

    $toResponse=$loadNewFile->xpath("//currency[code='" . $to . "']");


    $codeTo= (string) $toResponse[0]->code;
    $currTo= (string) $toResponse[0]->currencyName;
    $locTo= (string) $toResponse[0]->location;
    $rateTo= (string) $toResponse[0]->rate;

    $fromResponse=$loadNewFile->xpath("//currency[code='" . $from . "']");


    $codeFrom= (string) $fromResponse[0]->code;
    $currFrom= (string) $fromResponse[0]->currencyName;
    $locFrom= (string) $fromResponse[0]->location;
    $rateFrom= (string) $fromResponse[0]->rate;

    
    $conversion = number_format(($rateTo / $rateFrom),2);

    $conversion2 = number_format(($rateTo / $rateFrom) * $amnt,2);

    $formatedCalculation = str_replace( ',', '', $conversion2 );

    $dom2 = new DOMDocument("1.0");

    $root2 = $dom2->createElement('conv');

    $dom2->appendChild($root2);

    //$mainNode = $dom2->createElement("conv");

    $root2->appendChild($dom2->createElement("at",$gotTime)); 
    $root2->appendChild($dom2->createElement("rate",$conversion));
    

    $fromElement = $dom2->createElement("from");
    $fromElement->appendChild($dom2->createElement("code",$codeFrom)); 
    $fromElement->appendChild($dom2->createElement("curr",$currFrom)); 
    $fromElement->appendChild($dom2->createElement("loc",$locFrom)); 
    $fromElement->appendChild($dom2->createElement("amnt",$amnt)); 
    $root2->appendChild($fromElement);

    $toElement = $dom2->createElement("to");
    $toElement->appendChild($dom2->createElement("code",$codeTo)); 
    $toElement->appendChild($dom2->createElement("curr",$currTo)); 
    $toElement->appendChild($dom2->createElement("loc",$locTo)); 
    $toElement->appendChild($dom2->createElement("amnt",$formatedCalculation)); 
    $root2->appendChild($toElement);


    //$root2->appendChild($mainNode);

    $test2 = $dom2->saveXML();
    
    displayFormat($format, $test2);
}


function deleteCurrency ($cur,$action){

    date_default_timezone_set("Europe/London");

    $date = time();

    $dom = new DomDocument();
    $dom->load('../rateV1.xml');
    $xp = new DomXPath($dom);
    $res = $xp->query("//currency[code='" . $cur . "']/code");

       //print("<pre>".print_r($res->item(0) ,true)."</pre>");
        $res->item(0)->setAttribute('display','none');
        $dom->save('../rateV1.xml');


        $doc = new DOMDocument('1.0', "UTF-8");

        $action = $doc->createElement('action');

        $domAttribute = $doc->createAttribute('type');

        // Value for the created attribute
        $domAttribute->value = "del";

        // Don't forget to append it to the element
        $action->appendChild($domAttribute);

        $at = $doc->createElement("at", date('d M Y H:i',$date));
        $action->appendChild($at);

        $newRate = $doc->createElement("curr",$cur);
        $action->appendChild($newRate);
    

        $doc->appendChild($action);

        header('Content-Type: text/xml');
        print $doc->saveXML();
    

    
}


function postCurrency ($cur){

    $xml = simplexml_load_file("../rateV1.xml");

    $findRate = $xml->xpath("//currency[code='" . $cur . "']/rate");

   
    if ($findRate[0] == false){

        
        displayErrorMessage("2300",defaultFormat);
        die();
    }

    date_default_timezone_set("Europe/London");

    $date = time();

    $contents = file_get_contents(apiKey);

    $ratez=json_decode($contents);

    $rate = $ratez->rates;
    
    if ($rate === ""){
        displayErrorMessage("2300",defaultFormat);
        die();
    }

    $gbp = $rate->GBP;

    $newRate = array();

    foreach ($rate as $key=> $item) {

        if ($cur == $key){

            array_push($newRate,$cur,number_format(($item / $gbp),2));

        }

    }     

    //$newRate,$date,$cur,$filename
    //ratesF

 
    $obj = $xml->xpath("//currency[code='" . $cur . "']");
     
    $result = $xml->xpath("//currency[code='" . $cur . "']/code/@display");


    if(!empty($result)){
        
        foreach ($result as $node) {
            
            unset($node[0]);
        }
    }

    $savedOldRate = (string) $obj[0]->rate;
    
    $obj[0]->rate = $newRate[1];
    $obj[0]->at = date('d M Y H:i',$date);

    //$rTo= (string) $obj[0]->time;
    $rCode= (string) $obj[0]->code;
    $rCurr= (string) $obj[0]->currencyName;
    $rloc= (string) $obj[0]->location;
    $rRate= (string) $obj[0]->rate;

    //echo $xml->asXml();
    $xml->asXml("../rateV1.xml");

    $doc = new DOMDocument('1.0', "UTF-8");

    $action = $doc->createElement('action');

    $domAttribute = $doc->createAttribute('type');

    // Value for the created attribute
    $domAttribute->value = "post";

    // Don't forget to append it to the element
    $action->appendChild($domAttribute);

    $at = $doc->createElement("at", date('d M Y H:i',$date));

    $action->appendChild($at);

    $newRate = $doc->createElement("rate",$newRate[1]);
    $action->appendChild($newRate);
    
    $oldRate = $doc->createElement("old_rate",$savedOldRate);
    $action->appendChild($oldRate);

    $curren = $doc->createElement('curr'); 

    $code = $doc->createElement("code",$cur);
    $curren->appendChild($code);
    
    $name = $doc->createElement("name",$rCurr);
    $curren->appendChild($name);

    $loc = $doc->createElement("loc",$rloc);
    $curren->appendChild($loc);


    // Append it to the document itself
    $action->appendChild($curren);

    $doc->appendChild($action);

    //echo $doc->saveXML();
    header('Content-Type: text/xml');
    print $doc->saveXML();

    
}

function putCurrency ($cur){
      
    $xml = simplexml_load_file("../rateV1.xml");


    $obj = $xml->xpath("//currency[code='" . $cur . "']");

    if ($obj[0] == false){

        
        displayErrorMessage("2300",defaultFormat);
        die();
    }


    //print("<pre>".print_r($obj,true)."</pre>");
    if (empty($obj)){
    //echo "Currency does not exist new currency added";
    
    $xml = simplexml_load_file("../country.xml");
    
    $obj = $xml->xpath("//CcyNtry[Ccy='" . $cur . "']");

    $string="";

    $currencyName = (string)  $obj[0]->CcyNm;

    $newCurrencyArray = array();

        array_push($newCurrencyArray,$cur,$currencyName);

       
        for ($n = 0;$n < sizeof($obj);$n++){

            $location = $obj[$n]->CtryNm;

            

            $string = $string.$location.",";


        }
    array_push($newCurrencyArray,substr($string, 0, -1));


    $contents = file_get_contents(apiKey);

    $ratez=json_decode($contents);

    $rate = $ratez->rates;

    $gbp = $rate->GBP;

    $newRate = array();

    foreach ($rate as $key=> $item) {

        if ($cur == $key){

            array_push($newCurrencyArray,number_format(($item / $gbp),2) );

        }

    } 
    
    addNewCurr($newCurrencyArray,"../rateV1.xml");
    
    displayFile($newCurrencyArray);

    
    }else{
  
        postCurrency($cur);
       
    }

 
    
}

function displayFormat($format,$test2){
    if ($format == "xml"){
            

        header('Content-Type:text/xml');
        echo $test2;

    }
    else if ($format == "json"){
        $xml = simplexml_load_string($test2);
        $json = json_encode($xml);
        header ("Content-Type: application/json");
        echo $json;
    }
    else{
        displayErrorMessage("1400",$format);
        exit();
    }
}

function addNewCurr($newCurrencyArray,$filename){
    //print_r($newCurrencyArray);
    /// load XML, create XPath object
    $xml2 = new DomDocument();
    $xml2->preserveWhitespace = false;
    $xml2->load($filename);
    $xpath = new DOMXPath($xml2);

    // get node mainNode, which we will append to
    $mainNode = $xpath->query('//currency[last()]')->item(0);

    // create node john
    $newRate = $xml2->createElement('currency');


    $code = $xml2->createElement('code', $newCurrencyArray[0]);
    $newRate->appendChild($code);

    $rate = $xml2->createElement('rate', $newCurrencyArray[3]);
    $newRate->appendChild($rate);

    $currencyName = $xml2->createElement('currencyName', $newCurrencyArray[1]);
    $newRate->appendChild($currencyName);
    
    //$time = $xml2->createElement('time', $newCurrencyArray[2]);
    //$newRate->appendChild($time);

    $location = $xml2->createElement('location', $newCurrencyArray[2]);
    $newRate->appendChild($location);
    

    $mainNode->parentNode->insertBefore($newRate, $mainNode->nextSibling);


    
    // show result
    //header('Content-Type: text/plain');
    //print $xml2->saveXML();
    $xml2->save($filename); // save as file


}

function displayFile($newCurrencyArray){

    date_default_timezone_set("Europe/London");

    $date = time();

    $doc = new DOMDocument('1.0', "UTF-8");

    $action = $doc->createElement('action');

    $domAttribute = $doc->createAttribute('type');

    // Value for the created attribute
    $domAttribute->value = "put";

    // Don't forget to append it to the element
    $action->appendChild($domAttribute);

    $at = $doc->createElement("at",date('d M Y H:i',$date));
    $action->appendChild($at);

    $newRate = $doc->createElement("rate",$newCurrencyArray[3]);
    $action->appendChild($newRate);
    
    $curren = $doc->createElement('curr'); 

    $code = $doc->createElement("code",$newCurrencyArray[0]);
    $curren->appendChild($code);
    
    $name = $doc->createElement("name",$newCurrencyArray[1]);
    $curren->appendChild($name);

    $loc = $doc->createElement("loc",$newCurrencyArray[2]);
    $curren->appendChild($loc);

    // Append it to the document itself
    $action->appendChild($curren);

    $doc->appendChild($action);

    header('Content-Type: text/xml');
    print $doc->saveXML();
}

function is_decimal( $val )
{
    return (!(is_numeric( $val ) || floor( $val ) != $val) || preg_match('/\.\d{3,}/', $val) );
        
   
}

//deal with error messages
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
function loadRateFile($rateFileXml,$to,$from,$amnt){

    $getTime=$rateFileXml->xpath("/holder/@time");

    $gotTime= (string) $getTime[0]->time;

    $toResponse=$rateFileXml->xpath("//currency[code='" . $to . "']");


    $codeTo= (string) $toResponse[0]->code;
    $currTo= (string) $toResponse[0]->currencyName;
    $locTo= (string) $toResponse[0]->location;
    $rateTo= (string) $toResponse[0]->rate;

    $fromResponse=$rateFileXml->xpath("//currency[code='" . $from . "']");


    $codeFrom= (string) $fromResponse[0]->code;
    $currFrom= (string) $fromResponse[0]->currencyName;
    $locFrom= (string) $fromResponse[0]->location;
    $rateFrom= (string) $fromResponse[0]->rate;



    $conversion = number_format(($rateTo / $rateFrom),2);

    $conversion2 = number_format(($rateTo / $rateFrom) * $amnt,2);

    $formatedCalculation = str_replace( ',', '', $conversion2 );

    $dom2 = new DOMDocument("1.0");

    $root2 = $dom2->createElement('conv');

    $dom2->appendChild($root2);


    $root2->appendChild($dom2->createElement("at",$gotTime)); 
    $root2->appendChild($dom2->createElement("rate",$conversion));

    $fromElement = $dom2->createElement("from");
    $fromElement->appendChild($dom2->createElement("code",$codeFrom)); 
    $fromElement->appendChild($dom2->createElement("curr",$currFrom)); 
    $fromElement->appendChild($dom2->createElement("loc",$locFrom)); 
    $fromElement->appendChild($dom2->createElement("amnt",$amnt)); 
    $root2->appendChild($fromElement);

    $toElement = $dom2->createElement("to");
    $toElement->appendChild($dom2->createElement("code",$codeTo)); 
    $toElement->appendChild($dom2->createElement("curr",$currTo)); 
    $toElement->appendChild($dom2->createElement("loc",$locTo)); 
    $toElement->appendChild($dom2->createElement("amnt",$formatedCalculation)); 
    $root2->appendChild($toElement);


    $test2 = $dom2->saveXML();
    return $test2;
}



?>