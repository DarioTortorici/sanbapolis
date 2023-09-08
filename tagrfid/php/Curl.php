<?php

define("GET", 1);
define("POST", 2);
define("PUT", 3);
define("DELETE", 4);

class Curl{
    private $curlSession;
    private $url;
    private $headers;
    private $parameters;
    private $method;

    /**
     * @param string $url l'url a cui inoltrrare la richiesta
     * @param array $headers un vettore che rappresenta gli header della http request
     * @param array $parameters un vettore che rappresenta i parametri della http request
     * @param integer $method una costante tra GET POST PUT DELETE
     * @param bool $returnTransfer se si desidera ricevere la risposta sotto forma di stringa; true di default
     * @param bool $responseHeader se si desidera ignorare gli header della risposta; false di default
     */
    function __construct($url, $headers, $parameters, $method, $returnTransfer = true, $responseHeader = false)
    {
        $this->url = $url;
        $this->headers = $headers;
        $this->parameters = $parameters;
        $this->method = $method;
        
        $this->curlSession = curl_init();
        switch($this->method){
            case GET:
                //get già di default
                break;
            case POST:
                //curl_setopt($this->curlSession, CURLOPT_POST, true);//imposto la richiesta per il post
                curl_setopt($this->curlSession, CURLOPT_CUSTOMREQUEST, 'POST');//imposto la richiesta per il post
                //imposto anche un timeout per la risposta ed un numero massimo
                curl_setopt($this->curlSession, CURLOPT_CONNECTTIMEOUT,10);
                curl_setopt($this->curlSession, CURLOPT_TIMEOUT,30);
                break;
            case PUT:
                curl_setopt($this->curlSession, CURLOPT_CUSTOMREQUEST, 'PUT');
                break;
            case DELETE:
                curl_setopt($this->curlSession, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
        }
        curl_setopt($this->curlSession, CURLOPT_URL, $this->getUrl());//passo l'url
        curl_setopt($this->curlSession, CURLOPT_RETURNTRANSFER, $returnTransfer);//setto o meno la risposta come stringa da salvare in una variabile
        curl_setopt($this->curlSession, CURLOPT_HEADER, $responseHeader);//considero/non considero gli header della risposta
        if($headers != null){
            curl_setopt($this->curlSession, CURLOPT_HTTPHEADER, $this->getHeaders());//setto gli header della richiesra
        }
        if($parameters != null){
            curl_setopt($this->curlSession, CURLOPT_POSTFIELDS, $this->getParameters());//setto i paramentri della richiesra
        }
        return $method;
    }


    /**
     * esegue la richiesta tramite curl e chiude la curl
     * @return mixed la risposta della richiesta, false in caso di errore
     */
    public function execCurl(){
        $result=curl_exec($this->curlSession);//eseguo la richiesta
        curl_close($this->curlSession);//chiudo la curl

        return $result;
    }

    /**
     * Get the value of curlSession
     */
    public function getCurlSession()
    {
        return $this->curlSession;
    }

    /**
     * Get the value of url
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set the value of url
     */
    public function setUrl($url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get the value of headers
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Set the value of headers
     */
    public function setHeaders($headers): self
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * Get the value of parameters
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Set the value of parameters
     */
    public function setParameters($parameters): self
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * Get the value of method
     */
    public function getMethod()
    {
        $method = null;
        switch($this->method){
            case GET:
                $method = "get";
                break;
            case POST:
                $method = "post";
                break;
            case PUT:
                $method = "put";
                break;
            case DELETE:
                $method = "delete";
                break;
        }
        return $method;
    }

    /**
     * Set the value of method
     */
    public function setMethod($method): self
    {
        $this->method = $method;

        return $this;
    }
}