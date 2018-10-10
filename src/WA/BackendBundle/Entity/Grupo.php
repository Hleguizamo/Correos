<?php

namespace WA\BackendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Grupo
 *
 * @ORM\Table(name="grupo", indexes={@ORM\Index(name="fk_administrador_idx", columns={"administrador_id"})})
 * @ORM\Entity
 */
class Grupo
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
     * @ORM\Column(name="nombre", type="string", length=60, nullable=false)
     */
    private $nombre;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_creado", type="datetime", nullable=false)
     */
    private $fechaCreado;

    /**
     * @var string
     *
     * @ORM\Column(name="clasificacion", type="string", length=25, nullable=false)
     */
    private $clasificacion;

    /**
     * @var integer
     *
     * @ORM\Column(name="integrantes", type="integer", nullable=true)
     */
    private $integrantes;

    /**
     * @var string
     *
     * @ORM\Column(name="descripcion", type="string", length=250, nullable=false)
     */
    private $descripcion;

    /**
     * @var \Administrador
     *
     * @ORM\ManyToOne(targetEntity="Administrador")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="administrador_id", referencedColumnName="id")
     * })
     */
    private $administrador;

    /**
     * @var integer
     *
     * @ORM\Column(name="agrupar_proveedor", type="integer", nullable=true)
     */
    private $agruparProveedor;

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
     * @return Grupo
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
     * Set fechaCreado
     *
     * @param \DateTime $fechaCreado
     *
     * @return Grupo
     */
    public function setFechaCreado($fechaCreado)
    {
        $this->fechaCreado = $fechaCreado;

        return $this;
    }

    /**
     * Get fechaCreado
     *
     * @return \DateTime
     */
    public function getFechaCreado()
    {
        return $this->fechaCreado;
    }

    /**
     * Set clasificacion
     *
     * @param string $clasificacion
     *
     * @return Grupo
     */
    public function setClasificacion($clasificacion)
    {
        $this->clasificacion = $clasificacion;

        return $this;
    }

    /**
     * Get clasificacion
     *
     * @return string
     */
    public function getClasificacion()
    {
        return $this->clasificacion;
    }

    /**
     * Set integrantes
     *
     * @param integer $integrantes
     *
     * @return Grupo
     */
    public function setIntegrantes($integrantes)
    {
        $this->integrantes = $integrantes;

        return $this;
    }

    /**
     * Get integrantes
     *
     * @return integer
     */
    public function getIntegrantes()
    {
        return $this->integrantes;
    }

    /**
     * Set descripcion
     *
     * @param string $descripcion
     *
     * @return Grupo
     */
    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    /**
     * Get descripcion
     *
     * @return string
     */
    public function getDescripcion()
    {
        return $this->descripcion;
    }

    /**
     * Set administrador
     *
     * @param \WA\BackendBundle\Entity\Administrador $administrador
     *
     * @return Grupo
     */
    public function setAdministrador(\WA\BackendBundle\Entity\Administrador $administrador = null)
    {
        $this->administrador = $administrador;

        return $this;
    }

    /**
     * Get administrador
     *
     * @return \WA\BackendBundle\Entity\Administrador
     */
    public function getAdministrador()
    {
        return $this->administrador;
    }

    /**
     * Set agruparProveedor
     *
     * @param integer $agruparProveedor
     *
     * @return Grupo
     */
    public function setAgruparProveedor($agruparProveedor)
    {
        $this->agruparProveedor = $agruparProveedor;

        return $this;
    }

    /**
     * Get agruparProveedor
     *
     * @return integer
     */
    public function getAgruparProveedor()
    {
        return $this->agruparProveedor;
    }
}
