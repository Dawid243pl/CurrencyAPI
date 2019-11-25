<?php
/* Config FILE

CW1 Restful Currency Convertor
!---------------------------------------!
Module Code: UFCFX3-15-3
Module Leader: Prakash Chatterjee
Date: 21/11/2019 
!---------------------------------------!


By Dawid Koleczko 17024154
*/

define("jQuery","https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js");

define("baseRate","GBP");

define("apiKey","http://data.fixer.io/api/latest?access_key=cbc73bcd8ffa149c344ba19ef687fa31");

define("countryF","country.xml");

define("ratesF","rateV1.xml");

define("defaultFormat","xml");


define("params", array(
    "from",
    "to",
    "amnt",
    "format"

));

define("startingCurrencies", array(
    "AUD",
    "BRL",
    "CAD",
    "CHF",
    "CNY",
    "DKK",
    "EUR",
    "GBP",
    "HKD",
    "HUF",
    "INR",
    "JPY",
    "MXN",
    "MYR",
    "NOK",
    "NZD",
    "PHP",
    "RUB",
    "SEK",
    "SGD",
    "THB",
    "TRY",
    "USD",
    "ZAR"
));










?>