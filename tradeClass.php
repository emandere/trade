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
class OrdersTable 
{  
    private $curr;
    private $units;
   
    private $buyAcct;
    private $buyPrice;
    private $buyStopLoss;
    private $buyTakeProfit;
    private $buyOrder;
    private $buyTrade;
    private $buyProfit;
    
    
    private $sellAcct;
    private $sellPrice;
    private $sellStopLoss;
    private $sellTakeProfit;
    private $sellOrder;
    private $sellTrade;
    private $sellProfit;
    
    private $mongo;
    private $found;
    
    public function __construct($pair, $info)
    {
        $this->curr = $pair;
        $this->mongo = chop($info["mongo"]);
       
        $this->buyAcct = 0;
        $this->buyPrice = 0;
        $this->buyStopLoss = 0;
        $this->buyTakeProfit = 0;
        $this->buyOrder = 0;
        $this->buyTrade = 0;
        $this->buyProfit = 0;
        $this->buyUnits= 0;
   
        $this->sellAcct = 0;
        $this->sellPrice = 0;
        $this->sellStopLoss = 0;
        $this->sellTakeProfit = 0;
        $this->sellOrder = 0;
        $this->sellTrade = 0;
        $this->sellProfit = 0;
        $this->sellUnits= 0;
   
        $this->found = false;
      
        $this->readOrders();
    }
    
    public function readOrders()
    {
        $return["status"] = TRUE;
        $return["message"] = "success";
        
        try
        {  
            $filter = [ 'pair' => "$this->curr"]; 
        
            $mongoConn = new MongoDB\Driver\Manager("mongodb://".$this->mongo);
            
                $options = [ 'projection' => [ '_id' => 0 ]];
                $mongoQ = new MongoDB\Driver\Query($filter, $options);
                $mongoCurs = $mongoConn->executeQuery('test.Orders', $mongoQ);
                //var_dump($mongoCurs->toArray());
        
                foreach($mongoCurs as $rec)
                {
                    $this->buyUnits = $rec->buy_units;
                    $this->sellUnits = $rec->sell_units;
   
                    $this->buyAcct = $rec->buy_acct;
                    $this->buyPrice = $rec->buy_price;
                    $this->buyStopLoss = $rec->buy_sl;
                    $this->buyTakeProfit = $rec->buy_tp;
                    $this->buyProfit = $rec->buy_profit;

                    $this->sellAcct = $rec->sell_acct;
                    $this->sellPrice = $rec->sell_price;
                    $this->sellStopLoss = $rec->sell_sl;
                    $this->sellTakeProfit = $rec->sell_tp;
                    $this->sellProfit = $rec->sell_profit;

                    $this->found = true;
                }
               
        }
        catch (Exception $e) 
        {
            $return["status"] = FALSE;
            $return["message"] = $e->getMessage();
            print $e->getMessage();
            
        }
      
        return($return);
    }
    
    public function getUnits($side)
    {
        return( $side == "buy" ? $this->buyUnits : $this->sellUnits );
    }
    
    public function getAcct($side)
    {
        return( $side == "buy" ? $this->buyAcct : $this->sellAcct );
    }

    public function getPrice($side)
    {
        return( $side == "buy" ? $this->buyPrice : $this->sellPrice );
    }
    
    public function getStopLoss($side)
    {
        return( $side == "buy" ? $this->buyStopLoss : $this->sellStopLoss );
    }

    public function getTakeProfit($side)
    {
        return( $side == "buy" ? $this->buyTakeProfit : $this->sellTakeProfit );
    }
    
    public function getExpProfit($side)
    {
        return( $side == "buy" ? $this->buyProfit : $this->sellProfit );
    }
    
    public function isFound()
    {
        return($this->found);
    }
    
    public function setOrderTicket($side, $value)
    {
        if( $side == "buy" )
        {
            $this->buyOrder = $value;
        }
        else
        {
            $this->sellOrder = $value;
        }
    }
    
    public function getOrderTicket($side)
    {
        return( $side == "buy" ? $this->buyOrder : $this->sellOrder );
    }

    
    public function setTradeTicket($side, $value)
    {
        if( $side == "buy" )
        {
            $this->buyTrade = $value;
        }
        else
        {
            $this->sellTrade = $value;
        }
    }

    public function getTradeTicket($side)
    {
        return( $side == "buy" ? $this->buyTrade : $this->sellTrade );
    }

}

class HistoryTable 
{  
    private $curr;
    private $startDate;
    private $expectedPL;
    private $actualPL;
    private $status;
    private $mongo;
    private $found;
    
    public function __construct($pair, $info)
    {
        $this->curr = $pair;
        $this->mongo = chop($info["mongo"]);
        $this->found = false;
      
        $this->readHistory();
    }
    
    public function readHistory()
    {
        $return["status"] = TRUE;
        $return["message"] = "success";
        
        try
        {  
            $filter = [ 'pair' => "$this->curr"]; 
        
            $mongoConn = new MongoDB\Driver\Manager("mongodb://".$this->mongo);
            
                $options = [ 'projection' => [ '_id' => 0 ]];
                $mongoQ = new MongoDB\Driver\Query($filter, $options);
                $mongoCurs = $mongoConn->executeQuery('test.History', $mongoQ);
                //var_dump($mongoCurs->toArray());
        
                foreach($mongoCurs as $rec)
                {
                    $this->startDate = $rec->start_date;
                    $this->expectedPL = $rec->expected_pl;
                    $this->actualPL = $rec->actual_pl;
                    $this->status = $rec->status;
                    $this->found = true;
                }
               
        }
        catch (Exception $e) 
        {
            $return["status"] = FALSE;
            $return["message"] = $e->getMessage();
            print $e->getMessage();
            
        }
      
        return($return);
    }
    
    public function getStatus()
    {
        return( strtoupper($this->status));
    }

    public function isFound()
    {
        return($this->found);
    }
    
    public function setStatus( $value )
    {
        $this->status = strtoupper($value);
    }

    public function getStartDate()
    {
        return($this->startDate);
    }

    public function setStartDate( $value )
    {
        $this->startDate = $value;
    }

    public function getExpected()
    {
        return($this->expectedPL);
    }
    
    public function setExpected( $value )
    {
        $this->expectedPL = $value;
    }
    
    public function getActual()
    {
        return($this->actualPL);
    }
    
    public function setActual( $value )
    {
       $this->actualPL = $value;
    }
   
     public function updateHistory()
    {
        $return["status"] = TRUE;
        $return["message"] = "success";

        try
        {
            $mongoConn = new MongoDB\Driver\Manager("mongodb://".$this->mongo);
            $bulk = new MongoDB\Driver\BulkWrite;

           $filter = [ 'pair' => "$this->curr" ];
           
           $update = array( "pair" =>  $this->curr,
                            "expected_pl" =>  $this->expectedPL,
                            "actual_pl" => $this->actualPL,
                            "start_date" => $this->startDate,
                            "status" => $this->status );
/*                      $update = array( "pair" =>  $this->curr,
                            "expected_pl" =>  $this->expectedPL,
                            "actual_pl" => $this->,
                            "start_date" => $this->monDate->format('Y-m-d H:i'),
                            "status" => "active");*/

            try
            {
                $bulk->update($filter, [ '$set' => $update], ['multi' => true, 'upsert' => true] );
                $result = $mongoConn->executeBulkWrite('test.History', $bulk);

                //var_dump( $result);
                if( $result->getModifiedCount() == 0 && $result->getUpsertedCount() == 0 )
                {       
                    $return["status"] = FALSE;
                    $return["message"] = "update history failed";
                }
            }
            catch (Exception $e) 
            {
            //$retInfo["status"] = FALSE;
            print $e->getMessage();
            }
        } 
        catch (Exception $e) 
        {
            //$retInfo["status"] = FALSE;
            print $e->getMessage();
        }
    }

}

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
    protected $oanda;
    protected $percent;
    protected $sendBuy;
    protected $sendSell;
    protected $profit;
    protected $oldProfit;
    
    protected $history;
    
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
        $this->oanda = chop($info["oanda"]);
        $this->percent = $info["percent"] * .01;
        $this->sendBuy = $info["buy"];
        $this->sendSell = $info["sell"];
        $this->profit = $info["profit"];
        $this->oldProfit = 0;
        $this->history = NULL;
        
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
      $priceFormat = "M";
      //$url = "https://api-fxtrade.oanda.com/v1/candles?";
      //$url = $this->oanda."/v1/candles?";
      $url = $this->oanda."/v3/instruments/".$this->curr."/candles?";
      
      $args = "";
      $args2 = "";
      date_default_timezone_set("America/New_York");      
      
      $return["status"] = TRUE;
      $return["message"] = "success";
      
      if( $start == 0 && $end == 0 ) 
      {
        $bars = 1;
        $gran = "W";
        
         $args = sprintf("count=%d&price=%s&granularity=%s", 
                       $bars, $priceFormat, $gran);

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
             
             $args2= sprintf("instrument=%s&candleFormat=%s&granularity=%s&from=%d&to=%d", 
                       $this->curr, $format, $gran2, $openDateStart->getTimestamp(),$openDateEnd->getTimestamp());
         }
         
         $args = sprintf("price=%s&granularity=%s&dailyAlignment=%d"
                     . "&from=%d&to=%d", 
                       $priceFormat, $gran, $align, $startDate->getTimestamp(), 
                       $endDate->getTimestamp());
    
         
         }
       
       
        $ch = curl_init($url.$args);    
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-Accept-Datetime-Format: UNIX','Content-Type: application/json' , $this->auth ));    
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        $response = json_decode($result);
        print "$url.$args";
        var_dump($response);    
        if( curl_error($ch) )
        {
           $return["status"] = FALSE;
           $return["message"] = "Curl error - date range quotes";
        }
        else
        {    
   
            $response = json_decode($result);
            //var_dump($response);
            $this->quotes = [ "High" => 0,
                              "Low" => 0,
                              "Open" => 0];
          
            
           for( $i = 0; $i < count($response->candles); $i++ )
           {
               if( $i == 0 || floatval($response->candles[$i]->mid->h) > $this->quotes['High'])
               {
                   $this->quotes['High'] = floatval($response->candles[$i]->mid->h);                
               }

               if( $i == 0 || floatval($response->candles[$i]->mid->l) < $this->quotes['Low'])
               {
                   $this->quotes['Low'] = floatval($response->candles[$i]->mid->l);
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
               /*print "high = ".$this->quotes['High'];
               echo "<br>";
               print "low = ".$this->quotes['Low'];
               echo "<br>";*/

               
               $return = $this->setDollarAsk();
           }
        }           
   
    curl_close($ch);    
    return($return);
   }
   
    public function setDollarAsk( )
    {
        $return["status"] = TRUE;
        $return["message"] = "success";

        $usd = strpos($this->curr, "USD"); 
        $currency = "";
            
        if( $usd === FALSE )
        {
            $base = substr($this->curr , 4, 3);
            
            switch($base)
            {
                case "AUD":
                    $currency = "AUD_USD";
                   break;
                case "JPY":
                    $currency = "USD_JPY";
                   break;
                case "GBP":
                    $currency = "GBP_USD";
                   break;
           }
        }
        
        if( strlen($currency) > 0 )
        {
            
            $url = $this->oanda."/v3/accounts/".$this->acct1."/pricing?instruments=".$currency;
            $ch = curl_init($url);    
            
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $this->auth ));    
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);

             if( curl_error($ch) )
             {
                $return["status"] = FALSE;
                $return["message"] = "error getting dollar ask price";
             }
             else
             {    
                 //print "setDollarAsk() response: $result";          
                 $response= json_decode($result);
                 //var_dump($response);
           
                 $this->dollarAsk = $response->prices[0]->asks[0]->price;
                 print $this->dollarAsk;
             }   

             curl_close($ch);             
        }
        
        return($return);
    }  // end setDollarAsk
   
    public function sendOrders( )
    {
        $dec = ( $this->buyPrice > 100 ? 2 : 4);       
        $return["status"] = TRUE;
        $return["message"] = "SUCCESS";

        $buy_url = $this->oanda."/v3/accounts/".$this->acct1."/orders";
        
        if( strlen($this->acct2) > 0 )
        {
            $sell_url = $this->oanda."/v3/accounts/".$this->acct2."/orders";
        }
        else
        {
            $sell_url = $buy_url;
        }
        
        
        $buy["type"] = $sell["type"] = "MARKET_IF_TOUCHED"; 
        
        $buy["instrument"] = $sell["instrument"] = $this->curr;
        $buy["timeInForce"] = $sell["timeInForce"] = "GTD";
        $buy["units"] = $this->units;
        $sell["units"] = ( $this->units * -1);
        
        $buy["gtdTime"] = $sell["gtdTime"] = sprintf("%d", $this->expDate->getTimestamp());
            
        if( $dec == 4 )
        {
            $buy["price"] = sprintf("%.4f", $this->buyPrice);
               
            $sell["price"] = sprintf("%.4f", $this->sellPrice);
            
            $buy["takeProfitOnFill"] = [ "price" => sprintf("%.4f", $this->buyTakeProfit),
                                          "timeInForce" => "GTC" ];
           
            $sell["takeProfitOnFill"] = [ "price" => sprintf("%.4f", $this->sellTakeProfit),
                                          "timeInForce" => "GTC" ];
            
            
            $buy["stopLossOnFill"] = [ "price" => sprintf("%.4f", $this->buyStopLoss),
                                       "timeInForce" => "GTC" ];
            
            $sell["stopLossOnFill"] = [ "price" => sprintf("%.4f", $this->sellStopLoss),
                                       "timeInForce" => "GTC" ];
            
            
            
            /*$mBuy = sprintf("units=%d&price=%.4f&stopLoss=%.4f&takeProfit=%.4f", 
                             $this->units, $this->buyPrice, $this->buyStopLoss, $this->buyTakeProfit);        
        
            $cBuy = sprintf("instrument=%s&type=MARKET_IF_TOUCHED&timeInForce=GTD&gtdTime=%d", 
                          $this->curr, $this->expDate->getTimestamp());       
            
            $mSell = sprintf("units=%d&price=%.4f&stopLoss=%.4f&takeProfit=%.4f", 
                             $this->units, $this->sellPrice, $this->sellStopLoss, $this->sellTakeProfit);        
        
            $cSell = sprintf("instrument=%s&side=sell&type=marketIfTouched&expiry=%d", 
                          $this->curr, $this->expDate->getTimestamp());       
            */
        }
        else if( $dec == 2 )
        {
            $buy["price"] = sprintf("%.2f", $this->buyPrice);
               
            $sell["price"] = sprintf("%.2f", $this->sellPrice);
            
            $buy["takeProfitOnFill"] = [ "price" => sprintf("%.2f", $this->buyTakeProfit),
                                          "timeInForce" => "GTC" ];
           
            $sell["takeProfitOnFill"] = [ "price" => sprintf("%.2f", $this->sellTakeProfit),
                                          "timeInForce" => "GTC" ];
            
            
            $buy["stopLossOnFill"] = [ "price" => sprintf("%.2f", $this->buyStopLoss),
                                       "timeInForce" => "GTC" ];
            
            $sell["stopLossOnFill"] = [ "price" => sprintf("%.2f", $this->sellStopLoss),
                                       "timeInForce" => "GTC" ];
            
        }

        /*if( $this->buyTicket > 0 )
        {
           $buy_url = $buy_url."/".$this->buyTicket;
           $bArgs = $mBuy;
        }
        else
        {
            $bArgs = $mBuy."&".$cBuy;
        }
        
        /*if( $this->sellTicket > 0 )
        {
           $sell_url = $sell_url."/".$this->sellTicket;
           $sArgs = $mSell;
        }
        else
        {
            $sArgs = $mSell."&".$cSell;
        //}*/
        
        //print "$buy_url";
        //echo "<br>";
        //print $sell_url;
        //var_dump(json_encode($buy));
        //echo "<br>";
        
        if( $this->sendBuy )
        {
            $ch = curl_init($buy_url);     
            $date = "X-Accept-Datetime-Format: UNIX";
            $patch = "X-HTTP-Method-Override: PATCH";        
       
           /* if( $this->buyTicket > 0 )
            { 
                curl_setopt($ch, CURLOPT_HTTPHEADER, array($date, $this->auth, $patch ));    
            }*/
            
            $order["order"] = $buy;
            curl_setopt($ch, CURLOPT_HTTPHEADER, array($date, $this->auth, 'Content-Type: application/json'));    

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch,CURLOPT_POST, true);

            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($order));
            $result = curl_exec($ch);

            if( !curl_error($ch) )
            {    
                $response= json_decode($result);
                //var_dump($response);
                
                if( isset($response->errorMessage) )
                {
                    $return["status"] = FALSE;
                    $return["message"] = "error code for buy order ".$response->errorMessage;
                }
                else if( $this->buyTicket == 0 )
                {
                    $this->buyTicket = $response->relatedTransactionIDs[0];
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
            $patch = "X-HTTP-Method-Override: PATCH";        
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-Accept-Datetime-Format: UNIX','Content-Type: application/json' , $this->auth ));    
            
            /*if( $this->sellTicket > 0 )
            { 
                curl_setopt($ch, CURLOPT_HTTPHEADER, array($date, $this->auth, $patch ));    
            }*/
            
            $order["order"] = $sell;
            curl_setopt($ch, CURLOPT_HTTPHEADER, array($date, $this->auth, 'Content-Type: application/json'));    

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch,CURLOPT_POST, true);

            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($order));
            $result = curl_exec($ch);
            

            if( !curl_error($ch) )
            {
                $response = json_decode($result);
                //var_dump($response);
                
                if( isset($response->errorMessage) )
                {
                    $return["status"] = FALSE;
                    $return["message"] = "error code for buy order ".$response->errorMessage;
                }
                else if( $this->sellTicket == 0 )
                {
                    $this->sellTicket = $response->relatedTransactionIDs[0];
                }
            }
            else
            {
                $return["status"] = FALSE;
                $return["message"] = "curl error on sell order send";
            }
        
            curl_close($ch);             
        }
        
        if( $return["status"] )
        {  
            $return["message"] = "buy ticket = ".$this->buyTicket.", sell ticket = ".$this->sellTicket;
        }
        
        return $return;
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
           case "EU":
               $return = "EUR_USD";
               break;
           case "AU":
               $return = "AUD_USD";
               break;
           case "AJ":
               $return = "AUD_JPY";
               break;
           case "UF":
               $return = "USD_CHF";
               break;

        }

    return($return);
  }

    public function TransactionComplete()
    {
        if( $this->history != NULL )
        {
            if( !$this->history->isFound() ||   
                $this->history->getStatus() == "complete" )
            {
                $return["status"] = TRUE;
                $return["message"] = "complete";
            }
            else
            {
                $return["message"] = "trades still active";
                $return["status"] = false;
            }
        }
        else
        {
            $return["message"] = "tradeClass history not set";
            $return["status"] = false;
        }
        
        return($return);
    }

    public function SetTransactionHistory()
    {
        if( $this->history != NULL )
        {
            $this->history->setExpected( $this->profit );
            $this->history->setActual("0");
            $this->history->setStartDate($this->monDate->format('Y-m-d H:i'));
            $this->history->setStatus("active");
            $this->history->updateHistory();
        }
    }
    
   abstract public function setOrderValues( );
 
   abstract public function insertOrders($auto);
   
 }


class SupportResist extends Trade {
    
    public function __construct($pair, $info)
    {
        parent::__construct($pair, $info);
        $this->history = new HistoryTable($this->curr, $info );
    }
       
    public function setOrderValues( )
    {
            $dec = ( $this->quotes['High'] > 10 ? 2 : 4);
            $this->buyPrice = round( $this->quotes['High'], $dec, PHP_ROUND_HALF_UP );
            $this->sellPrice = round( $this->quotes['Low'], $dec, PHP_ROUND_HALF_DOWN );
            $dist = ( $this->buyPrice - $this->sellPrice ) * $this->percent;
    
            switch( $this->curr )
            {
                case "EUR_AUD":
                    $this->units = round($this->profit/($dist * $this->dollarAsk));
                  break;
                case "EUR_JPY":
                case "GBP_JPY":
                case "AUD_JPY":
                    $this->units = round($this->profit/( $dist * (1/$this->dollarAsk)));
                  break;
                case "GBP_USD":
                case "NZD_USD":
                case "EUR_USD":
                case "AUD_USD":
                    $this->units = round($this->profit/$dist);
                  break;
                case "USD_CAD":
                case "USD_CHF":
                    $this->units = round($this->profit/($dist * (1/($this->buyPrice + $dist))));
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

    
    public function insertOrders($auto)
    {
        
        $dec = ($this->buyPrice > 10 ? 2 : 4);
        $dist = round( $this->buyPrice - $this->sellPrice, $dec);
        $retInfo = [];
        
         try 
        {
        
        $mongoConn = new MongoDB\Driver\Manager("mongodb://".$this->mongo);
        $del = new MongoDB\Driver\BulkWrite;
        //$su_bulk = new MongoDB\Driver\BulkWrite;
        
        $criteria = [ "pair" =>  $this->curr ]; 

        $del->delete($criteria, [ "limit" => false ] );
        $result = $mongoConn->executeBulkWrite('test.Orders', $del);
        
            
        $insert = array("pair" => $this->curr, 
                         "buy_acct" => $this->acct1, 
                         "sell_acct" => ( strlen($this->acct2) > 0 ? $this->acct2 : $this->acct1 ) ,
                          "buy_units" =>  $this->units, 
                          "sell_units" =>  $this->units, 
                          "buy_price" =>  $this->buyPrice,
                          "sell_price" =>  $this->sellPrice,
                          "buy_sl" =>  $this->buyStopLoss,
                          "buy_tp" =>  $this->buyTakeProfit,
                          "sell_sl" =>  $this->sellStopLoss,
                          "sell_tp" =>  $this->sellTakeProfit,
                          "buy_profit" =>  $this->profit , 
                          "sell_profit" =>  $this->profit);
                   
            $ins = new MongoDB\Driver\BulkWrite; 
            $ins->insert($insert);
            $result = $mongoConn->executeBulkWrite('test.Orders', $ins);
        
    }
    catch (Exception $e) 
    {
        $retInfo["status"] = FALSE;
        $retInfo["message"] = $e->getMessage();
    }
   }
}

class MonitorTrade extends Trade {
    
    public function __construct($pair, $info)
    {
        $this->curr = $pair;
        $this->buyPrice = $info["buyPrice"];
        $this->sellPrice = $info["sellPrice"];
        $this->dollarAsk = 0;
        $this->units = 0;
        $this->buyTakeProfit = $info["buyTakeProfit"];
        $this->sellTakeProfit = $info["sellTakeProfit"];
        $this->buyStopLoss = $info["buyStopLoss"];
        $this->sellStopLoss = $info["sellStopLoss"];
        $this->expDate = 0;
        $this->buyTicket = $info["buyTicket"];
        $this->sellTicket = $info["sellTicket"];
        $this->auth = "Authorization: Bearer ".chop($info["token"]);
        $this->acct1 = chop($info["buyAcct"]);
        $this->acct2 = ( $info["buyAcct"] != $info["sellAcct"] ? $info["sellAcct"] : ""); 
        $this->getOpenPrice = FALSE;
        $this->mongo = chop($info["mongo"]);   
        $this->percent = 0;
        $this->sendBuy = $info["buy"];
        $this->sendSell = $info["sell"];
        $this->profit = $info["profit"];
       // $this->oldProfit = $info["old_profit"];
        
        $this->history = NULL;
    }

    public function setOrderValues( )
    {
            $return = $this->setDollarAsk();
            
            if( $return["status"] == TRUE )
            {    
                $dec = ( $this->buyPrice > 10 ? 2 : 4);
                $dist = ( $this->buyPrice - $this->buyStopLoss );

                switch( $this->curr )
                {
                    case "EUR_AUD":
                        $this->units = round($this->profit/($dist * $this->dollarAsk));
                      break;
                    case "EUR_JPY":
                    case "GBP_JPY":
                        $this->units = round($this->profit/( $dist * (1/$this->dollarAsk)));
                      break;
                    case "GBP_USD":
                    case "NZD_USD":
                        $this->units = round($this->profit/$dist);
                      break;
                    case "USD_CAD":
                        $this->units = round($this->profit/($dist * (1/($this->buyPrice + $dist))));
                      break;
                }

                $this->units++;
                date_default_timezone_set("America/New_York");
                $this->expDate = new DateTime();            
                $this->expDate->add(new DateInterval('P7D'));
            }
         }

    
    public function insertOrders($auto)
    {
        
        $dec = ($this->buyPrice > 10 ? 2 : 4);
        $dist = round( $this->buyPrice - $this->sellPrice, $dec);
        $retInfo = [];
        
         try 
        {
        
        $mongoConn = new MongoDB\Driver\Manager("mongodb://".$this->mongo);
        $ins = new MongoDB\Driver\BulkWrite; 
            
        $criteria = [ "pair" =>  $this->curr ]; 
           
        $buy_update = array("pair" => $this->curr, 
                            "buy_acct" => $this->acct1, 
                            "buy_units" =>  $this->units, 
                            "buy_price" =>  $this->buyPrice,
                            "buy_sl" =>  $this->buyStopLoss,
                            "buy_tp" =>  $this->buyTakeProfit,
                            "buy_profit" =>  $this->profit);
        
        $sell_update = array("pair" => $this->curr, 
                            "sell_acct" => ( strlen($this->acct2) > 0 ? $this->acct2 : $this->acct1 ) ,
                            "sell_units" =>  $this->units, 
                            "sell_price" =>  $this->sellPrice,
                            "sell_sl" =>  $this->sellStopLoss,
                            "sell_tp" =>  $this->sellTakeProfit,
                            "sell_profit" =>  $this->profit);
        
        $update = [];
        
        if( $this->sendBuy && $this->sendSell )
        {
            $update = array_merge( $buy_update, $sell_update );
        }
        else if( $this->sendBuy )
        {
            $update = $buy_update;
        }
        else
        {
            $update = $sell_update;
        }
        
        $ins->update($criteria, [ '$set' => $update], ['multi' => true, 'upsert' => true]);
        $result = $mongoConn->executeBulkWrite('test.Orders', $ins);
        
    }
    catch (Exception $e) 
    {
        $retInfo["status"] = FALSE;
        $retInfo["message"] = $e->getMessage();
    }
   }
}

class TradeRange extends Trade {

    public function __construct($pair, $info)
    {
        parent::__construct($pair, $info);
        $this->getOpenPrice = TRUE;        
    }
    
    public function setOrderValues( )
    {
        $dec = ($this->quotes['High'] > 10 ? 2 : 4);
        $dist = round( ($this->quotes['High'] - $this->quotes['Low'])/2, $dec);
        $this->buyPrice = round( $this->quotes['Open'] + $dist,$dec);
        $this->sellPrice = round( $this->quotes['Open'] - $dist, $dec);

        $risk = $this->profit;
            
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
    
    public function insertOrders($auto)
    {
        
        $dec = ($this->buyPrice > 10 ? 2 : 4);
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

  
