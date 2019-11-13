<?php
/**
 * Class for CoinMarketCap REST API
 * @author Zemin W.
 * Created at 2019-11-12
 */

 class CoinMarket{

    
    // resource url for api call
    public $apiUri = 'https://pro-api.coinmarketcap.com/v1/';

    // Get cURL resource
    private $curl;

    // request header
    private $headers = [
        'Accepts: application/json',
        'X-CMC_PRO_API_KEY: '
    ];
    
    /**
     * set api key to headers
     * @param $api_key => string
     *  */ 
    function __construct($api_key) {

        $this->headers[1] .= $api_key;
        $this->curl = curl_init();

    }

    function getListingLatest($start = 1, $limit = 0, $convert = null){

        $parameters = array();

        if($start > 1){
            $parameters['start'] = $start;
        }

        if($limit > 0){
            $parameters['limit'] = $limit;
        }

        if($convert){
            $parameters['convert'] = $convert;
        }

        $qs = '';

        if(count($parameters) > 0){
            // build query string
            $qs = http_build_query($parameters);
        }

        // query string encode the parameters
        $request = "{$this->apiUri}cryptocurrency/listings/latest?{$qs}"; // create the request URL
        
        
        // Set cURL options
        curl_setopt_array($this->curl, array(
            CURLOPT_URL => $request,            // set the request URL
            CURLOPT_HTTPHEADER => $this->headers,     // set the headers 
            CURLOPT_RETURNTRANSFER => 1         // ask for raw response instead of bool
        ));

        $response = curl_exec($this->curl); // Send the request, save the response

        $result_arr = json_decode($response);
        
        return $result_arr;
    }

    function close(){
        curl_close($this->curl); // Close request
    }



 }