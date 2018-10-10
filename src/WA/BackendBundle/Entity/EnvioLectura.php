<?php

namespace WA\BackendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EnvioLectura
 *
 * @ORM\Table(name="envio_lectura", indexes={@ORM\Index(name="envio_id", columns={"envio_id"}), @ORM\Index(name="envio_integrate_id", columns={"envio_integrate_id"})})
 * @ORM\Entity
 */
class EnvioLectura
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="asunto", type="string", length=200, nullable=false)
     */
    private $asunto;

    /**
     * @var string
     *
     * @ORM\Column(name="integrante", type="string", length=200, nullable=false)
     */
    private $integrante;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=100, nullable=false)
     */
    private $email;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha", type="datetime", nullable=false)
     */
    private $fecha;

    /**
     * @var string
     *
     * @ORM\Column(name="ip", type="string", length=30, nullable=false)
     */
    private $ip;

    /**
     * @var integer
     *
     * @ORM\Column(name="envio_integrate_id", type="integer", nullable=true)
     */
    private $envioIntegrateId;

    /**
     * @var \Envio
     *
     * @ORM\ManyToOne(targetEntity="Envio")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="envio_id", referencedColumnName="id")
     * })
     */
    private $envio;



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
     * Set asunto
     *
     * @param string $asunto
     *
     * @return EnvioLectura
     */
    public function setAsunto($asunto)
    {
        $this->asunto = $asunto;

        return $this;
    }

    /**
     * Get asunto
     *
     * @return string
     */
    public function getAsunto()
    {
        return $this->asunto;
    }

    /**
     * Set integrante
     *
     * @param string $integrante
     *
     * @return EnvioLectura
     */
    public function setIntegrante($integrante)
    {
        $this->integrante = $integrante;

        return $this;
    }

    /**
     * Get integrante
     *
     * @return string
     */
    public function getIntegrante()
    {
        return $this->integrante;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return EnvioLectura
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
     * Set fecha
     *
     * @param \DateTime $fecha
     *
     * @return EnvioLectura
     */
    public function setFecha($fecha)
    {
        $this->fecha = $fecha;

        return $this;
    }

    /**
     * Get fecha
     *
     * @return \DateTime
     */
    public function getFecha()
    {
        return $this->fecha;
    }

    /**
     * Set ip
     *
     * @param string $ip
     *
     * @return EnvioLectura
     */
    public function setIp($ip)
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * Get ip
     *
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Set envioIntegrateId
     *
     * @param integer $envioIntegrateId
     *
     * @return EnvioLectura
     */
    public function setEnvioIntegrateId($envioIntegrateId)
    {
        $this->envioIntegrateId = $envioIntegrateId;

        return $this;
    }

    /**
     * Get envioIntegrateId
     *
     * @return integer
     */
    public function getEnvioIntegrateId()
    {
        return $this->envioIntegrateId;
    }

    /**
     * Set envio
     *
     * @param \WA\BackendBundle\Entity\Envio $envio
     *
     * @return EnvioLectura
     */
    public function setEnvio(\WA\BackendBundle\Entity\Envio $envio = null)
    {
        $this->envio = $envio;

        return $this;
    }

    /**
     * Get envio
     *
     * @return \WA\BackendBundle\Entity\Envio
     */
    public function getEnvio()
    {
        return $this->envio;
    }
}
