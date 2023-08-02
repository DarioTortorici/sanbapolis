<?php

class Person{
    protected $id;
    protected $email;
    protected $name;
    protected $surname;
    protected $birthday;
    protected $city;
    protected $address;
    protected $telephone_number;

    public function __construct($id, $email, $name, $surname, $birthday, $city, $address, $telephone_number){
        $this->id = $id;
        $this->email = $email;
        $this->name = $name;
        $this->surname = $surname;
        $this->birthday = $birthday;
        $this->city = $city;
        $this->address = $address;
        $this->telephone_number = $telephone_number;
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
     * Get the value of email
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set the value of email
     */
    public function setEmail($email): self
    {
        $this->email = $email;

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
     * Get the value of surname
     */
    public function getSurname()
    {
        return $this->surname;
    }

    /**
     * Set the value of surname
     */
    public function setSurname($surname): self
    {
        $this->surname = $surname;

        return $this;
    }

    /**
     * Get the value of birthday
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * Set the value of birthday
     */
    public function setBirthday($birthday): self
    {
        $this->birthday = $birthday;

        return $this;
    }

    /**
     * Get the value of city
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set the value of city
     */
    public function setCity($city): self
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get the value of address
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set the value of address
     */
    public function setAddress($address): self
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get the value of telephone_number
     */
    public function getTelephoneNumber()
    {
        return $this->telephone_number;
    }

    /**
     * Set the value of telephone_number
     */
    public function setTelephoneNumber($telephone_number): self
    {
        $this->telephone_number = $telephone_number;

        return $this;
    }
}

?>