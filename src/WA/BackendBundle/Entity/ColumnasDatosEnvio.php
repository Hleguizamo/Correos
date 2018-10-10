<?php

namespace WA\BackendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ColumnasDatosEnvio
 *
 * @ORM\Table(name="columnas_datos_envio", indexes={@ORM\Index(name="identificador", columns={"identificador"}), @ORM\Index(name="envio_id", columns={"envio_id"})})
 * @ORM\Entity
 */
class ColumnasDatosEnvio
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
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_creado", type="datetime", nullable=false)
     */
    private $fechaCreado;

    /**
     * @var integer
     *
     * @ORM\Column(name="envio_id", type="integer", nullable=false)
     */
    private $envioId;

    /**
     * @var string
     *
     * @ORM\Column(name="datos", type="text", length=65535, nullable=false)
     */
    private $datos;

    /**
     * @var integer
     *
     * @ORM\Column(name="creador_id", type="integer", nullable=false)
     */
    private $creadorId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_modificado", type="datetime", nullable=false)
     */
    private $fechaModificado;

    /**
     * @var string
     *
     * @ORM\Column(name="identificador", type="string", length=30, nullable=true)
     */
    private $identificador;



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
     * Set fechaCreado
     *
     * @param \DateTime $fechaCreado
     *
     * @return ColumnasDatosEnvio
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
     * Set envioId
     *
     * @param integer $envioId
     *
     * @return ColumnasDatosEnvio
     */
    public function setEnvioId($envioId)
    {
        $this->envioId = $envioId;

        return $this;
    }

    /**
     * Get envioId
     *
     * @return integer
     */
    public function getEnvioId()
    {
        return $this->envioId;
    }

    /**
     * Set datos
     *
     * @param string $datos
     *
     * @return ColumnasDatosEnvio
     */
    public function setDatos($datos)
    {
        $this->datos = $datos;

        return $this;
    }

    /**
     * Get datos
     *
     * @return string
     */
    public function getDatos()
    {
        return $this->datos;
    }

    /**
     * Set creadorId
     *
     * @param integer $creadorId
     *
     * @return ColumnasDatosEnvio
     */
    public function setCreadorId($creadorId)
    {
        $this->creadorId = $creadorId;

        return $this;
    }

    /**
     * Get creadorId
     *
     * @return integer
     */
    public function getCreadorId()
    {
        return $this->creadorId;
    }

    /**
     * Set fechaModificado
     *
     * @param \DateTime $fechaModificado
     *
     * @return ColumnasDatosEnvio
     */
    public function setFechaModificado($fechaModificado)
    {
        $this->fechaModificado = $fechaModificado;

        return $this;
    }

    /**
     * Get fechaModificado
     *
     * @return \DateTime
     */
    public function getFechaModificado()
    {
        return $this->fechaModificado;
    }

    /**
     * Set identificador
     *
     * @param string $identificador
     *
     * @return ColumnasDatosEnvio
     */
    public function setIdentificador($identificador)
    {
        $this->identificador = $identificador;

        return $this;
    }

    /**
     * Get identificador
     *
     * @return string
     */
    public function getIdentificador()
    {
        return $this->identificador;
    }
}
