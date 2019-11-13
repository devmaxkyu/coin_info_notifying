<?php
/**
 * Class for parsing commands in email and generating response data
 * @author Zemin W.
 * Created at 2019-11-12
 * 
 * 
 */
define("TOP10", "top 10");
define("TOP5", "top 5");
define("PRICE", "price");
define("MARKET_CAP", "market cap");
define("VOLUME", "volume");
define("CIRCULATING_SUPPLY", "current supply");
define("PERCENT_CHANGE", "% change");

 class Parsing{

    public $commands = [
        'get' ,
        'price' , 
        'market cap' , 
        'volume' , 
        'current supply' , 
        '% change', 
        'top 10', 
        'top 5' 
    ];
    
    public $content = null;
    public $extracted_cmds = [];
    public $symbols = array();
    public $response_text = null;
    public $currency_list = array();

    function __construct($content, $currency_list){

        $this->content = $content;
        $this->currency_list = $currency_list;
    }

    function extract_cmds()
    {
        $this->extracted_cmds = array();

        foreach($this->commands as $key ){
            if(strpos($this->content, $key) === FALSE){
                continue;
            }
           
            array_push($this->extracted_cmds, $key);
            
        }
  

        return $this->extracted_cmds;
    }

    function has_symbol($symbol)
    {
        if(strpos($this->content, $symbol) === FALSE){
            return FALSE;
        }
        
        if(in_array($symbol, $this->symbols) == FALSE)
        {
            array_push($this->symbols, $symbol);
        }

        return TRUE;
    }
    function divideFloat($a, $b, $precision=2) {
        $a*=pow(10, $precision);
        $result=(int)($a / $b);
        if (strlen($result)==$precision) return '0.' . $result;
        else return preg_replace('/(\d{' . $precision . '})$/', '.\1', $result);
    }

    function makeNumberFormat($num){
       
        $cnt = strlen((string)explode('.', $num)[0]);
        if($cnt > 11){
           
            // billion
            return number_format($this->divideFloat($num, pow(10, 12)), 2). 'B';
        }else if ($cnt > 5){
            // million
            return number_format($this->divideFloat($num, pow(10, 6)), 2). 'M';
        }else if ($cnt > 2){
            // thousand
            return number_format($this->divideFloat($num, pow(10, 3)), 2). 'K';
        }else{
            return number_format($num, 2);
        }
    }

    function generate(){
        $d = new DateTime();

        $this->response_text = (string)$d->format('Y-m-d \@ H:i:s').'<br>------------------------------<br>';
        // generate response data
        foreach($this->currency_list->data as $coin){
            if($this->has_symbol($coin->symbol)){
                $line = $coin->symbol;
                //price
                if(in_array(PRICE, $this->extracted_cmds)){
                    $line .= ' (P) $'. number_format($coin->quote->USD->price, 2);
                }
                //market cap
                if(in_array(MARKET_CAP, $this->extracted_cmds)){
                   
                    $line .= ' (MC) $'. $this->makeNumberFormat($coin->quote->USD->market_cap);
                }

                //volume 24h
                if(in_array(VOLUME, $this->extracted_cmds)){
                    $line .= ' (24HR/Vol) $'. $this->makeNumberFormat($coin->quote->USD->volume_24h);
                }

                //current change
                if(in_array(CIRCULATING_SUPPLY, $this->extracted_cmds)){
                    $line .= ' (CS) '.$coin->symbol.' '. $this->makeNumberFormat($coin->circulating_supply);
                }


                //change 24h
                if(in_array(PERCENT_CHANGE, $this->extracted_cmds)){
                    $line .= ' (24HR/%) '. number_format($coin->quote->USD->percent_change_24h, 1).'%';
                }
                $this->response_text .= $line.'<br>';
            }
        }

        // check top list
        // top 10
        if(in_array(TOP10, $this->extracted_cmds)){
            $this->generateTop(10);
        }

        // top 5
        if(in_array(TOP5, $this->extracted_cmds)){
            $this->generateTop(5);
        }
        
        return $this->response_text;
    }

    function generateTop($limit){
        $i = 1;
        while($i < $limit + 1){
            $coin = $this->currency_list->data[$i-1];
            $line = $i.'. '. $coin->symbol;
            $line .= ' (MC) $'. $this->makeNumberFormat($coin->quote->USD->market_cap);
            $line .= ' (P) $'. number_format($coin->quote->USD->price, 2);
            $line .= ' (24HR/Vol) $'. $this->makeNumberFormat($coin->quote->USD->volume_24h);
            $line .= ' (CS) '.$coin->symbol.' '. $this->makeNumberFormat($coin->circulating_supply);
            $line .= ' (24HR/%) '. number_format($coin->quote->USD->percent_change_24h, 1).'%';
            $this->response_text .= $line.'<br>';
            $i++;
        }
    }

 }
