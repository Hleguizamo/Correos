<?php

namespace WA\BackendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Envio
 *
 * @ORM\Table(name="envio", indexes={@ORM\Index(name="fk_administrador_envio_idx", columns={"administrador_id"}), @ORM\Index(name="FK_grupo_envio", columns={"grupo_id"}), @ORM\Index(name="fk_administrador_envioCopia", columns={"copia_administrador_id"})})
 * @ORM\Entity
 */
class Envio
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
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_envio", type="datetime", nullable=true)
     */
    private $fechaEnvio;

    /**
     * @var string
     *
     * @ORM\Column(name="asunto", type="string", length=255, nullable=true)
     */
    private $asunto;

    /**
     * @var string
     *
     * @ORM\Column(name="contenido", type="text", length=65535, nullable=true)
     */
    private $contenido;

    /**
     * @var integer
     *
     * @ORM\Column(name="cantidad_enviados", type="integer", nullable=true)
     */
    private $cantidadEnviados;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_creado", type="datetime", nullable=true)
     */
    private $fechaCreado;

    /**
     * @var boolean
     *
     * @ORM\Column(name="estado", type="boolean", nullable=false)
     */
    private $estado;

    /**
     * @var string
     *
     * @ORM\Column(name="combinacion", type="string", length=55, nullable=true)
     */
    private $combinacion;

    /**
     * @var string
     *
     * @ORM\Column(name="columna_combinacion", type="string", length=55, nullable=true)
     */
    private $columnaCombinacion = ' ';

    /**
     * @var integer
     *
     * @ORM\Column(name="adjuntos", type="integer", nullable=true)
     */
    private $adjuntos;

    /**
     * @var integer
     *
     * @ORM\Column(name="programado", type="integer", nullable=true)
     */
    private $programado;

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
     * @var \Administrador
     *
     * @ORM\ManyToOne(targetEntity="Administrador")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="administrador_id", referencedColumnName="id")
     * })
     */
    private $administrador;

    /**
     * @var \Administrador
     *
     * @ORM\ManyToOne(targetEntity="Administrador")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="copia_administrador_id", referencedColumnName="id")
     * })
     */
    private $copiaAdministrador;



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
     * Set fechaEnvio
     *
     * @param \DateTime $fechaEnvio
     *
     * @return Envio
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
     * Set asunto
     *
     * @param string $asunto
     *
     * @return Envio
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
     * Set contenido
     *
     * @param string $contenido
     *
     * @return Envio
     */
    public function setContenido($contenido)
    {
        $this->contenido = $contenido;

        return $this;
    }

    /**
     * Get contenido
     *
     * @return string
     */
    public function getContenido()
    {
        return $this->contenido;
    }

    /**
     * Set cantidadEnviados
     *
     * @param integer $cantidadEnviados
     *
     * @return Envio
     */
    public function setCantidadEnviados($cantidadEnviados)
    {
        $this->cantidadEnviados = $cantidadEnviados;

        return $this;
    }

    /**
     * Get cantidadEnviados
     *
     * @return integer
     */
    public function getCantidadEnviados()
    {
        return $this->cantidadEnviados;
    }

    /**
     * Set fechaCreado
     *
     * @param \DateTime $fechaCreado
     *
     * @return Envio
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
     * Set estado
     *
     * @param boolean $estado
     *
     * @return Envio
     */
    public function setEstado($estado)
    {
        $this->estado = $estado;

        return $this;
    }

    /**
     * Get estado
     *
     * @return boolean
     */
    public function getEstado()
    {
        return $this->estado;
    }

    /**
     * Set combinacion
     *
     * @param string $combinacion
     *
     * @return Envio
     */
    public function setCombinacion($combinacion)
    {
        $this->combinacion = $combinacion;

        return $this;
    }

    /**
     * Get combinacion
     *
     * @return string
     */
    public function getCombinacion()
    {
        return $this->combinacion;
    }

    /**
     * Set columnaCombinacion
     *
     * @param string $columnaCombinacion
     *
     * @return Envio
     */
    public function setColumnaCombinacion($columnaCombinacion)
    {
        $this->columnaCombinacion = $columnaCombinacion;

        return $this;
    }

    /**
     * Get columnaCombinacion
     *
     * @return string
     */
    public function getColumnaCombinacion()
    {
        return $this->columnaCombinacion;
    }

    /**
     * Set adjuntos
     *
     * @param integer $adjuntos
     *
     * @return Envio
     */
    public function setAdjuntos($adjuntos)
    {
        $this->adjuntos = $adjuntos;

        return $this;
    }

    /**
     * Get adjuntos
     *
     * @return integer
     */
    public function getAdjuntos()
    {
        return $this->adjuntos;
    }

    /**
     * Set programado
     *
     * @param integer $programado
     *
     * @return Envio
     */
    public function setProgramado($programado)
    {
        $this->programado = $programado;

        return $this;
    }

    /**
     * Get programado
     *
     * @return integer
     */
    public function getProgramado()
    {
        return $this->programado;
    }

    /**
     * Set grupo
     *
     * @param \WA\BackendBundle\Entity\Grupo $grupo
     *
     * @return Envio
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
     * Set administrador
     *
     * @param \WA\BackendBundle\Entity\Administrador $administrador
     *
     * @return Envio
     */
    public function setAdministrador(\WA\BackendBundle\Entity\Administrador $administrador = null)
    {
        $this->administrador = $administrador;

        return $this;
    }

    /**
     * Get administrador
     *
     * @return \WA\BackendBundle\Entity\Administrador
     */
    public function getAdministrador()
    {
        return $this->administrador;
    }

    /**
     * Set copiaAdministrador
     *
     * @param \WA\BackendBundle\Entity\Administrador $copiaAdministrador
     *
     * @return Envio
     */
    public function setCopiaAdministrador(\WA\BackendBundle\Entity\Administrador $copiaAdministrador = null)
    {
        $this->copiaAdministrador = $copiaAdministrador;

        return $this;
    }

    /**
     * Get copiaAdministrador
     *
     * @return \WA\BackendBundle\Entity\Administrador
     */
    public function getCopiaAdministrador()
    {
        return $this->copiaAdministrador;
    }
}
