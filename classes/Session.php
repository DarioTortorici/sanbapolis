<?php

class Session {
    private $id;
    private $author;
    private $start_date_time;
    private $end_date_time;
    private $reservation;

    function __construct($id, $author, $start_date_time, $end_date_time, $reservation){
        $this->id = $id;
        $this->author = $author;
        $this->start_date_time = $start_date_time;
        $this->end_date_time = $end_date_time;
        $this->reservation = $reservation;
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
     * Get the value of author
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set the value of author
     */
    public function setAuthor($author): self
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get the value of start_date_time
     */
    public function getStartDateTime()
    {
        return $this->start_date_time;
    }

    /**
     * Set the value of start_date_time
     */
    public function setStartDateTime($start_date_time): self
    {
        $this->start_date_time = $start_date_time;

        return $this;
    }

    /**
     * Get the value of end_date_time
     */
    public function getEndDateTime()
    {
        return $this->end_date_time;
    }

    /**
     * Set the value of end_date_time
     */
    public function setEndDateTime($end_date_time): self
    {
        $this->end_date_time = $end_date_time;

        return $this;
    }

    /**
     * Get the value of reservation
     */
    public function getReservation()
    {
        return $this->reservation;
    }

    /**
     * Set the value of reservation
     */
    public function setReservation($reservation): self
    {
        $this->reservation = $reservation;

        return $this;
    }
}