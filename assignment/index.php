<?php


include 'functions.php';
error_reporting(0);


$keyArray= array();
foreach($_GET as $key=>$value){
 
    array_push($keyArray,$key);

}
for($z =0;$z<sizeof($keyArray);$z++){


    if ($keyArray[$z] == "from"){
        $from = htmlspecialchars($_GET["from"]);

    }else if ($keyArray[$z] == "to"){

        $to = htmlspecialchars($_GET["to"]);
    }else if ($keyArray[$z] == "amnt"){

        $amnt = htmlspecialchars($_GET["amnt"]);
    }else if ($keyArray[$z] == "format"){

        $format = htmlspecialchars($_GET["format"]);
    }else {
        displayErrorMessage("1100",$format);
        exit();
    }     

}

if ( (isset($_GET['from'])) && (isset($_GET['to'])) && (isset($_GET['amnt'])) && (isset($_GET['format']))  )  {
        
        
    
$xml_file_name ="rateV1.xml";


date_default_timezone_set("Europe/London");

$date = time();

$countryFileXml=simplexml_load_file("country.xml");

$rateFileXml=simplexml_load_file("rateV1.xml");

if($rateFileXml===FALSE) {
    displayErrorMessage("1500",$format);
    exit();
}

$supported_rates =array();


$getSupportedRates=$rateFileXml->xpath('//currency[not(@display)]');

    foreach ($getSupportedRates as $supportedRate) {

        $rateBack= (string) $supportedRate->code;

        array_push($supported_rates,$rateBack);
}   

$checkFrom = false;
$checkTo = false;


  
if (in_array($from, $supported_rates)) 
  { 
    $checkFrom = true;
  } 
else
  { 
    $checkFrom = false;
  } 

  if (in_array($to, $supported_rates)) 
  { 
    $checkTo = true;
  } 
else
  { 
    $checkTo = false;
  } 


if ( ($checkFrom == true) && ($checkTo == true) ){
   

//$supported_rates =array("AUD","BRL","CAD","CHF","CNY","DKK","EUR","GBP","HKD","HUF","INR","JPY","MXN","MYR","NOK","NZD","PHP","RUB","SEK","SGD","THB","TRY","USD","ZAR");

$currencyArray = array();


$checkTime=$rateFileXml->xpath('//currency[1]');


    $dateStamp= (string) $checkTime[0]->time;
    
    // if( strtotime($dateStamp) <= strtotime("-2 hours") ){

    if( strtotime($dateStamp) <= strtotime("-2 hours") ){

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
                    
                    array_push($currencyArray,$tempCurrency);
                
    

                    }
            }
        
        }


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

$loadNewFile = simplexml_load_string($test);
        //get new data from ratesV1
        $toResponse=$loadNewFile->xpath("//currency[code='" . $to . "'][not(@display)]");


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
        
    }else{

        //load the file
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

    }
    
   

/*!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!*/


if (!is_decimal($amnt) ){

    //echo "Currency must be a decimal number error 1300";
    displayErrorMessage("1300",$format);
    exit();
}
else {
 
    
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

    }else{
        displayErrorMessage("1200",$format);
        exit();
    }


}else{
    displayErrorMessage("1000",$format);
    exit();
}








?>