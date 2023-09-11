<?php

class Bucket{
    private $url;
    private $name;
    private $token;
    private $team;

    function __construct($url, $name, $token, $team){
        $this->url = $url;
        $this->name = $name;
        $this->token = $token;
        $this->team = $team;
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
     * Get the value of name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of name
     */
    public function setName($name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of token
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set the value of token
     */
    public function setToken($token): self
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get the value of team
     */
    public function getTeam()
    {
        return $this->team;
    }

    /**
     * Set the value of team
     */
    public function setTeam($team): self
    {
        $this->team = $team;

        return $this;
    }
}
