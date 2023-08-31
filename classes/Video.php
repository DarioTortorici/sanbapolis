<?php

class Video {
    private $id;
    private $path;
    private $name;
    private $author;
    private $note;
    private $session;
    private $camera;

    public function __construct($id, $path, $name, $note, $author, $session, $camera) {
        $this->id = $id;
        $this->path = $path;
        $this->name = $name;
        $this->note = $note;
        $this->author = $author;
        $this->session = $session;
        $this->camera = $camera;
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
     * Get the value of path
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set the value of path
     */
    public function setPath($path): self
    {
        $this->path = $path;

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

    /**
     * Get the value of session
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * Set the value of session
     */
    public function setSession($session): self
    {
        $this->session = $session;

        return $this;
    }

    /**
     * Get the value of camera
     */
    public function getCamera()
    {
        return $this->camera;
    }

    /**
     * Set the value of camera
     */
    public function setCamera($camera): self
    {
        $this->camera = $camera;

        return $this;
    }
}