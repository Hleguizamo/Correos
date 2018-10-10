<?php

namespace WA\BackendBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * Administrador
 *
 * @ORM\Table(name="administrador")
 * @ORM\Entity
 */
class Administrador implements UserInterface
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
     * @var string
     *
     * @ORM\Column(name="nombre", type="string", length=45, nullable=false)
     */
    private $nombre;

    /**
     * @var string
     *
     * @ORM\Column(name="usuario", type="string", length=45, nullable=false)
     */
    private $usuario;

    /**
     * @var string
     *
     * @ORM\Column(name="clave", type="string", length=100, nullable=false)
     */
    private $clave;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=45, nullable=true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="clave_email", type="string", length=100, nullable=false)
     */
    private $claveEmail;

    /**
     * @var boolean
     *
     * @ORM\Column(name="seguimiento", type="boolean", nullable=true)
     */
    private $seguimiento;

    /**
     * @var boolean
     *
     * @ORM\Column(name="tipo_usuario", type="boolean", nullable=true)
     */
    private $tipoUsuario;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_creado", type="datetime", nullable=false)
     */
    private $fechaCreado;

    /**
     * @var integer
     *
     * @ORM\Column(name="creador_id", type="integer", nullable=false)
     */
    private $creadorId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_ultimo_ingreso", type="datetime", nullable=true)
     */
    private $fechaUltimoIngreso;

    /**
     * @var string
     *
     * @ORM\Column(name="ultima_ip", type="string", length=30, nullable=true)
     */
    private $ultimaIp;

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
     * Set nombre
     *
     * @param string $nombre
     *
     * @return Administrador
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
     * Set usuario
     *
     * @param string $usuario
     *
     * @return Administrador
     */
    public function setUsuario($usuario)
    {
        $this->usuario = $usuario;

        return $this;
    }

    /**
     * Get usuario
     *
     * @return string
     */
    public function getUsuario()
    {
        return $this->usuario;
    }

    /**
     * Set clave
     *
     * @param string $clave
     *
     * @return Administrador
     */
    public function setClave($clave)
    {
        $this->clave = $clave;

        return $this;
    }

    /**
     * Get clave
     *
     * @return string
     */
    public function getClave()
    {
        return $this->clave;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Administrador
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
     * Get claveEmail
     *
     * @return string
     */
    public function getClaveEmail()
    {
        return $this->claveEmail;
    }

    /**
     * Set claveEmail
     *
     * @param string $claveEmail
     *
     * @return Administrador
     */
    public function setClaveEmail($claveEmail)
    {
        $this->claveEmail = $claveEmail;

        return $this;
    }

    /**
     * Set seguimiento
     *
     * @param boolean $seguimiento
     *
     * @return Administrador
     */
    public function setSeguimiento($seguimiento)
    {
        $this->seguimiento = $seguimiento;

        return $this;
    }

    /**
     * Get seguimiento
     *
     * @return boolean
     */
    public function getSeguimiento()
    {
        return $this->seguimiento;
    }

    /**
     * Set tipoUsuario
     *
     * @param boolean $tipoUsuario
     *
     * @return Administrador
     */
    public function setTipoUsuario($tipoUsuario)
    {
        $this->tipoUsuario = $tipoUsuario;

        return $this;
    }

    /**
     * Get tipoUsuario
     *
     * @return boolean
     */
    public function getTipoUsuario()
    {
        return $this->tipoUsuario;
    }

    /**
     * Set fechaCreado
     *
     * @param \DateTime $fechaCreado
     *
     * @return Administrador
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
     * Set creadorId
     *
     * @param integer $creadorId
     *
     * @return Administrador
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
     * Set fechaUltimoIngreso
     *
     * @param \DateTime $fechaUltimoIngreso
     *
     * @return Administrador
     */
    public function setFechaUltimoIngreso($fechaUltimoIngreso)
    {
        $this->fechaUltimoIngreso = $fechaUltimoIngreso;

        return $this;
    }

    /**
     * Get fechaUltimoIngreso
     *
     * @return \DateTime
     */
    public function getFechaUltimoIngreso()
    {
        return $this->fechaUltimoIngreso;
    }

    /**
     * Set ultimaIp
     *
     * @param string $ultimaIp
     *
     * @return Administrador
     */
    public function setUltimaIp($ultimaIp)
    {
        $this->ultimaIp = $ultimaIp;

        return $this;
    }

    /**
     * Get ultimaIp
     *
     * @return string
     */
    public function getUltimaIp()
    {
        return $this->ultimaIp;
    }

    /**
     * Set activo
     *
     * @param boolean $activo
     *
     * @return Administrador
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

    public function eraseCredentials() {
      $this->password = null;
    }

    public function getPassword() {
        return $this->getClave();
    }

    public function getRoles() {
        return array('ROLE_ADMINISTRADOR');
    }

    public function getSalt() {
        
    }

    public function getUsername() {
        return $this->getUsuario();
    }

    public function isAccountNonExpired() {
        return true;
    }

    public function isAccountNonLocked() {
        return true;
    }

    public function isCredentialsNonExpired() {
        return true;
    }

    public function isEnabled() {
        return true;
    }
}
