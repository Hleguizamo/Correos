<?php

namespace WA\BackendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Proveedores
 *
 * @ORM\Table(name="proveedores")
 * @ORM\Entity
 * @UniqueEntity(fields={"nit","codigo","emailRepresentanteLegal"}, message="Ya existe un registro con este Nit y/o Código y/o Email registrado")
 */
class Proveedores
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
     * @var integer
     *
     * @ORM\Column(name="nit", type="integer", nullable=false)
     */
    private $nit;

    /**
     * @var string
     *
     * @ORM\Column(name="nombre", type="string", length=256, nullable=false)
     */
    private $nombre;

    /**
     * @var string
     *
     * @ORM\Column(name="codigo", type="string", length=32, nullable=false)
     */
    private $codigo;

    /**
     * @var string
     *
     * @ORM\Column(name="representante_legal", type="string", length=255, nullable=false)
     */
    private $representanteLegal;

    /**
     * @var string
     *
     * @ORM\Column(name="email_representante_legal", type="string", length=255, nullable=false)
     */
    private $emailRepresentanteLegal;

    /**
     * @var string
     *
     * @ORM\Column(name="telefono_representante_legal", type="string", length=255, nullable=false)
     */
    private $telefonoRepresentanteLegal;



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
     * Set nit
     *
     * @param integer $nit
     *
     * @return Proveedores
     */
    public function setNit($nit)
    {
        $this->nit = $nit;

        return $this;
    }

    /**
     * Get nit
     *
     * @return integer
     */
    public function getNit()
    {
        return $this->nit;
    }

    /**
     * Set nombre
     *
     * @param string $nombre
     *
     * @return Proveedores
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
     * Set codigo
     *
     * @param string $codigo
     *
     * @return Proveedores
     */
    public function setCodigo($codigo)
    {
        $this->codigo = $codigo;

        return $this;
    }

    /**
     * Get codigo
     *
     * @return string
     */
    public function getCodigo()
    {
        return $this->codigo;
    }

    /**
     * Set representanteLegal
     *
     * @param string $representanteLegal
     *
     * @return Proveedores
     */
    public function setRepresentanteLegal($representanteLegal)
    {
        $this->representanteLegal = $representanteLegal;

        return $this;
    }

    /**
     * Get representanteLegal
     *
     * @return string
     */
    public function getRepresentanteLegal()
    {
        return $this->representanteLegal;
    }

    /**
     * Set emailRepresentanteLegal
     *
     * @param string $emailRepresentanteLegal
     *
     * @return Proveedores
     */
    public function setEmailRepresentanteLegal($emailRepresentanteLegal)
    {
        $this->emailRepresentanteLegal = $emailRepresentanteLegal;

        return $this;
    }

    /**
     * Get emailRepresentanteLegal
     *
     * @return string
     */
    public function getEmailRepresentanteLegal()
    {
        return $this->emailRepresentanteLegal;
    }

    /**
     * Set telefonoRepresentanteLegal
     *
     * @param string $telefonoRepresentanteLegal
     *
     * @return Proveedores
     */
    public function setTelefonoRepresentanteLegal($telefonoRepresentanteLegal)
    {
        $this->telefonoRepresentanteLegal = $telefonoRepresentanteLegal;

        return $this;
    }

    /**
     * Get telefonoRepresentanteLegal
     *
     * @return string
     */
    public function getTelefonoRepresentanteLegal()
    {
        return $this->telefonoRepresentanteLegal;
    }
}
