<?php
namespace WA\BackendBundle\Twig;

class WAExtension extends \Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Request
     */
    protected $request;

    /**
     * Constructor
     * 
     * @param ContainerInterface $container
     */
    public function __construct($container)
    {
        $this->container = $container;
        $this->em = $this->container->get('doctrine')->getManager();
        $this->emp = $this->container->get('doctrine')->getManager('contactosProveedor');
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('intGrupoProveedor', array($this, 'integranteGrupoProvedor')),
            new \Twig_SimpleFilter('intGrupoProveedorContacto', array($this, 'integranteGrupoProvedorContacto')),
            new \Twig_SimpleFilter('numeroleido', array($this, 'numeroleido'))
        );
    }

    public function numeroleido($envioIntegrante,$tipo=0){
        $condicion=' ig.proveedorId';
        if ($tipo=1) {
            $condicion=' ig.contactoProveedorId';
        }
        $numerolecturas=$this->em->createQuery("SELECT el.id FROM WABackendBundle:EnvioLectura el 
            JOIN WABackendBundle:IntegranteGrupo ig WITH ig.id=el.envioIntegrateId 
            WHERE $condicion=$envioIntegrante ")->getResult();
        return count($numerolecturas);
    }

    public function integranteGrupoProvedor($idProveedor,$grupoId=''){
        $contactosProveedor = $this->emp->createQuery("SELECT cp.id FROM WABackendBundle:Contactos cp WHERE cp.idProveedor=$idProveedor ")->getResult();
        /*$idContactos = array();
        foreach($contactosProveedor as $contactos){
            $idContactos[]=$contactos['id'];
        }
        if(count($idContactos)>0){
            $integrantesGrupo = $this->em->createQuery("SELECT ig.proveedorId FROM WABackendBundle:IntegranteGrupo ig WHERE ig.activo=1 AND ig.grupo=$grupoId AND ig.proveedorId IN(".implode(',',$idContactos).")")->getResult();
            
            if ($integrantesGrupo) {
                return true;
            }
        }*/
        $integrantesGrupo = $this->em->createQuery("SELECT ig.proveedorId FROM WABackendBundle:IntegranteGrupo ig WHERE ig.activo=1 AND ig.proveedorId=$idProveedor AND ig.grupo=$grupoId")->getResult();
        if ($integrantesGrupo) {
            return true;
        }
        return false;
    }

    public function integranteGrupoProvedorContacto($idProveedorContacto,$grupoId=''){
        /*$integrantesGrupo = $this->em->createQuery("SELECT ig.proveedorId FROM WABackendBundle:IntegranteGrupo ig WHERE ig.activo=1 AND ig.proveedorId=$idProveedorContacto AND ig.grupo=$grupoId")->getResult();*/
        $integrantesGrupo = $this->em->createQuery("SELECT ig.proveedorId FROM WABackendBundle:IntegranteGrupo ig WHERE ig.activo=1 AND ig.contactoProveedorId=$idProveedorContacto AND ig.grupo=$grupoId")->getResult();
        if ($integrantesGrupo) {
            return true;
        }
        return false;
    }

    public function getName()
    {
        return 'wa_extension';
    }
}
?>