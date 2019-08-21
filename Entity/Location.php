<?php
// src/NvCarga/Bundle/Entity/Location.php

namespace NvCarga\Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * @ORM\Entity
 * @ORM\Table(name="location")
 * @ORM\HasLifecycleCallbacks()
 */
class Location
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\Column(type="datetime")
     */
    protected $creationdate;
    /**
     * @ORM\ManyToOne(targetEntity="Warehouse", inversedBy="locations")
     * @ORM\JoinColumn(name="warehouse_id", referencedColumnName="id")
     */
    protected $warehouse;
    /**
     * @ORM\Column(type="integer", nullable=false))
     */
    protected $space;
    /**
     * @ORM\Column(type="string", length=20, nullable=false)
     */
    protected $position;
    /**
     * @ORM\Column(type="text")
     */
    protected $comment;
    public function __toString() {
        return (string) $this->getPosition();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set creationdate
     *
     * @param \DateTime $creationdate
     *
     * @return Location
     */
    public function setCreationdate($creationdate)
    {
        $this->creationdate = $creationdate;

        return $this;
    }

    /**
     * Get creationdate
     *
     * @return \DateTime
     */
    public function getCreationdate()
    {
        return $this->creationdate;
    }

    /**
     * Set space
     *
     * @param integer $space
     *
     * @return Location
     */
    public function setSpace($space)
    {
        $this->space = $space;

        return $this;
    }

    /**
     * Get space
     *
     * @return integer
     */
    public function getSpace()
    {
        return $this->space;
    }

    /**
     * Set position
     *
     * @param string $position
     *
     * @return Location
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set comment
     *
     * @param string $comment
     *
     * @return Location
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set warehouse
     *
     * @param \NvCarga\Bundle\Entity\Warehouse $warehouse
     *
     * @return Location
     */
    public function setWarehouse(\NvCarga\Bundle\Entity\Warehouse $warehouse = null)
    {
        $this->warehouse = $warehouse;

        return $this;
    }

    /**
     * Get warehouse
     *
     * @return \NvCarga\Bundle\Entity\Warehouse
     */
    public function getWarehouse()
    {
        return $this->warehouse;
    }

}
