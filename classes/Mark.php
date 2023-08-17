<?php

class Mark {
	private $id;
	private $timing;
	private $name;
	private $note;
	private $path_video;
	
	
	public function __construct($timing, $name, $note, $path_video = null, $id = null) {
        $this->timing = $timing;
        $this->name = $name;
        $this->note = $note;
		$this->id = $id;
        $this->path_video = $path_video;
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
	 * Get the value of timing
	 */
	public function getTiming()
	{
		return $this->timing;
	}

	/**
	 * Set the value of timing
	 */
	public function setTiming($timing): self
	{
		$this->timing = $timing;

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