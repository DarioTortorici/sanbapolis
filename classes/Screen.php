<?php

class Screen {
    private $id;
    private $path;
    private $name;
    private $note;
    private $path_video;

    public function __construct($path, $name, $note, $id = null, $path_video = null) {
        $this->path = $path;
        $this->name = $name;
        $this->note = $note;
        $this->path_video = $path_video;
        $this->id = $id;
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
     * Get the value of path_video
     */
    public function getPathVideo()
    {
        return $this->path_video;
    }

    /**
     * Set the value of path_video
     */
    public function setPathVideo($path_video): self
    {
        $this->path_video = $path_video;

        return $this;
    }
}