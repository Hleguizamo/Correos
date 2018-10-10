<?php

namespace WA\BackendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Cargos
 *
 * @ORM\Table(name="cargos")
 * @ORM\Entity
 */
class Cargos
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="nombre", type="string", length=256, nullable=false)
     */
    private $nombre;

    /**
     * @var integer
     *
     * @ORM\Column(name="sin_seguimiento", type="integer", nullable=true)
     */
    private $sinSeguimiento;



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
     * Set nombre
     *
     * @param string $nombre
     *
     * @return Cargos
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * Get nombre
     *
     * @return string
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Set sinSeguimiento
     *
     * @param integer $sinSeguimiento
     *
     * @return Cargos
     */
    public function setSinSeguimiento($sinSeguimiento)
    {
        $this->sinSeguimiento = $sinSeguimiento;

        return $this;
    }

    /**
     * Get sinSeguimiento
     *
     * @return integer
     */
    public function getSinSeguimiento()
    {
        return $this->sinSeguimiento;
    }
}
