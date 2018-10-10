<?php

namespace WA\BackendBundle\Controller;

use symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use WA\BackendBundle\Entity\Administrador;
use WA\BackendBundle\Form\AdministradorType;

/**
 * Administrador controller.
 *
 * @Route("/administrador")
 */
class AdministradorController extends Controller
{
    /**
     * Lists all Administrador entities.
     *
     * @Route("/", name="administrador_index")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
      $session=$request->getSession();

      if( $session->get('autenticado') ){
        $auditoria = $this->get('utilidadesAdministrador');
        $permisos=$session->get('permisos');
        $auditoria->registralog('Inicio Administrador', $session->get('id_usuario'));
        return $this->render('administrador/index.html.twig',array('menu'=>'usuarios','permiso'=>$permisos));

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
    * @category Correos\Administrador
    */
    public function xmlAction(Request $request){

      $session=$request->getSession();

      if ($request->isXmlHttpRequest() && $session->get('autenticado') ){
        $auditoria = $this->get('utilidadesAdministrador');
        $busqueda = $this->get('busquedaAdministrador');
        $where=$busqueda->busqueda();
        $ordenTipo = $request->get('sord');
        $ordenCampo = $request->get('sidx');
        $rows = $request->get('rows');
        $pagina = $request->get('page');
        $paginacion = ($pagina * $rows) - $rows;

        $em = $this->getDoctrine()->getManager();
        $entities = $em->createQuery(' SELECT a FROM WABackendBundle:Administrador a  '.$where.' ORDER BY '.$ordenCampo.' '.$ordenTipo);
        $entities->setMaxResults($rows);
        $entities->setFirstResult($paginacion);
        $entities = $entities->getResult();   

        $contador=$em->createQuery(" SELECT COUNT(a.id) AS contador FROM WABackendBundle:Administrador a ".$where)->getSingleResult();
        $numRegistros = $contador['contador'];
        $totalPagina = ceil($numRegistros / $rows);        
        $response = new Response();
        $response->setStatusCode(200);

        $response->headers->set('Content-Type', 'text/xml');
        $auditoria->registralog('Listado Administrador', $session->get('id_usuario'));
        return $this->render('administrador/index.xml.twig', array(
                'entities' => $entities,'numRegistros' => $numRegistros,
                'maxPagina' => $totalPagina,'pagina' => $pagina), $response);
      
      }else{
        $uri = $this->container->get('router')->generate('default_login');
        return new RedirectResponse($uri);
      }

    } 

    /**
     * Creates a new Administrador entity.
     *
     * @Route("/new", name="administrador_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
      //var_dump($request);exit();
      $session=$request->getSession();

      if( $session->get('autenticado') ){
        $auditoria = $this->get('utilidadesAdministrador');
        $administrador = new Administrador();
        $form = $this->createForm('WA\BackendBundle\Form\AdministradorType', $administrador);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

          $response=new Response;
          $response->setStatusCode(200);
          $response->headers->set('Content-Type', 'application/json');

          try{

            $clave=$request->get('clave');
            $claveEmail = $request->get('claveEmail');
            $usuario=$administrador->getUsuario();
            $json = array();
              
            $em = $this->getDoctrine()->getManager();
              
            //verificar longitud de la clave          
            if(strlen($clave)<8){
                $json['status'] = 2;
                $response->setContent(json_encode($json));
                return $response;  
            }
              
            //verificar disponibilidad del nombre de usuario
            $contador=$em->createQueryBuilder()
            ->select('COUNT(a.id) contador')
            ->from('WABackendBundle:Administrador','a')
            ->where('a.usuario=?1')
            ->setParameter(1,$administrador->getUsuario())
            ->getQuery()
            ->getOneOrNullResult();
              

            if($contador['contador']>=1){

              $json['status'] = 3;
              $response->setContent(json_encode($json));
              return $response; 

            }

            $administrador->setClave($clave);
            $administrador->setClaveEmail($claveEmail);
            $administrador->setFechaCreado(new \DateTime('now'));
            $administrador->setCreadorId(1);

            $em->persist($administrador);
            $em->flush();
            $auditoria->registralog('Nuevo Administrador', $session->get('id_usuario'));
            $json['status'] = 1;
            $response->setContent(json_encode($json));
          }catch(\Exception $e){
            $json['status'] = 1;
            $response->setStatusCode(500);
          }

          return $response;
          

        }

        return $this->render('administrador/new.html.twig', array(
          'administrador' => $administrador,
          'form' => $form->createView(),
          ));

      }else{
        $uri = $this->container->get('router')->generate('default_login');
        return new RedirectResponse($uri);
      }


    }//end function

    /**
     * Displays a form to edit an existing Administrador entity.
     *
     * @Route("/{id}/edit", name="administrador_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Administrador $administrador)
    {

      $session=$request->getSession();

      if( $session->get('autenticado') ){
        $auditoria = $this->get('utilidadesAdministrador');
        $editForm = $this->createForm('WA\BackendBundle\Form\AdministradorType', $administrador);
        $editForm->handleRequest($request);

        $em = $this->getDoctrine()->getManager();

        if ($editForm->isSubmitted()) {

          $response=new Response;
          $response->setStatusCode(200);
          $response->headers->set('Content-Type', 'application/json');

          try{

            $clave=$request->get('clave');
            $claveEmail = $request->get('claveEmail');
            $json = array();
              
            
              
            //verificar longitud de la clave          
            if(strlen($clave)<8){
                $json['status'] = 2;
                $response->setContent(json_encode($json));
                return $response;  
            }

            $administrador->setClave($clave);
            $administrador->setClaveEmail($claveEmail);

            $em->persist($administrador);
            $em->flush();
            $auditoria->registralog('Edito Administrador', $session->get('id_usuario'));
            $json['status'] = 1;

          }catch(\Exception $e){

            $json['status'] = 1;
            $response->setStatusCode(500);
          }
          $response->setContent(json_encode($json));
          return $response;
        
        }

        return $this->render('administrador/edit.html.twig', array(
          'administrador' => $administrador,
          'form' => $editForm->createView(),
          ));
      }else{

        $uri = $this->container->get('router')->generate('default_login');
        return new RedirectResponse($uri);
      }

    }//endif

    /**
     * Deletes a Administrador entity.
     *
     * @Route("/{id}", name="administrador_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Administrador $administrador)
    {

      $session=$request->getSession();

      if( $session->get('autenticado') ){
        $auditoria = $this->get('utilidadesAdministrador');
        $conexion=$this->getDoctrine()->getManager()->getConnection();
        $response=new Response;
        $response->setStatusCode(200);

        try{
          
          $conexion->beginTransaction();
          $idAdmin=$administrador->getId();

          //administrador referencia tabla grupo.

          $grupo = $conexion->query( 'SELECT COUNT(id) AS contador FROM grupo WHERE administrador_id = '.$idAdmin );
          $grupo = $grupo->fetch();

          if( $grupo['contador'] >= 1 ){

            $response->headers->set('Content-Type', 'application/json');
            $json = array();
            $json['status'] = 2;
            $response->setContent(json_encode($json));
            return $response;
          }

          $conexion->query(' DELETE FROM permisos_menu WHERE administrador_id='.$idAdmin);
          $conexion->query(' DELETE FROM administrador WHERE id='.$idAdmin);

          
          $conexion->commit();
          $auditoria->registralog('Eliminacion Administrador', $session->get('id_usuario'));
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

    public function permisosAction(Request $request, Administrador $administrador){

      $session=$request->getSession();

      if( $session->get('autenticado') ){
        $auditoria = $this->get('utilidadesAdministrador');
        $em=$this->getDoctrine()->getManager();

        //menu administradores
        $menu=$em->createQueryBuilder()
                ->select('m.titulo, m.id')
                ->from('WABackendBundle:Menu','m')
                ->getQuery()
                ->getResult();
        //dump($menu);exit();

        $arrayMenu=array();
        foreach ($menu as  $value) 
          $arrayMenu[$value['id']]=$value['titulo'];

        $permisos=$em->createQueryBuilder()
                    ->select(' m.id menu, pm.permiso')
                    ->from('WABackendBundle:PermisosMenu','pm')
                    ->join('pm.menu','m')
                    ->join('pm.administrador','a')
                    ->where('a.id=:admin')
                    ->setParameter(':admin',$administrador->getId())
                    ->getQuery()
                    ->getResult();
        //dump($permisos);exit();
        $arrayPermisos=array();
        foreach ($permisos as $value) 
          $arrayPermisos[ $value['menu'] ]= $value['permiso'];

        unset($menu, $permisos);
        $auditoria->registralog('Formulario de permisos Administrador', $session->get('id_usuario'));
        return $this->render('administrador/formPermisos.html.twig',array(
          'menus'=>$arrayMenu, 
          'permisos'=>$arrayPermisos,
          'administrador'=>$administrador
          ));

      }else{
        
        $uri = $this->container->get('router')->generate('default_login');
        return new RedirectResponse($uri);

      }

    }//endaction

    public function editPermisosAction(Request $request, Administrador $administrador){

      $session=$request->getSession();

      if( $session->get('autenticado') ){
        $auditoria = $this->get('utilidadesAdministrador');
        $conexion=$this->getDoctrine()->getManager()->getConnection();
        $response=new Response;

        try{
          
          $conexion->beginTransaction();

          $idAdmin=$administrador->getId();
          $conexion->query(' DELETE FROM permisos_menu WHERE administrador_id='.$idAdmin);

          $permisos=$request->get('permisos');
          $insert=' INSERT INTO  permisos_menu (menu_id, administrador_id, permiso) VALUES ';
          $values='';

          foreach ($permisos as $key => $value) {
            if( $values!='' )
              $values.=', ';

            $values.='( '.$key.', '.$idAdmin.','.$value.' )';

          }

          $conexion->query($insert.$values);
          $response->setStatusCode(200);
          $conexion->commit();
          $auditoria->registralog('Edito permisos Administrador', $session->get('id_usuario'));
        }catch(\Exception $e){

          $response->setStatusCode(500);
          $conexion->rollBack();

        }

        return $response;

      }else{
        $uri = $this->container->get('router')->generate('default_login');
        return new RedirectResponse($uri);
      }
 
    }//endaction

  }//end class
