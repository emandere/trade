<?php

include 'oandaTO.php';

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
           
           switch ($_POST["Pair"]) 
            {
              case "EA":
              case "EJ":
              case "GJ":
              case "GU":
              case "NU":
              case "UC":
                $final[0] = UpdateTrades($_POST["Pair"], $info,$Oanda);
               break;
              case "ALL":
                $final[0] = UpdateTrades("EA", $info,$Oanda);
                $final[1] = UpdateTrades("EJ", $info,$Oanda);
                $final[2] = UpdateTrades("GJ", $info,$Oanda);
                $final[3] = UpdateTrades("GU", $info,$Oanda);
                $final[4] = UpdateTrades("NU", $info,$Oanda);
                $final[5] = UpdateTrades("UC", $info,$Oanda);
               break;
           }
       
        }
    }
    else
    {
        $temp["status"] =  "false"; 
        $temp["message"] = "ERROR: can't open infile";
        $final[0] = temp;
    }

    print json_encode($final);
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

function CheckTradeComplete( $pair, $Oanda )
{
    $complete = FALSE;
    
    //print "expected: ".$Oanda->getHistory($pair)->getExpected();
    //print " actual: ".$Oanda->getHistory($pair)->getActual();
    
    if( $Oanda->getOrders($pair)->getTradeTicket("sell") == 0 && 
        $Oanda->getOrders($pair)->getTradeTicket("buy") == 0   )
    {
        if( $Oanda->getHistory($pair)->getStatus() == "COMPLETE" )
        {
            $complete = TRUE;
        }
        else if( ( $Oanda->getHistory($pair)->getActual() *1.0) >= 
                ( $Oanda->getHistory($pair)->getExpected() * .75 ) )
        {
            $complete = TRUE;
            //print "trade is complete, update status";
            $Oanda->getHistory($pair)->setStatus( "COMPLETE");
            $Oanda->getHistory($pair)->updateHistory();
            
            
        }
    }
    
    return($complete);
    
}

function UpdateTrades( $pair, $info, $Oanda )
 {
    $result = [];
    $curr = abbrevToPair($pair);
    CheckTradeComplete( $curr, $Oanda );
    
    if( $Oanda->getHistory($curr)->getStatus() != "COMPLETE" )
    {
        $tradeProfit = 0;
        $bought = false;
        $sold = false;
        $input = [];
        
        if( $Oanda->getOrders($curr)->getTradeTicket("buy") > 0 )
        {
            $bought = true;
            $tradeProfit = $Oanda->getOrders($curr)->getExpProfit("buy");
        }
        
        if( $Oanda->getOrders($curr)->getTradeTicket("sell") > 0 )
        {
            $sold = true;
            $tradeProfit += $Oanda->getOrders($curr)->getExpProfit("sell");
        }    
        
        //$tradeProfit += $Oanda->getHistory($curr)->getActual();
        //$tradeProfit = $Oanda->getHistory($curr)->getExpected() - $tradeProfit;
        $tradeProfit += $Oanda->getHistory($curr)->getExpected() - $Oanda->getHistory($curr)->getActual();

        $input["profit"] = $tradeProfit;

        $input["buyTicket"] = $Oanda->getOrders($curr)->getOrderTicket("buy");
        $input["buyPrice"] = $Oanda->getOrders($curr)->getPrice("buy");
        $input["buyTakeProfit"] = $Oanda->getOrders($curr)->getTakeProfit("buy");
        $input["buyStopLoss"] = $Oanda->getOrders($curr)->getStopLoss("buy");
        
        $input["sellPrice"] = $Oanda->getOrders($curr)->getPrice("sell");
        $input["sellTakeProfit"] = $Oanda->getOrders($curr)->getTakeProfit("sell");
        $input["sellStopLoss"] = $Oanda->getOrders($curr)->getStopLoss("sell");
        $input["sellTicket"] = $Oanda->getOrders($curr)->getOrderTicket("sell");
        
        $input["sellAcct"] = $Oanda->getOrders($curr)->getAcct("sell");
        $input["buyAcct"] = $Oanda->getOrders($curr)->getAcct("buy");
        
        $input["buy"] = false;
        $input["sell"] = false;
        
        
        if( !$bought && $Oanda->getOrders($curr)->getExpProfit("buy") != $tradeProfit )
        {
            $input["buy"] = true;
        }
    
        if( !$sold && $Oanda->getOrders($curr)->getExpProfit("sell") != $tradeProfit )
        {
            $input["sell"] = true;
        }
        
        //print_r(array_merge( $info, $input ));
        if( $input["buy"] || $input["sell"] )
        {
            $mon = new MonitorTrade( $curr, array_merge( $info, $input ));
            $mon->setOrderValues();
            $reesult = $mon->sendOrders();
            if( $result["status"] )
            {
                $mon->insertOrders(true);
            }
        }
        
    }
    else
    {
        $result["status"] = "true";
        $result["message"] = "complete";
    }
  
    $result["future"] = " ";
    $result["pair"] = $pair;
    return($result);
 }

 /* get open trades for oanda, either for specific or all currencies.
     * populate in ? structure, then read appropriate .txt file and 
     * get pip amt/monitor start date.
     *  
     * /
     */
?>