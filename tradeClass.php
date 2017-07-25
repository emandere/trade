<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of tradeClass
 *
 * @author user
 */
  abstract class Trade
 {
    protected $curr;
    protected $buyPrice;
    protected $sellPrice;
    protected $dollarAsk;
    protected $units;
    protected $buyTakeProfit;
    protected $sellTakeProfit;
    protected $buyStopLoss;
    protected $sellStopLoss;
    protected $expDate;
    protected $buyTicket;
    protected $sellTicket;
    protected $auth;
    protected $acct1;
    protected $acct2;
    protected $quotes;
    protected $getOpenPrice;
    protected $monDate;
    protected $mongo;
    protected $percent;
    protected $sendBuy;
    protected $sendSell;
    
    
    public function __construct($pair, $info)
    {
        $this->curr = $this->abbrevToPair($pair);
        $this->buyPrice = 0;
        $this->sellPrice = 0;
        $this->dollarAsk = 0;
        $this->units = 0;
        $this->buyTakeProfit = 0;
        $this->sellTakeProfit = 0;
        $this->buyStopLoss = 0;
        $this->sellStopLoss = 0;
        $this->expDate = 0;
        $this->buyTicket = 0;
        $this->sellTicket = 0;
        $this->auth = "Authorization: Bearer ".chop($info["token"]);
        $this->acct1 = chop($info["acct1"]);
        $this->acct2 = chop($info["acct2"]);
        $this->getOpenPrice = FALSE;
        $this->mongo = chop($info["mongo"]);   
        $this->percent = $info["percent"] * .01;
        $this->sendBuy = $info["buy"];
        $this->sendSell = $info["sell"];
        
        /*$cvars = [ "curr" => $this->curr,
                    "acct1" => $this->acct1,
                    "acct2" => $this->acct2,
                    "auth" => $this->auth,
                    "mongo" => $this->mongo,
                    "percent" => $this->percent];
         
        print_r($cvars);*/
    }
    
 
    public function getQuotes( $start, $end )
    {
      $return = [];
      $format = "midpoint";
      $url = "https://api-fxtrade.oanda.com/v1/candles?";
      $args = "";
      $args2 = "";
      date_default_timezone_set("America/New_York");      
      
      $return["status"] = TRUE;
      $return["message"] = "success";
      
      if( $start == 0 && $end == 0 ) 
      {
        $bars = 1;
        $gran = "W";
        
         $args = sprintf("instrument=%s&count=%d&candleFormat=%s&granularity=%s", 
                       $this->curr, $bars, $format, $gran);

    
         $this->monDate = new DateTime("now");
         
      }
      else
      {    
         $bars = 0;
         $gran = "H8";
         $align = 16;
      
         
         //print "$start $end in getQuotes()";
         $startDate = DateTime::createFromFormat('Y-m-d H:i', $start);
         
         $endDate = DateTime::createFromFormat('Y-m-d H:i', $end);
         $this->monDate = clone $endDate;
         
         if( $this->getOpenPrice )
         {
             $openDateStart = DateTime::createFromFormat('Y-m-d H:i', $end);
             $openDateStart->add(new DateInterval('PT1M'));
             
             $openDateEnd = DateTime::createFromFormat('Y-m-d H:i', $end);
             $openDateEnd->add(new DateInterval('PT2M'));
             
             $this->monDate = clone $openDateStart;
             //print $openDateStart->format('Y-m-d H:i'); 
             //print $openDateEnd->format('Y-m-d H:i'); 
             
             $bars2 = 0;
             $gran2 = "M1";
             
             $args2= sprintf("instrument=%s&candleFormat=%s&granularity=%s&start=%d&end=%d", 
                       $this->curr, $format, $gran2, $openDateStart->getTimestamp(),$openDateEnd->getTimestamp());
          
             
             
         }
         
         $args = sprintf("instrument=%s&candleFormat=%s&granularity=%s&dailyAlignment=%d"
                     . "&start=%d&end=%d", 
                       $this->curr, $format, $gran, $align, $startDate->getTimestamp(), 
                       $endDate->getTimestamp());
    
      }
       
       
        $ch = curl_init($url.$args);    
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-Accept-Datetime-Format: UNIX','Content-Type: application/json' , $this->auth ));    
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
              
        if( curl_error($ch) )
        {
           $return["status"] = FALSE;
           $return["message"] = "Curl error - date range quotes";
        }
        else
        {    
   
            $response = json_decode($result);
            //print $result;
            $this->quotes = [ "High" => 0,
                              "Low" => 0,
                              "Open" => 0];
          
           
            
           for( $i = 0; $i < count($response->candles); $i++ )
           {
               if( $i == 0 || $response->candles[$i]->highMid > $this->quotes['High'])
               {
                   $this->quotes['High'] = $response->candles[$i]->highMid;                
               }

               if( $i == 0 || $response->candles[$i]->lowMid < $this->quotes['Low'])
               {
                   $this->quotes['Low'] = $response->candles[$i]->lowMid;
               }
    
           }
           
            if( count($response->candles) == 0 )
            {
                $return["status"] = FALSE;
                $return["message"] = "no candles returned for date range";
            }
            
            if( $return["status"] && $args2 )
            {
                $ch = curl_init($url.$args2);  
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-Accept-Datetime-Format: UNIX','Content-Type: application/json' , $this->auth ));    
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $result = curl_exec($ch);
                
                if( curl_error($ch) )
                {
                   $return["status"] = FALSE;
                   $return["message"] = "Curl error - open price quote";
                }
                else
                {
                   $response = json_decode($result);
                   //var_dump($response);
                   $retTime = new DateTime();
                   
                   for( $i = 0; $i < count($response->candles); $i++ )
                   {
                        $retTime->setTimestamp($response->candles[$i]->time/1000000);
                        if( $retTime->getTimestamp() == $openDateStart->getTimestamp())
                        {
                            $this->quotes['Open'] = $response->candles[$i]->openMid;                
                        }
                   }
                   
                   if( count($response->candles) == 0 )
                   {
                    $return["status"] = FALSE;
                    $return["message"] = "no candles returned for open price";
                   }
                   
                }
            }   
           
           if( $return["status"] ) 
           {    
                $usd = strpos($this->curr, "USD");

                if( $usd === FALSE )
                {
                    $base = substr($this->curr , 4, 3);

                     switch($base)
                     {
                         case "AUD":
                           $this->setDollarAsk("AUD_USD");
                         break;
                         case "JPY":
                           $this->setDollarAsk("USD_JPY");
                         break;
                         case "GBP":
                           $this->setDollarAsk("GBP_USD");
                         break;
                     }

                     if( $this->dollarAsk == 0 )
                     {
                        $return["status"] = FALSE;
                        $return["message"] = "error getting dollar ask price";
                     }     
                 }
           }
        }           
   
    curl_close($ch);    
    return($return);
   }
   
    public function setDollarAsk( $currency )
    {
       $url = "https://api-fxtrade.oanda.com/v1/prices?instruments=".$currency;
       $ch = curl_init($url);    
         
       curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $this->auth ));    
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
       $result = curl_exec($ch);

        if( curl_error($ch) )
        {
          // print "error: ".curl_error($ch);    
        }
        else
        {    
            //print "setDollarAsk() response: $result";          
            $response= json_decode($result);
            $this->dollarAsk = $response->prices[0]->ask;
        }   
        
        curl_close($ch);             

    }  // end setDollarAsk()
   
    abstract public function setOrderValues( $profit );
  
    public function sendOrders( )
    {
        $dec = ( $this->buyPrice > 100 ? 2 : 4);       
        $buy_url = "https://api-fxtrade.oanda.com/v1/accounts/".$this->acct1."/orders";
        
        if( strlen($this->acct2) > 0 )
        {
            $sell_url = "https://api-fxtrade.oanda.com/v1/accounts/".$this->acct2."/orders";
        }
        else
        {
            $sell_url = $buy_url;
        }
        
        $return = [];
   
        $return["status"] = TRUE;
        $return["message"] = "success";
        
        
        if( $dec == 4 )
        {
          $bArgs = sprintf("instrument=%s&units=%d&side=buy&type=marketIfTouched&expiry=%d&price=%.4f"
                        . "&stopLoss=%.4f&takeProfit=%.4f", 
                          $this->curr, $this->units, $this->expDate->getTimestamp(), $this->buyPrice,
                          $this->buyStopLoss, $this->buyTakeProfit);        
        
          
          $sArgs = sprintf("instrument=%s&units=%d&side=sell&type=marketIfTouched&expiry=%d&price=%.4f"
                        . "&stopLoss=%.4f&takeProfit=%.4f", 
                          $this->curr, $this->units, $this->expDate->getTimestamp(), $this->sellPrice,
                          $this->sellStopLoss, $this->sellTakeProfit);        
          
        }
        if( $dec == 2 )
        {
          $bArgs = sprintf("instrument=%s&units=%d&side=buy&type=marketIfTouched&expiry=%d&price=%.2f"
                        . "&stopLoss=%.2f&takeProfit=%.2f", 
                          $this->curr, $this->units, $this->expDate->getTimestamp(), $this->buyPrice,
                          $this->buyStopLoss, $this->buyTakeProfit);        

          
          $sArgs = sprintf("instrument=%s&units=%d&side=sell&type=marketIfTouched&expiry=%d&price=%.2f"
                        . "&stopLoss=%.2f&takeProfit=%.2f", 
                          $this->curr, $this->units, $this->expDate->getTimestamp(), $this->sellPrice,
                          $this->sellStopLoss, $this->sellTakeProfit);        

          
        }

        
        if( $this->sendBuy )
        {
            $ch = curl_init($buy_url);     
            $date = "X-Accept-Datetime-Format: UNIX";


            curl_setopt($ch, CURLOPT_HTTPHEADER, array($date, $this->auth ));    
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch,CURLOPT_POST, true);

            curl_setopt($ch, CURLOPT_POSTFIELDS, $bArgs);
            $result = curl_exec($ch);

            if( !curl_error($ch) )
            {    
                //print "SendOrders() response: $result";          
                $response= json_decode($result);

                if( isset($response->code) )
                {
                    $return["status"] = FALSE;
                    $return["message"] = "error code for buy order ".$response->code;
                }
                else
                {
                    $this->buyTicket = $response->orderOpened->id;
                }
            }
            else
            {
                $return["status"] = FALSE;
                $return["message"] = "curl error on buy order send";
            }
        
            curl_close($ch);             
        }
        
        if( $return["status"] && $this->sendSell )
        {
            $ch = curl_init($sell_url);     
            $date = "X-Accept-Datetime-Format: UNIX";
        
            curl_setopt($ch, CURLOPT_HTTPHEADER, array($date, $this->auth ));    
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch,CURLOPT_POST, true);
                
            curl_setopt($ch, CURLOPT_POSTFIELDS, $sArgs);
            $result = curl_exec($ch);

            if( !curl_error($ch) )
            {
                $response = json_decode($result);
                if( isset($response->code) )
                {
                    $return["status"] = FALSE;
                    $return["message"] = "error code for sell order ".$response->code;
                }
                else
                {
                   $this->sellTicket = $response->orderOpened->id;
                   $return["message"] = "buy ticket = ".$this->buyTicket.", sell ticket = ".$this->sellTicket;
               }

            }
            else
            {
                $return["status"] = FALSE;
                $return["message"] = "curl error on sell order send";
            }
        
            curl_close($ch);             
        }
        
        return $return;
    }

    public function OrderExists()
    {
        $found = FALSE;
        $mongoConn = new MongoDB\Driver\Manager("mongodb://".$this->mongo);
        
        //$filter = [];
        $filter = [ 'pair' => "$this->curr",
                    'account' => "$this->acct", 
                    'units' => $this->units, 
                    'side' => "$this->side" ]; 
        
        print_r($filter);
        
        $options = [ 'projection' => [ '_id' => 0 ]];
        $mongoQ = new MongoDB\Driver\Query($filter, $options);
        $mongoCurs = $mongoConn->executeQuery('test.Trades', $mongoQ);
        //var_dump($mongoCurs->toArray());
        
        foreach($mongoCurs as $rec)
        {
           $this->pipRange = $rec->pips;
           $this->strategy = $rec->strategy;
           $this->monStartDate = $rec->mon_date;
  
           print"$this->curr $this->pipRange $this->strategy $this->monStartDate";
           //echo "<br";
           
           if( $this->pipRange > 0 &&
              ( strtoupper($this->strategy) == "SUPRES" || 
                strtoupper($this->strategy) == "RANGE" ))
             {
                $found = true;
             }
          }        
   
          return($found);
    }

    function abbrevToPair( $abbrev )
    {
        $return = "";

        switch ($abbrev) 
        {
            case "EA":
              $return = "EUR_AUD";
               break;
            case "EJ":
              $return = "EUR_JPY";
               break;
            case "GJ":
              $return = "GBP_JPY";
               break;
            case "GU":
               $return = "GBP_USD";
               break;
            case "NU":
               $return = "NZD_USD";
               break;
            case "UC":
               $return = "USD_CAD";
               break;
        }

    return($return);
  }
  
  abstract public function updateDB($auto);
   
 }

class TradeRange extends Trade {

    public function __construct($pair, $info)
    {
        parent::__construct($pair, $info);
        $this->getOpenPrice = TRUE;        
    }
    
    public function setOrderValues( $profit )
    {
        $dec = ($this->quotes['High'] > 100 ? 2 : 4);
        $dist = round( ($this->quotes['High'] - $this->quotes['Low'])/2, $dec);
        $this->buyPrice = round( $this->quotes['Open'] + $dist,$dec);
        $this->sellPrice = round( $this->quotes['Open'] - $dist, $dec);

        $risk = $profit;
            
        switch( $this->curr )
        {
            case "EUR_AUD":
                $this->units = round($risk/($dist * $this->dollarAsk));
               break;
           case "EUR_JPY":
           case "GBP_JPY":
                $this->units = round($risk/( $dist * (1/$this->dollarAsk)));
               break;
           case "GBP_USD":
           case "NZD_USD":
                $this->units = round($risk/$dist);
               break;
           case "USD_CAD":
                $this->units = round($risk/($dist * (1/($this->buyPrice + $dist))));
               break;
          }

         $this->units++;
         $this->buyStopLoss = $this->buyPrice - ($dist*2);                
         $this->buyTakeProfit = $this->buyPrice + $dist;
         $this->sellStopLoss = $this->sellPrice + ($dist*2);                
         $this->sellTakeProfit = $this->sellPrice-$dist;

         date_default_timezone_set("America/New_York");

         $this->expDate = new DateTime();            
         $this->expDate->add(new DateInterval('P7D'));
     
        //print $this->expDate->format('Y-m-d H:i'); 
        /*echo "<br>";
        print "buy/sell $this->units";    
        echo "<br>";
        print "buy $this->buyPrice tp $this->buyTakeProfit sl $this->buyStopLoss";
        echo "<br>";
        print " sell $this->sellPrice tp $this->sellTakeProfit sl $this->sellStopLoss";
        */
    }    
    
    public function updateDB($auto)
    {
        
        $dec = ($this->buyPrice > 100 ? 2 : 4);
        $dist = round( $this->quotes['High'] - $this->quotes['Low'], $dec);
        $mongoConn = new MongoDB\Driver\Manager("mongodb://".$this->mongo);
        $bu_bulk = new MongoDB\Driver\BulkWrite;
        $su_bulk = new MongoDB\Driver\BulkWrite;
        
        $update = array("units" =>  $this->units, 
                        "strategy" => "Range",
                        "pips" => $dist,
                        "mon_date" => $this->monDate->format('Y-m-d H:i'), 
                        "auto" => ( $auto ? "yes" : "no") );
       
        $buy_upd = array("account" =>$this->acct,
                         "pair" => $this->curr,             
                         "side" => "buy");
        
        $bu_bulk->update($buy_upd, [ '$set' => $update]);
        $result = $mongoConn->executeBulkWrite('test.Trades', $bu_bulk);
        
        if( $result->getModifiedCount() == 0 )
        {
            //print "buy rec not found ";
            
            $buy_ins = array("pair" => $this->curr, 
                        "account" =>$this->acct, 
                       "units" =>  $this->units, 
                       "side" => "buy",
                       "strategy" => "Range",
                       "pips" => $dist,
                       "mon_date" => $this->monDate->format('Y-m-d H:i'), 
                       "auto" => ( $auto ? "yes" : "no") );
                  
            
            $bi_bulk = new MongoDB\Driver\BulkWrite; 
            $bi_bulk->insert($buy_ins);
            $result = $mongoConn->executeBulkWrite('test.Trades', $bi_bulk);
        
        }
        

        $sell_upd = array("account" =>$this->acct,
                         "pair" => $this->curr,             
                         "side" => "sell");
         

        $su_bulk->update($sell_upd, [ '$set' => $update]);
        $result = $mongoConn->executeBulkWrite('test.Trades', $su_bulk);
        
        if( $result->getModifiedCount() == 0 )
        {
            //print "sell rec not found ";
            
            $sell_ins = array("pair" => $this->curr, 
                        "account" =>$this->acct, 
                       "units" =>  $this->units, 
                       "side" => "sell",
                       "strategy" => "Range",
                       "pips" => $dist,
                       "mon_date" => $this->monDate->format('Y-m-d H:i'), 
                       "auto" => ( $auto ? "yes" : "no") );
                  
            
            $si_bulk = new MongoDB\Driver\BulkWrite; 
            $si_bulk->insert($sell_ins);
            $result = $mongoConn->executeBulkWrite('test.Trades', $si_bulk);
        }
    }
}

class SupportResist extends Trade {
    
    public function setOrderValues( $profit )
    {
            $dec = ( $this->quotes['High'] > 100 ? 2 : 4);
            $this->buyPrice = round( $this->quotes['High'], $dec, PHP_ROUND_HALF_UP );
            $this->sellPrice = round( $this->quotes['Low'], $dec, PHP_ROUND_HALF_DOWN );
            $dist = ( $this->buyPrice - $this->sellPrice ) * $this->percent;
    
            switch( $this->curr )
            {
                case "EUR_AUD":
                    $this->units = round($profit/($dist * $this->dollarAsk));
                  break;
                case "EUR_JPY":
                case "GBP_JPY":
                    $this->units = round($profit/( $dist * (1/$this->dollarAsk)));
                  break;
                case "GBP_USD":
                case "NZD_USD":
                    $this->units = round($profit/$dist);
                  break;
                case "USD_CAD":
                    $this->units = round($profit/($dist * (1/($this->buyPrice + $dist))));
                  break;
            }

            $this->units++;
            $this->buyStopLoss = round( $this->buyPrice - $dist,  $dec, PHP_ROUND_HALF_DOWN);                
            $this->buyTakeProfit = round( $this->buyPrice + $dist, $dec, PHP_ROUND_HALF_UP);
            $this->sellStopLoss =  round( $this->sellPrice + $dist,  $dec, PHP_ROUND_HALF_UP);
            $this->sellTakeProfit = round( $this->sellPrice - $dist, $dec, PHP_ROUND_HALF_DOWN);
            date_default_timezone_set("America/New_York");
            $this->expDate = new DateTime();            
            $this->expDate->add(new DateInterval('P7D'));
     
        /*print $this->expDate->format('Y-m-d H:i'); 
        echo "<br>";
        print "buy/sell $this->units";    
        echo "<br>";
        print "buy $this->buyPrice tp $this->buyTakeProfit sl $this->buyStopLoss";
        echo "<br>";
        print " sell $this->sellPrice tp $this->sellTakeProfit sl $this->sellStopLoss";
          */         
    }

    
    public function updateDB($auto)
    {
        
        $dec = ($this->buyPrice > 100 ? 2 : 4);
        $dist = round( $this->buyPrice - $this->sellPrice, $dec);
        $retInfo = [];
        
         try 
        {
        
        $mongoConn = new MongoDB\Driver\Manager("mongodb://".$this->mongo);
        $bu_bulk = new MongoDB\Driver\BulkWrite;
        $su_bulk = new MongoDB\Driver\BulkWrite;
        
        $update = array("units" =>  $this->units, 
                        "strategy" => "SupRes",
                        "pips" => $dist,
                        "mon_date" => $this->monDate->format('Y-m-d H:i'), 
                        "auto" => ( $auto ? "yes" : "no") );
       
       
        $buy_upd = array("account" =>$this->acct,
                         "pair" => $this->curr,             
                         "side" => "buy");
        
        $bu_bulk->update($buy_upd, [ '$set' => $update]);
        $result = $mongoConn->executeBulkWrite('test.Trades', $bu_bulk);
        
        if( $result->getModifiedCount() == 0 )
        {
            //print "buy rec not found ";
            
            $buy_ins = array("pair" => $this->curr, 
                        "account" =>$this->acct, 
                       "units" =>  $this->units, 
                       "side" => "buy",
                       "strategy" => "SupRes",
                       "pips" => $dist,
                       "mon_date" => $this->monDate->format('Y-m-d H:i'), 
                       "auto" => ( $auto ? "yes" : "no") );
                   
            $bi_bulk = new MongoDB\Driver\BulkWrite; 
            $bi_bulk->insert($buy_ins);
            $result = $mongoConn->executeBulkWrite('test.Trades', $bi_bulk);
        
        }
        

        $sell_upd = array("account" =>$this->acct,
                         "pair" => $this->curr,             
                         "side" => "sell");
         

        $su_bulk->update($sell_upd, [ '$set' => $update]);
        $result = $mongoConn->executeBulkWrite('test.Trades', $su_bulk);
        
        if( $result->getModifiedCount() == 0 )
        {
            //print "sell rec not found ";
            
            $sell_ins = array("pair" => $this->curr, 
                        "account" =>$this->acct, 
                       "units" =>  $this->units, 
                       "side" => "sell",
                       "strategy" => "SupRes",
                       "pips" => $dist,
                       "mon_date" => $this->monDate->format('Y-m-d H:i'),
                       "auto" => ( $auto ? "yes" : "no") );
                  
            $si_bulk = new MongoDB\Driver\BulkWrite; 
            $si_bulk->insert($sell_ins);
            $result = $mongoConn->executeBulkWrite('test.Trades', $si_bulk);
        }
    }
    catch (Exception $e) 
    {
        $retInfo["status"] = FALSE;
        $retInfo["message"] = $e->getMessage();
    }

    
    
    }
}   
  
