<?php

class Team{
    private $id;
    private $name;
    private $society;
    private $sport;
    private $code;

    function __construct($id, $name, $society, $sport, $code){
        $this->id = $id;
        $this->name = $name;
        $this->society = $society;
        $this->sport = $sport;
        $this->code = $code;
    }    

    /**
     * Get the value of id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id
     */
    public function setId($id): self
    {
        $this->id = $id;

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
     * Get the value of society
     */
    public function getSociety()
    {
        return $this->society;
    }

    /**
     * Set the value of society
     */
    public function setSociety($society): self
    {
        $this->society = $society;

        return $this;
    }

    /**
     * Get the value of sport
     */
    public function getSport()
    {
        return $this->sport;
    }

    /**
     * Set the value of sport
     */
    public function setSport($sport): self
    {
        $this->sport = $sport;

        return $this;
    }

    /**
     * Get the value of code
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set the value of code
     */
    public function setCode($code): self
    {
        $this->code = $code;

        return $this;
    }
}