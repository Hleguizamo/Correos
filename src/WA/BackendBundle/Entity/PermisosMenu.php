<?php

namespace WA\BackendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PermisosMenu
 *
 * @ORM\Table(name="permisos_menu", indexes={@ORM\Index(name="menu_id", columns={"menu_id", "administrador_id"}), @ORM\Index(name="FK_administrador_permisosMenu", columns={"administrador_id"}), @ORM\Index(name="IDX_43731A96CCD7E912", columns={"menu_id"})})
 * @ORM\Entity
 */
class PermisosMenu
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
     * @ORM\Column(name="permiso", type="integer", nullable=false)
     */
    private $permiso;

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
     * @var \Menu
     *
     * @ORM\ManyToOne(targetEntity="Menu")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="menu_id", referencedColumnName="id")
     * })
     */
    private $menu;



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
     * Set permiso
     *
     * @param integer $permiso
     *
     * @return PermisosMenu
     */
    public function setPermiso($permiso)
    {
        $this->permiso = $permiso;

        return $this;
    }

    /**
     * Get permiso
     *
     * @return integer
     */
    public function getPermiso()
    {
        return $this->permiso;
    }

    /**
     * Set administrador
     *
     * @param \WA\BackendBundle\Entity\Administrador $administrador
     *
     * @return PermisosMenu
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
     * Set menu
     *
     * @param \WA\BackendBundle\Entity\Menu $menu
     *
     * @return PermisosMenu
     */
    public function setMenu(\WA\BackendBundle\Entity\Menu $menu = null)
    {
        $this->menu = $menu;

        return $this;
    }

    /**
     * Get menu
     *
     * @return \WA\BackendBundle\Entity\Menu
     */
    public function getMenu()
    {
        return $this->menu;
    }
}
