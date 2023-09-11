<?php

class Reservation {
    private $id;
    private $author;
    private $start_date_time;
    private $end_date_time;
    private $team;
    private $calendar_event;
    private $note;

    function __construct($id, $author, $start_date_time, $end_date_time, $team, $calendar_event, $note){
        $this->id = $id;
        $this->author = $author;
        $this->start_date_time = $start_date_time;
        $this->end_date_time = $end_date_time;
        $this->team = $team;
        $this->calendar_event = $calendar_event;
        $this->note = $note;
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
     * Get the value of calendar_event
     */
    public function getCalendarEvent()
    {
        return $this->calendar_event;
    }

    /**
     * Set the value of calendar_event
     */
    public function setCalendarEvent($calendar_event): self
    {
        $this->calendar_event = $calendar_event;

        return $this;
    }

    /**
     * Get the value of note
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * Set the value of note
     */
    public function setNote($note): self
    {
        $this->note = $note;

        return $this;
    }
}