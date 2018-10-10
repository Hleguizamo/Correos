<?php

namespace WA\BackendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EnvioIntegrante
 *
 * @ORM\Table(name="envio_integrante", indexes={@ORM\Index(name="fk_envio_envioIntegrante_idx", columns={"envio_id"}), @ORM\Index(name="fk_integranteGrupo_envioIntegrante_idx", columns={"integrante_grupo_id"})})
 * @ORM\Entity
 */
class EnvioIntegrante
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
     * @var boolean
     *
     * @ORM\Column(name="enviado", type="boolean", nullable=false)
     */
    private $enviado;

    /**
     * @var integer
     *
     * @ORM\Column(name="reenviado", type="integer", nullable=true)
     */
    private $reenviado = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_envio", type="datetime", nullable=false)
     */
    private $fechaEnvio;

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
     * @var \IntegranteGrupo
     *
     * @ORM\ManyToOne(targetEntity="IntegranteGrupo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="integrante_grupo_id", referencedColumnName="id")
     * })
     */
    private $integranteGrupo;



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
     * Set enviado
     *
     * @param boolean $enviado
     *
     * @return EnvioIntegrante
     */
    public function setEnviado($enviado)
    {
        $this->enviado = $enviado;

        return $this;
    }

    /**
     * Get enviado
     *
     * @return boolean
     */
    public function getEnviado()
    {
        return $this->enviado;
    }

    /**
     * Set reenviado
     *
     * @param integer $reenviado
     *
     * @return EnvioIntegrante
     */
    public function setReenviado($reenviado)
    {
        $this->reenviado = $reenviado;

        return $this;
    }

    /**
     * Get reenviado
     *
     * @return integer
     */
    public function getReenviado()
    {
        return $this->reenviado;
    }

    /**
     * Set fechaEnvio
     *
     * @param \DateTime $fechaEnvio
     *
     * @return EnvioIntegrante
     */
    public function setFechaEnvio($fechaEnvio)
    {
        $this->fechaEnvio = $fechaEnvio;

        return $this;
    }

    /**
     * Get fechaEnvio
     *
     * @return \DateTime
     */
    public function getFechaEnvio()
    {
        return $this->fechaEnvio;
    }

    /**
     * Set envio
     *
     * @param \WA\BackendBundle\Entity\Envio $envio
     *
     * @return EnvioIntegrante
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

    /**
     * Set integranteGrupo
     *
     * @param \WA\BackendBundle\Entity\IntegranteGrupo $integranteGrupo
     *
     * @return EnvioIntegrante
     */
    public function setIntegranteGrupo(\WA\BackendBundle\Entity\IntegranteGrupo $integranteGrupo = null)
    {
        $this->integranteGrupo = $integranteGrupo;

        return $this;
    }

    /**
     * Get integranteGrupo
     *
     * @return \WA\BackendBundle\Entity\IntegranteGrupo
     */
    public function getIntegranteGrupo()
    {
        return $this->integranteGrupo;
    }
}
