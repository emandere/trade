<?php

include 'oandaTO.php';
   
class TradeMonitor 
 {
    protected $curr;
    protected $quotes;
    protected $units;
    protected $side;
    protected $mongo;
    protected $auth;
    protected $acct;
    protected $newStopLoss;
    protected $newTakeProfit;
    protected $currStopLoss;
    protected $currTakeProfit;
    
    public function __construct($info)
    {
        $this->curr = $info['curr'];
        $this->auth = "Authorization: Bearer ".chop($info['token']);
        $this->acct = chop($info['acct']);
        $this->units = $info['units'];
        $this->side = $info['side'];
        $this->ticket = $info['ticket'];
        $this->currStopLoss = $info['stopLoss'];
        $this->currTakeProfit = $info['takeProfit'];
        $this->mongo = chop($info['mongo']);
        
    }
    
    public function getStrat()
    {
        return $this->strategy;
    }
    
    public function readDB()
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

    public function getQuotes()
    {
      $return = FALSE;
      $format = "midpoint";
      $url = "https://api-fxtrade.oanda.com/v1/candles?";
      $args = "";
      date_default_timezone_set("America/New_York");      
      
      
      $dateStart = DateTime::createFromFormat('Y-m-d H:i', $this->monStartDate);
      $dateEnd = new DateTime();
      
      /*print "starting from ";
      print $dateStart->format('Y-m-d H:i'); 
      print " and ending at ";
      print $dateEnd->format('Y-m-d H:i');  */
             
      $gran = "M15";
             
      $args= sprintf("instrument=%s&candleFormat=%s&granularity=%s&start=%d&end=%d", 
                     $this->curr, $format, $gran, $dateStart->getTimestamp(),$dateEnd->getTimestamp());
          
             
             
        $ch = curl_init($url.$args);    
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-Accept-Datetime-Format: UNIX','Content-Type: application/json' , $this->auth ));    
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        
        if( curl_error($ch) )
        {
           print "error: ".curl_error($ch);    
        }
        else
        {
            $response = json_decode($result);
            //print $result;
            
           $this->quotes = [ "High" => 0,
                             "Low" => 0];
            
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
           
           //print_r($this->quotes);           
           if( $this->quotes['High'] > 0 && $this->quotes['Low'] > 0 )
           {
                $return = TRUE; 
           }
           
        }
        
        return($return);
    }
    
    public function sendOrder()
    {
        $dec = ($this->quotes['High'] > 100 ? 2 : 4);

        if( $this->side == "buy")
        {
            $this->newTakeProfit = round($this->quotes['Low'] + $this->pipRange, $dec);
            $this->newStopLoss = round($this->quotes['High'] - $this->pipRange, $dec);
        }
        else if( $this->side == "sell")
        {
            $this->newTakeProfit = round($this->quotes['High'] - $this->pipRange, $dec);
            $this->newStopLoss = round($this->quotes['Low'] + $this->pipRange, $dec);
        }
        
        $args = NULL;
        
        $args = "stopLoss=".$this->currStopLoss."&takeProfit=".$this->newTakeProfit;;
        
        /*if( $this->newStopLoss != $this->currStopLoss ) 
        {
            $args = "stopLoss=".$this->newStopLoss;
        }
            
        if( $this->newTakeProfit != $this->currTakeProfit )
        {
            if( $args )
            {
                $args .= "&takeProfit=".$this->newTakeProfit;
            }
            else
            {
                $args .= "takeProfit=".$this->newTakeProfit;                
            }
            
        }*/
        
        if( $args )
        {
            
             $url = "https://api-fxtrade.oanda.com/v1/accounts/".$this->acct."/trades/".$this->ticket;
             $ch = curl_init($url);

             $date = "X-Accept-Datetime-Format: UNIX";
             $patch = "X-HTTP-Method-Override: PATCH";        
 
             curl_setopt($ch, CURLOPT_HTTPHEADER, array($date, $this->auth, $patch ));    
             curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
             curl_setopt($ch,CURLOPT_POST, true);
             curl_setopt($ch, CURLOPT_POSTFIELDS, $args);
             $result = curl_exec($ch);
             
             print $result;
        }
        else
        {
            print "no changes necessary";
            
        }
        
    }
        
 }
  
// print( "account is ");
 //print $_POST["Acct"];
  processInput();
  

  
function processInput()
{
    $infile = "info.txt";
    $fh = fopen($infile, "r");
    
    if( $fh )
    {
       $mongo = fgets($fh);
       $tok = fgets($fh);
       $primary = fgets($fh);
       $second = fgets($fh);            
       fclose($fh);
           
       
       if( $mongo && $tok && $primary && $second )
       {
           $info = array("token" => $tok, 
                         "acct1" => $primary,
                         "acct2" => $second,
                         "mongo" => $mongo );
           
          
           $Oanda = new oandaTO($info);
           
           //print_r( $Oanda->getTrades("GBP_USD") );
           //print_r( $Oanda->getTrades("NZD_USD") );
           
           
           //getOrders( $_POST["Pair"], $info);
           //getHistory( $_POST["Pair"], $info);
           switch ($_POST["Pair"]) 
            {
              case "EA":
                $Oanda->getHistory("EUR_AUD"); 
                //UpdateTrades("EUR_AUD", $info);
               break;
              case "EJ":
                $Oanda->getHistory("EUR_JPY"); 
                //UpdateTrades("EUR_JPY", $info);
               break;
              case "GJ":
                 $Oanda->getHistory("GBP_JPY"); 
                 //UpdateTrades("GBP_JPY", $info);
               break;
              case "GU":
                  $Oanda->getHistory("GBP_USD");
                 //UpdateTrades("GBP_USD", $info);
               break;
              case "NU":
                  $Oanda->getHistory("NZD_USD");
                 //UpdateTrades("NZD_USD", $info);
               break;
              case "UC":
                  $Oanda->getHistory("USD_CAD");
                //UpdateTrades("USD_CAD", $info);
               break;
              case "ALL":
                //UpdateTrades("ALL", $info);
               break;
           }
       }
    }
    else
    {
        print "error opening $infile";
    }

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

/*function UpdateTrades( $pair, $info )
 {
        $url = "https://api-fxtrade.oanda.com/v1/accounts/".chop($info['acct'])."/orders";
        $auth = "Authorization: Bearer ".chop($info['token']);
        $args = NULL;
        
        if( $pair != "ALL")
        {
            $args = sprintf("?instrument=%s", $pair); 
        }
        
        $ch = ( $args ? curl_init($url.$args) : curl_init($url) );
                    
        curl_setopt($ch, CURLOPT_HTTPHEADER, array($auth));    
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $result = curl_exec($ch);
        
        if( curl_error($ch) )
        {
            print curl_error($ch);
        }
        else
        {
         $response = json_decode($result);
         var_dump(json_decode($result));
        
          for( $i = 0; $i < count($response->trades); $i++ )
           {
               $monInfo = array("mongo" => $info['mongo'],
                                "token" => $info['token'], 
                                "acct" => $info['acct'], 
                                "curr" => $response->trades[$i]->instrument,
                                "units" => $response->trades[$i]->units, 
                                "side" => $response->trades[$i]->side, 
                                "ticket" => $response->trades[$i]->id, 
                                "stopLoss" => $response->trades[$i]->stopLoss, 
                                "takeProfit" => $response->trades[$i]->takeProfit);
                
               //print_r($monInfo);
               
               $monitor = new TradeMonitor($monInfo);             
               if( $monitor->readDB() )
               {
                   if( strtoupper($monitor->getStrat()) == 'RANGE' && $monitor->getQuotes() )
                   {
                        $monitor->sendOrder();
                   }
                   else if( strtoupper($monitor->getStrat()) == "SUPRES")
                   {
                       $monitor->sendOrder();
                   }
                   
               }
           } 
        }
    
    
 }*/

function getOrders( $pair, $info )
 {
        $url = "https://api-fxtrade.oanda.com/v1/accounts/".chop($info['acct'])."/orders";
        $auth = "Authorization: Bearer ".chop($info['token']);
        $args = NULL;
        $orders = [];
        
        if( $pair != "ALL")
        {
            $args = sprintf("?instrument=%s", abbrevToPair($pair) ); 
        }
        
        $ch = ( $args ? curl_init($url.$args) : curl_init($url) );
                    
        curl_setopt($ch, CURLOPT_HTTPHEADER, array($auth));    
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $result = curl_exec($ch);
        
        if( curl_error($ch) )
        {
            print curl_error($ch);
        }
        else
        {
         $response = json_decode($result);
         //var_dump(json_decode($result));
         
         
          for( $i = 0; $i < count($response->orders); $i++ )
           {
               $orderInfo = array("curr" => $response->orders[$i]->instrument,
                                "units" => $response->orders[$i]->units, 
                                "side" => $response->orders[$i]->side, 
                                "ticket" => $response->orders[$i]->id, 
                                "stopLoss" => $response->orders[$i]->stopLoss, 
                                "takeProfit" => $response->orders[$i]->takeProfit);
               
              
               $orders[$i] = $orderInfo;
           }
        }
    
        print_r($orders);
 }

 function getHistory( $pair, $info )
 {
     
        $url = "https://api-fxtrade.oanda.com/v1/accounts/".chop($info['acct'])."/transactions";
        $auth = "Authorization: Bearer ".chop($info['token']);
        $args = NULL;
        $orders = [];
        
        if( $pair != "ALL")
        {
            $args = sprintf("?instrument=%s", abbrevToPair($pair) ); 
        }
        
        $ch = ( $args ? curl_init($url.$args) : curl_init($url) );
                    
        curl_setopt($ch, CURLOPT_HTTPHEADER, array($auth));    
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $result = curl_exec($ch);
        
        if( curl_error($ch) )
        {
            print curl_error($ch);
        }
        else
        {
         $response = json_decode($result);
         //var_dump( $result );
         $startDate = DateTime::createFromFormat('Y-m-d H:i', "2017-07-24 08:00");
         $realPL = 0;
         
         for( $i = 0; $i < count($response->transactions); $i++ )
           {
              
              if( $response->transactions[$i]->type == "TAKE_PROFIT_FILLED" || 
                  $response->transactions[$i]->type == "STOP_LOSS_FILLED" || 
                  $response->transactions[$i]->type == "TRADE_CLOSE" )
              {
                  if( strrpos( $response->transactions[$i]->time, "T") == 10 ) 
                  {        
                        $sDate = sprintf( "%s %s", substr($response->transactions[$i]->time , 0, 10), 
                                             substr($response->transactions[$i]->time, 11, 5 ) );

                        $tempDate = DateTime::createFromFormat('Y-m-d H:i', $sDate);
                        if( $tempDate >= $startDate )
                        {
                             $realPL += $response->transactions[$i]->pl;
                        }

                    }
              
                
               }    
                  
           }
           print " pl = $realPL";
           
        }
    
 }
 /* get open trades for oanda, either for specific or all currencies.
     * populate in ? structure, then read appropriate .txt file and 
     * get pip amt/monitor start date.
     *  
     * /
     */
?>