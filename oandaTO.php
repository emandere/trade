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

    private $primaryHistory;
    private $secondHistory;
    private $hTable;
    
    private $acct1;
    private $acct2;
    private $auth;
    
    private $mongo;
   
   // private $tickets;
    
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
    
        //print_r( $this->hTable );
    }

    public function getHistory($pair)
    {
        if( $this->hTable[$pair] == NULL )
        {
            $info["mongo"] = $this->mongo;
            $this->hTable[$pair] = new HistoryTable($pair, $info);
            
            if( $this->hTable[$pair]->isFound() )
            {
                print $this->hTable[$pair]->getStatus(); 
            }
            else
            {
                print "history not found";
            }
        }
        
    }
    
    public function getOrders($pair)
    {
        $orders = [];
        
        if( !$this->pulledOrders )
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
        }
        
        $j = 0;
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
               
              
               $orders[$j] = $orderInfo;
               $j++;
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
               
              
               $orders[$j] = $orderInfo;
               $j++;
           }
        }
    
      return($orders);
    }
    
    public function getTrades($pair)
    {
        $trades = [];
        
        if( !$this->pulledTrades )
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
        }
        
        $j = 0;
        for( $i = 0; $i < count($this->primaryTrades->trades); $i++ )
        {
            
            if( $this->primaryTrades->trades[$i]->instrument == $pair )
            {
                $tradeInfo = array("curr" => $this->primaryTrades->trades[$i]->instrument,
                                "units" => $this->primaryTrades->trades[$i]->units, 
                                "side" => $this->primaryTrades->trades[$i]->side, 
                                "ticket" => $this->primaryTrades->trades[$i]->id, 
                                "stopLoss" => $this->primaryTrades->trades[$i]->stopLoss, 
                                "takeProfit" => $this->primaryTrades->trades[$i]->takeProfit);
               
              
               $trades[$j] = $tradeInfo;
               $j++;
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
                                "takeProfit" => $this->secondTrades->trades[$i]->takeProfit);
               
              
               $trades[$j] = $tradeInfo;
               $j++;
           }
        }
    
      return($trades);
    }
    
    }
