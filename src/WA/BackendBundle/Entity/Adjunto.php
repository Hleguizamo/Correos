<?php

namespace WA\BackendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Adjunto
 *
 * @ORM\Table(name="adjunto", uniqueConstraints={@ORM\UniqueConstraint(name="id", columns={"id"})}, indexes={@ORM\Index(name="id_envio", columns={"envio_id"})})
 * @ORM\Entity
 */
class Adjunto
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
     * @ORM\Column(name="adjunto", type="text", length=65535, nullable=false)
     */
    private $adjunto;

    /**
     * @var string
     *
     * @ORM\Column(name="tipo", type="string", length=200, nullable=false)
     */
    private $tipo;

    /**
     * @var integer
     *
     * @ORM\Column(name="peso", type="integer", nullable=false)
     */
    private $peso;

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
     * Set adjunto
     *
     * @param string $adjunto
     *
     * @return Adjunto
     */
    public function setAdjunto($adjunto)
    {
        $this->adjunto = $adjunto;

        return $this;
    }

    /**
     * Get adjunto
     *
     * @return string
     */
    public function getAdjunto()
    {
        return $this->adjunto;
    }

    /**
     * Set tipo
     *
     * @param string $tipo
     *
     * @return Adjunto
     */
    public function setTipo($tipo)
    {
        $this->tipo = $tipo;

        return $this;
    }

    /**
     * Get tipo
     *
     * @return string
     */
    public function getTipo()
    {
        return $this->tipo;
    }

    /**
     * Set peso
     *
     * @param integer $peso
     *
     * @return Adjunto
     */
    public function setPeso($peso)
    {
        $this->peso = $peso;

        return $this;
    }

    /**
     * Get peso
     *
     * @return integer
     */
    public function getPeso()
    {
        return $this->peso;
    }

    /**
     * Set envio
     *
     * @param \WA\BackendBundle\Entity\Envio $envio
     *
     * @return Adjunto
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
