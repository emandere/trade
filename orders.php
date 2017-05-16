 <?php

include 'tradeClass.php';

// phpinfo();     // put your code here
 processInput();
 
function processInput()
{
    $infile = "info.txt";
    $fh = fopen($infile, "r");
    
    if( $fh )
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") 
        {            
            $profit = $_POST["Profit"];
            $mongo = fgets($fh);
            $tok = fgets($fh);
            $primary = fgets($fh);
            $second = fgets($fh);            
            fclose($fh);
            
            $acct = ( $_POST["Acct"] == 'Primary' ? $primary : $second );
            
            $validDates = FALSE;
            if( ( isDateValid( $_POST["Start"] ) && 
                  isDateValid( $_POST["End"] ) ) || 
                 ( $_POST["Strat"] == 'SupRes' && 
                     chop($_POST["Start"]) == 0 &&
                     chop($_POST["End"]) == 0))                
            {
                    
                $validDates = TRUE;
            }

            
            if( $validDates && $mongo && $tok && $acct && $profit > 0 )
            {
                
                $info = array("profit" =>$profit, 
                              "token" => $tok, 
                              "acct" => $acct, 
                              "strat" => $_POST["Strat"],
                              "startDate" => $_POST["Start"], 
                              "endDate" => $_POST["End"],
                              "mongo" => $mongo);
                
                
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
            else
            {
              print "Check values for ";
              
              if( !$validDates )
              {
                  print "dates";
              } 
              
              if( !$tok || !$acct )
              {
                  print ", token and account# in infile";    
              }
              
              if( $profit <= 0 )
              {
                  print ", profit";
              }
                              
            }
            
        }        
    }
    else
    {
        print "error opening $infile";
    }
}

function isDateValid( $date )
{
    $valid = false;
   
    
    //print "$year $mon $day $hour";
    if( strlen( chop($_POST["Start"])) == 13 )
    {  
        $year = substr($date , 0, 4) * 1;
        $mon = substr($date , 5, 2) * 1;
        $day = substr($date , 8, 2) * 1;
        $hour = substr($date , 11, 2) * 1;
        if( $year >= 2017 && 
            ($mon > 0 && $mon < 13 ) && 
            ($day > 0 && $day < 32 ) &&
            ($hour >= 0 && $hour < 24 ) )
        {
            $valid = true;        
        }   
    }
    
   return $valid; 
}

function execute( $pair, $info )
 {
    $order = NULL;
    
    switch( $info['strat'])
    {
        case 'SupRes':
            $order = new SupportResist($pair, $info['token'], $info['acct'], $info['mongo']);
        break;
        case 'Range':
            $order = new TradeRange($pair, $info['token'], $info['acct'], $info['mongo']);    
            break;   
    }
    
    if( $order != NULL )
    {
        //phpinfo();
        if( $order->getQuotes($info['startDate'], $info['endDate']) == TRUE )
        {
            $order->setOrderValues($info['profit']);          
            $order->sendOrders();          
            $order->updateDB();
                    
            
        }
    } 
 }
 
   

?>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   