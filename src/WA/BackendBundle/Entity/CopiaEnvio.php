<?php

namespace WA\BackendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CopiaEnvio
 *
 * @ORM\Table(name="copia_envio", indexes={@ORM\Index(name="fk_grupo_integranteGrupo_idx", columns={"envio_id"}), @ORM\Index(name="fk_proveedor_integranteGrupo_idx", columns={"proveedor_id"}), @ORM\Index(name="fk_asociado_integranteGrupo_idx", columns={"asociado_id"})})
 * @ORM\Entity
 */
class CopiaEnvio
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
     * @var integer
     *
     * @ORM\Column(name="asociado_id", type="bigint", nullable=true)
     */
    private $asociadoId;

    /**
     * @var integer
     *
     * @ORM\Column(name="proveedor_id", type="integer", nullable=true)
     */
    private $proveedorId;

    /**
     * @var integer
     *
     * @ORM\Column(name="envio_id", type="bigint", nullable=false)
     */
    private $envioId;

    /**
     * @var boolean
     *
     * @ORM\Column(name="activo", type="boolean", nullable=false)
     */
    private $activo;



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
     * Set asociadoId
     *
     * @param integer $asociadoId
     *
     * @return CopiaEnvio
     */
    public function setAsociadoId($asociadoId)
    {
        $this->asociadoId = $asociadoId;

        return $this;
    }

    /**
     * Get asociadoId
     *
     * @return integer
     */
    public function getAsociadoId()
    {
        return $this->asociadoId;
    }

    /**
     * Set proveedorId
     *
     * @param integer $proveedorId
     *
     * @return CopiaEnvio
     */
    public function setProveedorId($proveedorId)
    {
        $this->proveedorId = $proveedorId;

        return $this;
    }

    /**
     * Get proveedorId
     *
     * @return integer
     */
    public function getProveedorId()
    {
        return $this->proveedorId;
    }

    /**
     * Set envioId
     *
     * @param integer $envioId
     *
     * @return CopiaEnvio
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
     * Set activo
     *
     * @param boolean $activo
     *
     * @return CopiaEnvio
     */
    public function setActivo($activo)
    {
        $this->activo = $activo;

        return $this;
    }

    /**
     * Get activo
     *
     * @return boolean
     */
    public function getActivo()
    {
        return $this->activo;
    }
}
