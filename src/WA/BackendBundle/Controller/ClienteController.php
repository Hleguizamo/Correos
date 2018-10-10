<?php

namespace WA\BackendBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use WA\BackendBundle\Entity\Cliente;
use WA\BackendBundle\Form\ClienteType;

/**
 * Cliente controller.
 *
 * @Route("/cliente")
 */
class ClienteController extends Controller
{
    /**
     * Lists all Cliente entities.
     *
     * @Route("/", name="cliente_index")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
      $session = $request->getSession();

      if( $session->get('autenticado') ){
        $auditoria = $this->get('utilidadesAdministrador');
        $permisos=$session->get('permisos');
        $auditoria->registralog('Inicio Cliente', $session->get('id_usuario'));
        return $this->render('cliente/index.html.twig',array('menu'=>'usuarios','permiso'=>$permisos));
      }else{
        $uri = $this->container->get('router')->generate('default_login');
        return new RedirectResponse($uri);
      }
      
    }

    /**
    * Devuelve una respuesta en xml con todos los administradores registrados.
    * @param object $request Objeto peticion de Symfony 3
    * @return Objeto xml con administradores.
    * @author Julián casas <j.casas@waplicaciones.com.co>
    * @since 3
    * @category Correos\Cliente
    */
    public function xmlAction(Request $request){

      if ($request->isXmlHttpRequest()){
        $session = $request->getSession();
        $auditoria = $this->get('utilidadesAdministrador');
        $busqueda = $this->get('busquedaAdministrador');
        $where=$busqueda->busqueda();
        $ordenTipo = $request->get('sord');
        $ordenCampo = $request->get('sidx');
        $rows = $request->get('rows');
        $pagina = $request->get('page');
        $paginacion = ($pagina * $rows) - $rows;

        $em = $this->getDoctrine()->getManager();
        $entities = $em->createQuery(' SELECT c FROM WABackendBundle:Cliente c  '.$where.' ORDER BY '.$ordenCampo.' '.$ordenTipo);
        $entities->setMaxResults($rows);
        $entities->setFirstResult($paginacion);
        $entities = $entities->getResult();   

        $contador=$em->createQuery(" SELECT COUNT(c.id) AS contador FROM WABackendBundle:Cliente c ".$where)->getSingleResult();
        $numRegistros = $contador['contador'];

        $totalPagina = ceil($numRegistros / $rows);        
        $response = new Response();
        $response->setStatusCode(200);

        $response->headers->set('Content-Type', 'text/xml');
        $auditoria->registralog('Listado Cliente', $session->get('id_usuario'));
        return $this->render('cliente/index.xml.twig', array(
                'entities' => $entities,'numRegistros' => $numRegistros,
                'maxPagina' => $totalPagina,'pagina' => $pagina), $response);
      
      }

      $response = new Response();
      $response->setStatusCode(500);

    } 
}
