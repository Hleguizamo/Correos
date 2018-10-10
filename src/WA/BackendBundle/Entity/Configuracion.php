<?php

namespace WA\BackendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Configuracion
 *
 * @ORM\Table(name="configuracion")
 * @ORM\Entity
 */
class Configuracion
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
     * @ORM\Column(name="envio_inmediato", type="integer", nullable=false)
     */
    private $envioInmediato;

    /**
     * @var integer
     *
     * @ORM\Column(name="envio_programado", type="integer", nullable=false)
     */
    private $envioProgramado;



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
     * Set envioInmediato
     *
     * @param integer $envioInmediato
     *
     * @return Configuracion
     */
    public function setEnvioInmediato($envioInmediato)
    {
        $this->envioInmediato = $envioInmediato;

        return $this;
    }

    /**
     * Get envioInmediato
     *
     * @return integer
     */
    public function getEnvioInmediato()
    {
        return $this->envioInmediato;
    }

    /**
     * Set envioProgramado
     *
     * @param integer $envioProgramado
     *
     * @return Configuracion
     */
    public function setEnvioProgramado($envioProgramado)
    {
        $this->envioProgramado = $envioProgramado;

        return $this;
    }

    /**
     * Get envioProgramado
     *
     * @return integer
     */
    public function getEnvioProgramado()
    {
        return $this->envioProgramado;
    }
}
