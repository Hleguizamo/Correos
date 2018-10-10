<?php

namespace WA\BackendBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use WA\BackendBundle\Entity\Proveedores;
use WA\BackendBundle\Form\ProveedorType;

/**
 * Proveedor controller.
 *
 * @Route("/proveedor")
 */
class ProveedorController extends Controller
{
    /**
    * Construye un archivo csv para exportar
    * @param array $entities Array de objetos con los que se 
    * construirá el archivo csv
    */
   public function exportarAction(Request $request){
        $session = $request->getSession();
        $em = $this->getDoctrine()->getManager('contactosProveedor');
        $entities = $em->createQuery("SELECT p FROM WABackendBundle:Proveedores p")->getResult();
        $response = new Response();
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment;filename=proveedores.csv');
        $content = utf8_decode("NIT;CODIGO;NOMBRE;REPRESENTANTE LEGAL;EMAIL REPRESENTANTE LEGAL;TELEFONO REPRESENTANTE LEGAL")."\n";
        
        $n_entities = count($entities);
        $row = 1;

        foreach ($entities as $entity){
                $content .= utf8_decode($entity->getNit()).";";
                $content .= utf8_decode($entity->getCodigo()).";";
                $content .= utf8_decode($entity->getNombre()).";";
                $content .= utf8_decode($entity->getRepresentanteLegal()).";";
                $content .= utf8_decode($entity->getEmailRepresentanteLegal()).";";
                $content .= utf8_decode($entity->getTelefonoRepresentanteLegal()).";";

                if(!($row == $n_entities)){
                        $content.="\n";
                }
                $row++;
        }
        $response->setContent($content);
        return $response;
   }

    /**
     * Lists all proveedor entities.
     *
     * @Route("/", name="proveedor")
     * @Method("GET")
     */
    public function indexAction()
    {
      return $this->render('proveedor/index.html.twig',array('menu'=>'usuarios'));
    }

    /**
    * Devuelve una respuesta en xml con todos los proveedores registrados.
    * @param object $request Objeto peticion de Symfony 3
    * @return Objeto xml con proveedores.
    * @author Julián casas <j.casas@waplicaciones.com.co>
    * @since 3
    * @category Correos\proveedor
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

        $em = $this->getDoctrine()->getManager('contactosProveedor');
        $entities = $em->createQuery('SELECT p FROM WABackendBundle:Proveedores p INNER JOIN WABackendBundle:Contactos c WITH c.idProveedor = p.id AND c.idArea = 2'.$where.' GROUP BY p.nit ORDER BY '.$ordenCampo.' '.$ordenTipo);
        $entities->setMaxResults($rows);
        $entities->setFirstResult($paginacion);
        $entities = $entities->getResult();   

        $contador=$em->createQuery("SELECT COUNT(DISTINCT(p.nit)) AS contador FROM WABackendBundle:Proveedores p INNER JOIN WABackendBundle:Contactos c WITH c.idProveedor = p.id AND c.idArea = 2 ".$where)->getSingleResult();
        $numRegistros = $contador['contador'];
        $totalPagina = ceil($numRegistros / $rows);        
        $response = new Response();
        $response->setStatusCode(200);

        $response->headers->set('Content-Type', 'text/xml');
        $auditoria->registralog('Listado Proveedores', $session->get('id_usuario'));
        return $this->render('proveedor/index.xml.twig', array(
                'entities' => $entities,'numRegistros' => $numRegistros,
                'maxPagina' => $totalPagina,'pagina' => $pagina), $response);
      
      }

      $response = new Response();
      $response->setStatusCode(500);

    } 

    /**
     * Creates a new Administrador entity.
     *
     * @Route("/new", name="administrador_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
      $session = $request->getSession();
      $auditoria = $this->get('utilidadesAdministrador');
      $proveedor = new Proveedores();
      $form = $this->createForm('WA\BackendBundle\Form\ProveedorType', $proveedor);
      $form->handleRequest($request);
      if ($form->isSubmitted() && $form->isValid()) {

        $response=new Response;
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'application/json');

        try{
          $json = array();
          $em = $this->getDoctrine()->getManager('contactosProveedor');
          
          $em->persist($proveedor);
          $em->flush();
          $json['status'] = 1;
          $response->setContent(json_encode($json));
        }catch(\Exception $e){
          dump($e->getMessage());exit();
          $json['status'] = 1;
          $response->setStatusCode(500);
        }

        return $response;
        

      }
      $auditoria->registralog('Nuevo Proveedor', $session->get('id_usuario'));
      return $this->render('proveedor/new.html.twig', array(
        'form' => $form->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Proveedor entity.
     *
     * @Route("/{id}/edit", name="administrador_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request){
      $em = $this->getDoctrine()->getManager('contactosProveedor');
      $session = $request->getSession();
      $auditoria = $this->get('utilidadesAdministrador');
      $proveedor = $em->getRepository('WABackendBundle:Proveedores')->find($request->get('id'));
      $editForm = $this->createForm('WA\BackendBundle\Form\ProveedorType', $proveedor);
      $editForm->handleRequest($request);

      if ($editForm->isSubmitted() && $editForm->isValid()) {

        $response=new Response;
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'application/json');

        try{

          $em->persist($proveedor);
          $em->flush();
          $auditoria->registralog('Edicion Proveedor', $session->get('id_usuario'));
          $json['status'] = 1;

        }catch(\Exception $e){

          $json['status'] = 1;
          $response->setStatusCode(500);
        }
        $response->setContent(json_encode($json));
        return $response;
      
      }

      return $this->render('proveedor/edit.html.twig', array(
        'proveedor' => $proveedor,
        'form' => $editForm->createView(),
        ));
    }

    /**
     * Deletes a Administrador entity.
     *
     * @Route("/{id}", name="administrador_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request)
    {
      $session = $request->getSession();
      $auditoria = $this->get('utilidadesAdministrador');
      $conexion=$this->getDoctrine()->getManager('contactosProveedor')->getConnection();
      $response=new Response;

      try{
        
        $conexion->beginTransaction();
        $conexion->query('DELETE FROM proveedores WHERE id='.$request->get('id'));

        $response->setStatusCode(200);
        $conexion->commit();
        $auditoria->registralog('Eliminacion Proveedor', $session->get('id_usuario'));
      }catch(\Exception $e){
        $response->setStatusCode(500);
        $conexion->rollBack();
      }
      return $response;
    }
}
