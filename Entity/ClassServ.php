<?php
// src/NvCarga/Bundle/Entity/ClassServ.php

namespace NvCarga\Bundle\Entity;

class ClassServ
{
    protected $id;
    protected $name;
    protected $description;
    protected $measure;
    protected $price;
    protected $amount;
    protected $total;
    protected $medependof;

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

   public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

   public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setMeasure($measure)
    {
        $this->measure = $measure;

        return $this;
    }

    public function getMeasure()
    {
        return $this->measure;
    }

   public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    public function getPrice()
    {
        return $this->price;
    }

   public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    public function getAmount()
    {
        return $this->amount;
    }

   public function setTotal($total)
    {
        $this->total = $total;

        return $this;
    }

    public function getTotal()
    {
        return $this->total;
    }
}
