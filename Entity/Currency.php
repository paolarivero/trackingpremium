<?php
// src/NvCarga/Bundle/Entity/Currency.php

namespace NvCarga\Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="currency")
 * @ORM\HasLifecycleCallbacks()
 */
class Currency
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id; // Identificador de la entidad
     /**
     * @Assert\NotBlank(message = "Asigne el nombre del país")
     * @Assert\Length(
     *      min = 1,
     *      max = 100,
     *      minMessage = "El nombre del país debe tener al menos {{ limit }} caracteres",
     *      maxMessage = "El nombre del país no puede tener mas de {{ limit }} caracteres"
     * )
     * @ORM\Column(type="string", length=100, nullable=false)
     */
    protected $country;
    /**
     * @Assert\NotBlank(message = "Asigne el nombre del moneda")
     * @Assert\Length(
     *      min = 1,
     *      max = 100,
     *      minMessage = "El nombre de la moneda debe tener al menos {{ limit }} caracteres",
     *      maxMessage = "El nombre de la moneda no puede tener mas de {{ limit }} caracteres"
     * )
     * @ORM\Column(type="string", length=100, nullable=false)
     */
    protected $currency;
    /**
     * @Assert\NotBlank(message = "Asigne el códigodel moneda")
     * @Assert\Length(
     *      min = 1,
     *      max = 100,
     *      minMessage = "El código de la moneda debe tener al menos {{ limit }} caracteres",
     *      maxMessage = "El código de la moneda no puede tener mas de {{ limit }} caracteres"
     * )
     * @ORM\Column(type="string", length=100, nullable=false)
     */
    protected $code;
    /**
     * @Assert\NotBlank(message = "Asigne el símbolo del moneda")
     * @Assert\Length(
     *      min = 1,
     *      max = 100,
     *      minMessage = "El símbolo de la moneda debe tener al menos {{ limit }} caracteres",
     *      maxMessage = "El símbolo de la moneda no puede tener mas de {{ limit }} caracteres"
     * )
     * @ORM\Column(type="string", length=100, nullable=false)
     */
    protected $symbol;
    
    public function __toString() {
        return (string) ($this->getCode() . " (". $this->getCountry() . ")");
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
     * Set country
     *
     * @param string $country
     *
     * @return Currency
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set currency
     *
     * @param string $currency
     *
     * @return Currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * Get currency
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return Currency
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set symbol
     *
     * @param string $symbol
     *
     * @return Currency
     */
    public function setSymbol($symbol)
    {
        $this->symbol = $symbol;

        return $this;
    }

    /**
     * Get symbol
     *
     * @return string
     */
    public function getSymbol()
    {
        return $this->symbol;
    }
}
