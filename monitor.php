<?php

 class TradeMonitor
 {
    protected $curr;
    protected $quotes;
    protected $pipRange;
    protected $strategy;
    protected $monStartDate;
    protected $ticket;
    protected $units;
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
        
        
    }
    
    public function getTrades()
    {
        
    }
 
    
  }
 
  processInput();
  
function processInput()
{
    $infile = "info.txt";
    $fh = fopen($infile, "r");
    
    if( $fh )
    {
       $tok = fgets($fh);
       $primary = fgets($fh);
       $second = fgets($fh);            
       fclose($fh);
           
       $acct = ( $_POST["Acct"] == 'Primary' ? $primary : $second );

       if( $tok && $acct )
       {
           $info = array("token" => $tok, 
                         "acct" => $acct);
                
           switch ($_POST["Pair"]) 
            {
              case "EA":
                 UpdateTrades("EUR_AUD", $info);
               break;
              case "EJ":
                 UpdateTrades("EUR_JPY", $info);
               break;
              case "GJ":
                 UpdateTrades("GBP_JPY", $info);
               break;
              case "GU":
                 UpdateTrades("GBP_USD", $info);
               break;
              case "NU":
                 UpdateTrades("NZD_USD", $info);
               break;
              case "UC":
                UpdateTrades("USD_CAD", $info);
               break;
              case "ALL":
                UpdateTrades("ALL", $info);
               break;
           }
       }
    }
    else
    {
        print "error opening $infile";
    }

}

function UpdateTrades( $pair, $info )
 {
        $url = "https://api-fxtrade.oanda.com/v1/accounts/".chop($info['acct'])."/trades";
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
        //var_dump(json_decode($result));
        
           for( $i = 0; $i < count($response->trades); $i++ )
           {
               $monInfo = array("token" => $info['token'], 
                                "acct" => $info['acct'], 
                                "curr" => $response->trades[$i]->instrument,
                                "units" => $response->trades[$i]->units, 
                                "side" => $response->trades[$i]->side, 
                                "ticket" => $response->trades[$i]->id, 
                                "stopLoss" => $response->trades[$i]->stopLoss, 
                                "takeProfit" => $response->trades[$i]->takeProfit);
                
               print_r($monInfo);
               $monitor = new TradeMonitor($monInfo);
                
           } 
        }
    
    
 }

    /* get open trades for oanda, either for specific or all currencies.
     * populate in ? structure, then read appropriate .txt file and 
     * get pip amt/monitor start date.
     *  
     * /
     */
?>