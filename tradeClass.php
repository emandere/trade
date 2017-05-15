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
    protected $acct;
    protected $quotes;
    protected $getOpenPrice;
    protected $monDate;
    
    public function __construct($pair, $token, $acct)
    {
        $this->curr = $pair;
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
        $this->auth = "Authorization: Bearer ".chop($token);
        $this->acct = chop($acct);
        $this->getOpenPrice = FALSE;
    }
    
    public function setGetOpenPrice( $getIt )
    {
        $this->getOpenPrice = $getIt;
    }
    
    public function getQuotes( $start, $end )
    {
      $return = TRUE;
      $format = "midpoint";
      $url = "https://api-fxtrade.oanda.com/v1/candles?";
      $args = "";
      $args2 = "";
      date_default_timezone_set("America/New_York");      
      
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
         $startDate = DateTime::createFromFormat('Y-m-d H', $start);
         
         $endDate = DateTime::createFromFormat('Y-m-d H', $end);
         $this->monDate = clone $endDate;
         
         if( $this->getOpenPrice )
         {
             $openDateStart = DateTime::createFromFormat('Y-m-d H', $end);
             $openDateStart->add(new DateInterval('PT1M'));
             
             $openDateEnd = DateTime::createFromFormat('Y-m-d H', $end);
             $openDateEnd->add(new DateInterval('PT2M'));
             
             $this->monDate = clone $openDateStart;
             //print $openDateStart->format('Y-m-d H:i'); 
             //print $openDateEnd->format('Y-m-d H:i'); 
             
             $bars2 = 0;
             $gran2 = "M1";
             
             $args2= sprintf("instrument=%s&candleFormat=%s&granularity=%s&start=%d&end=%d", 
                       $this->curr, $format, $gran2, $openDateStart->getTimestamp(),$openDateEnd->getTimestamp());
          
             
             
         }
         
         //print $startDate->format('Y-m-d H:i'); 
         //print $endDate->format('Y-m-d H:i'); 
   //      echo "<br";
         
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
           print "error: ".curl_error($ch);    
           $return = FALSE;
           
        }
        else
        {    
   
            $response = json_decode($result);
           // print $result;
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
       
            if( $args2 )
            {
                $ch = curl_init($url.$args2);  
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-Accept-Datetime-Format: UNIX','Content-Type: application/json' , $this->auth ));    
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $result = curl_exec($ch);
                
                if( curl_error($ch) )
                {
                    print "error: ".curl_error($ch);    
                   $return = FALSE;
                }
                else
                {
                   $response = json_decode($result);
                   var_dump($response);
                   $retTime = new DateTime();
                   for( $i = 0; $i < count($response->candles); $i++ )
                   {
                        $retTime->setTimestamp($response->candles[$i]->time/1000000);
                        if( $retTime->getTimestamp() == $openDateStart->getTimestamp())
                        {
                            $this->quotes['Open'] = $response->candles[$i]->openMid;                
                            //print "setting open";
                            
                        }
                   }
                }
            }   
           
           print "Low = ".$this->quotes['Low'];
           print "High = ".$this->quotes['High'];
           print "Open = ".$this->quotes['Open'];
           
           if( $return ) 
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
                          $return = FALSE;
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
           print "error: ".curl_error($ch);    
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
        $url = "https://api-fxtrade.oanda.com/v1/accounts/".$this->acct."/orders";
        //print "sendORders url: $url";
        
        $return = FALSE;
        
        if( $dec == 4 )
        {
          $bArgs = sprintf("instrument=%s&units=%d&side=buy&type=marketIfTouched&expiry=%d&price=%.4f"
                        . "&stopLoss=%.4f&takeProfit=%.4f", 
                          $this->curr, $this->units, $this->expDate->getTimestamp(), $this->buyPrice,
                          $this->sellStopLoss, $this->buyTakeProfit);        
        
          
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
                          $this->buyStopLoss, $this->sellTakeProfit);        

          
        }

        
        $ch = curl_init($url);     
        $date = "X-Accept-Datetime-Format: UNIX";
        
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, array($date, $this->auth ));    
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch,CURLOPT_POST, true);
        
        curl_setopt($ch, CURLOPT_POSTFIELDS, $bArgs);
        $result = curl_exec($ch);
 
        if( !curl_error($ch) )
        {    
            print "SendOrders() response: $result";          
            $response= json_decode($result);
            $this->buyTicket = $response->orderOpened->id;
            echo "<br>";

            curl_setopt($ch, CURLOPT_POSTFIELDS, $sArgs);
            $result = curl_exec($ch);
           
            if( !curl_error($ch) )
            {
               $response = json_decode($result);
               print $result;
               $this->sellTicket = $response->orderOpened->id;
               $return = TRUE;
               
            }

        }   

        print "$this->curr tickets: buy $this->buyTicket   sell $this->sellTicket";         
        curl_close($ch);             
        return $return;
    }

    abstract public function updateDB();
   
 }

class TradeRange extends Trade {

    public function __construct($pair, $token, $acct)
    {
        parent::__construct($pair, $token, $acct);
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
    
    public function updateDB()
    {
        
        $dec = ($this->buyPrice > 100 ? 2 : 4);
        $dist = round( $this->quotes['High'] - $this->quotes['Low'], $dec);
        $mongoConn = new MongoDB\Driver\Manager("mongodb://localhost:27017");
        $bu_bulk = new MongoDB\Driver\BulkWrite;
        $su_bulk = new MongoDB\Driver\BulkWrite;
        
        $update = array("units" =>  $this->units, 
                        "strategy" => "Range",
                        "pips" => $dist,
                        "mon_date" => $this->monDate->format('Y-m-d H:i'));
       
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
                       "mon_date" => $this->monDate->format('Y-m-d H:i'));            
            
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
                       "mon_date" => $this->monDate->format('Y-m-d H:i'));            
            
            $si_bulk = new MongoDB\Driver\BulkWrite; 
            $si_bulk->insert($sell_ins);
            $result = $mongoConn->executeBulkWrite('test.Trades', $si_bulk);
        }
    }
}

class SupportResist extends Trade {
    
    public function setOrderValues( $profit )
    {
            $this->buyPrice = $this->quotes['High'];
            $this->sellPrice = $this->quotes['Low'];

            $dist = $this->buyPrice - $this->sellPrice;
            $risk = $profit * 2;
            $dec = ( $this->buyPrice > 100 ? 2 : 4);

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
            $this->buyStopLoss = round(($this->buyPrice + $this->sellPrice)/2, $dec);                
            $this->buyTakeProfit = round(($this->buyPrice+($dist/2)),$dec);
            $this->sellStopLoss = $this->buyStopLoss; 
            $this->sellTakeProfit = round(($this->sellPrice-($dist/2)),$dec);

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

    
    public function updateDB()
    {
        
        $dec = ($this->buyPrice > 100 ? 2 : 4);
        $dist = round( $this->buyPrice - $this->sellPrice, $dec);
        $mongoConn = new MongoDB\Driver\Manager("mongodb://localhost:27017");
        $bu_bulk = new MongoDB\Driver\BulkWrite;
        $su_bulk = new MongoDB\Driver\BulkWrite;
        
        $update = array("units" =>  $this->units, 
                        "strategy" => "SupRes",
                        "pips" => $dist,
                        "mon_date" => $this->monDate->format('Y-m-d H:i'));
       
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
                       "mon_date" => $this->monDate->format('Y-m-d H:i'));            
            
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
                       "mon_date" => $this->monDate->format('Y-m-d H:i'));            
            
            $si_bulk = new MongoDB\Driver\BulkWrite; 
            $si_bulk->insert($sell_ins);
            $result = $mongoConn->executeBulkWrite('test.Trades', $si_bulk);
        }
    }
}   
  