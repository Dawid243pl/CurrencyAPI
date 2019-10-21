<?php
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

        echo "Error 2100 Currency code in wrong format or is missing";
        die();
    }


    if ($action == "del"){

        $dom = new DomDocument();
        $dom->load('../rateV1.xml');
        $xp = new DomXPath($dom);
        $res = $xp->query("//currency[code='" . $cur . "']");
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

        $newCurrencyArray = array();

        array_push($newCurrencyArray,$cur,$currencyName,date('d F Y H:i',$date));

       
        for ($n = 0;$n < sizeof($obj);$n++){

            $location = $obj[$n]->CtryNm;

            

            $string = $string.$location.",";


        }
        array_push($newCurrencyArray,substr($string, 0, -1));
        //print_r($newCurrencyArray);
       
        $url = "http://data.fixer.io/api/latest?access_key=cbc73bcd8ffa149c344ba19ef687fa31";

        $contents = file_get_contents($url);

        $ratez=json_decode($contents);

        $rate = $ratez->rates;

        $gbp = $rate->GBP;

        $newRate = array();

        foreach ($rate as $key=> $item) {

            if ($cur == $key){

                array_push($newCurrencyArray,$item / $gbp);

            }

        }     
        //print_r($newCurrencyArray);

        $xml = simplexml_load_file("../rateV1.xml");
    

        $obj = $xml->xpath("//currency[code='" . $cur . "']");

        //if set overrigh if not put new;

        //print("<pre>".print_r($obj,true)."</pre>");
        if (empty($obj)){
            //echo "Currency does not exist new currency added";
                        // Open and parse the XML file

        //$xml2 = simplexml_load_file($filename);    

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
    
        $rate = $xml2->createElement('rate', $newCurrencyArray[4]);
        $newRate->appendChild($rate);

        $currencyName = $xml2->createElement('currencyName', $newCurrencyArray[1]);
        $newRate->appendChild($currencyName);
        
        $time = $xml2->createElement('time', $newCurrencyArray[2]);
        $newRate->appendChild($time);

        $location = $xml2->createElement('location', $newCurrencyArray[3]);
        $newRate->appendChild($location);
        

        $mainNode->parentNode->insertBefore($newRate, $mainNode->nextSibling);


        
        // show result
        //header('Content-Type: text/plain');
        //print $xml2->saveXML();
        $xml2->save($filename); // save as file


       

        $doc = new DOMDocument('1.0', "UTF-8");

        $action = $doc->createElement('action');
    
        $domAttribute = $doc->createAttribute('type');
    
        // Value for the created attribute
        $domAttribute->value = "put";
    
        // Don't forget to append it to the element
        $action->appendChild($domAttribute);
    
        $at = $doc->createElement("at",$newCurrencyArray[2]);
        $action->appendChild($at);
    
        $newRate = $doc->createElement("rate",$newCurrencyArray[4]);
        $action->appendChild($newRate);
        
        $curren = $doc->createElement('curr'); 
    
        $code = $doc->createElement("code",$newCurrencyArray[0]);
        $curren->appendChild($code);
        
        $name = $doc->createElement("name",$newCurrencyArray[1]);
        $curren->appendChild($name);
    
        $loc = $doc->createElement("loc",$newCurrencyArray[3]);
        $curren->appendChild($loc);
    
        // Append it to the document itself
        $action->appendChild($curren);
    
        $doc->appendChild($action);
    
        header('Content-Type: text/xml');
        print $doc->saveXML();


           
                    }else{
                        $xml = simplexml_load_file("../rateV1.xml");

                        //echo $obj[0]->code." swaped for".$newCurrencyArray[0]."<br>";

                        $obj[0]->code = $newCurrencyArray[0];
                        $obj[0]->rate = $newCurrencyArray[4];
                        $obj[0]->currencyName = $newCurrencyArray[1];
                        $obj[0]->time = $newCurrencyArray[2];
                        $obj[0]->location = $newCurrencyArray[3];
                        
                        //echo $xml->asXml();
                        $xml->asXml($filename);

                        $doc = new DOMDocument('1.0', "UTF-8");

                        $action = $doc->createElement('action');
                    
                        $domAttribute = $doc->createAttribute('type');
                    
                        // Value for the created attribute
                        $domAttribute->value = "put";
                    
                        // Don't forget to append it to the element
                        $action->appendChild($domAttribute);
                    
                        $at = $doc->createElement("at",$newCurrencyArray[2]);
                        $action->appendChild($at);
                    
                        $newRate = $doc->createElement("rate",$newCurrencyArray[4]);
                        $action->appendChild($newRate);
                        
                        $curren = $doc->createElement('curr'); 
                    
                        $code = $doc->createElement("code",$newCurrencyArray[0]);
                        $curren->appendChild($code);
                        
                        $name = $doc->createElement("name",$newCurrencyArray[1]);
                        $curren->appendChild($name);
                    
                        $loc = $doc->createElement("loc",$newCurrencyArray[3]);
                        $curren->appendChild($loc);
                    
                        // Append it to the document itself
                        $action->appendChild($curren);
                    
                        $doc->appendChild($action);
                    
                        //echo $doc->saveXML();
                        header('Content-Type: text/xml');
                        print $doc->saveXML();

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

        $xml = simplexml_load_file("../rateV1.xml");
    

        $obj = $xml->xpath("//currency[code='" . $cur . "']");
        

        $obj[0]->rate = $newRate[1];
        $obj[0]->at = date('d F Y H:i',$date);
        //echo $xml->asXml();
        $xml->asXml($filename);
    

    $xml3=simplexml_load_file($filename);

    $response=$xml3->xpath("//currency[code='" . $cur . "']");

    $rTo= (string) $response[0]->time;
    $rCode= (string) $response[0]->code;
    $rCurr= (string) $response[0]->currencyName;
    $rloc= (string) $response[0]->location;
    $rRate= (string) $response[0]->rate;

    $doc = new DOMDocument('1.0', "UTF-8");

    $action = $doc->createElement('action');

    $domAttribute = $doc->createAttribute('type');

    // Value for the created attribute
    $domAttribute->value = "post";

    // Don't forget to append it to the element
    $action->appendChild($domAttribute);

    $at = $doc->createElement("at",date('d F Y H:i',$date));
    $action->appendChild($at);

    $newRate = $doc->createElement("rate",$newRate[1]);
    $action->appendChild($newRate);
    
    $oldRate = $doc->createElement("old_rate",$obj[0]->rate);
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


    }else{
        echo "Error 2000 Action not recognised or is missing";
        die();
    }


?>        