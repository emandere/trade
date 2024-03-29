<?php

include 'tradeClass.php';

 processInput();
 
function getInfileInfo()
{
    $infile = "secrets/info.txt";
    $fh = fopen($infile, "r");
    $info = [];
    
    if( $fh )
    {
         $oanda = fgets($fh);  
         $mongo = fgets($fh);
         $tok = fgets($fh);
         $primary = fgets($fh);
         $second = fgets($fh);            
         
         if( $mongo && $tok && $primary && $second && $oanda )
         {
            $info = [ "status" => TRUE,
                      "oanda" => chop($oanda),
                      "mongo" => chop($mongo),
                      "token" => chop($tok), 
                      "primary" => chop($primary),
                      "second" => chop($second) ];
         }
         else
         {
             $info["status"] = FALSE;
             $info["message"] = "Check values for oanda url, mongo, token, accounts in infile";
         }
    }
    else
    {
        $info["status"] = FALSE;
        $info["message"] = "error opening $infile";
    }

    return($info);
}


 function readDB( $fileInfo, $pair )
 {
    $found = FALSE;
    $dbInfo = [];
    $retInfo = [];
    
  try 
  {
    
    $mongoConn = new MongoDB\Driver\Manager("mongodb://".$fileInfo["mongo"]);
    $find = ['pair' => abbrevToPair($pair) ];
    $options = [ 'projection' => [ '_id' => 0 ]];
    $mongoQ = new MongoDB\Driver\Query($find, $options);
   
    try 
    {
            $mongoCurs = $mongoConn->executeQuery('test.Parms', $mongoQ);
            //var_dump($mongoCurs->toArray());
            foreach($mongoCurs as $rec)
            {
                $dbInfo["profit_1"] = $rec->profit_1;
                $dbInfo["risk_1"] = $rec->risk_1;
                $dbInfo["p_move_1"] = $rec->p_move_1;
                $dbInfo["start_d_1"] = $rec->start_day_1;
                $dbInfo["end_d_1"] = $rec->end_day_1;
                $dbInfo["start_t_1"] = $rec->start_time_1;
                $dbInfo["end_t_1"] = $rec->end_time_1;
                $dbInfo["strat_1"] = $rec->strat_1;
                $dbInfo["acct_1"] = $rec->acct_1;
                $dbInfo["enable_1"] = $rec->enable_1;
                $dbInfo["profit_2"] = $rec->profit_2;
                $dbInfo["risk_2"] = $rec->risk_2;
                $dbInfo["p_move_2"] = $rec->p_move_2;
                $dbInfo["start_d_2"] = $rec->start_day_2;
                $dbInfo["end_d_2"] = $rec->end_day_2;
                $dbInfo["start_t_2"] = $rec->start_time_2;
                $dbInfo["end_t_2"] = $rec->end_time_2;
                $dbInfo["strat_2"] = $rec->strat_2;
                $dbInfo["acct_2"] = $rec->acct_2;
                $dbInfo["enable_2"] = $rec->enable_2;

                
                
                $found = TRUE;
                $retInfo = setDefaults($dbInfo);
                
                if( $retInfo["status"]  )
                {
                    if($retInfo["acct"] == 'Split' )
                    {
                        $retInfo["acct1"] = $fileInfo["primary"];
                        $retInfo["acct2"] = $fileInfo["second"];

                    }
                    else
                    {
                        $retInfo["acct1"] = ( $retInfo["acct"] == "Primary" ?  $fileInfo["primary"] : $fileInfo["second"]); 
                        $retInfo["acct2"] = " ";
                    }
                }
        
            }
    }
    catch (Exception $e) 
    {
        $retInfo["status"] = FALSE;
        $retInfo["message"] = $e->getMessage();
    }
  }
  catch (Exception $e) 
  {
        $retInfo["status"] = FALSE;
        $retInfo["message"] = $e->getMessage();
  }

  
    if( !$found && !array_key_exists( "message", $retInfo ))
    {
        $retInfo["status"] = FALSE;
        $retInfo["message"] = "error reading from test.parms";
    }
    
    return($retInfo);
    
}

function setDefaults( $dbInfo )
{
     date_default_timezone_set("America/New_York");
     $dateInfo = getdate();
     $wday = $dateInfo["wday"] + 1;
     $hours = $dateInfo["hours"];
     $min = $dateInfo["minutes"];
     $returnInfo = [];
     $useDates = [];
     
     $use1Data = ( $dbInfo["enable_1"] == 'Y' && $dbInfo["start_d_1"] > 0 ? true : false );
     $use2Data = ( $dbInfo["enable_2"] == 'Y' && $dbInfo["start_d_2"] > 0 ? true : false );
     $returnInfo["status"] = TRUE;

     
     $st_1_h = strtok( $dbInfo["start_t_1"] , ":") ;
     $st_1_m = strtok(":") ;

     $et_1_h = strtok( $dbInfo["end_t_1"] , ":") ;
     $et_1_m = strtok(":") ;

     $st_2_h = strtok( $dbInfo["start_t_2"] , ":") ;
     $st_2_m = strtok(":") ;
        
     $et_2_h = strtok( $dbInfo["end_t_2"] , ":") ;
     $et_2_m = strtok(":") ;
     
     
     if( !$use1Data && !$use2Data )
     {
         $returnInfo["status"] = FALSE;
         $returnInfo["message"] = "No trade parms enabled";
         $returnInfo["future"] = FALSE;
     }
     else if( !$use2Data || $wday < $dbInfo["end_d_2"]  || 
        ( $wday == $dbInfo["end_d_2"] && 
            ( ( $hours < $et_2_h) || ($hours == $et_2_h && $min < $et_2_m ) ) ) ) 
   
         {
         if( $use1Data && ( $wday > $dbInfo["end_d_1"]  || 
            ( $wday == $dbInfo["end_d_1"] && 
            ( ( $hours > $et_1_h) || ($hours == $et_1_h && $min >= $et_1_m ) ) ) ) )
         {
             $useDates = [ "e_diff" => $wday - $dbInfo["end_d_1"], 
                          "e_time" => $dbInfo["end_t_1"],
                          "s_diff" => $wday - $dbInfo["start_d_1"], 
                          "s_time" => $dbInfo["start_t_1"] ];
             
             if( $use2Data )
             {
                 $useDates["f_diff"] = $dbInfo["end_d_2"] - $wday;
                 $useDates["f_time"] = $dbInfo["end_t_2"];
             }
             else
             {
                 $useDates["f_diff"] = (7-$wday) + $dbInfo["end_d_1"];
                 $useDates["f_time"] = $dbInfo["end_t_1"];
             }
                          
             $returnInfo["profit"] = $dbInfo["profit_1"];
             $returnInfo["risk"] = $dbInfo["risk_1"];            
             $returnInfo["strat"] = $dbInfo["strat_1"];
             $returnInfo["acct"] = $dbInfo["acct_1"];
             $returnInfo["percent"] = $dbInfo["p_move_1"];  
         }
         else if( $use1Data )
         {
            $returnInfo["status"] = FALSE;
            $useDates["f_diff"] = $dbInfo["end_d_1"] - $wday;;
            $useDates["f_time"] = $dbInfo["end_t_1"];
         }
         else if( $use2Data )
         {
            $returnInfo["status"] = FALSE;
            $useDates["f_diff"] = $dbInfo["end_d_2"] - $wday;;
            $useDates["f_time"] = $dbInfo["end_t_2"];
         }

            
     }
     else if( $use2Data && ($wday > $dbInfo["end_d_2"]  || 
            ( $wday == $dbInfo["end_d_2"] && 
            ( ( $hours > $et_2_h) || ($hours == $et_2_h && $min >= $et_2_m ) ) ) ) )
    {
             $useDates = [ "e_diff" => $wday - $dbInfo["end_d_2"], 
                           "e_time" => $dbInfo["end_t_2"],
                           "s_diff" => $wday - $dbInfo["start_d_2"], 
                           "s_time" => $dbInfo["start_t_2"] ];
             
             if( $use1Data )
             {
                $useDates["f_diff"] = (7-$wday) + $dbInfo["end_d_1"];
                $useDates["f_time"] = $dbInfo["end_t_1"];             
             }
             else
             {
                 $useDates["f_diff"] = (7-$wday) + $dbInfo["end_d_2"];
                 $useDates["f_time"] = $dbInfo["end_t_2"];
             }

             $returnInfo["profit"] = $dbInfo["profit_2"];
             $returnInfo["risk"] = $dbInfo["risk_2"];            
             $returnInfo["strat"] = $dbInfo["strat_2"];
             $returnInfo["acct"] = $dbInfo["acct_2"];
             $returnInfo["percent"] = $dbInfo["p_move_2"];
             
    }
    /*else if( $use2Data )
    {
       $returnInfo["status"] = FALSE;
       $useDates["f_diff"] = $dbInfo["end_d_2"] - $wday;;
       $useDates["f_time"] = $dbInfo["end_t_2"];
        print "last if";
    }*/
            
    
    if( sizeof($useDates) > 0 )
    {
        $sDate = new DateTime("now");
        $eDate = new DateTime("now");
        $fDate = new DateTime("now");
        $errDate = new DateTime("now");
        
        if( $returnInfo["status"] )
        {
            $interval = sprintf( "P%dD", $useDates["s_diff"]);
            $sDate->sub(new DateInterval($interval));

            $interval = sprintf( "P%dD", $useDates["e_diff"]);
            $eDate->sub(new DateInterval($interval));

            $errDate->add(new DateInterval('PT1M'));

            $returnInfo["start"] = sprintf("%s %s", $sDate->format('Y-m-d'), $useDates["s_time"]); 
            $returnInfo["end"] = sprintf("%s %s", $eDate->format('Y-m-d'), $useDates["e_time"]);
            $returnInfo["error"] = $errDate->format('Y-m-d h:i');
        }
        
        $interval = sprintf( "P%dD", $useDates["f_diff"]);
        $fDate->add(new DateInterval($interval));
        $returnInfo["future"] = sprintf("%s %s", $fDate->format('Y-m-d'), $useDates["f_time"]);
        
    }
 
    
    /*$returnInfo["profit"] = $dbInfo["profit_1"];
    $returnInfo["strat"] = $dbInfo["strat_1"];
    $returnInfo["acct"] = $dbInfo["acct_1"];
    $returnInfo["percent"] = $dbInfo["move_1"];  
    $returnInfo["start"] = "2017-07-16 16:00"; 
    $returnInfo["end"] = "2017-07-17 00:00";
    */   
    
    return( $returnInfo );
     
}

function getInput( $fileInfo, $pair )
{
    $info = [];
    
    
    if( array_key_exists( "auto", $_POST ) )
    {
       $info = readDB($fileInfo, $pair);    
       //var_dump($info);
       
       if( $info["status"] || $info["future"] )
       {
            $info["pair"] = $pair;
            $info["auto"] = TRUE;
       }
    }
    else
    {
      $info["status"] = TRUE;
      $info["message"] = "success";
      $info["profit"] = $_POST["profit"];
      $info["risk"] = $_POST["risk"];    
      $info["strat"] = $_POST["strat"]; 
      $info["start"] = $_POST["start"];
      $info["end"] = $_POST["end"];  
      $info["pair"] = $pair; 
      $info["future"] = " "; 
      $info["error"] = " ";
      $info["auto"] = FALSE;
      $info["percent"] = $_POST["percent"];                       
      
      if($_POST["acct"] == 'Split' )
      {
          $info["acct1"] = $fileInfo["primary"];
          $info["acct2"] = $fileInfo["second"];
          
      }
      else
      {
         $info["acct1"] = ( $_POST["acct"] == "Primary" ?  $fileInfo["primary"] : $fileInfo["second"]); 
         $info["acct2"] = "";
         
      }
      
    }
    
    if( $info["status"] )
    {
       $info["buy"] = ( array_key_exists( "buy", $_POST ) ? true : false );
       $info["sell"] = ( array_key_exists( "sell", $_POST ) ? true : false );
    }
    
    //print_r($info);
    return($info);
 }

function processInput()
{
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") 
    {            
        $infile = getInfileInfo();

        if ($infile["status"] )
        {
            switch ($_POST["pair"]) 
            {
                case "EA":
                case "EJ":
                case "GJ":
                case "GU":
                case "NU":
                case "UC":
                case "EU":
                case "AU":
                case "AJ":
                case "UF":
                    $final[0] = execute($_POST["pair"], $infile);
                    break;
                case "ALL":
                default:
                    /* $final[0] =  [ "status" => "true", 
                      "message" => "success",
                      "pair" => "ALL",
                      "future" => $input["future"] ];
                     */
                    $final[0] = execute("EA", $infile);
                    $final[1] = execute("EJ", $infile);
                    $final[2] = execute("GJ", $infile);
                    $final[3] = execute("GU", $infile);
                    $final[4] = execute("NU", $infile);
                    $final[5] = execute("UC", $infile);
                    $final[6] = execute("EU", $infile);
                    $final[7] = execute("AU", $infile);
                    $final[8] = execute("AJ", $infile);
                    $final[9] = execute("UF", $infile);
                    break;
            }
        }
        else
        {
            $final[0] = $infile;
        }
            
        print json_encode($final);
       
   }         
}

 function execute( $pair, $infile )
 {
    $order = NULL;
    $result = [];
    
    $result["status"] =  true;
    
    $input = getInput($infile, $pair);
    
    if( $input["status"] )
    {
        $validDates = FALSE;
        $args = array_merge( $infile, $input );
                    
        if( ( isDateValid( $input["start"] ) && isDateValid( $input["end"] ) ) || 
              ( $input["strat"] == 'SupRes' && $input["start"] == '0' && $input["end"] == '0' ) )                
        {
            $validDates = TRUE;
        }
                    
                 
        if( $validDates && $args["profit"] > 0 && $args["risk"] > 0 && $args["percent"] > 0 && 
            $args["percent"] <= 100 && ( $args["buy"] || $args["sell"] ) )   
        {   
                
            switch( $args['strat'])
            {
                case 'SupRes':
                    $order = new SupportResist($pair, $args);
                break;
                case 'Range':
                    //$order = new TradeRange($pair, $info);    
                        $result["status"] =  false;
                        $result["message"] = "strategy is obsolete";

                    break;   
            }

            if( $order != NULL )
            {

                //$result = $order->TransactionComplete();

                if( $result["status"] )
                {
                    $result = $order->getQuotes($args["start"], $args["end"]);

                    if( $result["status"] )
                    {
                        $order->setOrderValues( );          
                        $result = $order->sendOrders();          

                        if( $result["status"] )
                        {    
                            $order->SetTransactionHistory( );
                            $order->insertOrders( $args["auto"] );
                        }

                    }
                }
            } 

            if( $result["status"] )
            {
                $result["status"] = "true";
                $result["future"] = $args["future"];
            }
            else
            {
                $result["status"] = "false";
                $result["future"] = $args["error"];
            }
        }
        else
        {
            $result["status"] =  "false"; 
            $result["message"] = "Check values for ";
                                        
            if( !$validDates )
            {
               $result["message"] .= "dates; ";
            } 

            if( $args["profit"] <= 0 )
            {
                $result["message"] .= "profit;";
            }
            
            if( $args["risk"] <= 0 )
            {
                $result["message"] .= "risk;";
            }
            
            if( $args["percent"] <= 0 || $args["percent"] > 100 )
            {
                $result["message"] .= "percent;";
            }

            if( !$args["buy"] && !$args["sell"] )
            {
                $result["message"] .= "buy & sell select ";
            }
                      
        }
    }
    else
    {
        $result = $input;
        $result["status"] = "false";
    }
    
    //$result["status"] = "false";
    //$result["future"] = $info["error"];
    $result["pair"] = $pair;
    return( $result);
   }

function isDateValid( $date )
{
    $valid = false;
    
//print "$year $mon $day $hour";
    if( strlen($date) == 16 )
    {  
        $year = substr($date , 0, 4) * 1;
        $mon = substr($date , 5, 2) * 1;
        $day = substr($date , 8, 2) * 1;
        $hour = substr($date , 11, 2) * 1;
        $min = substr($date , 14, 2) * 1;
        
        if( $year >= 2017 && 
            $mon > 0 && $mon < 13  && 
            $day > 0 && $day < 32  &&
            $hour >= 0 && $hour < 24 && 
            $min >= 0 && $min < 60 )
        {
            $valid = true;        
        }   
    }
    
   return $valid; 
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
 
?>