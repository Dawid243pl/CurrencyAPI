<?php

function is_decimal( $val )
{

    if(strpos($val,".") !== false){
        
        return true;
    }else{
        return false;
    }
}



if (   (isset($_GET['from'])) && (isset($_GET['to'])) && (isset($_GET['amnt'])) && (isset($_GET['format']))  )  {
        
        $from = htmlspecialchars($_GET["from"]);
        
        $to = htmlspecialchars($_GET["to"]);

        $amnt = htmlspecialchars($_GET["amnt"]);

        $format = htmlspecialchars($_GET["format"]);
       

$url = "http://data.fixer.io/api/latest?access_key=cbc73bcd8ffa149c344ba19ef687fa31";

$contents = file_get_contents($url);


$ratez=json_decode($contents);

$rate = $ratez->rates;

$gbp = $rate->GBP;



$xml_file_name ="rateV1.xml";

date_default_timezone_set("Europe/London");


//$date = date('d F Y H:i');

$date = time();

//echo $date."Time Now<br>";

//echo date('m/d/Y', 1299446702);


$xml2=simplexml_load_file("country.xml");

$xml3=simplexml_load_file("rateV1.xml");

$supported_rates =array();

$xml4=simplexml_load_file("rateV1.xml");


$getSupportedRates=$xml4->xpath('//currency[not(@display)]');

    foreach ($getSupportedRates as $supportedRate) {

        $rateBack= (string) $supportedRate->code;

        array_push($supported_rates,$rateBack);
}   

$checkFrom = false;
$checkTo = false;

for ($b =0;$b< sizeof($supported_rates);$b++ ){


    if($from == $supported_rates[$b]){

        $checkFrom = true;

    }

    if($to == $supported_rates[$b]){

        $checkTo = true;

    }


}

if ( ($checkFrom == true) && ($checkTo == true) ){
   

//$supported_rates =array("AUD","BRL","CAD","CHF","CNY","DKK","EUR","GBP","HKD","HUF","INR","JPY","MXN","MYR","NOK","NZD","PHP","RUB","SEK","SGD","THB","TRY","USD","ZAR");


$currencyArray = array();


//open file check the last update date hr?
//if more than 2 hrs or less than time update else do nothing



$ratesFile=simplexml_load_file("rateV1.xml");

$checkTime=$ratesFile->xpath('//currency');

foreach ($checkTime as $backTime) {
        
    $dateStamp= (string) $backTime->time;

    //echo (strtotime($dateStamp) . "Time stamped<br>");
    
    //echo date('d F Y H:i', strtotime($dateStamp))."Time stamped human<br>";
    
    //echo strtotime('+1 day', strtotime($dateStamp))."+1 day<br>";

    // if( strtotime($dateStamp) <= strtotime("-2 hours") ){

    if( 1 <2  ){

        //echo "TIME IS BIGGER THAN 2 or databse time is bigger then curr time";
                
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


        $res2=$xml2->xpath('//CcyNtry');

        $string = "";

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

$test = $dom->saveXML();

$dom->save($xml_file_name);

        //get new data from api

    }else{

        //dont do anything
    }
    
    //echo $dateStamp."<br>";
    break; 

} 




/*!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!*/


$toResponse=$xml3->xpath("//currency[code='" . $to . "'][not(@display)]");


$atTo= (string) $toResponse[0]->time;
$codeTo= (string) $toResponse[0]->code;
$currTo= (string) $toResponse[0]->currencyName;
$locTo= (string) $toResponse[0]->location;
$rateTo= (string) $toResponse[0]->rate;

$fromResponse=$xml3->xpath("//currency[code='" . $from . "'][not(@display)]");


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



if (!is_decimal($amnt) ){

    echo "Currency must be a decimal number error 1300";
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
        echo "Error 1400 Format must be XML OR JSON";

    }
    
}


}else{
    echo "Error Currency type not recognised 1200";
}


}else{
    echo "Error Required parameters missing 1000";
}








?>