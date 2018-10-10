<?php

namespace WA\BackendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Cliente
 *
 * @ORM\Table(name="cliente", indexes={@ORM\Index(name="codigo_idx", columns={"codigo"})})
 * @ORM\Entity
 */
class Cliente
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
     * @ORM\Column(name="zona", type="string", length=100, nullable=false)
     */
    private $zona;

    /**
     * @var integer
     *
     * @ORM\Column(name="codigo", type="integer", nullable=true)
     */
    private $codigo;

    /**
     * @var string
     *
     * @ORM\Column(name="bloqueo_d", type="string", length=10, nullable=false)
     */
    private $bloqueoD;

    /**
     * @var string
     *
     * @ORM\Column(name="drogueria", type="string", length=100, nullable=false)
     */
    private $drogueria;

    /**
     * @var string
     *
     * @ORM\Column(name="nit", type="string", length=100, nullable=false)
     */
    private $nit;

    /**
     * @var string
     *
     * @ORM\Column(name="bloqueo_r", type="string", length=100, nullable=false)
     */
    private $bloqueoR;

    /**
     * @var string
     *
     * @ORM\Column(name="asociado", type="string", length=100, nullable=false)
     */
    private $asociado;

    /**
     * @var string
     *
     * @ORM\Column(name="nit_dv", type="string", length=100, nullable=false)
     */
    private $nitDv;

    /**
     * @var string
     *
     * @ORM\Column(name="direcion", type="string", length=100, nullable=false)
     */
    private $direcion;

    /**
     * @var string
     *
     * @ORM\Column(name="cod_mun", type="string", length=100, nullable=false)
     */
    private $codMun;

    /**
     * @var string
     *
     * @ORM\Column(name="ciudad", type="string", length=100, nullable=false)
     */
    private $ciudad;

    /**
     * @var string
     *
     * @ORM\Column(name="ruta", type="string", length=100, nullable=false)
     */
    private $ruta;

    /**
     * @var string
     *
     * @ORM\Column(name="un_geogra", type="string", length=100, nullable=false)
     */
    private $unGeogra;

    /**
     * @var string
     *
     * @ORM\Column(name="depto", type="string", length=100, nullable=false)
     */
    private $depto;

    /**
     * @var string
     *
     * @ORM\Column(name="telefono", type="string", length=100, nullable=false)
     */
    private $telefono;

    /**
     * @var string
     *
     * @ORM\Column(name="centro", type="string", length=100, nullable=false)
     */
    private $centro;

    /**
     * @var string
     *
     * @ORM\Column(name="p_s", type="string", length=100, nullable=false)
     */
    private $pS;

    /**
     * @var string
     *
     * @ORM\Column(name="p_carga", type="string", length=100, nullable=false)
     */
    private $pCarga;

    /**
     * @var string
     *
     * @ORM\Column(name="diskette", type="string", length=100, nullable=false)
     */
    private $diskette;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="cliente_tiempo", type="datetime", nullable=false)
     */
    private $clienteTiempo = '0000-00-00 00:00:00';

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=100, nullable=false)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="email_asociado", type="string", length=100, nullable=false)
     */
    private $emailAsociado;

    /**
     * @var string
     *
     * @ORM\Column(name="tipo_cliente", type="string", length=1, nullable=false)
     */
    private $tipoCliente;

    /**
     * @var string
     *
     * @ORM\Column(name="cupo_asociado", type="string", length=100, nullable=false)
     */
    private $cupoAsociado;

    /**
     * @var integer
     *
     * @ORM\Column(name="estado", type="integer", nullable=false)
     */
    private $estado = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="centro_costo", type="string", length=100, nullable=true)
     */
    private $centroCosto;



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
     * Set zona
     *
     * @param string $zona
     *
     * @return Cliente
     */
    public function setZona($zona)
    {
        $this->zona = $zona;

        return $this;
    }

    /**
     * Get zona
     *
     * @return string
     */
    public function getZona()
    {
        return $this->zona;
    }

    /**
     * Set codigo
     *
     * @param integer $codigo
     *
     * @return Cliente
     */
    public function setCodigo($codigo)
    {
        $this->codigo = $codigo;

        return $this;
    }

    /**
     * Get codigo
     *
     * @return integer
     */
    public function getCodigo()
    {
        return $this->codigo;
    }

    /**
     * Set bloqueoD
     *
     * @param string $bloqueoD
     *
     * @return Cliente
     */
    public function setBloqueoD($bloqueoD)
    {
        $this->bloqueoD = $bloqueoD;

        return $this;
    }

    /**
     * Get bloqueoD
     *
     * @return string
     */
    public function getBloqueoD()
    {
        return $this->bloqueoD;
    }

    /**
     * Set drogueria
     *
     * @param string $drogueria
     *
     * @return Cliente
     */
    public function setDrogueria($drogueria)
    {
        $this->drogueria = $drogueria;

        return $this;
    }

    /**
     * Get drogueria
     *
     * @return string
     */
    public function getDrogueria()
    {
        return $this->drogueria;
    }

    /**
     * Set nit
     *
     * @param string $nit
     *
     * @return Cliente
     */
    public function setNit($nit)
    {
        $this->nit = $nit;

        return $this;
    }

    /**
     * Get nit
     *
     * @return string
     */
    public function getNit()
    {
        return $this->nit;
    }

    /**
     * Set bloqueoR
     *
     * @param string $bloqueoR
     *
     * @return Cliente
     */
    public function setBloqueoR($bloqueoR)
    {
        $this->bloqueoR = $bloqueoR;

        return $this;
    }

    /**
     * Get bloqueoR
     *
     * @return string
     */
    public function getBloqueoR()
    {
        return $this->bloqueoR;
    }

    /**
     * Set asociado
     *
     * @param string $asociado
     *
     * @return Cliente
     */
    public function setAsociado($asociado)
    {
        $this->asociado = $asociado;

        return $this;
    }

    /**
     * Get asociado
     *
     * @return string
     */
    public function getAsociado()
    {
        return $this->asociado;
    }

    /**
     * Set nitDv
     *
     * @param string $nitDv
     *
     * @return Cliente
     */
    public function setNitDv($nitDv)
    {
        $this->nitDv = $nitDv;

        return $this;
    }

    /**
     * Get nitDv
     *
     * @return string
     */
    public function getNitDv()
    {
        return $this->nitDv;
    }

    /**
     * Set direcion
     *
     * @param string $direcion
     *
     * @return Cliente
     */
    public function setDirecion($direcion)
    {
        $this->direcion = $direcion;

        return $this;
    }

    /**
     * Get direcion
     *
     * @return string
     */
    public function getDirecion()
    {
        return $this->direcion;
    }

    /**
     * Set codMun
     *
     * @param string $codMun
     *
     * @return Cliente
     */
    public function setCodMun($codMun)
    {
        $this->codMun = $codMun;

        return $this;
    }

    /**
     * Get codMun
     *
     * @return string
     */
    public function getCodMun()
    {
        return $this->codMun;
    }

    /**
     * Set ciudad
     *
     * @param string $ciudad
     *
     * @return Cliente
     */
    public function setCiudad($ciudad)
    {
        $this->ciudad = $ciudad;

        return $this;
    }

    /**
     * Get ciudad
     *
     * @return string
     */
    public function getCiudad()
    {
        return $this->ciudad;
    }

    /**
     * Set ruta
     *
     * @param string $ruta
     *
     * @return Cliente
     */
    public function setRuta($ruta)
    {
        $this->ruta = $ruta;

        return $this;
    }

    /**
     * Get ruta
     *
     * @return string
     */
    public function getRuta()
    {
        return $this->ruta;
    }

    /**
     * Set unGeogra
     *
     * @param string $unGeogra
     *
     * @return Cliente
     */
    public function setUnGeogra($unGeogra)
    {
        $this->unGeogra = $unGeogra;

        return $this;
    }

    /**
     * Get unGeogra
     *
     * @return string
     */
    public function getUnGeogra()
    {
        return $this->unGeogra;
    }

    /**
     * Set depto
     *
     * @param string $depto
     *
     * @return Cliente
     */
    public function setDepto($depto)
    {
        $this->depto = $depto;

        return $this;
    }

    /**
     * Get depto
     *
     * @return string
     */
    public function getDepto()
    {
        return $this->depto;
    }

    /**
     * Set telefono
     *
     * @param string $telefono
     *
     * @return Cliente
     */
    public function setTelefono($telefono)
    {
        $this->telefono = $telefono;

        return $this;
    }

    /**
     * Get telefono
     *
     * @return string
     */
    public function getTelefono()
    {
        return $this->telefono;
    }

    /**
     * Set centro
     *
     * @param string $centro
     *
     * @return Cliente
     */
    public function setCentro($centro)
    {
        $this->centro = $centro;

        return $this;
    }

    /**
     * Get centro
     *
     * @return string
     */
    public function getCentro()
    {
        return $this->centro;
    }

    /**
     * Set pS
     *
     * @param string $pS
     *
     * @return Cliente
     */
    public function setPS($pS)
    {
        $this->pS = $pS;

        return $this;
    }

    /**
     * Get pS
     *
     * @return string
     */
    public function getPS()
    {
        return $this->pS;
    }

    /**
     * Set pCarga
     *
     * @param string $pCarga
     *
     * @return Cliente
     */
    public function setPCarga($pCarga)
    {
        $this->pCarga = $pCarga;

        return $this;
    }

    /**
     * Get pCarga
     *
     * @return string
     */
    public function getPCarga()
    {
        return $this->pCarga;
    }

    /**
     * Set diskette
     *
     * @param string $diskette
     *
     * @return Cliente
     */
    public function setDiskette($diskette)
    {
        $this->diskette = $diskette;

        return $this;
    }

    /**
     * Get diskette
     *
     * @return string
     */
    public function getDiskette()
    {
        return $this->diskette;
    }

    /**
     * Set clienteTiempo
     *
     * @param \DateTime $clienteTiempo
     *
     * @return Cliente
     */
    public function setClienteTiempo($clienteTiempo)
    {
        $this->clienteTiempo = $clienteTiempo;

        return $this;
    }

    /**
     * Get clienteTiempo
     *
     * @return \DateTime
     */
    public function getClienteTiempo()
    {
        return $this->clienteTiempo;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Cliente
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
     * Set emailAsociado
     *
     * @param string $emailAsociado
     *
     * @return Cliente
     */
    public function setEmailAsociado($emailAsociado)
    {
        $this->emailAsociado = $emailAsociado;

        return $this;
    }

    /**
     * Get emailAsociado
     *
     * @return string
     */
    public function getEmailAsociado()
    {
        return $this->emailAsociado;
    }

    /**
     * Set tipoCliente
     *
     * @param string $tipoCliente
     *
     * @return Cliente
     */
    public function setTipoCliente($tipoCliente)
    {
        $this->tipoCliente = $tipoCliente;

        return $this;
    }

    /**
     * Get tipoCliente
     *
     * @return string
     */
    public function getTipoCliente()
    {
        return $this->tipoCliente;
    }

    /**
     * Set cupoAsociado
     *
     * @param string $cupoAsociado
     *
     * @return Cliente
     */
    public function setCupoAsociado($cupoAsociado)
    {
        $this->cupoAsociado = $cupoAsociado;

        return $this;
    }

    /**
     * Get cupoAsociado
     *
     * @return string
     */
    public function getCupoAsociado()
    {
        return $this->cupoAsociado;
    }

    /**
     * Set estado
     *
     * @param integer $estado
     *
     * @return Cliente
     */
    public function setEstado($estado)
    {
        $this->estado = $estado;

        return $this;
    }

    /**
     * Get estado
     *
     * @return integer
     */
    public function getEstado()
    {
        return $this->estado;
    }

    /**
     * Set centroCosto
     *
     * @param string $centroCosto
     *
     * @return Cliente
     */
    public function setCentroCosto($centroCosto)
    {
        $this->centroCosto = $centroCosto;

        return $this;
    }

    /**
     * Get centroCosto
     *
     * @return string
     */
    public function getCentroCosto()
    {
        return $this->centroCosto;
    }
}
