<?php

class parmsClass
{    
    private $profit_1;
    private $risk_1;
    private $move_1;
    private $start_d_1;
    private $end_d_1;
    private $start_t_1;
    private $end_t_1;
    private $strat_1;
    private $acct_1;
    private $enable_1;
    private $profit_2;
    private $risk_2;
    private $move_2;
    private $start_d_2;
    private $end_d_2;
    private $start_t_2;
    private $end_t_2;
    private $strat_2;
    private $acct_2;
    private $enable_2;
    private $mongo;
    private $primary;
    private $second;
    
    public function __construct( $info )
    {
        $this->profit_1 = 0;
        $this->risk_1 = 0;
        $this->move_1 = 100;
        $this->start_d_1 = 1;
        $this->end_d_1 = 1;
        $this->start_t_1 = "00:00";
        $this->end_t_1 = "00:00";
        $this->strat_1 = "";
        $this->acct_1 = "Split";
        $this->enable_1 = 'Y';
        $this->profit_2 = 0;
        $this->risk_2 = 0;
        $this->move_2 = 100;
        $this->start_d_2 = 1;
        $this->end_d_2 = 1;
        $this->start_t_2 = "00:00";
        $this->end_t_2 = "00:00";
        $this->strat_2 = "";
        $this->acct_2 = "Split";
        $this->primary = $info["primary"];
        $this->second = $info["second"];
        $this->mongo = $info["mongo"];
        $this->enable_2 = 'Y';
        
    }
  
    public function readDB( $pair )
    {
        $found = FALSE;
       
        try
        {  
                $mongoConn = new MongoDB\Driver\Manager("mongodb://".$this->mongo);
                $find = [ 'pair' => $pair ];
                $options = [ 'projection' => [ '_id' => 0 ]];
                $mongoQ = new MongoDB\Driver\Query($find, $options);
                $mongoCurs = $mongoConn->executeQuery('test.Parms', $mongoQ);
                //var_dump($mongoCurs->toArray());
        
                foreach($mongoCurs as $rec)
                {
                    $this->profit_1 = ( $rec->profit_1 ? $rec->profit_1 : $this->profit_1 );
                    $this->risk_1 = ( $rec->risk_1 ? $rec->risk_1 : $this->risk_1 );
                    $this->p_move_1 = ( $rec->p_move_1 ? $rec->p_move_1 : $this->p_move_1 );
                    $this->start_d_1 = ( $rec->start_day_1 ? $rec->start_day_1 : $this->start_d_1 ); 
                    $this->end_d_1 = ( $rec->end_day_1 ? $rec->end_day_1 : $this->end_d_1 );
                    $this->start_t_1 = ( $rec->start_time_1 ? $rec->start_time_1 : $this->start_t_1 );
                    $this->end_t_1 = ( $rec->end_time_1 ? $rec->end_time_1 : $this->end_t_1 );
                    $this->strat_1 = ( $rec->strat_1 ? $rec->strat_1 : $this->strat_1 );
                    $this->acct_1 = ( $rec->acct_1 ? $rec->acct_1: $this->acct_1 );
                    $this->enable_1 = ( $rec->enable_1 ? $rec->enable_1 : $this->enable_1 );
                    $this->profit_2 = ( $rec->profit_2 ? $rec->profit_2 : $this->profit_2 );
                    $this->risk_2 = ( $rec->risk_2 ? $rec->risk_2 : $this->risk_2 );
                    $this->p_move_2 = ( $rec->p_move_2 ? $rec->p_move_2 : $this->p_move_2 );
                    $this->start_d_2 = ( $rec->start_day_2 ? $rec->start_day_2 : $this->start_d_2 );
                    $this->end_d_2 = ( $rec->end_day_2 ? $rec->end_day_2 : $this->end_d_2 ); 
                    $this->start_t_2 = ( $rec->start_time_2 ? $rec->start_time_2 : $this->start_t_2 ); 
                    $this->end_t_2 = ( $rec->end_time_2 ? $rec->end_time_2 : $this->end_t_2 );
                    $this->strat_2 = ( $rec->strat_2 ? $rec->strat_2 : $this->strat_2 );
                    $this->acct_2 = ( $rec->acct_2 ? $rec->acct_2 : $this->acct_2 );
                    $this->enable_2 = ( $rec->enable_2 ? $rec->enable_2 : $this->enable_2 );
                 
                   $found = TRUE;
                }        
        }
        catch (Exception $e) 
        {
            //$retInfo["status"] = FALSE;
            print $e->getMessage();
        }
      
    return($found);
    }
    
    
    public function getValues()
    {
        $info = array("prof1" => $this->profit_1,
                      "risk1" => $this->risk_1,
                      "p_move1" => $this->p_move_1,
                      "sd1" => $this->start_d_1, 
                      "ed1" => $this->end_d_1, 
                      "st1" => $this->start_t_1,
                      "et1" => $this->end_t_1, 
                      "strat1" => $this->strat_1,
                      "acct1" => $this->acct_1,
                      "enable1" => $this->enable_1,
                      "prof2" => $this->profit_2,
                      "risk2" => $this->risk_2,
                      "p_move2" => $this->p_move_2,
                      "sd2" => $this->start_d_2,
                      "ed2" => $this->end_d_2,
                      "st2" => $this->start_t_2,
                      "et2" => $this->end_t_2,
                      "strat2" => $this->strat_2,
                      "acct2" => $this->acct_2,
                      "enable2" => $this->enable_2,
                      "primary" => $this->primary,
                      "second" => $this->second );
        
       
        //print_r($info);
        return($info);
    }
    
    public function setValues( $info )
    {
        $this->profit_1 = $info["prof1"];
        $this->risk_1 = $info["risk1"];
        $this->p_move_1 = $info["p_move1"];
        $this->start_d_1 = $info["sd1"];
        $this->end_d_1 = $info["ed1"];
        $this->start_t_1 = $info["st1"];
        $this->end_t_1 = $info["et1"];
        $this->strat_1 = $info["strat1"];
        $this->acct_1 = $info["acct1"];
        $this->enable_1 = $info["enable1"]; 
        $this->profit_2 = $info["prof2"];
        $this->risk_2 = $info["risk2"];
        $this->p_move_2 = $info["p_move2"];
        $this->start_d_2 = $info["sd2"];
        $this->end_d_2 = $info["ed2"];
        $this->start_t_2 = $info["st2"];
        $this->end_t_2 = $info["et2"];
        $this->strat_2 = $info["strat2"];
        $this->acct_2 = $info["acct2"];
        $this->enable_2 = $info["enable2"];
        
    }
    
    public function updateDB( $pair )
    {
        
        
        try
        {
            $mongoConn = new MongoDB\Driver\Manager("mongodb://".$this->mongo);
            $bulk = new MongoDB\Driver\BulkWrite;

            $find = [ 'pair' => $pair ];
            $update = array("profit_1" =>  $this->profit_1,
                             "risk_1" =>  $this->risk_1,
                            "p_move_1" =>  $this->p_move_1,
                            "start_day_1" => $this->start_d_1,
                            "end_day_1" => $this->end_d_1,
                            "start_time_1" => $this->start_t_1,
                            "end_time_1" => $this->end_t_1,
                            "strat_1" => $this->strat_1,
                            "acct_1" => $this->acct_1,
                            "enable_1" => $this->enable_1,
                            "profit_2" =>  $this->profit_2,
                            "risk_2" =>  $this->risk_2,
                            "p_move_2" =>  $this->p_move_2,
                            "start_day_2" => $this->start_d_2,
                            "end_day_2" => $this->end_d_2,
                            "start_time_2" => $this->start_t_2,
                            "end_time_2" => $this->end_t_2,
                            "strat_2" => $this->strat_2,
                            "acct_2" => $this->acct_2 , 
                            "enable_2" => $this->enable_2);

            try
            {
                $bulk->update($find, [ '$set' => $update], ['multi' => true, 'upsert' => true] );
                $result = $mongoConn->executeBulkWrite('test.Parms', $bulk);

                //var_dump( $result);
                
                if( $result->getModifiedCount() == 0 && $result->getUpsertedCount() == 0 && 
                    $result->getMatchedCount() == 0  )
                {       
                    print "Error....Re-Submit";
                    //var_dump( $result);
                }
                else
                {
                    print "Update Successful for ".$pair."!";
                    echo "<br>";
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

$info = readInfile();

if( $info  )
{    
    $update = FALSE;
    $read = FALSE;
    
    if( sizeof($_POST) > 0 )
    {
        if( array_key_exists( "update", $_POST ))
            $update = TRUE;       
        else if( array_key_exists( "read", $_POST ) )
            $read = TRUE;       
    }
    
    $p = new parmsClass( $info );

    if( $update ) 
    {
        $info = getValues( $info );
        
        if( validInput( $info ) )
        {
            $p->setValues( $info );
            $info["pair1"] = $_POST["pair1"];
            $info["pair2"] = $_POST["pair2"];
    
            switch ($_POST["pair2"]) 
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
                    $p->updateDB(abbrevToPair( $_POST["pair2"] ));   
                 break;
                case "ALL":
                    $p->updateDB(abbrevToPair("EA"));
                    $p->updateDB(abbrevToPair("EJ"));
                    $p->updateDB(abbrevToPair("GJ"));
                    $p->updateDB(abbrevToPair("GU"));
                    $p->updateDB(abbrevToPair("NU"));
                    $p->updateDB(abbrevToPair("UC"));
                    $p->updateDB(abbrevToPair("EU"));
                    $p->updateDB(abbrevToPair("AU"));
                    $p->updateDB(abbrevToPair("AJ"));
                    $p->updateDB(abbrevToPair("UF"));
                break;
            }   
            
        }
    
    }
    else if( $read )
    {
        switch ($_POST["pair1"]) 
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
                $p->readDB(abbrevToPair($_POST["pair1"]));
             break;
        }
        
        $info = $p->getValues();
        $info["pair1"] = $_POST["pair1"];
        $info["pair2"] = ' ';
    }
    else
    {
        $info["pair1"] = ' ';
        $info["pair2"] = ' ';
        $info = $p->getValues();
    }

    if( sizeof($info) > 0 )
    {
        showPage($info);    
    }
    else
    {
       print "error reading database";
    }
 }
 else 
 {
     print "error reading infile";
 }

function readInfile()
{
        $infile = "secrets/info.txt";
        $fh = fopen($infile, "r");
        $info = FALSE;
        
        if( $fh )
        {
            $oanda = fgets($fh);
            $mon = fgets($fh);
            $tok = fgets($fh);
            $prim = fgets($fh);
            $sec = fgets($fh);            
            fclose($fh);
        
            if( $mon && $prim && $sec )
            {
             
                $info = array("primary" => chop($prim), 
                               "second" => chop($sec), 
                               "mongo" => chop($mon) ); 
            }
        }
    
     return($info);   

}

function getValues( $fileInfo )
{
    //$acct1 = ($_POST["acct1"] == "Primary" ? $fileInfo["primary"] : $fileInfo["second"]);
    //$acct2 = ($_POST["acct2"] == "Primary" ? $fileInfo["primary"] : $fileInfo["second"]);
        
     
    
    $info = array("prof1" => $_POST["prof1"], 
                  "risk1" => $_POST["risk1"],
                  "p_move1" => $_POST["pm1"],
                   "sd1" => $_POST["sd1"], 
                   "ed1" => $_POST["ed1"], 
                   "st1" => $_POST["st1"],
                   "et1" => $_POST["et1"], 
                   "strat1" => $_POST["strat1"],
                   "acct1" => $_POST["acct1"],
                   "prof2" => $_POST["prof2"],
                   "risk2" => $_POST["risk2"],
                   "p_move2" => $_POST["pm2"],
                   "sd2" => $_POST["sd2"],
                   "ed2" => $_POST["ed2"],
                   "st2" => $_POST["st2"],
                   "et2" => $_POST["et2"],
                   "strat2" => $_POST["strat2"],
                   "acct2" => $_POST["acct2"],
                   "primary" => $fileInfo["primary"],
                   "second" => $fileInfo["second"],
                   "enable1" => 'N',
                   "enable2" => 'N' );
    
     if( array_key_exists( "enable1", $_POST ) && $_POST["enable1"] == 'Y' )
        $info["enable1"] = $_POST["enable1"];
             
     if( array_key_exists( "enable2", $_POST ) && $_POST["enable2"] == 'Y' )
        $info["enable2"] = $_POST["enable2"];
     
     //print_r($info);
     return($info);
}

 function validInput( )
 {
     $valid = TRUE;
     $error;
     
     $v1 = floatval( $_POST["prof1"] );
     $v2 = floatval( $_POST["prof2"] );
     $v3 = floatval( $_POST["risk1"] );
     $v4 = floatval( $_POST["risk2"] );
     
     if( !is_float($v1) || !is_float($v2) || !is_float($v3) || !is_float($v4) || 
          $v1 <= 0 || $v2 <= 0 || $v3 <= 0 || $v4 <= 0) 
     {
        print "invalid profit/risk values";        
        echo "<br>";
        $valid = FALSE;
     }   

     $v1 = intval( $_POST["pm1"] );
     $v2 = intval( $_POST["pm2"] );
     
     if( !is_int($v1) || !is_int($v2) || 
          $v1 <= 0 || $v2 <= 0 || $v1 > 100 || $v2 > 100 ) 
     {
        print "invalid % pip move values";        
        echo "<br>";
        $valid = FALSE;
     }   

     
     $v1 = intval( $_POST["sd1"] );
     $v2 = intval( $_POST["ed1"] );
     $v3 = intval( $_POST["sd2"] );
     $v4 = intval( $_POST["ed2"] );
     

     if( !is_numeric($v1) || !is_numeric($v2) ||  !is_numeric($v3) || 
         !is_numeric($v4) || $v1 < 1 || $v1 > 6 || $v2 < 1 || $v2 > 6 ||  
         $v3 < 1 || $v3 > 6 || $v4 < 1 || $v4 > 6 || $v1 > $v2 || 
         $v3 > $v4 ) 
     {
        print "invalid date ( 1: Sunday - 6: Friday )...end d >= start d";        
        echo "<br>";
        $valid = FALSE;
    }
     
     $v1 = strtok( $_POST["st1"] , ":") ;
     $v2 = strtok( ":") ;

     $v3 = strtok( $_POST["et1"] , ":") ;
     $v4 =  strtok( ":") ;

     if( strlen( $_POST["st1"]) != 5 || strlen( $_POST["et1"]) != 5 || !is_numeric($v1) || 
         !is_numeric($v2) || !is_numeric($v3) || !is_numeric($v4) ||
         intval($v1) < 0 || intval($v1) > 23 || intval($v2) < 0 || intval($v2) > 59  || 
         intval($v3) < 0 || intval($v3) > 23 || intval($v4) < 0 || intval($v4) > 59 ) 
         
     {
        print "invalid start/end time#1 ( valid \"hh:mm\" )";        
        echo "<br>";
        $valid = FALSE;
    }
     
     $v1 = strtok( $_POST["st2"] , ":");
     $v2 = strtok( ":");

     $v3 = strtok( $_POST["et2"] , ":");
     $v4 = strtok( ":");
     
     
     if( strlen( $_POST["st2"]) != 5 || strlen( $_POST["et2"]) != 5 || !is_numeric($v1) || 
         !is_numeric($v2) || !is_numeric($v3) || !is_numeric($v4) ||
         intval($v1) < 0 || intval($v1) > 23 || intval($v2) < 0 || intval($v2) > 59  || 
         intval($v3) < 0 || intval($v3) > 23 || intval($v4) < 0 || intval($v4) > 59 ) 
     {
         
        print "invalid start/end time#2 ( valid \"hh:mm\" )";        
        echo "<br>";
        $valid = FALSE;
    }
    
    return($valid);
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

 function showPage( $info )
 {
?>  
       <html>
          <head>
              <title>Order Parameters</title>
              <meta charset="windows-1252">
              <meta name="viewport" content="width=device-width, initial-scale=1.0">
              <style>
            .container { width: 100%;
                        height: 450px;
                        margin: auto;
                       }
             .left    { max-width: 350px;
                        height: 425px;
                        padding: 10px;
                        border-style: solid;
                        float: left;
                    }
             .right { max-width: 350px;
                      float: left;
                      height: 425px;
                      padding: 10px;
                      border-style: solid;
                     }
             .day { max-width: 350px;
                      float: left;
                      display: inline-block;
             }
                     
              </style>
         </head>
         <script>
          function day_name(src, dest) {
            var day = "";
            var value=document.getElementById(src).value;
              
               switch(value)
              {
                  case "1":
                      day = "(Sun)";
                      break;
                  case "2":
                     day = "(Mon)";
                      break;
                  case "3":
                     day = "(Tues)";
                      break;
                  case "4":
                     day = "(Wed)";
                      break;
                  case "5":
                     day = "(Thurs)";
                      break;
                  case "6":
                     day = "(Fri)";
                      break;
                  default:
                      day="(???)";
                  break;    
                }
                document.getElementById(dest).innerHTML = day;
          }
          
          function set_initial_values(){
              day_name('sd1', 'sd1_desc');
              day_name('ed1', 'ed1_desc');
              day_name('sd2', 'sd2_desc');
              day_name('ed2', 'ed2_desc');
              

              document.getElementById("enable1").checked = ( '<?php print $info["enable1"]?>'  == 'Y' ? true : false );

              document.getElementById("enable2").checked = ( '<?php print $info["enable2"]?>'  == 'Y' ? true : false );

              if( '<?php print $info["acct1"]?>' == 'Primary' || '<?php print $info["acct1"]?>' == 'Second' )
                    document.getElementById("acct1").value = '<?php print $info["acct1"]?>';
              else
                    document.getElementById("acct1").value = 'Split';
                  
              if( '<?php print $info["acct2"]?>' == 'Primary' || '<?php print $info["acct2"]?>' == 'Second' )
                    document.getElementById("acct2").value = '<?php print $info["acct2"]?>';
              else
                    document.getElementById("acct2").value = 'Split';
              
              document.getElementById("strat1").value = ( ('<?php print $info["strat1"]?>' == 'SupRes' || '<?php print $info["strat1"]?>' == ' ') ? 
                "SupRes" : "Range" );

              document.getElementById("strat2").value = ( ('<?php print $info["strat2"]?>' == 'SupRes' || '<?php print $info["strat2"]?>' == ' ') ? 
                "SupRes" : "Range" );

              
              if( '<?php print $info["pair1"]?>' != ' ') 
                  document.getElementById("pair1").value = "<?php print $info["pair1"]?>";
              else
                  document.getElementById("pair1").value = "AJ";
              
              if( '<?php print $info["pair2"]?>' != ' ' ) 
                  document.getElementById("pair2").value = "<?php print $info["pair2"]?>";
              else
                  document.getElementById("pair2").value = "AJ";
            }
          
          window.onload = set_initial_values;
          </script>
         <form action="<?php $_PHP_SELF ?>" method="post">
         <div class="container" >
             <div class="left" >
              <!--<body>-->
              <label for "prof1"> Profit #1</label>
              <input type ="text" name ="prof1" value = "<?php print $info["prof1"]?>">
              <br><br> 
              <label for "risk1"> Risk #1</label>
              <input type ="text" name ="risk1" value = "<?php print $info["risk1"]?>">
              <br><br>
              <label for "pm1">Win Move% #1</label>
              <input type ="text" name ="pm1" value = "<?php print $info["p_move1"]?>"> (1 -100)
              <br><br> 
              <label for "strat1">Select Strategy #1</label>
              <select id="strat1" name="strat1">
                <option value="SupRes"> Current Week Support/Resistance </option>
                <option value="Range"> Current Week Range </option>
              </select>        
              <br><br>
              <label for "sd1"> Start Day #1</label>
              <input type ="text" id = "sd1" name ="sd1" onkeyup="day_name('sd1', 'sd1_desc')" value = "<?php print $info["sd1"]?>">
              <span  id="sd1_desc"> </span>
              <br><br>
              <label for "st1"> Start Time #1</label>
              <input type ="text" name ="st1" value = "<?php print $info["st1"]?>">
              <br><br> 
              <label for "ed1"> End Day #1</label>
              <input type ="text" id = "ed1" name ="ed1" onkeyup="day_name('ed1', 'ed1_desc')" value = "<?php print $info["ed1"]?>">
              <span  id="ed1_desc"> </span>
              <br><br>
              <label for "et1"> End Time #1</label>
              <input type ="text" name ="et1" value = "<?php print $info["et1"]?>">
              <br><br>
              <select id = "acct1" name="acct1">
                 <option  value="Primary"> Primary </option>
                 <option  value="Second"> Acct 2 </option>
                 <option value = "Split"> Split </option> 
              </select>  
              <input type="checkbox" id = "enable1" name="enable1" value="Y" >Enable Trade #1<br>
             </div>
             <div class="right" >
              <!--<body>-->
              <label for "prof2"> Profit #2</label>
              <input type ="text" name ="prof2" value = "<?php print $info["prof2"]?>">
              <br><br> 
              <label for "risk2"> Risk #2</label>
              <input type ="text" name ="risk2" value = "<?php print $info["risk2"]?>">
              <br><br>
              <label for "pm2">Win Move% #2</label>
              <input type ="text" name ="pm2" value = "<?php print $info["p_move2"]?>"> (1 -100)
              <br><br> 
              <label for "strat2">Select Strategy #2</label>
              <select id = "strat2" name="strat2">
               <option value="SupRes"> Current Week Support/Resistance </option>
               <option value="Range"> Current Week Range </option>
              </select>        
              <br><br>
              <label for "sd2"> Start Day #2</label>
              <input type ="text" id = "sd2" name ="sd2" onkeyup="day_name('sd2', 'sd2_desc')" value = "<?php print $info["sd2"]?>">  
              <span  id="sd2_desc"> </span>
              <br><br>
              <label for "st2"> Start Time #2</label>
              <input type ="text" name ="st2" value = "<?php print $info["st2"]?>">
              <br><br> 
              <label for "ed2"> End Day #2</label>
              <input type ="text" id = "ed2" name ="ed2" onkeyup="day_name('ed2', 'ed2_desc')" value = "<?php print $info["ed2"]?>"> 
              <span  id="ed2_desc"> </span>
              <br><br>
              <label for "et2"> End Time #2</label>
              <input type ="text" name ="et2" value = "<?php print $info["et2"]?>">
              <br><br>
              <select id = "acct2" name="acct2">
                 <option  value="Primary"> Primary </option>
                 <option value="Second"> Acct 2 </option>    
                 <option value = "Split"> Split </option> 
              </select>        
              <input type="checkbox" id = "enable2" name="enable2" value="Y" >Enable Trade #2<br>
             </div>
         </div>
              <br><br> 
           <input type ="submit" id = "read" name ="read" value="Read Trade Parms">   
              <select id="pair1" name="pair1">
                <option value="AJ"> AUD/JPY </option>
                  <option value = "AU"> AUD/USD </option>
                  <option value = "EA"> EUR/AUD </option>                  
                  <option value = "EJ"> EUR/JPY </option>
                  <option value = "EU"> EUR/USD </option>
                  <option value = "GJ"> GBP/JPY </option>
                  <option value = "GU"> GBP/USD </option>
                  <option value = "NU"> NZD/USD </option>
                  <option value = "UC"> USD/CAD </option>
                  <option value = "UF"> USD/CHF </option>
              </select>        
              <br><br> 
              <input type ="submit" id = "update" name ="update" value="Update Trade Parms">
              <select id="pair2" name="pair2">
                <option  value="AJ"> AUD/JPY </option>
                  <option value = "AU"> AUD/USD </option>
                  <option value = "EA"> EUR/AUD </option>                  
                  <option value = "EJ"> EUR/JPY </option>
                  <option value = "EU"> EUR/USD </option>
                  <option value = "GJ"> GBP/JPY </option>
                  <option value = "GU"> GBP/USD </option>
                  <option value = "NU"> NZD/USD </option>
                  <option value = "UC"> USD/CAD </option>
                  <option value = "UF"> USD/CHF </option>
                  <option value = "ALL">All    </option>
              </select>           
         </form>
       </head>
      </html>
<?php
}
