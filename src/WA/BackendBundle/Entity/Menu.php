<?php

namespace WA\BackendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Menu
 *
 * @ORM\Table(name="menu", indexes={@ORM\Index(name="menu_padre_id", columns={"menu_padre_id"})})
 * @ORM\Entity
 */
class Menu
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
     * @ORM\Column(name="titulo", type="string", length=100, nullable=false)
     */
    private $titulo;

    /**
     * @var string
     *
     * @ORM\Column(name="controlador", type="string", length=100, nullable=false)
     */
    private $controlador;

    /**
     * @var string
     *
     * @ORM\Column(name="icono", type="string", length=100, nullable=false)
     */
    private $icono;

    /**
     * @var integer
     *
     * @ORM\Column(name="menu_padre_id", type="integer", nullable=true)
     */
    private $menuPadreId;



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
     * Set titulo
     *
     * @param string $titulo
     *
     * @return Menu
     */
    public function setTitulo($titulo)
    {
        $this->titulo = $titulo;

        return $this;
    }

    /**
     * Get titulo
     *
     * @return string
     */
    public function getTitulo()
    {
        return $this->titulo;
    }

    /**
     * Set controlador
     *
     * @param string $controlador
     *
     * @return Menu
     */
    public function setControlador($controlador)
    {
        $this->controlador = $controlador;

        return $this;
    }

    /**
     * Get controlador
     *
     * @return string
     */
    public function getControlador()
    {
        return $this->controlador;
    }

    /**
     * Set icono
     *
     * @param string $icono
     *
     * @return Menu
     */
    public function setIcono($icono)
    {
        $this->icono = $icono;

        return $this;
    }

    /**
     * Get icono
     *
     * @return string
     */
    public function getIcono()
    {
        return $this->icono;
    }

    /**
     * Set menuPadreId
     *
     * @param integer $menuPadreId
     *
     * @return Menu
     */
    public function setMenuPadreId($menuPadreId)
    {
        $this->menuPadreId = $menuPadreId;

        return $this;
    }

    /**
     * Get menuPadreId
     *
     * @return integer
     */
    public function getMenuPadreId()
    {
        return $this->menuPadreId;
    }
}
