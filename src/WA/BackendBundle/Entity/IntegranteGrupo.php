<?php

namespace WA\BackendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * IntegranteGrupo
 *
 * @ORM\Table(name="integrante_grupo", indexes={@ORM\Index(name="fk_grupo_integranteGrupo_idx", columns={"grupo_id"}), @ORM\Index(name="fk_proveedor_integranteGrupo_idx", columns={"proveedor_id"}), @ORM\Index(name="fk_asociado_integranteGrupo_idx", columns={"asociado_id"}), @ORM\Index(name="fk_tipoIntegrante_integranteGrupo_idx", columns={"tipo_integrante_id"})})
 * @ORM\Entity
 */
class IntegranteGrupo
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
     * @ORM\Column(name="proveedor_id", type="integer", nullable=true)
     */
    private $proveedorId;

    /**
     * @var integer
     *
     * @ORM\Column(name="contacto_proveedor_id", type="integer", nullable=true)
     */
    private $contactoProveedorId;

    /**
     * @var boolean
     *
     * @ORM\Column(name="activo", type="boolean", nullable=false)
     */
    private $activo;

    /**
     * @var \Cliente
     *
     * @ORM\ManyToOne(targetEntity="Cliente")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="asociado_id", referencedColumnName="id")
     * })
     */
    private $asociado;

    /**
     * @var \Grupo
     *
     * @ORM\ManyToOne(targetEntity="Grupo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="grupo_id", referencedColumnName="id")
     * })
     */
    private $grupo;

    /**
     * @var \TipoIntegrante
     *
     * @ORM\ManyToOne(targetEntity="TipoIntegrante")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tipo_integrante_id", referencedColumnName="id")
     * })
     */
    private $tipoIntegrante;



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
     * Set proveedorId
     *
     * @param integer $proveedorId
     *
     * @return IntegranteGrupo
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
     * Set contactoProveedorId
     *
     * @param integer $contactoProveedorId
     *
     * @return IntegranteGrupo
     */
    public function setContactoProveedorId($contactoProveedorId)
    {
        $this->contactoProveedorId = $contactoProveedorId;

        return $this;
    }

    /**
     * Get contactoProveedorId
     *
     * @return integer
     */
    public function getContactoProveedorId()
    {
        return $this->contactoProveedorId;
    }

    /**
     * Set activo
     *
     * @param boolean $activo
     *
     * @return IntegranteGrupo
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

    /**
     * Set asociado
     *
     * @param \WA\BackendBundle\Entity\Cliente $asociado
     *
     * @return IntegranteGrupo
     */
    public function setAsociado(\WA\BackendBundle\Entity\Cliente $asociado = null)
    {
        $this->asociado = $asociado;

        return $this;
    }

    /**
     * Get asociado
     *
     * @return \WA\BackendBundle\Entity\Cliente
     */
    public function getAsociado()
    {
        return $this->asociado;
    }

    /**
     * Set grupo
     *
     * @param \WA\BackendBundle\Entity\Grupo $grupo
     *
     * @return IntegranteGrupo
     */
    public function setGrupo(\WA\BackendBundle\Entity\Grupo $grupo = null)
    {
        $this->grupo = $grupo;

        return $this;
    }

    /**
     * Get grupo
     *
     * @return \WA\BackendBundle\Entity\Grupo
     */
    public function getGrupo()
    {
        return $this->grupo;
    }

    /**
     * Set tipoIntegrante
     *
     * @param \WA\BackendBundle\Entity\TipoIntegrante $tipoIntegrante
     *
     * @return IntegranteGrupo
     */
    public function setTipoIntegrante(\WA\BackendBundle\Entity\TipoIntegrante $tipoIntegrante = null)
    {
        $this->tipoIntegrante = $tipoIntegrante;

        return $this;
    }

    /**
     * Get tipoIntegrante
     *
     * @return \WA\BackendBundle\Entity\TipoIntegrante
     */
    public function getTipoIntegrante()
    {
        return $this->tipoIntegrante;
    }
}
