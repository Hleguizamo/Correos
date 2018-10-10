<?php

namespace WA\BackendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DatosEnvio
 *
 * @ORM\Table(name="datos_envio", indexes={@ORM\Index(name="identificador", columns={"identificador"}), @ORM\Index(name="envio_id", columns={"envio_id"})})
 * @ORM\Entity
 */
class DatosEnvio
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
     * @var string
     *
     * @ORM\Column(name="codigo_destinatario", type="string", length=255, nullable=true)
     */
    private $codigoDestinatario;

    /**
     * @var integer
     *
     * @ORM\Column(name="tipo_usuario", type="integer", nullable=true)
     */
    private $tipoUsuario;

    /**
     * @var integer
     *
     * @ORM\Column(name="envio_id", type="integer", nullable=false)
     */
    private $envioId;

    /**
     * @var integer
     *
     * @ORM\Column(name="creador_id", type="integer", nullable=false)
     */
    private $creadorId;

    /**
     * @var string
     *
     * @ORM\Column(name="datos", type="text", length=65535, nullable=true)
     */
    private $datos;

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
     * @return DatosEnvio
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
     * Set codigoDestinatario
     *
     * @param string $codigoDestinatario
     *
     * @return DatosEnvio
     */
    public function setCodigoDestinatario($codigoDestinatario)
    {
        $this->codigoDestinatario = $codigoDestinatario;

        return $this;
    }

    /**
     * Get codigoDestinatario
     *
     * @return string
     */
    public function getCodigoDestinatario()
    {
        return $this->codigoDestinatario;
    }

    /**
     * Set tipoUsuario
     *
     * @param integer $tipoUsuario
     *
     * @return DatosEnvio
     */
    public function setTipoUsuario($tipoUsuario)
    {
        $this->tipoUsuario = $tipoUsuario;

        return $this;
    }

    /**
     * Get tipoUsuario
     *
     * @return integer
     */
    public function getTipoUsuario()
    {
        return $this->tipoUsuario;
    }

    /**
     * Set envioId
     *
     * @param integer $envioId
     *
     * @return DatosEnvio
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
     * Set creadorId
     *
     * @param integer $creadorId
     *
     * @return DatosEnvio
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
     * Set datos
     *
     * @param string $datos
     *
     * @return DatosEnvio
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
     * Set fechaModificado
     *
     * @param \DateTime $fechaModificado
     *
     * @return DatosEnvio
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
     * @return DatosEnvio
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
