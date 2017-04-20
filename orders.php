     <?php

  class Trade
 {
    private $curr;
    private $buyPrice;
    private $sellPrice;
    private $dollarAsk;
    private $units;
    private $buyTakeProfit;
    private $sellTakeProfit;
    private $stopLoss;
    private $expDate;
    private $buyTicket;
    private $sellTicket;
    private $auth;
    private $acct;
    
    public function __construct($pair, $token, $acct)
    {
        $this->curr = $pair;
        $this->buyPrice = 0;
        $this->sellPrice = 0;
        $this->dollarAsk = 0;
        $this->units = 0;
        $this->buyTakeProfit = 0;
        $this->sellTakeProfit = 0;
        $this->stopLoss = 0;
        $this->expDate = 0;
        $this->buyTicket = 0;
        $this->sellTicket = 0;
        $this->auth = "Authorization: Bearer ".chop($token);
        $this->acct = chop($acct);
        
    }
    
    public function setCurr( $currPair )
    {
//        settype($curr, "string"); // $foo is now 5   (integer)
        $this->curr = $currPair;
//        print "setCurr() pair is $this->curr\n";
    }

    public function setPrices( )
    {
       $bars = 1;
       $format = "midpoint";
       $gran = "W";
       $url = "https://api-fxtrade.oanda.com/v1/candles?";
       $return = TRUE;
       
       $args = sprintf("instrument=%s&count=%d&candleFormat=%s&granularity=%s", 
                       $this->curr, $bars, $format, $gran);


        $ch = curl_init($url.$args);    
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $this->auth ));    
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        
        if( curl_error($ch) )
        {
           print "error: ".curl_error($ch);    
        }
        else
        {    
           
           $response= json_decode($result);
           //print "setPrices() response: $result";
                    
           $this->buyPrice = $response->candles[0]->highMid;
           $this->sellPrice = $response->candles[0]->lowMid;
           settype($this->buyPrice, "double");
           settype($this->sellPrice, "double");  
      
           $usd = strpos($this->curr, "USD");
           
           if( $this->buyPrice == 0 || $this->sellPrice == 0 )
           {
              $return = FALSE;      
           }
           
           if( $return == TRUE && $usd === FALSE )
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
            
                $return = ( $this->dollarAsk <= 0 ? FALSE : $return );

            }
        }    
           
        curl_close($ch);
        return($return);
    }  // end setPrices( )

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

    
    public function setOrderValues( $profit )
    {
        
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
        $this->stopLoss = round(($this->buyPrice + $this->sellPrice)/2, $dec);                
        $this->buyTakeProfit = round(($this->buyPrice+($dist/2)),$dec);
        $this->sellTakeProfit = round(($this->sellPrice-($dist/2)),$dec);
  
        date_default_timezone_set("America/New_York");
        
        $this->expDate = new DateTime();
        //print "orig timestamp = ".$this->expDate->getTimestamp();

        $this->expDate->add(new DateInterval('P7D'));
      
        //print "new timestamp = ".$this->expDate->getTimestamp();

       /* print $this->expDate->format('Y-m-d H:i'); 
        echo "<br>";
        print "buy/sell $this->units";    
        echo "<br>";
        print "buy $this->buyPrice tp $this->buyTakeProfit";
        print "sell $this->sellPrice tp $this->sellTakeProfit";
        print "stop loss $this->stopLoss";
        */
        
     }
 
    public function sendOrders( )
    {
        $dec = ( $this->buyPrice > 100 ? 2 : 4);       
        $url = "https://api-fxtrade.oanda.com/v1/accounts/".$this->acct."/orders";
        //print "sendORders url: $url";
        
        if( $dec == 4 )
        {
          $bArgs = sprintf("instrument=%s&units=%d&side=buy&type=marketIfTouched&expiry=%d&price=%.4f"
                        . "&stopLoss=%.4f&takeProfit=%.4f", 
                          $this->curr, $this->units, $this->expDate->getTimestamp(), $this->buyPrice,
                          $this->stopLoss, $this->buyTakeProfit);        
        
          
          $sArgs = sprintf("instrument=%s&units=%d&side=sell&type=marketIfTouched&expiry=%d&price=%.4f"
                        . "&stopLoss=%.4f&takeProfit=%.4f", 
                          $this->curr, $this->units, $this->expDate->getTimestamp(), $this->sellPrice,
                          $this->stopLoss, $this->sellTakeProfit);        
          
        }
        if( $dec == 2 )
        {
          $bArgs = sprintf("instrument=%s&units=%d&side=buy&type=marketIfTouched&expiry=%d&price=%.2f"
                        . "&stopLoss=%.2f&takeProfit=%.2f", 
                          $this->curr, $this->units, $this->expDate->getTimestamp(), $this->buyPrice,
                          $this->stopLoss, $this->buyTakeProfit);        

          
          $sArgs = sprintf("instrument=%s&units=%d&side=sell&type=marketIfTouched&expiry=%d&price=%.2f"
                        . "&stopLoss=%.2f&takeProfit=%.2f", 
                          $this->curr, $this->units, $this->expDate->getTimestamp(), $this->sellPrice,
                          $this->stopLoss, $this->sellTakeProfit);        

          
        }

        
        $ch = curl_init($url);     
        $date = "X-Accept-Datetime-Format: UNIX";
         print "auth: $this->auth";
       // print "bargs: $bArgs   sArgs: $sArgs";
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
            }

        }   

        print "$this->curr tickets: buy $this->buyTicket   sell $this->sellTicket";         
        curl_close($ch);             

    }
    
}
  // phpinfo();     // put your code here
 processInput();
 
function processInput()
{
    $infile = "C:\Users\user\Documents\my php files\info.txt";
    $fh = fopen($infile, "r");
    
    if( $fh )
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") 
        {
            $profit = $_POST["Profit"];
            $tok = fgets($fh);
            $acct = fgets($fh);
            fclose($fh);

            if( $tok && $acct && $profit > 0 )
            {
         //       print( "token = $tok acct = $acct");
                
                $info = array("profit" =>$profit, 
                              "token" => $tok, 
                              "acct" => $acct);
                
                switch ($_POST["Pair"]) 
                {
                  case "EA":
                     execute("EUR_AUD", $info);
                    break;
                  case "EJ":
                     execute("EUR_JPY", $info);
                    break;
                  case "GJ":
                     execute("GBP_JPY", $info);
                   break;
                  case "GU":
                    execute("GBP_USD", $info);
                   break;
                  case "NU":
                    execute("NZD_USD", $info);
                   break;
                  case "UC":
                    execute("USD_CAD", $info);
                   break;
                  case "ALL":
                    execute("EUR_AUD", $info);
                    execute("EUR_JPY", $info);
                    execute("GBP_JPY", $info);
                    execute("GBP_USD", $info);
                    execute("NZD_USD", $info);
                    execute("USD_CAD", $info);
                   break;
               }

            }

        }
    }
    else
    {
        print "error opening $infile";
    }
}

function execute( $pair, $info )
 {
      
    $order = new Trade($pair, $info['token'], $info['acct']);
     
    if( $order->setPrices() )
    {
        $order->setOrderValues($info['profit']);          
        $order->sendOrders();          
    }
     
 }
 
   
?>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   