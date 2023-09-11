<?php

class Bucket{
    private $url;
    private $name;
    private $token;
    private $team;
    private $org;
    private $db;

    function __construct($url, $name, $token, $team, $org, $db){
        $this->url = $url;
        $this->name = $name;
        $this->token = $token;
        $this->team = $team;
        $this->org = $org;
        $this->db = $db;
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

    /**
     * Get the value of org
     */
    public function getOrg()
    {
        return $this->org;
    }

    /**
     * Set the value of org
     */
    public function setOrg($org): self
    {
        $this->org = $org;

        return $this;
    }

    /**
     * Get the value of db
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * Set the value of db
     */
    public function setDb($db): self
    {
        $this->db = $db;

        return $this;
    }
}
