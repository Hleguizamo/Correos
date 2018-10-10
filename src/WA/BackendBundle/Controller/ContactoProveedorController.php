<?php

namespace WA\BackendBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use WA\BackendBundle\Entity\Contactos;
use WA\BackendBundle\Form\ContactosProveedorType;

/**
 * Proveedor controller.
 *
 * @Route("/proveedor")
 */
class ContactoProveedorController extends Controller
{
    public function cargaContactosProveedorAction(Request $request){
      $session = $request->getSession();
      $auditoria = $this->get('utilidadesAdministrador');
      $emp = $this->getDoctrine()->getManager('contactosProveedor');
      
      $response = new Response();
      $resp = array();
      if($request->getMethod() == 'POST'){
          ini_set('memory_limit', '512M');
          $files = $request->files->get('form');
          if($files['archivo']){
              $file = $files['archivo'];
              $file->move('./uploads/',$file->getClientOriginalName());
              $filename = './uploads/'.$file->getClientOriginalName();
              $excelObj = \PHPExcel_IOFactory::load($filename);
              $sheetData = $excelObj->getActiveSheet()->toArray(null,true,true,true);
              $resp['status'] = 1;
              
              $n_inserts = 200;
              $n_insert = 0;
              $total = 0;
              $insertados = 0;
              $rechazados = array();
              
              foreach($sheetData as $n_fila=>$data){
                  if($n_fila > 1){
                      $cargo=$emp->getRepository('WABackendBundle:Cargos')->findOneByNombre(trim($data['A']));
                      if ($cargo) {
                        $idcargo = $cargo->getId();
                      }else{
                        $idcargo =1;
                      }
                      $area=$emp->getRepository('WABackendBundle:Areas')->findOneByNombre(trim($data['B']));
                      if ($area) {
                        $idArea = $area->getId();
                      }else{
                        $idArea =1;
                      }
                      $total++;
                      $nombreContacto = trim($data['C']);
                      $ciudad = trim($data['D']);
                      $email = trim($data['E']);
                      $telefono = trim($data['F']);
                      $movil = trim($data['G']);
                      unset($data); //** Elimino el uso de memoria inecesario **/
                      
                      $sw_valido = true;
                      $errores = array();
                      
                      //** Si falta alguno de los campos obligatorios **//
                      if($idcargo == "" || $idArea == "" || $nombreContacto == "" || $ciudad == "" || $email == "" || 
                          $telefono == "" || $movil == ""){
                          $sw_valido = false;
                          $errores[] = 'Complete los campos obligatorios';
                      }
                      
                      if($sw_valido){
                          $n_insert++;
                          $insertados++;
                          $proveedor = new Contactos();
                          $proveedor->setNombreContacto($nombreContacto);
                          $proveedor->setCiudad($ciudad);
                          $proveedor->setEmail($email);
                          $proveedor->setTelefono($telefono);
                          $proveedor->setMovil($movil);
                          $proveedor->setFechaCreacion(new \DateTime());
                          $proveedor->setIdProveedor($emp->getReference('WABackendBundle:Proveedores',$request->get('proveedorId')));
                          $proveedor->setIdCargo($emp->getReference('WABackendBundle:Cargos',$idcargo));
                          $proveedor->setIdArea($emp->getReference('WABackendBundle:Areas',$idArea));
                          $emp->persist($proveedor);
                          if($n_insert == $n_inserts){
                              $emp->flush();
                              $n_insert = 0;
                          }
                      }else{
                          $rechazado = array();
                          $rechazado['fila'] = $n_fila;
                          $rechazado['errores'] = $errores;
                          
                          //** Agrego el registro rechazado al array de rechazados **//
                          $rechazados[] = $rechazado;
                      }
                  }
              }
              $emp->flush();
              $auditoria->registralog('Carga de contactos proveedor A.Plano', $session->get('id_usuario'));
              $resp['total'] = $total;
              $resp['insertados'] = $insertados;
              $resp['n_rechazados'] = count($rechazados);
              $resp['rechazados'] = $rechazados;
          }else{
              $resp['status'] = 3;
              $resp['message'] = 'El archivo proveedores es obligatorio';
          }
      }else{
          $resp['status'] = 4;
          $resp['message'] = 'Imposible responder a su solicitud';
      }
      $response->setContent(json_encode($resp));
      return $response;
    }

    /**
     * vista de cargue de informacion
     */
    public function cargueMasivoAction(Request $request){
        return $this->render('proveedor/cargueMasivo.html.twig',array('proveedorId'=>$request->get('proveedorId')));
    }

     /**
    * Devuelve una respuesta en xml con todos los proveedores registrados.
    * @param object $request Objeto peticion de Symfony 3
    * @return Objeto xml con proveedores.
    * @author Cristian Preciado <c.preciado@waplicaciones.com.co>
    * @since 3
    * @category Correos\proveedor
    */
    public function contactoXmlAction(Request $request){

      if ($request->isXmlHttpRequest()){
        $session = $request->getSession();
        $auditoria = $this->get('utilidadesAdministrador');
        $busqueda = $this->get('busquedaAdministrador');
        $filtro=$busqueda->busqueda();
        $filtroBusqueda = ($filtro) ? $filtro : ' WHERE p.id != 0 ';
        $ordenTipo = $request->get('sord');
        $ordenCampo = $request->get('sidx');
        $rows = $request->get('rows');
        $pagina = $request->get('page');
        $paginacion = ($pagina * $rows) - $rows;

        $em = $this->getDoctrine()->getManager('contactosProveedor');
        $entities = $em->createQuery(' SELECT cp FROM WABackendBundle:Contactos cp LEFT JOIN cp.idProveedor p LEFT JOIN cp.idCargo c '.$filtroBusqueda.' AND cp.idProveedor = '.$request->get('porveedorId').' ORDER BY '.$ordenCampo.' '.$ordenTipo);
        $entities->setMaxResults($rows);
        $entities->setFirstResult($paginacion);
        $entities = $entities->getResult();   

        $contador=$em->createQuery(" SELECT COUNT(p.id) AS contador FROM WABackendBundle:Contactos cp LEFT JOIN cp.idProveedor p LEFT JOIN cp.idCargo c ".$filtroBusqueda.' AND cp.idProveedor = '.$request->get('porveedorId'))->getSingleResult();
        $numRegistros = $contador['contador'];
        $totalPagina = ceil($numRegistros / $rows);        
        $response = new Response();
        $response->setStatusCode(200);

        $response->headers->set('Content-Type', 'text/xml');
        $auditoria->registralog('Listado contactos proveedor', $session->get('id_usuario'));
        return $this->render('contactoProveedor/index.xml.twig', array(
                'entities' => $entities,'numRegistros' => $numRegistros,
                'maxPagina' => $totalPagina,'pagina' => $pagina), $response);
      
      }

      $response = new Response();
      $response->setStatusCode(500);

    } 

    /**
     * Lists all proveedor entities.
     *
     * @Route("/", name="proveedor")
     * @Method("GET")
     */
    public function indexAction()
    {
      return $this->render('contactoProveedor/index.html.twig',array('menu'=>'usuarios'));
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
      $contactoProveedor = new Contactos();
      $contactoProveedor->setFechaCreacion(new \DateTime());
      $form = $this->createForm('WA\BackendBundle\Form\ContactosProveedorType', $contactoProveedor, ['id' => $request->get('proveedorId')]);
      $form->handleRequest($request);
      if ($form->isSubmitted() && $form->isValid()) {

        $response=new Response;
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'application/json');

        try{
          $json = array();
          $em = $this->getDoctrine()->getManager('contactosProveedor');
          
          $em->persist($contactoProveedor);
          $em->flush();
          $auditoria->registralog('Creacion contactos proveedor', $session->get('id_usuario'));
          $json['status'] = 1;
          $response->setContent(json_encode($json));
        }catch(\Exception $e){
          dump($e->getMessage());exit();
          $json['status'] = 1;
          $response->setStatusCode(500);
        }

        return $response;

      }

      return $this->render('contactoProveedor/new.html.twig', array(
        'form' => $form->createView(),
        'idProveedor' => $request->get('proveedorId')
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
      $contactoProveedor = $em->getRepository('WABackendBundle:Contactos')->find($request->get('id'));
      $editForm = $this->createForm('WA\BackendBundle\Form\ContactosProveedorType', $contactoProveedor, ['id' => $contactoProveedor->getIdProveedor()->getId()]);
      $editForm->handleRequest($request);
      //dump($editForm->getErrors());exit();
      if ($editForm->isSubmitted() && $editForm->isValid()) {

        $response=new Response;
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'application/json');

        try{

          $em->persist($contactoProveedor);
          $em->flush();
          $auditoria->registralog('Creacion contactos proveedor', $session->get('id_usuario'));
          $json['status'] = 1;

        }catch(\Exception $e){

          $json['status'] = 1;
          $response->setStatusCode(500);
        }
        $response->setContent(json_encode($json));
        return $response;
      
      }

      return $this->render('contactoProveedor/edit.html.twig', array(
        'contactoProveedor' => $contactoProveedor,
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

      $conexion=$this->getDoctrine()->getManager('contactosProveedor')->getConnection();
      $response=new Response;
      $session = $request->getSession();
      $auditoria = $this->get('utilidadesAdministrador');

      try{
        
        $conexion->beginTransaction();
        $conexion->query('DELETE FROM contactos WHERE id='.$request->get('id'));

        $response->setStatusCode(200);
        $conexion->commit();
        $auditoria->registralog('Eliminacion contactos proveedor', $session->get('id_usuario'));
      }catch(\Exception $e){
        $response->setStatusCode(500);
        $conexion->rollBack();
      }
      return $response;
    }
}
