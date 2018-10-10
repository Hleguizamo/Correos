<?php

namespace WA\BackendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Contactos
 *
 * @ORM\Table(name="contactos", uniqueConstraints={@ORM\UniqueConstraint(name="email", columns={"email"})}, indexes={@ORM\Index(name="id_proveedor", columns={"id_proveedor"}), @ORM\Index(name="id_cargo", columns={"id_cargo"}), @ORM\Index(name="id_area", columns={"id_area"})})
 * @ORM\Entity
 */
class Contactos
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
     * @ORM\Column(name="nombre_contacto", type="string", length=255, nullable=false)
     */
    private $nombreContacto;

    /**
     * @var string
     *
     * @ORM\Column(name="ciudad", type="string", length=255, nullable=false)
     */
    private $ciudad;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=false)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="telefono", type="string", length=255, nullable=false)
     */
    private $telefono;

    /**
     * @var string
     *
     * @ORM\Column(name="movil", type="string", length=255, nullable=false)
     */
    private $movil;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_creacion", type="datetime", nullable=false)
     */
    private $fechaCreacion = 'CURRENT_TIMESTAMP';

    /**
     * @var \Proveedores
     *
     * @ORM\ManyToOne(targetEntity="Proveedores")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_proveedor", referencedColumnName="id")
     * })
     */
    private $idProveedor;

    /**
     * @var \Cargos
     *
     * @ORM\ManyToOne(targetEntity="Cargos")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_cargo", referencedColumnName="id")
     * })
     */
    private $idCargo;

    /**
     * @var \Areas
     *
     * @ORM\ManyToOne(targetEntity="Areas")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_area", referencedColumnName="id")
     * })
     */
    private $idArea;



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
     * Set nombreContacto
     *
     * @param string $nombreContacto
     *
     * @return Contactos
     */
    public function setNombreContacto($nombreContacto)
    {
        $this->nombreContacto = $nombreContacto;

        return $this;
    }

    /**
     * Get nombreContacto
     *
     * @return string
     */
    public function getNombreContacto()
    {
        return $this->nombreContacto;
    }

    /**
     * Set ciudad
     *
     * @param string $ciudad
     *
     * @return Contactos
     */
    public function setCiudad($ciudad)
    {
        $this->ciudad = $ciudad;

        return $this;
    }

    /**
     * Get ciudad
     *
     * @return string
     */
    public function getCiudad()
    {
        return $this->ciudad;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Contactos
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set telefono
     *
     * @param string $telefono
     *
     * @return Contactos
     */
    public function setTelefono($telefono)
    {
        $this->telefono = $telefono;

        return $this;
    }

    /**
     * Get telefono
     *
     * @return string
     */
    public function getTelefono()
    {
        return $this->telefono;
    }

    /**
     * Set movil
     *
     * @param string $movil
     *
     * @return Contactos
     */
    public function setMovil($movil)
    {
        $this->movil = $movil;

        return $this;
    }

    /**
     * Get movil
     *
     * @return string
     */
    public function getMovil()
    {
        return $this->movil;
    }

    /**
     * Set fechaCreacion
     *
     * @param \DateTime $fechaCreacion
     *
     * @return Contactos
     */
    public function setFechaCreacion($fechaCreacion)
    {
        $this->fechaCreacion = $fechaCreacion;

        return $this;
    }

    /**
     * Get fechaCreacion
     *
     * @return \DateTime
     */
    public function getFechaCreacion()
    {
        return $this->fechaCreacion;
    }

    /**
     * Set idProveedor
     *
     * @param \WA\BackendBundle\Entity\Proveedores $idProveedor
     *
     * @return Contactos
     */
    public function setIdProveedor(\WA\BackendBundle\Entity\Proveedores $idProveedor = null)
    {
        $this->idProveedor = $idProveedor;

        return $this;
    }

    /**
     * Get idProveedor
     *
     * @return \WA\BackendBundle\Entity\Proveedores
     */
    public function getIdProveedor()
    {
        return $this->idProveedor;
    }

    /**
     * Set idCargo
     *
     * @param \WA\BackendBundle\Entity\Cargos $idCargo
     *
     * @return Contactos
     */
    public function setIdCargo(\WA\BackendBundle\Entity\Cargos $idCargo = null)
    {
        $this->idCargo = $idCargo;

        return $this;
    }

    /**
     * Get idCargo
     *
     * @return \WA\BackendBundle\Entity\Cargos
     */
    public function getIdCargo()
    {
        return $this->idCargo;
    }

    /**
     * Set idArea
     *
     * @param \WA\BackendBundle\Entity\Areas $idArea
     *
     * @return Contactos
     */
    public function setIdArea(\WA\BackendBundle\Entity\Areas $idArea = null)
    {
        $this->idArea = $idArea;

        return $this;
    }

    /**
     * Get idArea
     *
     * @return \WA\BackendBundle\Entity\Areas
     */
    public function getIdArea()
    {
        return $this->idArea;
    }
}
