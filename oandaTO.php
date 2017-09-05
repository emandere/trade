<?php


include 'tradeClass.php';

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of oandaTO
 *
 * @author user
 */
class oandaTO {
    //put your code here

    private $primaryTrades;
    private $secondTrades;
    private $pulledTrades;
    
    private $primaryOrders;
    private $secondOrders;
    private $pulledOrders;
    private $oTable;

    private $hTable;
    
    private $acct1;
    private $acct2;
    private $auth;
    
    private $mongo;
   
    public function __construct($info)
    {
        $this->auth = "Authorization: Bearer ".chop($info["token"]);
        $this->mongo = chop($info["mongo"]);
        $this->acct1 = chop($info["acct1"]);
        $this->acct2 = chop($info["acct2"]);
        $this->pulledOrders =  FALSE;
        $this->pulledTrades =  FALSE;
        
        $this->hTable = [ "EUR_AUD" => NULL, 
                          "EUR_JPY" => NULL,
                          "GBP_JPY" => NULL, 
                          "GBP_USD" => NULL, 
                          "NZD_USD" => NULL,
                          "USD_CAD" => NULL ];
        
        $this->oTable = $this->hTable;
        $this->hPL = $this->hTable;
 
        //print_r( $this->hTable );
    }

    public function getHistory($pair)
    {
        if( $this->hTable[$pair] == NULL )
        {
            $info["mongo"] = $this->mongo;
            $this->hTable[$pair] = new HistoryTable($pair, $info);
            
            /*if( $this->hTable[$pair]->isFound() )
            {
                print $this->hTable[$pair]->getStatus(); 
            }
            else
            {
                print "history not found";
            }*/
        }
        
        if( $this->hTable[$pair]->isFound() )
        {
            $primaryHistory = NULL;
            $secondHistory = NULL;
            
            if( $this->hPL[$pair] == NULL )
            {
            $url_1 = "https://api-fxtrade.oanda.com/v1/accounts/".$this->acct1."/transactions?instrument=".$pair;
            $url_2 = "https://api-fxtrade.oanda.com/v1/accounts/".$this->acct2."/transactions?instrument=".$pair;
            
            $ch = curl_init($url_1);

            curl_setopt($ch, CURLOPT_HTTPHEADER, array($this->auth));    
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $result = curl_exec($ch);
            //var_dump(json_decode($result));
            
            if( curl_error($ch) )
            {
                print curl_error($ch);
            }
            else
            {
              $primaryHistory = json_decode($result);
              
              $ch = curl_init($url_2);

              curl_setopt($ch, CURLOPT_HTTPHEADER, array($this->auth));    
              curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

              $result = curl_exec($ch);
              //var_dump(json_decode($result));
              
              if( curl_error($ch) )
              {
                 print curl_error($ch_1);
              }
              else
              {
                $secondHistory = json_decode($result);
        
              }
            }
        
        
        //print $this->hTable->getStartDate();
        $startDate = DateTime::createFromFormat('Y-m-d H:i', $this->hTable[$pair]->getStartDate());
        $realPL = 0;
         
        if( $primaryHistory != NULL && $secondHistory != NULL )
        {
         for( $i = 0; $i < count($primaryHistory->transactions); $i++ )
         {
              if( $primaryHistory->transactions[$i]->type == "TAKE_PROFIT_FILLED" || 
                  $primaryHistory->transactions[$i]->type == "STOP_LOSS_FILLED" || 
                  $primaryHistory->transactions[$i]->type == "TRADE_CLOSE") 
              {
                  if( strrpos( $primaryHistory->transactions[$i]->time, "T") == 10 ) 
                  {        
                        $sDate = sprintf( "%s %s", substr($primaryHistory->transactions[$i]->time , 0, 10), 
                                             substr($primaryHistory->transactions[$i]->time, 11, 5 ) );

                        $tempDate = DateTime::createFromFormat('Y-m-d H:i', $sDate);
                        if( $tempDate >= $startDate )
                        {
                             $realPL += $primaryHistory->transactions[$i]->pl;
                             $realPL += $primaryHistory->transactions[$i]->interest;
                             
                        }

                    }
               }    
           }
           
           for( $i = 0; $i < count($secondHistory->transactions); $i++ )
           {
              if( $secondHistory->transactions[$i]->type == "TAKE_PROFIT_FILLED" || 
                  $secondHistory->transactions[$i]->type == "STOP_LOSS_FILLED" || 
                  $secondHistory->transactions[$i]->type == "TRADE_CLOSE" ) 
              {
                  if( $secondHistory->transactions[$i]->instrument == $pair && 
                      strrpos( $secondHistory->transactions[$i]->time, "T") == 10 ) 
                  {        
                        $sDate = sprintf( "%s %s", substr($secondHistory->transactions[$i]->time , 0, 10), 
                                             substr($secondHistory->transactions[$i]->time, 11, 5 ) );

                        $tempDate = DateTime::createFromFormat('Y-m-d H:i', $sDate);
                        if( $tempDate >= $startDate )
                        {
                             $realPL += $secondHistory->transactions[$i]->pl;
                             $realPL += $secondHistory->transactions[$i]->interest;
                        }

                    }
               }    
           }
            
            $this->hPL[$pair] = round($realPL, 2, PHP_ROUND_HALF_UP);
            $this->hTable[$pair]->setActual($this->hPL[$pair]);
            $this->hTable[$pair]->updateHistory();
        }
      }  
       
        return( $this->hTable[$pair] );
      }
    
      
    }
    
    
    public function getOrders($pair)
    {
        $orders = [];
        
        if( $this->oTable[$pair] == NULL )
        {
            $info["mongo"] = $this->mongo;
            $this->oTable[$pair] = new OrdersTable($pair, $info);
        }
        
        if( $this->oTable[$pair]->isFound() && !$this->pulledOrders )
        {
            $url_1 = "https://api-fxtrade.oanda.com/v1/accounts/".$this->acct1."/orders";
            $url_2 = "https://api-fxtrade.oanda.com/v1/accounts/".$this->acct2."/orders";
            
           
            $ch = curl_init($url_1);

            curl_setopt($ch, CURLOPT_HTTPHEADER, array($this->auth));    
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $result = curl_exec($ch);
            //var_dump(json_decode($result));
            
            if( curl_error($ch) )
            {
                print curl_error($ch_1);
            }
            else
            {
              $this->primaryOrders = json_decode($result);

              $ch = curl_init($url_2);

              curl_setopt($ch, CURLOPT_HTTPHEADER, array($this->auth));    
              curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

              $result = curl_exec($ch);
              //var_dump(json_decode($result));
              
              if( curl_error($ch) )
              {
                 print curl_error($ch_1);
              }
              else
              {
                $this->secondOrders = json_decode($result);
                $this->pulledOrders = TRUE;
              }
            }
        
        for( $i = 0; $i < count($this->primaryOrders->orders); $i++ )
        {
            if( $this->primaryOrders->orders[$i]->instrument == $pair )
            {
                $orderInfo = array("curr" => $this->primaryOrders->orders[$i]->instrument,
                                "units" => $this->primaryOrders->orders[$i]->units, 
                                "side" => $this->primaryOrders->orders[$i]->side, 
                                "ticket" => $this->primaryOrders->orders[$i]->id, 
                                "stopLoss" => $this->primaryOrders->orders[$i]->stopLoss, 
                                "takeProfit" => $this->primaryOrders->orders[$i]->takeProfit);
               
               
                if( $orderInfo["units"] == $this->oTable[$pair]->getUnits($orderInfo["side"]) && 
                    $orderInfo["stopLoss"] == $this->oTable[$pair]->getStopLoss($orderInfo["side"]) && 
                    $orderInfo["takeProfit"] == $this->oTable[$pair]->getTakeProfit($orderInfo["side"]) )
                {
                     $this->oTable[$pair]->setOrderTicket( $orderInfo["side"], $orderInfo["ticket"]); 
                }
           }
        }
        
        for( $i = 0; $i < count($this->secondOrders->orders); $i++ )
        {
            if( $this->secondOrders->orders[$i]->instrument == $pair )
            {
                $orderInfo = array("curr" => $this->secondOrders->orders[$i]->instrument,
                                "units" => $this->secondOrders->orders[$i]->units, 
                                "side" => $this->secondOrders->orders[$i]->side, 
                                "ticket" => $this->secondOrders->orders[$i]->id, 
                                "stopLoss" => $this->secondOrders->orders[$i]->stopLoss, 
                                "takeProfit" => $this->secondOrders->orders[$i]->takeProfit);
               
              if( $orderInfo["units"] == $this->oTable[$pair]->getUnits($orderInfo["side"]) && 
                    $orderInfo["stopLoss"] == $this->oTable[$pair]->getStopLoss($orderInfo["side"]) && 
                    $orderInfo["takeProfit"] == $this->oTable[$pair]->getTakeProfit($orderInfo["side"]) )
                {
                     $this->oTable[$pair]->setOrderTicket( $orderInfo["side"], $orderInfo["ticket"]); 
                }
           }
        }
        
        $this->getTrades($pair);
     }
      return($this->oTable[$pair]);
    }
    
    public function getTrades($pair)
    {
        $trades = [];
        
        if( $this->oTable[$pair] == NULL )
        {
            $info["mongo"] = $this->mongo;
            $this->oTable[$pair] = new OrdersTable($pair, $info);
        }
        
        if( $this->oTable[$pair]->isFound() && !$this->pulledTrades )
        {
            $url_1 = "https://api-fxtrade.oanda.com/v1/accounts/".$this->acct1."/trades";
            $url_2 = "https://api-fxtrade.oanda.com/v1/accounts/".$this->acct2."/trades";
            
           
            $ch = curl_init($url_1);

            curl_setopt($ch, CURLOPT_HTTPHEADER, array($this->auth));    
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $result = curl_exec($ch);
            //var_dump(json_decode($result));
            
            if( curl_error($ch) )
            {
                print curl_error($ch_1);
            }
            else
            {
              $this->primaryTrades = json_decode($result);

              $ch = curl_init($url_2);

              curl_setopt($ch, CURLOPT_HTTPHEADER, array($this->auth));    
              curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

              $result = curl_exec($ch);
              //var_dump(json_decode($result));
              
              if( curl_error($ch) )
              {
                 print curl_error($ch_1);
              }
              else
              {
                $this->secondTrades = json_decode($result);
                $this->pulledTrades = TRUE;
              }
            }
        for( $i = 0; $i < count($this->primaryTrades->trades); $i++ )
        {
            
            if( $this->primaryTrades->trades[$i]->instrument == $pair )
            {
                $tradeInfo = array("curr" => $this->primaryTrades->trades[$i]->instrument,
                                "units" => $this->primaryTrades->trades[$i]->units, 
                                "side" => $this->primaryTrades->trades[$i]->side, 
                                "ticket" => $this->primaryTrades->trades[$i]->id, 
                                "stopLoss" => $this->primaryTrades->trades[$i]->stopLoss, 
                                "takeProfit" => $this->primaryTrades->trades[$i]->takeProfit, 
                                "acct" => $this->acct1 );
               
                if( $tradeInfo["units"] == $this->oTable[$pair]->getUnits($tradeInfo["side"]) && 
                    $tradeInfo["stopLoss"] == $this->oTable[$pair]->getStopLoss($tradeInfo["side"]) && 
                    $tradeInfo["takeProfit"] == $this->oTable[$pair]->getTakeProfit($tradeInfo["side"]) )
                {
                     $this->oTable[$pair]->setTradeTicket( $tradeInfo["side"], $tradeInfo["ticket"]); 
                }

           }
        }
        
        for( $i = 0; $i < count($this->secondTrades->trades); $i++ )
        {
            if( $this->secondTrades->trades[$i]->instrument == $pair )
            {
                $tradeInfo = array("curr" => $this->secondTrades->trades[$i]->instrument,
                                "units" => $this->secondTrades->trades[$i]->units, 
                                "side" => $this->secondTrades->trades[$i]->side, 
                                "ticket" => $this->secondTrades->trades[$i]->id, 
                                "stopLoss" => $this->secondTrades->trades[$i]->stopLoss, 
                                "takeProfit" => $this->secondTrades->trades[$i]->takeProfit, 
                                "acct" => $this->acct2 );
                
                if( $tradeInfo["units"] == $this->oTable[$pair]->getUnits($tradeInfo["side"]) && 
                    $tradeInfo["stopLoss"] == $this->oTable[$pair]->getStopLoss($tradeInfo["side"]) && 
                    $tradeInfo["takeProfit"] == $this->oTable[$pair]->getTakeProfit($tradeInfo["side"]) )
                {
                     $this->oTable[$pair]->setTradeTicket( $tradeInfo["side"], $tradeInfo["ticket"]); 
                }

           }
        }
        }
    }
    
    }
