<?php

namespace WA\BackendBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Debug\ExceptionHandler;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use WA\BackendBundle\Entity\EnvioLectura;
use WA\BackendBundle\Entity\EnvioIntegrante;
use WA\BackendBundle\Entity\Envio;
use WA\BackendBundle\Entity\CopiaEnvio;
use WA\BackendBundle\Form\EnvioType;

/**
 * Envio controller.
 *
 * @Route("/envio")
 */
class EnvioController extends Controller
{
    private $servicioexel;
    private $activeSheet;
    /**
     * Lists all Envio entities.
     *
     * @Route("/", name="envio_index")
     * @Method("GET")
     */
    public function indexAction( Request $request )
    {

      $session = $request->getSession();

      if( $session->get('autenticado') ){
        $auditoria = $this->get('utilidadesAdministrador');

        $permisos=$session->get('permisos');
        $auditoria->registralog('Ingreso a Envios', $session->get('id_usuario'));
        return $this->render('envio/index.html.twig', array(
          'menu' =>'envios',
          'permiso'=>$permisos
          ));

      }else{
        $uri = $this->container->get('router')->generate('default_login');
        return new RedirectResponse($uri);
      }
    }

    /**
    * Devuelve una respuesta en xml con todos los envios registrados.
    * @param object $request Objeto peticion de Symfony 3
    * @return Objeto xml con envios.
    * @author Julián casas <j.casas@waplicaciones.com.co>
    * @since 3
    * @category Correos\Envio
    */
    public function xmlAction(Request $request){

      $session=$request->getSession(); 

      if ($request->isXmlHttpRequest() && $session->get('autenticado') ){
        $auditoria = $this->get('utilidadesAdministrador');

        $busqueda = $this->get('busquedaAdministrador');
        $where=$busqueda->busqueda();
        $filtroBusqueda = ($where) ? $where.' AND a.id='.$session->get('id_usuario'): 'WHERE a.id='.$session->get('id_usuario');
        $ordenTipo = $request->get('sord');
        $ordenCampo = $request->get('sidx');
        $rows = $request->get('rows');
        $pagina = $request->get('page');
        $paginacion = ($pagina * $rows) - $rows;

        $em = $this->getDoctrine()->getManager();
        $entities = $em->createQuery(' SELECT e.id,e.asunto, e.adjuntos, e.cantidadEnviados, g.nombre AS grupo, a.nombre AS administrador  FROM WABackendBundle:Envio e JOIN e.administrador a JOIN e.grupo g  '.$filtroBusqueda.' ORDER BY '.$ordenCampo.' '.$ordenTipo);
        $entities->setMaxResults($rows);
        $entities->setFirstResult($paginacion);
        $entities = $entities->getResult();   

        $contador=$em->createQuery(' SELECT COUNT(e.id) AS contador FROM WABackendBundle:Envio e JOIN e.administrador a JOIN e.grupo g  '.$filtroBusqueda)->getSingleResult();
        $numRegistros = $contador['contador'];
        $totalPagina = ceil($numRegistros / $rows);        
        $response = new Response();
        $response->setStatusCode(200);

        $response->headers->set('Content-Type', 'text/xml');
        $auditoria->registralog('Listado de Envios', $session->get('id_usuario'));
        return $this->render('envio/index.xml.twig', array(
          'entities' => $entities,'numRegistros' => $numRegistros,
          'maxPagina' => $totalPagina,'pagina' => $pagina), $response);

      }else{
        $uri = $this->container->get('router')->generate('default_login');
        return new RedirectResponse($uri);
      }

    } 

    /**
     * Creates a new Envio entity.
     *
     * @Route("/new", name="envio_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {

      $session=$request->getSession();
      if( $session->get('autenticado') ){
        $auditoria = $this->get('utilidadesAdministrador');

        $envio = new Envio();
        $form = $this->createForm('WA\BackendBundle\Form\EnvioType', $envio,['id_administrador' => $session->get('id_usuario')]);
        $form->handleRequest($request);

        if ($form->isSubmitted() ) {
          
          $response = new Response;
          $json=array();
          try{
            
            $em = $this->getDoctrine()->getManager();
            $conexion=$em->getConnection();

            $conexion->beginTransaction();
          
            $envio->setAdministrador( $em->getReference('WABackendBundle:Administrador',$session->get('id_usuario')) );
            $envio->setCantidadEnviados(0);
            $envio->setFechaCreado(new \DateTime());
            
            $em->persist($envio);
            $em->flush();

            $json['id']=$envio->getId();

            $response->headers->set('Content-Type', 'application/json');
            $response->setContent(json_encode($json));
       
            $response->setStatusCode(200);
            $auditoria->registralog('Creacion nuevo envio', $session->get('id_usuario'));
            $conexion->commit();
          }catch(\Exception $e){
           
             $conexion->rollBack();
             $response->setStatusCode(500);
          }
          return $response;

        }//endif 

        return $this->render('envio/new.html.twig', array(
          'envio' => $envio,
          'form' => $form->createView(),
          ));
      }else{
        $uri = $this->container->get('router')->generate('default_login');
        return new RedirectResponse($uri);
      }

    }//end action


    /**
     * Displays a form to edit an existing Envio entity.
     *
     * @Route("/{id}/edit", name="envio_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Envio $envio)
    {

      $session=$request->getSession();
      if( $session->get('autenticado') ){
        $auditoria = $this->get('utilidadesAdministrador');

        $editForm = $this->createForm('WA\BackendBundle\Form\EnvioType', $envio,['id_administrador' => $session->get('id_usuario')]);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted()) {

          $response = new Response;
          $json=array();
          try{
            
            $em = $this->getDoctrine()->getManager();
            $conexion=$em->getConnection();

            $conexion->beginTransaction();
            
            $envio->setAdministrador( $em->getReference('WABackendBundle:Administrador',$session->get('id_usuario')) );
            
            $em->persist($envio);
            $em->flush();

            $json['id']=$envio->getId();

            $response->headers->set('Content-Type', 'application/json');
            $response->setContent(json_encode($json));
       
            $response->setStatusCode(200);
            $auditoria->registralog('Edicion envio', $session->get('id_usuario'));
            $conexion->commit();
          }catch(\Exception $e){
            
            $conexion->rollBack();
            $response->setStatusCode(500);
           
          }
          return $response;
        }

        return $this->render('envio/edit.html.twig', array(
          'envio' => $envio,
          'form' => $editForm->createView()
          ));
      }else{
        $uri = $this->container->get('router')->generate('default_login');
        return new RedirectResponse($uri);
      }

    }//end action

    /**
     * Deletes a Envio entity.
     *
     * @Route("/{id}", name="envio_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Envio $envio)
    {
      $session=$request->getSession();

      if( $session->get('autenticado') ){
        $auditoria = $this->get('utilidadesAdministrador');
        $conexion=$this->getDoctrine()->getManager()->getConnection();
        $response=new Response;
        $response->setStatusCode(200);

        try{
          
          $conexion->beginTransaction();
          $idEnvio=$envio->getId();

          $conexion->query(' DELETE FROM envio_integrante WHERE envio_id='.$idEnvio);
          $conexion->query(' DELETE FROM datos_envio WHERE envio_id='.$idEnvio);
          $conexion->query(' DELETE FROM copia_envio WHERE envio_id='.$idEnvio);
          $conexion->query(' DELETE FROM columnas_datos_envio WHERE envio_id='.$idEnvio);
          $conexion->query(' DELETE FROM envio WHERE id='.$idEnvio);

          $auditoria->registralog('Eliminacion envio', $session->get('id_usuario'));
          $conexion->commit();

        }catch(\Exception $e){
          $response->setStatusCode(500);
          $conexion->rollBack();         
        }

        return $response;

      }else{

        $uri = $this->container->get('router')->generate('default_login');
        return new RedirectResponse($uri);

      }//end else
    }//end action
    
    
    public function destinatariosAction(Request $request){

        $session = $request->getSession();
        $auditoria = $this->get('utilidadesAdministrador');
        /*$permisos = $session->get('permisos');
        if(!$auditoria->seguridad_http('envio',3,$permisos))
            return $this->redirect($this->generateUrl('admin_login'));
        */
        $em = $this->getDoctrine()->getManager();
        $idEnvio=$request->get('idEnvio');
        
        $entity = $em->createQuery("SELECT g.id, g.clasificacion, e.asunto FROM WABackendBundle:Envio e JOIN e.grupo g WHERE e.id=".$idEnvio)->getSingleResult();

        $entityintegrante = $em->createQuery("SELECT a.id, ig.proveedorId FROM WABackendBundle:IntegranteGrupo ig LEFT JOIN ig.asociado a WHERE ig.grupo=".$entity['id'])->getResult();
        $asociado=false;
        $proveedor=false;
        if ($entityintegrante) {
          foreach ($entityintegrante as $value) {
            if ($value['id']) {
              $asociado=true;
            }
            if ($value['proveedorId']) {
              $proveedor=true;
            }
          }
        }

        if (!$entity) {
            throw $this->createNotFoundException('El grupo que intenta consultar no tiene Entidad.');
        }
        
        if($entity['clasificacion'] == "Asociados"){
            $tipoGrupo = 1;
        }else{
            $tipoGrupo = 2;
        }
        $auditoria->registralog('Agregacion destinatarios', $session->get('id_usuario'));
        //return $this->render('envio/destinatarios.html.twig', array('envioId'=>$idEnvio,'tipoGrupo'=>$tipoGrupo));
        return $this->render('envio/destinatariosEnvio.html.twig', array('envioId'=>$idEnvio,'tipoGrupo'=>$tipoGrupo,'menu' =>'envios','entity'=>$entity,'proveedor'=>$proveedor,'asociado'=>$asociado));
        
    }
    
    
    public function proveedoresXmlAction(Request $request){
        if ($request->isXmlHttpRequest()){
            $session = $request->getSession();
            $envioId = $request->get('envioId');
            $auditoria = $this->get('utilidadesAdministrador');
            /*$permisos = $session->get('permisos');
            
            if(!$auditoria->seguridad_http('envio',3,$permisos))
                return $this->redirect($this->generateUrl('admin_login'));
            */
            $em = $this->getDoctrine()->getManager();
            $emp = $this->getDoctrine()->getManager('contactosProveedor');
            $busqueda = $this->get('busquedaAdministrador');

            $integrantesEnvio = array();
            $envioIntegrante=$em->createQuery("SELECT ig.proveedorId, ig.contactoProveedorId, ei.enviado, ig.id as idIntegrante,ei.fechaEnvio FROM WABackendBundle:EnvioIntegrante ei 
              JOIN ei.integranteGrupo ig WHERE ei.envio=".$envioId)->getResult();
            $idsProveedorarray=array();
            $idsContactoProveedorarray=array();
            foreach ($envioIntegrante as $key => $envio) {
              $idsProveedorarray[]=$envio['proveedorId'];
              if ($envio['contactoProveedorId']) {
                $idsContactoProveedorarray[]=$envio['contactoProveedorId'];
              }
              $integrantesEnvio[$envio['proveedorId']]['enviado']=$envio['enviado'];
              $integrantesEnvio[$envio['proveedorId']]['integrante']=$envio['idIntegrante'];
              $integrantesEnvio[$envio['proveedorId']]['fechaEnvio']=$envio['fechaEnvio'];
            }
            $idsProveedor=array_unique($idsProveedorarray);
            $idsContactoProveedor=array_unique($idsContactoProveedorarray);
            $condicionContacto=' c.id IN ('.implode(',', $idsContactoProveedor).') ';
            if (!$idsProveedor) {
              $idsProveedor = array(0);
            }
            if (!$idsContactoProveedor) {
              $condicionContacto='p.id != 0';
            }
            $filtro=$busqueda->busqueda();
            $filtroBusqueda = ($filtro) ? $filtro.' AND p.id != 0': 'WHERE '.$condicionContacto;
            $ordenenvio=$ordenproveedor="";
            $OrdenTipo = $request->get('sord');
            $OrdenCampo = $request->get('sidx');
            $rows = $request->get('rows');
            $pagina = $request->get('page');
            $paginacion = ($pagina * $rows) - $rows;

            $integrantesEnvio = array();
            $envioIntegrante=$em->createQuery("SELECT ig.proveedorId, ig.contactoProveedorId, ei.enviado, ig.id as idIntegrante,ei.fechaEnvio FROM WABackendBundle:EnvioIntegrante ei 
              JOIN ei.integranteGrupo ig WHERE ei.envio=".$envioId)->getResult();
            $idsProveedor='';
            foreach ($envioIntegrante as $key => $envio) {
              if($key==0){
                $idsProveedor.=$envio['proveedorId'];
              }else{
                $idsProveedor.=','.$envio['proveedorId'];
              }
              $integrantesEnvio[$envio['proveedorId']]['enviado']=$envio['enviado'];
              $integrantesEnvio[$envio['proveedorId']]['integrante']=$envio['idIntegrante'];
              $integrantesEnvio[$envio['proveedorId']]['fechaEnvio']=$envio['fechaEnvio'];
            }
            $condicionprovedor="";
            if ($idsProveedor) {
              $condicionprovedor=" AND p.id IN (".$idsProveedor.") ";
            }

            $entities = $emp->createQuery("SELECT p.nit, p.codigo, p.nombre, p.representanteLegal, p.emailRepresentanteLegal, c.nombreContacto, c.email, p.id,c.id AS contactoId 
              FROM WABackendBundle:Proveedores p 
              LEFT JOIN WABackendBundle:Contactos c WITH c.idProveedor = p.id $ordenproveedor
              $filtroBusqueda ".$condicionprovedor."
              ORDER BY $OrdenCampo $OrdenTipo ");
            $entities->setMaxResults($rows);
            $entities->setFirstResult($paginacion);
            $entities= $entities->getResult();

            $Contador = $emp->createQuery("SELECT COUNT(p.nit) AS contador FROM WABackendBundle:Proveedores p INNER JOIN WABackendBundle:Contactos c WITH c.idProveedor = p.id $filtroBusqueda ".$condicionprovedor)->getSingleResult();
            
            $numRegistros = $Contador['contador'];
            $totalPagina = ceil($numRegistros / $rows);
            $estados = array('' => 'Inactivo', 1 => 'Activo');
            $response = new Response();
            $response->setStatusCode(200);
            $response->headers->set('Content-Type', 'text/xml');
            $auditoria->registralog('Listado de Proveedores Envio', $session->get('id_usuario'));
            
            return $this->render('envio/proveedores.xml.twig', array(
                    'entities' => $entities,'numRegistros' => $numRegistros,
                    'maxPagina' => $totalPagina,'pagina' => $pagina, 'integrantesGrupo' => $integrantesEnvio ), $response);
        }
    }
    
    
    public function agregarProveedorAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $proveedorId = $request->get('proveedorId');
        $envioId = $request->get('envioId');
        $auditoria = $this->get('utilidadesAdministrador');
        $session = $request->getSession();
        if ($request->get('multiple') == 'true') {
            $busqueda = $this->get('busquedaAdministrador');
            $filtro=$busqueda->busqueda();
            $filtroBusqueda = ($filtro) ? $filtro.' AND en.id='.$envioId : 'WHERE en.id='.$envioId;
            $sql = "SELECT ig.proveedor_id AS id,cp.id AS asignado 
                FROM envio en 
                INNER JOIN grupo g ON g.id = en.grupo_id 
                INNER JOIN integrante_grupo ig ON g.id = ig.grupo_id AND ig.proveedor_id IS NOT NULL
                LEFT JOIN copia_envio cp ON cp.proveedor_id=ig.proveedor_id AND cp.envio_id= ".$envioId." ".$filtroBusqueda;
            $entities= $em->getConnection()->query($sql)->fetchAll();
            $batchSize = 200;
            $i=0;
            $insert1='';
            $insert0='INSERT INTO copia_envio (asociado_id, proveedor_id, envio_id, activo) VALUES ';
            $registros=0;
            foreach($entities as $d){
                if(!$d['asignado']){
                    if($insert1!='')
                        $insert1.=', ';
                    $insert1.='(NULL,\''.$d['id'].'\', \''.$envioId.'\', \'1\')';
                    if (($i % $batchSize) == 0) {
                        $em->getConnection()->query($insert0.$insert1);
                        $insert1='';
                    }
                    $i++;
                }
            }
            if($i >1){
                $em->getConnection()->query($insert0.$insert1);
            }
        } else {
            $entidad = $em->getRepository('WABackendBundle:CopiaEnvio')->findOneBy(array('proveedorId' => $proveedorId, 'envioId' => $envioId));
            if (!$entidad) {
                $integrante = new CopiaEnvio();
                $integrante->setActivo(1);
                /*$integrante->setEnvio($em->getReference('WABackendBundle:Envio', $envioId));
                $integrante->setProveedor($em->getReference('WABackendBundle:Proveedor', $proveedorId));*/
                $integrante->setEnvioId($envioId);
                $integrante->setProveedorId($proveedorId);
                $em->persist($integrante);
                $em->flush();
            }
        }
        $auditoria->registralog('Agrega proveedores', $session->get('id_usuario'));
        $response = new Response(json_encode(array('resultado' => '1')));
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
    
    
    public function eliminarProveedorAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $proveedorId = $request->get('proveedorId');
        $envioId = $request->get('envioId');
        $auditoria = $this->get('utilidadesAdministrador');
        $session = $request->getSession();
        if ($request->get('multiple') == 'true') {
            $sql = "DELETE FROM copia_envio WHERE envio_id = '$envioId' AND proveedor_id IS NOT NULL AND asociado_id IS NULL";
            $em->getConnection()->query($sql);
        } else {
            $entidad = $em->getRepository('WABackendBundle:CopiaEnvio')->findOneBy(array('proveedorId' => $proveedorId, 'envioId' => $envioId));
            if ($entidad) {
                $em->remove($entidad);
                $em->flush();
            }
        }
        $auditoria->registralog('Elimina proveedores', $session->get('id_usuario'));
        $response = new Response(json_encode(array('resultado' => '1')));
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
    
   public function asociadosXmlAction(Request $request){
        if ($request->isXmlHttpRequest()){
            $session = $request->getSession();
            $auditoria = $this->get('utilidadesAdministrador');
            /*$permisos = $session->get('permisos');
            $auditoria = $this->get('auditoria');
            if(!$auditoria->seguridad_http('envio',3,$permisos))
                return $this->redirect($this->generateUrl('admin_login'));
            */
            $em = $this->getDoctrine()->getManager();
            $envioId = $request->get('envioId');
            $busqueda = $this->get('busquedaAdministrador');
            $filtro=$busqueda->busqueda();
            $filtroBusqueda = ($filtro) ? $filtro.' AND en.id='.$envioId : 'WHERE en.id='.$envioId;
            $OrdenTipo = $request->get('sord');
            $OrdenCampo = $request->get('sidx');
            $rows = $request->get('rows');
            $pagina = $request->get('page');
            $paginacion = ($pagina * $rows) - $rows;  
            $sql = "SELECT a.id, a.depto,a.ciudad,a.centro,a.codigo,a.drogueria,a.nit,a.asociado,a.email, a.email_asociado, cp.id AS asignado, ci.enviado AS enviado, ig.id as idIntegrante,ci.fecha_envio as fechaEnvio,COUNT(el.id) as leido 
                FROM envio en 
                INNER JOIN grupo g ON g.id = en.grupo_id 
                INNER JOIN integrante_grupo ig ON g.id = ig.grupo_id AND ig.activo=1
                INNER JOIN cliente a ON a.id=ig.asociado_id 
                LEFT JOIN copia_envio cp ON cp.asociado_id=a.id AND en.id=cp.envio_id
                LEFT JOIN envio_integrante ci ON ci.integrante_grupo_id=ig.id AND ci.envio_id=$envioId
                LEFT JOIN envio_lectura el ON el.envio_integrate_id=ig.id AND el.envio_id=$envioId    
                $filtroBusqueda
                GROUP BY a.id
                ORDER BY $OrdenCampo $OrdenTipo
                LIMIT $paginacion, $rows";
               //echo $sql;
            $entities= $em->getConnection()->query($sql)->fetchAll();
            $sql = "SELECT COUNT(a.id) FROM envio en 
                INNER JOIN grupo g ON g.id = en.grupo_id 
                INNER JOIN integrante_grupo ig ON g.id = ig.grupo_id AND ig.activo=1
                INNER JOIN cliente a ON a.id=ig.asociado_id
                $filtroBusqueda";
            $Contador=$em->getConnection()->query($sql)->fetchColumn();
            $numRegistros = $Contador;
            $totalPagina = ceil($numRegistros / $rows);
            $response = new Response();
            $response->setStatusCode(200);
            $response->headers->set('Content-Type', 'text/xml');
            $auditoria->registralog('Listado Asociados Envio', $session->get('id_usuario'));
            //$auditoria->registralog('Listado de Costos', $session->get('id_usuario'));
         
            return $this->render('envio/asociados.xml.twig', array(
                    'entities' => $entities,'numRegistros' => $numRegistros,
                    'maxPagina' => $totalPagina,'pagina' => $pagina,'tipoGrupo'=>$request->get('tipoGrupo')), $response);
        }
    }
    
    
    public function agregarAsociadoAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $asociadoId = $request->get('asociadoId');
        $envioId = $request->get('envioId');
        $session = $request->getSession();
        $auditoria = $this->get('utilidadesAdministrador');
        if($request->get('multiple') == 'true'){
            set_time_limit(0);
            $busqueda = $this->get('busquedaAdministrador');
            $filtro=$busqueda->busqueda();
            $filtroBusqueda = ($filtro) ? $filtro.' AND en.id='.$envioId : 'WHERE en.id='.$envioId;
            $sql = "SELECT a.id, cp.id AS asignado FROM envio en INNER JOIN grupo g ON g.id = en.grupo_id INNER JOIN integrante_grupo ig ON g.id = ig.grupo_id 
                INNER JOIN cliente a ON a.id=ig.asociado_id LEFT JOIN copia_envio cp ON cp.asociado_id=a.id AND cp.envio_id=".$envioId." ".$filtroBusqueda;
            $entities= $em->getConnection()->query($sql)->fetchAll();
            $batchSize = 200;
            $i=0;
            $insert1='';
            $insert0='INSERT INTO copia_envio (asociado_id, proveedor_id, envio_id, activo) VALUES ';
            $registros=0;
            foreach($entities as $d){
                if(!$d['asignado']){
                    if($insert1!='')
                        $insert1.=', ';
                    $insert1.='(\''.$d['id'].'\', NULL, \''.$envioId.'\', \'1\')';
                    if (($i % $batchSize) == 0) {
                        $em->getConnection()->query($insert0.$insert1);
                        $insert1='';
                    }
                    $i++;
                }
            }
            if($i >1){
                $em->getConnection()->query($insert0.$insert1);
            }
        }else{
            $entidad = $em->getRepository('WABackendBundle:CopiaEnvio')->findOneBy(array('asociadoId' => $asociadoId, 'envioId' => $envioId));
            if(!$entidad){           
                $integrante = new CopiaEnvio();
                $integrante->setActivo(1);
                /*$integrante->setEnvio($em->getReference('WABackendBundle:Envio', $envioId));
                $integrante->setAsociado($em->getReference('WABackendBundle:Cliente', $asociadoId));*/
                
                $integrante->setEnvioId($envioId);
                $integrante->setAsociadoId($asociadoId);
                
                $em->persist($integrante);
                $em->flush();
            }
        }
        $auditoria->registralog('Agrega asociados', $session->get('id_usuario'));
        $response = new Response(json_encode(array('resultado' => '1')));
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
    
    public function eliminarAsociadoAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $asociadoId = $request->get('asociadoId');
        $envioId = $request->get('envioId');
        $session = $request->getSession();
        $auditoria = $this->get('utilidadesAdministrador');
        if ($request->get('multiple') == 'true') {
            $sql = "DELETE FROM copia_envio WHERE envio_id = '$envioId' AND asociado_id IS NOT NULL AND proveedor_id IS NULL";
            $em->getConnection()->query($sql)->execute();
            
            $response = new Response(json_encode(array('resultado' => '1')));
            $response->setStatusCode(200);
            $response->headers->set('Content-Type', 'application/json');
            
        } else {
            $entidad_del = $em->getRepository('WABackendBundle:CopiaEnvio')->findOneBy(array('asociadoId' => $asociadoId, 'envioId' => $envioId));
            if($entidad_del){
                $em->remove($entidad_del);
                $em->flush();

                $response = new Response(json_encode(array('resultado' => '1')));
                $response->setStatusCode(200);
                $response->headers->set('Content-Type', 'application/json');
            }else{
                $response = new Response(json_encode(array('resultado' => '0')));
                $response->headers->set('Content-Type', 'application/json');
            }
        }
        $auditoria->registralog('Eliminacion asociados', $session->get('id_usuario'));
        return $response;
    }

    /**
    * Devuelve una respuesta en xml con todos los envios registrados.
    * @param object $request Objeto peticion de Symfony 3
    * @return Objeto xml con envios.
    * @author Julián casas <j.casas@waplicaciones.com.co>
    * @since 3
    * @category Correos\Envio
    */
    public function preVisualizacionAction(Request $request, Envio $envio){

      $session = $request->getSession();
      if( $session->get('autenticado') ){
        $auditoria = $this->get('utilidadesAdministrador');

        if( $envio->getGrupo()->getAgruparProveedor() == 1  && $envio->getGrupo()->getClasificacion() == "No Aplica" ){
            $groupBy = " GROUP BY pr.nit ";
        }else{
            $groupBy = "";
        }
  
        /*$sql=" SELECT ig.id, ig.asociado_id, ig.proveedor_id, ig.grupo_id, ei.id AS enviado 
            FROM integrante_grupo ig 
            LEFT JOIN envio_integrante ei ON ig.id = ei.integrante_grupo_id
            AND ei.envio_id=".$envio->getId()." 
            LEFT JOIN proveedor pr ON ig.proveedor_id = pr.id WHERE ig.activo=1 AND ig.grupo_id=".$envio->getGrupo()->getId().$groupBy;
        */
        $sql=" SELECT ig.id, ig.asociado_id, ig.proveedor_id, ig.grupo_id, ei.id AS enviado 
            FROM integrante_grupo ig 
            LEFT JOIN envio_integrante ei ON ig.id = ei.integrante_grupo_id
            AND ei.envio_id=".$envio->getId()." 
            WHERE ig.activo=1 AND ig.grupo_id=".$envio->getGrupo()->getId().$groupBy;

        $em=$this->getDoctrine()->getManager();
        $integrantes= $em->getConnection()->query($sql)->fetchAll();
        
        $configuracion = $em->createQueryBuilder()
                          ->select('c')
                          ->from('WABackendBundle:Configuracion','c')
                          ->getQuery()
                          ->getOneOrNullResult();
        
        $tipoEnvio = "inmediato";
        
        if( $envio->getAdjuntos() > 0 ){

          $administrador = $session->get('administrador');
          $fecha = $administrador->getFechaCreado();
          $fecha = $fecha->format('Ymd');
          $carpeta = $envio->getId().'/'.$fecha.$administrador->getId();
          $ruta = "../adjuntos/".$carpeta;

          if (is_dir( $ruta )) {

            if ($dh = opendir( $ruta )) {

              while ( ( $file = readdir( $dh ) ) !== false) {

                if( !is_dir( $file ) ){

                  $pesoArchivo = filesize( $ruta.'/'.$file );

                  if( $pesoArchivo <= ( $configuracion->getEnvioInmediato() * 1024 )){
                      $tipoEnvio="inmediato";
                  }else{
                      $tipoEnvio="programado";
                  }
                }//endif
              }//end while
              closedir($dh);
            }// end if
          }// end if
        }// end if
        
        if($tipoEnvio == "programado"){

            $envio->setProgramado(1);
            
            $em->persist($envio);
            $em->flush();
        }
        //dump($integrantes);exit;

        $busquedaGrupo = "SELECT * FROM integrante_grupo WHERE grupo_id = ".$envio->getGrupo()->getId()." AND (proveedor_id IS NOT NULL OR contacto_proveedor_id IS NOT NULL ) LIMIT 1";
        $controlGrupo= $em->getConnection()->query($busquedaGrupo)->fetchAll();
        if ($session->get('id_usuario')==5) {
          $controlGrupo=false;
        }
        $auditoria->registralog('Vista previa envios', $session->get('id_usuario'));
        return $this->render('envio/vistaPrevia.html.twig', array(
            'envio'=>$envio,
            'integrantes'=>$integrantes,
            'tipoEnvio' => $tipoEnvio,
            'controlMensaje' => $controlGrupo
        ));

      
      }else{
        $uri = $this->container->get('router')->generate('default_login');
        return new RedirectResponse($uri);
      }

    }


    public function enviarAction( Request $request ){
        $session = $request->getSession();
        $permisos = $session->get('permisos')['envio'];
        $auditoria = $this->get('utilidadesAdministrador');

        $autenticacionMail=FALSE;
        $info='';

        $usuario = $session->get('emailAdmin');
        $clave = $session->get('emailClave');
        $transport = \Swift_SmtpTransport::newInstance('mail.coopidrogas.com.co')
        ->setUsername($usuario)
        ->setPassword($clave);
        
        try{
          $resultado =$transport->start();
          $autenticacionMail=TRUE;
        }catch(\Swift_TransportException $exentionMailer){
          $info=$exentionMailer->getMessage();
          $resultado = 6;
        }catch(\Exeption $e){
          $info=$e->getMessage();
          $resultado = 7;
        }
        
        $mailer = \Swift_Mailer::newInstance($transport);

        if ($autenticacionMail) {
          $em = $this->getDoctrine()->getManager();
          $emp = $this->getDoctrine()->getManager('contactosProveedor');
          $emsp = $this->getDoctrine()->getManager('proveedores');
          $emspConect = $emsp->getConnection();

          $entity = $em->getRepository('WABackendBundle:Envio')->find($request->get('envioId'));

          if (!$entity)
              throw $this->createNotFoundException('La entidad <Envio> no ha sido encontrada.');
          

          $adjuntos=array();
          $na=0;

          $administrador = $session->get('administrador');
          $fecha = $administrador->getFechaCreado();
          $fecha = $fecha->format('Ymd');
          $carpeta = $entity->getId().'/'.$fecha.$administrador->getId().'/otros';
          //$ruta = "../adjuntos/".$carpeta;
          $ruta=$this->get('kernel')->getRootDir().'/../web/adjuntos/'.$carpeta;
          
          if( $entity->getAdjuntos() > 0 ){

            if ( is_dir($ruta) ) {

              if ( $rep = opendir( $ruta ) ) { //Abrimos el directorio

                while ($arc = readdir($rep)) {  //Leemos el arreglo de archivos contenidos en el directorio: readdir recibe como parametro el directorio abierto

                  if($arc != '..' && $arc !='.' && $arc !=''){

                    $adjuntos[ $na ] = $ruta.'/'.$arc; 
                    $na++;

                  }
                  
                }
              
              }//end if
            }//end if
          }//end if

          $contenido=$entity->getContenido();

          $destinatario=array();

          /************SE BUSCAN PROVEEDORES BLOQUEADOS SIPPROVEEDORES*************/
          $arrayProvBloqueados = array();
          $sqlProvBloqueados = "SELECT * FROM proveedor WHERE estado =0 ";
          $proveedoresBloqueados = $emspConect->query($sqlProvBloqueados);
          foreach($proveedoresBloqueados as $provBloq){
            $arrayProvBloqueados[$provBloq['nit']] = $provBloq['nit'];
          }
          /*************************/

          /**********SE BUSCAN LOS CARGOS SIN SEGUIMIENTO ****************/

          $emContacConect = $emp->getConnection();
          $arrayCargoSinSeguimiento = array();

          $sqlCargoSinSeg = "SELECT * FROM cargos WHERE sin_seguimiento =1 ";

          $cargosBloqueados = $emContacConect->query($sqlCargoSinSeg);
          foreach($cargosBloqueados as $cargoBloq){
            $arrayCargoSinSeguimiento[$cargoBloq['id']] = $cargoBloq['id'];
          }
//var_dump($arrayCargoSinSeguimiento);
          /**************************/
          
          //**SI VIENE DE REENVIO** --> Obtener los datos del destinatario

          if( $request->get('reenvioProveedorId') || $request->get('reenvioAsociadoId') ){
              
            //Asociado
              
            if($request->get('reenvioAsociadoId')){
                $entityDestinatario = $em->getRepository('WABackendBundle:IntegranteGrupo')->findOneByAsociado($request->get('reenvioAsociadoId'));
                $Query = $em->createQuery('SELECT c FROM WABackendBundle:Cliente c WHERE c.id = '.$request->get('reenvioAsociadoId'));
                //17 03 2017 Alejandro Ardila quito AND c.estado = 1
                $destinatarioEntitie = $Query->getArrayResult();
                $destinatarioEntitie=$destinatarioEntitie[0];
                $permitidos=array('zona','codigo','drogueria','nit','asociado','direcion','ciudad','ruta','depto','telefono','centro','email','emailAsociado');

                foreach($permitidos as $campo){

                    if(isset($destinatarioEntitie[$campo]))
                        $contenido = str_replace("//$campo//", utf8_decode($destinatarioEntitie[$campo]), $contenido);

                }
                if($entity->getGrupo()->getClasificacion()=='Asociados'){
                  $destinatario['email']=$destinatarioEntitie['emailAsociado'];
                  $destinatario['nombre']=$destinatarioEntitie['asociado'];
                  $destinatario['Codigo']=$destinatarioEntitie['codigo'];
                  $destinatario['Nit']=$destinatarioEntitie['nit'];
                  $destinatario['id']=$destinatarioEntitie['id'];
                }else{
                  $destinatario['email']=$destinatarioEntitie['email'];
                  $destinatario['nombre']=$destinatarioEntitie['drogueria'];
                  $destinatario['Codigo']=$destinatarioEntitie['codigo'];
                  $destinatario['Nit']=$destinatarioEntitie['nit'];
                  $destinatario['id']=$destinatarioEntitie['id'];
                }

                $destinatario['tipo']='Asociado';

                $destinatario['cargo']=0;

            //-->proveedor**
            }elseif($request->get('reenvioProveedorId')){     

              $entityDestinatario = $em->getRepository('WABackendBundle:IntegranteGrupo')->findOneBy(array('grupo'=>$entity->getGrupo()->getId(),'proveedorId'=>$request->get('reenvioProveedorId')));
              if ($entityDestinatario->getContactoProveedorId()) {
                //$Query = $emp->createQuery('SELECT p.nit, cp.nombreContacto AS nombre, p.nombre AS empresa, cp.telefono AS telefono, cp.movil AS celular, cp.email AS email1, p.codigo, p.id FROM WABackendBundle:Contactos cp LEFT JOIN cp.idProveedor p WHERE cp.id = '.$entityDestinatario->getContactoProveedorId());
                $Query = $emp->createQuery('SELECT p.nit, cp.nombreContacto AS nombre, p.nombre AS empresa, cp.telefono AS telefono, cp.movil AS celular, cp.email AS email1, p.codigo, p.id, car.id AS cargoId FROM WABackendBundle:Contactos cp LEFT JOIN cp.idProveedor p LEFT JOIN cp.idCargo car WHERE cp.id = '.$entityDestinatario->getContactoProveedorId());
              }else{
                $Query = $emp->createQuery('SELECT p.nit,  p.representanteLegal AS nombre, p.nombre AS empresa, p.telefonoRepresentanteLegal AS telefono, p.telefonoRepresentanteLegal AS celular, p.emailRepresentanteLegal AS email1, p.codigo, p.id FROM WABackendBundle:Proveedores p WHERE p.id = '.$entityDestinatario->getProveedorId());
              }

              $destinatarioEntitie = $Query->getArrayResult();
              $destinatarioEntitie=$destinatarioEntitie[0];

              $permitidos=array('nit','nombre','empresa','telefono','celular','email1');

              foreach($permitidos as $campo){

                  if(isset($destinatarioEntitie[$campo]))
                      $contenido = str_replace("//$campo//", utf8_decode($destinatarioEntitie[$campo]), $contenido);

              }
              
              $destinatario['email']=$destinatarioEntitie['email1'];
              $destinatario['nombre']=$destinatarioEntitie['nombre'];
              $destinatario['Codigo']=$destinatarioEntitie['codigo'];
              $destinatario['Nit']=$destinatarioEntitie['nit'];
              $destinatario['id']=$destinatarioEntitie['id'];
              $destinatario['tipo']='Proveedor';

              if( isset($destinatarioEntitie['cargoId']) ){
                $destinatario['cargo']=$destinatarioEntitie['cargoId'];
              }else{
                $destinatario['cargo']=0;
              }
            }

          }else{
              
            $entityDestinatario = $em->getRepository('WABackendBundle:IntegranteGrupo')->find($request->get('destinatarioId'));

            if($entityDestinatario->getAsociado()){
              $Query = $em->createQuery('SELECT c FROM WABackendBundle:Cliente c WHERE c.id = :clienteId');
              $Query->setParameter('clienteId', $entityDestinatario->getAsociado()->getId());
              $destinatarioEntitie = $Query->getArrayResult();
              $destinatarioEntitie=$destinatarioEntitie[0];
              $permitidos=array('zona','codigo','drogueria','nit','asociado','direcion',
              'ciudad','ruta','depto','telefono','centro','email','emailAsociado');
              foreach($permitidos as $campo){
                  if(isset($destinatarioEntitie[$campo]))
                      $contenido = str_replace("//$campo//", utf8_decode($destinatarioEntitie[$campo]), $contenido);

              }
              if($entity->getGrupo()->getClasificacion()=='Asociados'){
                $destinatario['email']=$destinatarioEntitie['emailAsociado'];
                $destinatario['nombre']=$destinatarioEntitie['asociado'];
                $destinatario['Codigo']=$destinatarioEntitie['codigo'];
                $destinatario['Nit']=$destinatarioEntitie['nit'];
                $destinatario['id']=$destinatarioEntitie['id'];
                $destinatario['tipo']='Drogueria';
              }else{
                $destinatario['email']=$destinatarioEntitie['email'];
                $destinatario['nombre']=$destinatarioEntitie['drogueria'];
                $destinatario['Codigo']=$destinatarioEntitie['codigo'];
                $destinatario['Nit']=$destinatarioEntitie['nit'];
                $destinatario['id']=$destinatarioEntitie['id'];
                $destinatario['tipo']='Asociado';
              }

              $destinatario['cargo']=0;
            }else{

              //Proveedor.
              if($entity->getGrupo()->getAgruparProveedor() == 1 && $entity->getGrupo()->getClasificacion() == "No Aplica"){

                //Recuperar el id de los contactos del proveedor.

                



                //consultar en bdcontactos->contactos
                //mirar como se asocian los Proveedores a un grupo

                $em = $entityDestinatario->getProveedor()->getNit();

                $nitProveedores = $entityDestinatario->getProveedor()->getNit();
                
                $proveedores = $emp->getRepository('WABackendBundle:Proveedores')->findBy(array('nit' => $nitProveedores));
                
                $arrayContenido = array();
                foreach($proveedores as $proveedorDestino){
                    
                  $destinatario[$proveedorDestino->getId()]['email']=$proveedorDestino->getEmail1();
                  $destinatario[$proveedorDestino->getId()]['email1']=$proveedorDestino->getEmail2();
                  $destinatario[$proveedorDestino->getId()]['nombre']=$proveedorDestino->getNombre().' '.$proveedorDestino->getApellido();
                  $destinatario[$proveedorDestino->getId()]['Codigo']=$proveedorDestino->getCodigo();
                  $destinatario[$proveedorDestino->getId()]['Nit']=$proveedorDestino->getNit();
                  $destinatario[$proveedorDestino->getId()]['id']=$proveedorDestino->getId();
                  $destinatario['tipo']='Proveedor';

                  $destinatario['cargo']=0;

                }

              }else{
                
                if ($entityDestinatario->getContactoProveedorId()) {
                  //$Query = $emp->createQuery('SELECT p.nit, cp.nombreContacto AS nombre, p.nombre AS empresa, cp.telefono AS telefono, cp.movil AS celular, cp.email AS email1, p.codigo, p.id FROM WABackendBundle:Contactos cp LEFT JOIN cp.idProveedor p WHERE cp.id = '.$entityDestinatario->getContactoProveedorId());
                  $Query = $emp->createQuery('SELECT p.nit, cp.nombreContacto AS nombre, p.nombre AS empresa, cp.telefono AS telefono, cp.movil AS celular, cp.email AS email1, p.codigo, p.id, car.id AS cargoId FROM WABackendBundle:Contactos cp LEFT JOIN cp.idProveedor p LEFT JOIN cp.idCargo car WHERE cp.id = '.$entityDestinatario->getContactoProveedorId());
                }else{
                  $Query = $emp->createQuery('SELECT p.nit,  p.representanteLegal AS nombre, p.nombre AS empresa, p.telefonoRepresentanteLegal AS telefono, p.telefonoRepresentanteLegal AS celular, p.emailRepresentanteLegal AS email1, p.codigo, p.id FROM WABackendBundle:Proveedores p WHERE p.id = '.$entityDestinatario->getProveedorId());
                }
                $destinatarioEntitie = $Query->getArrayResult();
                $destinatarioEntitie=$destinatarioEntitie[0];

                $permitidos=array('nit','nombre','apellido','empresa','cargo','direccion','telefono',
                'fax','celular','email1','email2','clasificacion');

                foreach($permitidos as $campo){

                    if(isset($destinatarioEntitie[$campo]))
                        $contenido = str_replace("//$campo//", utf8_decode($destinatarioEntitie[$campo]), $contenido);
                }

                $destinatario['email']=$destinatarioEntitie['email1'];
                $destinatario['email1']=$destinatarioEntitie['email1'];
                $destinatario['nombre']=$destinatarioEntitie['nombre'];
                $destinatario['Codigo']=$destinatarioEntitie['codigo'];
                $destinatario['Nit']=$destinatarioEntitie['nit'];
                $destinatario['id']=$destinatarioEntitie['id'];
                $destinatario['tipo']='Proveedor';

                if( isset($destinatarioEntitie['cargoId']) ){
                  $destinatario['cargo']=$destinatarioEntitie['cargoId'];
                }else{
                  $destinatario['cargo']=0;
                }
                  
              }
                
                
            }
          }

          if($entity->getCombinacion()==1){

            $columna=$entity->getColumnaCombinacion();

            $idEnvio=$request->get('envioId');            
            $cabezeraSabana = $em->getRepository('WABackendBundle:DatosEnvio')->findOneBy(array('envioId' => $idEnvio));

            $condicion = $destinatario[$columna];
            $sabanaDatos = $em->createQuery("SELECT u FROM WABackendBundle:ColumnasDatosEnvio u WHERE u.envioId = '".$idEnvio."' AND u.identificador  = '$condicion'")->execute();
            
            $resultado = strpos($contenido, '//TABLA//');
            $control=0;
            if($resultado !== FALSE){
                //Crear la tabla con el contenido para enviar.
                $columnas = unserialize($cabezeraSabana->getDatos());
                $contColumnas = count($columnas) - 1;
                $posicion = 0;
                $informacionTabla = '<table class="tablaDatos"><tr>';
                foreach($columnas as $ThArray){
                    $informacionTabla .= '<td>' . ucfirst(htmlentities($ThArray)) . '</td>';
                }
                $informacionTabla .= '</tr>';
                foreach ($sabanaDatos as $dato) {
                    $adjuntosCsv[] = $dato->getId();
                    $data = unserialize($dato->getDatos());
                    $contColumnas = count($data) - 1;
                    $informacionTabla .= '<tr>';
                    for ($i = 0; $i <= $contColumnas; $i++) {
                        $informacionTabla .= '<td>' . $data[$i] . '</td>';
                    }
                    $informacionTabla .= '</tr>';
                }
                $informacionTabla .= '</table>';
                $control++;
                if($sabanaDatos){
                    $contenido = str_replace("//TABLA//", $informacionTabla, $contenido);
                }else{
                    $contenido = str_replace("//TABLA//", "", $contenido);
                }
            }

            $usuarioTipo=$destinatario['tipo'];

            $resultado = strpos($contenido, '//ARCHIVO//');
            if($resultado !== FALSE){

                if($sabanaDatos){
                    $linkArchivo='<a style="color:#E01F69;font-size:24px;" href="'.$this->generateUrl('envio_xls', array('envio_id' => $entity->getId(),'tipo_usuario'=>$usuarioTipo,'id_usuario'=>$destinatario['id'],'llave'=>md5($entity->getId().$usuarioTipo.$destinatario['id'])), UrlGeneratorInterface::ABSOLUTE_URL).'" target="_blank">Clic aqu&iacute; para descargar Archivo</a>';
                    $control++;
                    $contenido = str_replace("//ARCHIVO//", $linkArchivo, $contenido);

                }else{

                    $contenido = str_replace("//ARCHIVO//", ' ', $contenido);

                }
            }

            $resultadoC = strpos($contenido, '//COMBINAR//');

            if($resultadoC !== FALSE){

              foreach ($sabanaDatos as $dato) {

                $datosFila = unserialize($dato->getDatos());
                $cantidadDatos = count($datosFila) - 1;

                for($i = 0; $i <= $cantidadDatos; $i++){

                    $contenido = str_replace('//'.($i+1).'//', utf8_decode($datosFila[$i]), $contenido);

                }
              }
              $contenido = str_replace('//COMBINAR//', '', $contenido);
            }
          }

          //if(filter_var($destinatario['email'], FILTER_VALIDATE_EMAIL)){
          $message = '';

          if($entityDestinatario->getAsociado() || ($destinatario['tipo'] == "Proveedor" && (!isset($arrayProvBloqueados[isset($destinatario['nit'])])) ) ){

            if( !isset( $arrayCargoSinSeguimiento[ $destinatario['cargo'] ] )  ){

                //Si es reenvio; ponemos un prefijo en el asunto.
                if($request->get('reenvioProveedorId')||$request->get('reenvioAsociadoId')){
                  $message=\Swift_Message::newInstance('Reenviado: '.$entity->getAsunto());
                }else{
                  $message=\Swift_Message::newInstance($entity->getAsunto());
                }
                    
                //$message->setFrom($session->get('correo'),$session->get('nombre'))->setContentType('text/html');
                $message->setFrom(array($session->get('emailAdmin') => $session->get('nombreAdmin')))->setContentType('text/html');
                    
                if($entityDestinatario->getAsociado()){
                  $message->setTo($destinatario['email'],$destinatario['nombre']);
                  //echo $destinatario['email']." ".$destinatario['nombre'];
                }else{

                  if($entity->getGrupo()->getAgruparProveedor() == 1 && $entity->getGrupo()->getClasificacion() == "No Aplica"){
                    $arrayDestino = array();

                    foreach($destinatario as $destino){
                      if(isset($destino['email'])){
                          
                        if(filter_var($destino['email'], FILTER_VALIDATE_EMAIL)){

                          $arrayDestino[$destino['email']] = $destino['nombre'];

                        }

                      }
                      if(isset($destino['email1'])){

                          if(filter_var($destino['email1'], FILTER_VALIDATE_EMAIL))

                            $arrayDestino[$destino['email']] = $destino['nombre'];
                      }

                    }
                    $message->setTo($arrayDestino);
                    //$message->setTo(array('j.bedoya@coopidrogas.com.co' => 'Johan Bedoya','a.cardenas@coopidrogas.com.co' => 'Andersson Cardenas','c.preciado@waplicaciones.co' => 'Cristian'));
                      
                  }else{
                      if(filter_var($destinatario['email'], FILTER_VALIDATE_EMAIL)){

                        $message->setTo($destinatario['email'],$destinatario['nombre']);
                        //$message->setTo(array('j.bedoya@coopidrogas.com.co' => 'Johan Bedoya','a.cardenas@coopidrogas.com.co' => 'Andersson Cardenas','c.preciado@waplicaciones.co' => 'Cristian'));

                      }
                  }
                    
                }

                // Copia del envio.  
                if($destinatario['tipo']=='Proveedor'){
                  /*if($entity->getGrupo()->getAgruparProveedor() == 1 && $entity->getGrupo()->getClasificacion() == "No Aplica"){

                    foreach($destinatario as $destino){

                      $copia_envio = $em->getRepository('WABackendBundle:CopiaEnvio')->findOneBy(array('proveedorId'=>$destino['id'],'envioId'=>$entity->getId()));

                      if($copia_envio){

                          //$message->addCc($session->get('correo'),$session->get('administrador'));
                          $message->addCc(array('j.restrepo@waplicaciones.co' => 'destinatario'));
                          

                      }

                    }
                  }else{*/

                      $copia_envio = $em->getRepository('WABackendBundle:CopiaEnvio')->findOneBy(array('proveedorId'=>$destinatario['id'],'envioId'=>$entity->getId()));

                  //}
                }else{

                  $copia_envio = $em->getRepository('WABackendBundle:CopiaEnvio')->findOneBy(array('asociadoId'=>$destinatario['id'],'envioId'=>$entity->getId()));

                }

                //Copia envío administrador con sesión activa
                
                if($copia_envio){
                  //$message->addCc($session->get('correo'),$session->get('administrador'));
                  $message->addCc($session->get('emailAdmin'),$session->get('nombreAdmin'));
                }
                  
                set_time_limit(500);
                
                $template= str_replace('../../../uploads/images', $this->get('kernel')->getRootDir().'/../web/uploads/images',
                        $this->renderView('envio/email_send.html.twig'
                        ,array(
                          'mensaje'=>$contenido,'destinatario'=>$destinatario,'destinatarioId'=>$entityDestinatario->getId(),
                          'idEnvio'=>$entity->getId()
                        )));

                $message->setBody($template);
                
                foreach ($adjuntos as $path) {

                  $attachment = \Swift_Attachment::fromPath($path);
                  $message->attach($attachment);

                }
                try{
                  //$message->addCc(array('alejandro@waplicaciones.co' => 'Alejandro Ardila'));
                  //$message->addCc('alejandro@waplicaciones.co','Alejandro Ardila');
                  //print_r($message->getTo());
                  //print_r($message->getCc());
                  $mailer->send($message);
                  $resultado=1;


                  //Si es reenvio; ponemos un prefijo en el asunto.
                  if($request->get('reenvioProveedorId')||$request->get('reenvioAsociadoId')){

                    if($request->get('reenvioAsociadoId')){

                      $entityDestinatario = $em->getRepository('WABackendBundle:IntegranteGrupo')->findOneBy(array('asociado'=>$request->get('reenvioAsociadoId'),'grupo'=>$entity->getGrupo()));
                      $Enviado = $em->getRepository('WABackendBundle:EnvioIntegrante')->findOneBy(array('envio'=>$request->get('envioId'),'integranteGrupo'=>$entityDestinatario->getId()));

                    }else{

                      $entityDestinatario = $em->getRepository('WABackendBundle:IntegranteGrupo')->findOneBy(array('proveedorId'=>$request->get('reenvioProveedorId'),'grupo'=>$entity->getGrupo()));
                      $Enviado = $em->getRepository('WABackendBundle:EnvioIntegrante')->findOneBy(array('envio'=>$request->get('envioId'),'integranteGrupo'=>$entityDestinatario->getId()));

                    }
                    
                    if($Enviado){
                        $Enviado->setReenviado($Enviado->getReenviado()+1);
                        $em->persist($Enviado);
                        $em->flush();
                    }
                    

                  }else{
                      

                    if($entity->getGrupo()->getAgruparProveedor() == 1 && $entity->getGrupo()->getClasificacion() == "No Aplica"){
                        
                        
                      foreach($proveedores as $proveedorDestino){
                          
                        $integrantes = $em->getRepository('WABackendBundle:IntegranteGrupo')->findOneBy(array('grupo' => $entity->getGrupo()->getId() , 'proveedorId' => $proveedorDestino->getId()));
                        
                        
                        $Enviado = new EnvioIntegrante();
                        $Enviado->setEnvio($em->getReference('WABackendBundle:Envio', $entity->getId()));
                        $Enviado->setIntegranteGrupo($em->getReference('WABackendBundle:IntegranteGrupo', $integrantes->getId()));
                        $Enviado->setEnviado(0);
                        $Enviado->setReenviado(0);
                        $Enviado->setFechaEnvio(new \DateTime());
                        $em->persist($Enviado);
                        $em->flush();

                      }
                        
                    }else{

                        $Enviado = new EnvioIntegrante();
                        $Enviado->setEnvio($em->getReference('WABackendBundle:Envio', $entity->getId()));
                        $Enviado->setIntegranteGrupo($em->getReference('WABackendBundle:IntegranteGrupo', $entityDestinatario->getId()));
                        $Enviado->setEnviado(0);
                        $Enviado->setReenviado(0);
                        $Enviado->setFechaEnvio(new \DateTime());
                        $em->persist($Enviado);
                        $em->flush();

                    }   

                    $envios_num = $entity->getCantidadEnviados()+1;
                    $sql="UPDATE envio e SET e.cantidad_enviados = '".$envios_num."' WHERE e.id=".$entity->getId();
                    $em->getConnection()->query($sql);

                  }

                }catch(\Exception $e){
                  $info=$e->getMessage();
                  $resultado=2;
                }catch(\ContextErrorException $e){
                  $info=$e->getMessage();
                  $resultado=2;
                }


            }else{

              $resultado=1;
              
              $info = "Email no Enviado. ".$destinatario['nombre']." No Habilitado para envio de correos. ";
            }


          }else{
              $resultado=1;
              
              $info = "Email no Enviado. Proveedor ".$destinatario['nombre']." Bloqueado por informacion desactualizada.";
          }
          
          
        }

          
        $auditoria->registralog('Envio de Correo', $session->get('id_usuario'));
        $response = new Response(json_encode(array('resultado' => $resultado,'info' => $info)));
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'application/json');
        return $response;

    }// end action
    
    
    
    public function vistaPreviaMensajesAction(Request $request){
        //$auditoria = $this->get('auditoria');
        $session = $request->getSession();
        $auditoria = $this->get('utilidadesAdministrador');
        /*$permisos = $session->get('permisos');
        if(!$auditoria->seguridad_http('envio',1,$permisos))
            return $this->redirect($this->generateUrl('admin_login'));
         */
        
        $em = $this->getDoctrine()->getManager();
        $emp = $this->getDoctrine()->getManager('contactosProveedor');
        $envioId=$request->get('envioId');
        $tipoGrupo=$request->get('tipoGrupo');
        $destinatarioId=$request->get('destinatarioId');
        $entity = $em->createQuery("SELECT e, g FROM WABackendBundle:Envio e JOIN e.grupo g WHERE e.id=".$envioId)->getSingleResult();
        if (!$tipoGrupo) {
          if($entity->getGrupo()->getClasificacion() == "Asociados"){
              $tipoGrupo = 1;
          }else{
              $tipoGrupo = 2;
          }
        }
        if (!$destinatarioId) {
          $integrante=$em->getRepository('WABackendBundle:IntegranteGrupo')->findOneByGrupo($entity->getGrupo()->getId());
          $destinatarioId=$integrante->getProveedorId();
        }
        
        if(!$entity){
            throw $this->createNotFoundException('Unable to find Envio entity.');
        }
        
        $adjuntos=array();$na=0;
        $carpetaAdmin='';
        if($entity->getAdjuntos()>0){
            $administrador = $em->getRepository('WABackendBundle:Administrador')->findOneById($session->get('id_usuario'));
            $fechaCreado = $administrador->getFechaCreado();
            $fechaCreado=$fechaCreado->format('Ymd');
            $carpetaAdmin =$fechaCreado.$administrador->getId();
            $ruta=$this->get('kernel')->getRootDir().'/../web/adjuntos/'.$entity->getId().'/'.$carpetaAdmin.'/otros';
            if (is_dir($ruta)) {
                if ($dh = opendir($ruta)) {
                    while (($file = readdir($dh)) !== false) {
                        if(!is_dir($file)){
                            $adjuntos[$na]=$file;
                            $na++;
                        }
                    }
                    closedir($dh);
                }
            }
            /*if (is_dir("./adjuntos/".$entity->getId())) {
                if ($dh = opendir("./adjuntos/".$entity->getId())) {
                    while (($file = readdir($dh)) !== false) {
                        if(!is_dir($file)){
                            $adjuntos[$na]=$file;
                            $na++;
                        }
                    }
                    closedir($dh);
                }
            }*/
        }
        $destinatario=array();
        $contenido=$entity->getContenido();
        $usuarioTipo=$request->get('Tipo');
        switch($usuarioTipo){
            case 'asociado':
                $Query = $em->createQuery('SELECT c FROM WABackendBundle:Cliente c WHERE c.id = :clienteId');
                $Query->setParameter('clienteId', $destinatarioId);
                
                $destinatarioEntitie = $Query->getArrayResult();
                $destinatarioEntitie=$destinatarioEntitie[0];
                
                $permitidos=array('zona','codigo','drogueria','nit','asociado','direcion','ciudad','ruta','depto','telefono','centro','email','emailAsociado');
                foreach($permitidos as $campo){
                    if(isset($destinatarioEntitie[$campo]))
                        $contenido = str_replace("//$campo//", utf8_decode($destinatarioEntitie[$campo]), $contenido);
                }
                $destinatario['Codigo']=$destinatarioEntitie['codigo'];
                $destinatario['Nit']=$destinatarioEntitie['nit'];
                $destinatario['id']=$destinatarioEntitie['id'];
            break;
            case 'proveedor':
                
                //$Query = $em->createQuery('SELECT p FROM WABackendBundle:Proveedor p WHERE p.id = :proveedorId');
                //$Query->setParameter('proveedorId', $request->get('destinatarioId'));
                
                $Query = $emp->createQuery('SELECT p.id, p.nit, p.nombre AS empresa, p.codigo, p.representanteLegal, p.emailRepresentanteLegal, p.telefonoRepresentanteLegal, cp.nombreContacto AS nombre,cp.ciudad,cp.email AS email1, cp.telefono, cp.movil, cp.fechaCreacion, cp.id AS idContacto FROM WABackendBundle:Contactos cp LEFT JOIN cp.idProveedor p WHERE cp.id = :destinatarioId');
                $Query->setParameter('destinatarioId', $destinatarioId);
                
                $destinatarioEntitie = $Query->getArrayResult();
                if (!$destinatarioEntitie) {
                  $Query = $emp->createQuery('SELECT p.id, p.nit, p.nombre AS empresa, p.codigo, p.representanteLegal AS nombre, p.emailRepresentanteLegal AS email1, p.telefonoRepresentanteLegal FROM WABackendBundle:Proveedores p WHERE p.id = :destinatarioId');
                  $Query->setParameter('destinatarioId', $destinatarioId);
                  
                  $destinatarioEntitie = $Query->getArrayResult();
                }
                $destinatarioEntitie=$destinatarioEntitie[0];
                
                $permitidos=array('nit','nombre','apellido','empresa','cargo','direccion','telefono','fax','celular','email1','email2','clasificacion');
                //$permitidos=array('nit','nombreContacto','nombre','telefono','movil','email');
                foreach($permitidos as $campo){
                    if(isset($destinatarioEntitie[$campo]))
                        $contenido = str_replace("//$campo//", utf8_decode($destinatarioEntitie[$campo]), $contenido);
                }
                
                $destinatario['Codigo']=$destinatarioEntitie['codigo'];
                $destinatario['Nit']=$destinatarioEntitie['nit'];
                //$destinatario['id']=$destinatarioEntitie['id'];
                $destinatario['id']=$destinatarioEntitie['id'];
            break;
        }
        if($entity->getCombinacion()==1 && $entity->getColumnaCombinacion()){
            $columna=$entity->getColumnaCombinacion();
            $cabezeraSabana = $em->getRepository('WABackendBundle:DatosEnvio')->findOneBy(array('envioId' => $envioId));
            $condicion = $destinatario[$columna];
            $sabanaDatos = $em->createQuery("SELECT u FROM WABackendBundle:ColumnasDatosEnvio u WHERE u.envioId = '".$envioId."' AND u.identificador  = '$condicion'")->execute();
            
            $resultado = strpos($contenido, '//TABLA//');
            $control=0;
            if($resultado !== FALSE){
                //Crear la tabla con el contenido para enviar.
                $columnas = unserialize($cabezeraSabana->getDatos());
                $contColumnas = count($columnas) - 1;
                $posicion = 0;
                $informacionTabla = '<table class="tablaDatos"><tr>';
                for ($i = 0; $i <= $contColumnas; $i++) {
                    $informacionTabla .= '<td>' . ucfirst(utf8_decode($columnas[$i])) . '</td>';
                }
                $informacionTabla .= '</tr>';
                foreach ($sabanaDatos as $dato) {
                    $adjuntosCsv[] = $dato->getId();
                    $data = unserialize($dato->getDatos());
                    $contColumnas = count($data) - 1;
                    $informacionTabla .= '<tr>';
                    for ($i = 0; $i <= $contColumnas; $i++) {
                        $informacionTabla .= '<td>' . $data[$i] . '</td>';
                    }
                    $informacionTabla .= '</tr>';
                }
                $informacionTabla .= '</table>';
                $control++;
                $contenido = str_replace("//TABLA//", $informacionTabla, $contenido);
            }
            $resultado = strpos($contenido, '//ARCHIVO//');
            if($resultado !== FALSE){
                //Crear link para descargar el archivo.
                $linkArchivo='<a style="color:#E01F69;font-size:24px;" href="'.$this->get('router')->generate('envio_xls', array('envio_id'=>$envioId,'tipo_usuario'=>$usuarioTipo,'id_usuario'=>$destinatario['id'],'llave'=>md5($envioId.$usuarioTipo.$destinatario['id'])), true).'" target="_blank">Clic aqu&iacute; para descargar Archivo</a>';
                $control++;
                $contenido = str_replace("//ARCHIVO//", $linkArchivo, $contenido);
            }
            $resultadoC = strpos($contenido, '//COMBINAR//');
            if($resultadoC !== FALSE){
                foreach ($sabanaDatos as $dato) {
                    $datosFila = unserialize($dato->getDatos());
                    $cantidadDatos = count($datosFila) - 1;
                    for($i = 0; $i <= $cantidadDatos; $i++){
                        $contenido = str_replace('//'.($i+1).'//', utf8_decode($datosFila[$i]), $contenido);
                    }
                }
                $contenido = str_replace('//COMBINAR//', '', $contenido);
            }
        }
        $auditoria->registralog('Vista previa mensaje', $session->get('id_usuario'));
        return $this->render('envio/vistaPreviaMensaje.html.twig', array('entity'=>$entity,'contenido'=>$contenido,'destinatario'=>$destinatarioEntitie,'adjuntos'=>$adjuntos,'usuarioTipo'=>$usuarioTipo,'tipoGrupo'=>$tipoGrupo,'carpetaAdmin'=>$carpetaAdmin));
    }
    
    public function leidosProveedorAction(Request $request){
        $idProveedor = $request->get('idProveedor');
        $envio_id = $request->get('idEnvio');
        
        return $this->render('envio/leidosProveedor.html.twig', array(
                    'idProveedor' => $idProveedor,
                    'idEnvio' => $envio_id,
                ));
    }
    
    
    public function leidosProveedorXmlAction(Request $request){
        if ($request->isXmlHttpRequest()){
            $session = $request->getSession();
            $auditoria = $this->get('utilidadesAdministrador');
            /*$permisos = $session->get('permisos');
            $auditoria = $this->get('auditoria');
            if(!$auditoria->seguridad_http('envio',3,$permisos))
                return $this->redirect($this->generateUrl('admin_login'));
            */
            $em = $this->getDoctrine()->getManager();
            $proveedor_id = $request->get('idProveedor');
            $envio_id = $request->get('idEnvio');
            
            $busqueda = $this->get('busqueda');
           
            $rows = $request->get('rows');
            $pagina = $request->get('page');
            $paginacion = ($pagina * $rows) - $rows;  
            
            $entities = $em->getRepository('WABackendBundle:EnvioLectura')->findBy(array('envioIntegrateId'=>$proveedor_id, 'envioId'=>$envio_id));
            
//            $Contador=$em->getConnection()->query($sql)->fetchColumn();
//            $numRegistros = $Contador;
//            $totalPagina = ceil($numRegistros / $rows);
            $response = new Response();
            $response->setStatusCode(200);
            $response->headers->set('Content-Type', 'text/xml');
            $auditoria->registralog('Listado leidos proveedor', $session->get('id_usuario'));
         
            return $this->render('WABackendBundle:Envio:leidos.xml.twig', array(
                                'entities' => $entities,'idProveedor' => $proveedor_id,
                                'idEnvio' => $envio_id,), $response);
        }
    }

    public function lecturaAction(Request $request){
      header('Content-type: image/png');
      echo gzinflate(base64_decode('6wzwc+flkuJiYGDg9fRwCQLSjCDMwQQkJ5QH3wNSbCVBfsEMYJC3jH0ikOLxdHEMqZiTnJCQAOSxMDB+E7cIBcl7uvq5rHNKaAIA'));
      $em = $this->getDoctrine()->getManager();
      $emp = $this->getDoctrine()->getManager('contactosProveedor');
      $idEnvio            =$request->get('idEnvio');
      $destinatarioId     = $request->get('destinatarioId');
      $tipoDestinatario   =$request->get('Tipo');
      $entityEnvio = $em->getRepository('WABackendBundle:Envio')->find($idEnvio);
      if($entityEnvio){
          /*$entityDestinatario = $em->getRepository('WABackendBundle:IntegranteGrupo')->find($destinatarioId);
          if($entityDestinatario){*/
              $destinatario=array();
              if($tipoDestinatario=='Drogueria' || $tipoDestinatario=='Asociado'){
                  $entityDestino = $em->getRepository('WABackendBundle:IntegranteGrupo')->find($destinatarioId); //$em->getRepository('WABackendBundle:Cliente')->find($destinatarioId);
                  if($tipoDestinatario=='Drogueria'){
                      $destinatario['nombre']=$entityDestino->getAsociado()->getDrogeria();
                      $destinatario['email']=$entityDestino->getAsociado()->getEmail();
                  }else{
                      $destinatario['nombre']=$entityDestino->getAsociado()->getAsociado();
                      $destinatario['email']=$entityDestino->getAsociado()->getEmailAsociado();
                  }
              }
              if($tipoDestinatario=='Proveedor'){
                  $entityDestino = $em->getRepository('WABackendBundle:IntegranteGrupo')->find($destinatarioId);
                  if ($entityDestino) {
                    $datosProveedor= $emp->getRepository('WABackendBundle:Proveedores')->find($entityDestino->getProveedorId());
                    $destinatario['nombre']=$datosProveedor->getNombre().' '.$datosProveedor->getRepresentanteLegal();
                    $destinatario['email']=$datosProveedor->getEmailRepresentanteLegal();
                  }
              }
              $lecturas = $em->getRepository('WABackendBundle:EnvioIntegrante')->findOneBy(array('integranteGrupo'=>$destinatarioId,'envio'=>$idEnvio));
              if($lecturas){
                  $lecturas->setEnviado($lecturas->getEnviado()+1);
                  $em->persist($lecturas);
              }
              if($destinatario){
                  $lectura=new EnvioLectura();
                  $lectura->setAsunto($entityEnvio->getAsunto());
                  $lectura->setIntegrante($destinatario['nombre']);
                  $lectura->setEmail($destinatario['email']);
                  $lectura->setFecha(new \DateTime());
                  $lectura->setIp($request->getClientIp());
                  $lectura->setEnvio($em->getReference('WABackendBundle:Envio', $idEnvio));
                  $lectura->setEnvioIntegrateId($destinatarioId);
                  $em->persist($lectura);
                  $em->flush();
                  echo "si";
              }
          //}
      }
      exit();
    }// end if
    
    
    /**
     * REcibe solicitud desde el email enviado y construye un CSV con los datos correspondientes al destinatario que reliza la solicitud.
     * @param int $id Identificador del envio.
     * @return \Symfony\Component\HttpFoundation\Response mixted json('Resultado de la operacion')
     * @author Julian Restrepo <j.restrepo@waplicaciones.co>
     * @category BackendBundle\Envio
     */
    public function xlsAction(Request $request) {
        //echo "entra";exit();
        ini_set("memory_limit", "1024M");
        ini_set('max_execution_time', 5000);
        $llave1=$request->get('llave');
        if(!$llave1){
            echo "Se requiere llave de acceso";exit();
        }
        $em = $this->getDoctrine()->getManager();
        $emp = $this->getDoctrine()->getManager('contactosProveedor');
        $envio = $em->getRepository('WABackendBundle:Envio')->find($request->get('envio_id'));
        if(!$envio){
            throw $this->createNotFoundException('La informaci&oacute;n requerida ya no esta disponible.');
        }
        if($request->get('tipo_usuario')== 'asociado' || $request->get('tipo_usuario')== 'Asociado' || $request->get('tipo_usuario')== 'drogueria' || $request->get('tipo_usuario')== 'Drogueria'){
            $datosUsuario = $em->getRepository('WABackendBundle:Cliente')->find($request->get('id_usuario'));
            $asociado = true;
        }else{
            //$datosUsuario = $em->getRepository('WABackendBundle:Proveedor')->find($request->get('id_usuario'));
            $datosUsuario = $emp->createQuery("SELECT p.id,p.codigo, cp.id AS idContacto, p.nit FROM WABackendBundle:Contactos cp LEFT JOIN cp.idProveedor p WHERE p.id=".$request->get('id_usuario'))->getResult();
            $asociado = false;
            if (!$datosUsuario) {
              $datosUsuario = $emp->createQuery('SELECT p.id, p.nit, p.codigo FROM WABackendBundle:Proveedores p WHERE p.id = :destinatarioId')->setParameter('destinatarioId', $request->get('id_usuario'))->getResult();
            }
            $datosUsuario = $datosUsuario[0];
            //var_dump($datosUsuario);exit();
        }
        if(!$datosUsuario){
            //throw $this->createNotFoundException('Imposible encontrar informacion del usuario.');
            echo "Lo sentimos, no encontramos información para entregarle.";exit();
        }
        $nameFile='';
        $idDestinatario=0;
        if ($envio->getColumnaCombinacion() == 'Codigo') {
            
            if($asociado){
                $parametroBusqueda = $datosUsuario->getCodigo();
                $posicion = 0;
                $nameFile=$datosUsuario->getCodigo();
                $idDestinatario=$datosUsuario->getId();
            }else{
                $parametroBusqueda = $datosUsuario['codigo'];
                $posicion = 0;
                $nameFile=$datosUsuario['codigo'];
                $idDestinatario=$datosUsuario['id'];
            }
            
            
        }else{
            
            if($asociado){
                $parametroBusqueda = $datosUsuario->getNit();
                $posicion = 0;
                $nameFile=$datosUsuario->getNit();
                $idDestinatario=$datosUsuario->getId();
            }else{
                $parametroBusqueda = $datosUsuario['nit'];
                $posicion = 0;
                $nameFile=$datosUsuario['nit'];
                $idDestinatario=$datosUsuario['id'];
            }
            
            
            
        }
        $llave2=md5($envio->getId().$request->get('tipo_usuario').$idDestinatario);
        if($llave1!=$llave2){
            echo "Llave de acceso no corresponde.";exit();
        }
        $condicion = $parametroBusqueda;
        $informacion = $em->createQuery("SELECT u FROM WABackendBundle:ColumnasDatosEnvio u WHERE u.envioId = '".$request->get('envio_id')."' AND u.identificador  = '$condicion'")->execute();
        $datosEnvio = $em->getRepository('WABackendBundle:DatosEnvio')->findOneBy(array('envioId' => $request->get('envio_id')));
        
        
        //$excelService = $this->get('xls.service_xls5');
        $excelService = $this->get('phpexcel')->createPHPExcelObject();
        //$excelService->excelObj->setActiveSheetIndex(0);
        $excelService->setActiveSheetIndex(0);
        $activeSheet = $excelService->getActiveSheet();
        $titulosColumnas = @unserialize($datosEnvio->getDatos());
        $cantidadColumnas = count($titulosColumnas) - 1;
        $cont = "A";
        for ($i = 0; $i <= $cantidadColumnas; $i++) {
            $activeSheet->setCellValue("$cont" . '1', ucfirst(trim($titulosColumnas[$i])));
            $cont++;
        }
        $num = 2;
        foreach ($informacion as $info) {
            $datos = @unserialize($info->getDatos());
            $contadorDatos = count($datos) - 1;
            $cont = "A";
            for ($i = 0; $i <= $contadorDatos; $i++) {
                $pesos=strpos(trim($datos[$i]), '$');
                if($pesos !== false){
                    $activeSheet->setCellValue("$cont" . $num, str_replace(array(' ','.','$',','),array('','','','.'),trim($datos[$i])));
                    $activeSheet->getStyle("$cont" . $num)->getNumberFormat()->setFormatCode('"$"#,##0.00_-');
                }else{
                    $activeSheet->setCellValue("$cont" . $num, ucfirst(trim($datos[$i])));
                }
                $cont++;
            }
            $num++;
        }
        /*$excelService->getActiveSheet()->setTitle('asociados');
        $response = $excelService->getResponse();
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment;filename='.$nameFile.'.xls');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        return $response;*/
        
        header('Content-Type: ');
        header('Content-Disposition: attachment;filename="'.$nameFile.'.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($excelService,'Excel2007');
        $objWriter->save('php://output'); 
        exit();  
    }

    public function xlsLeidosAsociadosAction(Request $request){
      ini_set("memory_limit", "1024M");
      ini_set('max_execution_time', 5000);
      $envioId = $request->get('envioId');
      $busqueda = $this->get('busquedaAdministrador');
      $filtro=$busqueda->busqueda();
      $filtroBusqueda = ($filtro) ? $filtro.' AND en.id='.$envioId : 'WHERE en.id='.$envioId;
      $OrdenTipo = $request->get('sord');
      $OrdenCampo = $request->get('sidx');
      $em = $this->getDoctrine()->getManager();
      $this->servicioexel = $this->get('phpexcel')->createPHPExcelObject();
      $this->servicioexel->setActiveSheetIndex(0);
      $this->activeSheet = $this->servicioexel->getActiveSheet();
      $nameFile='';
      if ($request->get('detalle')) { 
        $nameFile='detalle';
        $sql = "SELECT a.id, a.depto,a.ciudad,a.centro,a.codigo,a.drogueria,a.nit,a.asociado,a.email, a.email_asociado, ci.enviado AS enviado, ig.id as idIntegrante,ci.fecha_envio as fechaEnvio,el.fecha,el.ip 
              FROM envio en 
              INNER JOIN grupo g ON g.id = en.grupo_id 
              INNER JOIN integrante_grupo ig ON g.id = ig.grupo_id AND ig.activo=1
              INNER JOIN cliente a ON a.id=ig.asociado_id 
              LEFT JOIN envio_integrante ci ON ci.integrante_grupo_id=ig.id AND ci.envio_id=$envioId
              LEFT JOIN envio_lectura el ON el.envio_integrate_id=ig.id AND el.envio_id=$envioId    
              $filtroBusqueda
              ORDER BY $OrdenCampo $OrdenTipo";
        $asociado= $em->getConnection()->query($sql)->fetchAll();
        $titulos = array(1 => 'Drogueria',2 => 'Nit',3 => 'Email',4 => 'Codigo',5 => 'Ciudad',6 => 'Centro',7 => 'F.Envio',8 => 'F.Lectura',9 => 'IP');
        $num=1;
        $colum="A";
        $datos=array();
        $this->armarDatos($colum,9,$num,$titulos);
        foreach ($asociado as $key => $value) {
          $num++;
          $datos[1]=$value['drogueria'];
          $datos[2]=$value['nit'];
          $datos[3]=$value['email'];
          $datos[4]=$value['codigo'];
          $datos[5]=$value['ciudad'];
          $datos[6]=$value['centro'];
          $datos[7]=$value['fechaEnvio'];
          $datos[8]=$value['fecha'];
          $datos[9]=$value['ip'];
          $this->armarDatos($colum,9,$num,$datos);
        }
        unset($datos);
      }else{
        $nameFile='consolidado';
        $sql = "SELECT a.id, a.depto,a.ciudad,a.centro,a.codigo,a.drogueria,a.nit,a.asociado,a.email, a.email_asociado, ci.enviado AS enviado, ig.id as idIntegrante,ci.fecha_envio as fechaEnvio,COUNT(el.id) as leido,el.fecha,el.ip 
              FROM envio en 
              INNER JOIN grupo g ON g.id = en.grupo_id 
              INNER JOIN integrante_grupo ig ON g.id = ig.grupo_id AND ig.activo=1
              INNER JOIN cliente a ON a.id=ig.asociado_id 
              LEFT JOIN envio_integrante ci ON ci.integrante_grupo_id=ig.id AND ci.envio_id=$envioId
              LEFT JOIN envio_lectura el ON el.envio_integrate_id=ig.id AND el.envio_id=$envioId    
              $filtroBusqueda
              GROUP BY a.id
              ORDER BY $OrdenCampo $OrdenTipo";
        $asociado= $em->getConnection()->query($sql)->fetchAll();
        $titulos = array(1 => '# de veces leida',2 => 'Drogueria',3 => 'Nit',4 => 'Email',5 => 'Codigo',6 => 'Ciudad',7 => 'Centro',8 => 'F.Envio',9 => 'F.Lectura',10 => 'IP');
        $num=1;
        $colum="A";
        $datos=array();
        $this->armarDatos($colum,10,$num,$titulos);
        foreach ($asociado as $key => $value) {
          $num++;
          $datos[1]=$value['leido'];
          $datos[2]=$value['drogueria'];
          $datos[3]=$value['nit'];
          $datos[4]=$value['email'];
          $datos[5]=$value['codigo'];
          $datos[6]=$value['ciudad'];
          $datos[7]=$value['centro'];
          $datos[8]=$value['fechaEnvio'];
          $datos[9]=$value['fecha'];
          $datos[10]=$value['ip'];
          $this->armarDatos($colum,10,$num,$datos);
        }
        unset($datos);
      }
      header('Content-Type: ');
      header('Content-Disposition: attachment;filename="'.$nameFile.'.xlsx"');
      header('Cache-Control: max-age=0');
      $objWriter = \PHPExcel_IOFactory::createWriter($this->servicioexel,'Excel2007');
      $objWriter->save('php://output'); 
      exit();
    }

    public function xlsLeidosProveedorAction(Request $request){
      ini_set("memory_limit", "1024M");
      ini_set('max_execution_time', 5000);
      $envioId = $request->get('envioId');
      $em = $this->getDoctrine()->getManager();
      $emp = $this->getDoctrine()->getManager('contactosProveedor');
      $this->servicioexel = $this->get('phpexcel')->createPHPExcelObject();
      $this->servicioexel->setActiveSheetIndex(0);
      $this->activeSheet = $this->servicioexel->getActiveSheet();
      if ($request->get('detalle')) {
        $nameFile='detalle';
        $sql = "SELECT el.ip,el.fecha AS fechaLectura,ei.fecha_envio AS fechaEnvio,ig.proveedor_id AS proveedorId, ig.contacto_proveedor_id AS contactoProveedorId
                FROM envio en 
                JOIN envio_lectura el ON el.envio_id=en.id
                JOIN envio_integrante ei ON ei.integrante_grupo_id=el.envio_integrate_id
                JOIN integrante_grupo ig ON ig.id=el.envio_integrate_id
                WHERE en.id=$envioId";
        $envioIntegrante= $em->getConnection()->query($sql)->fetchAll();
        $titulos = array(1 => 'Proveedor',2 => 'Nit',3 => 'Codigo',4 => 'Nombre Representante',5 => 'Email Representante',6 => 'Nombre Contacto',7 => 'Email',8 => 'F.Envio',9 => 'F.Lectura',10 => 'IP');
        $num=1;
        $colum="A";
        $datos=array();
        $this->armarDatos($colum,10,$num,$titulos);
        foreach ($envioIntegrante as $key => $envio) {
          $num++;
          if ($envio['contactoProveedorId']!=null) {
            $contatoProveedor=$emp->getRepository('WABackendBundle:Contactos')->find($envio['contactoProveedorId']);
            $datos[1]=$contatoProveedor->getIdProveedor()->getNombre();
            $datos[2]=$contatoProveedor->getIdProveedor()->getNit();
            $datos[3]=$contatoProveedor->getIdProveedor()->getCodigo();
            $datos[4]=$contatoProveedor->getIdProveedor()->getRepresentanteLegal();
            $datos[5]=$contatoProveedor->getIdProveedor()->getEmailRepresentanteLegal();
            $datos[6]=$contatoProveedor->getNombreContacto();
            $datos[7]=$contatoProveedor->getEmail();
          }else{
            $proveedor=$emp->getRepository('WABackendBundle:Proveedores')->find($envio['proveedorId']);
            $datos[1]=$proveedor->getNombre();
            $datos[2]=$proveedor->getNit();
            $datos[3]=$proveedor->getCodigo();
            $datos[4]=$proveedor->getRepresentanteLegal();
            $datos[5]=$proveedor->getEmailRepresentanteLegal();
            $datos[6]='';
            $datos[7]='';
          }
          $datos[8]=$envio['fechaEnvio'];
          $datos[9]=$envio['fechaLectura'];
          $datos[10]=$envio['ip'];
          $this->armarDatos($colum,10,$num,$datos);
        }
        unset($datos);
      }else{
        $nameFile='consolidado';
        $sql = "SELECT el.ip,el.fecha AS fechaLectura,ei.fecha_envio AS fechaEnvio,ig.proveedor_id AS proveedorId, ig.contacto_proveedor_id AS contactoProveedorId, COUNT(el.envio_integrate_id) AS leido
                FROM envio en 
                JOIN envio_lectura el ON el.envio_id=en.id
                JOIN envio_integrante ei ON ei.integrante_grupo_id=el.envio_integrate_id
                JOIN integrante_grupo ig ON ig.id=el.envio_integrate_id
                WHERE en.id=$envioId
                GROUP BY el.envio_integrate_id";
        $envioIntegrante= $em->getConnection()->query($sql)->fetchAll();
        $titulos = array(1 => '# de veces leida',2 => 'Proveedor',3 => 'Nit',4 => 'Codigo',5 => 'Nombre Representante',6 => 'Email Representante',7 => 'Nombre Contacto',8 => 'Email',9 => 'F.Envio',10 => 'F.Lectura',11 => 'IP');
        $num=1;
        $colum="A";
        $datos=array();
        $this->armarDatos($colum,11,$num,$titulos);
        foreach ($envioIntegrante as $key => $envio) {
          $num++;
          if ($envio['contactoProveedorId']!=null) {
            $contatoProveedor=$emp->getRepository('WABackendBundle:Contactos')->find($envio['contactoProveedorId']);
            $datos[1]=$envio['leido'];
            $datos[2]=$contatoProveedor->getIdProveedor()->getNombre();
            $datos[3]=$contatoProveedor->getIdProveedor()->getNit();
            $datos[4]=$contatoProveedor->getIdProveedor()->getCodigo();
            $datos[5]=$contatoProveedor->getIdProveedor()->getRepresentanteLegal();
            $datos[6]=$contatoProveedor->getIdProveedor()->getEmailRepresentanteLegal();
            $datos[7]=$contatoProveedor->getNombreContacto();
            $datos[8]=$contatoProveedor->getEmail();
          }else{
            $proveedor=$emp->getRepository('WABackendBundle:Proveedores')->find($envio['proveedorId']);
            $datos[1]=$envio['leido'];
            $datos[2]=$proveedor->getNombre();
            $datos[3]=$proveedor->getNit();
            $datos[4]=$proveedor->getCodigo();
            $datos[5]=$proveedor->getRepresentanteLegal();
            $datos[6]=$proveedor->getEmailRepresentanteLegal();
            $datos[7]='';
            $datos[8]='';
          }
          $datos[9]=$envio['fechaEnvio'];
          $datos[10]=$envio['fechaLectura'];
          $datos[11]=$envio['ip'];
          $this->armarDatos($colum,11,$num,$datos);
        }
        unset($datos);
      }
      header('Content-Type: ');
      header('Content-Disposition: attachment;filename="'.$nameFile.'.xlsx"');
      header('Cache-Control: max-age=0');
      $objWriter = \PHPExcel_IOFactory::createWriter($this->servicioexel,'Excel2007');
      $objWriter->save('php://output'); 
      exit();
    }

    private function armarDatos($columnainicial,$ncolumnas,$nfila,$datos){
      for ($i=1; $i <= $ncolumnas ; $i++) {
        $this->activeSheet->setCellValue($columnainicial.$nfila, $datos[$i]);
        $columnainicial++;
      }
    }
}//fin class
