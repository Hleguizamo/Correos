<?php

namespace WA\BackendBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use WA\BackendBundle\Entity\Configuracion;
use WA\BackendBundle\Form\ConfiguracionType;

/**
 * Configuracion controller.
 *
 * @Route("/configuracion")
 */
class ConfiguracionController extends Controller
{
    /**
     * Lists all Configuracion entities.
     *
     * @Route("/", name="configuracion_index")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {

      $session = $request->getSession();
      if( $session->get('autenticado') ){
        $auditoria = $this->get('utilidadesAdministrador');
        $em = $this->getDoctrine()->getManager();

        $configuracion =$em->createQueryBuilder()
        ->select('c')
        ->from('WABackendBundle:Configuracion','c')
        ->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult();
          //dump($configuracion);exit;

        if($configuracion){

          $editForm = $this->createForm('WA\BackendBundle\Form\ConfiguracionType', $configuracion);
          $editForm->handleRequest($request);
          $auditoria->registralog('Edicion configuracion', $session->get('id_usuario'));
          return $this->render('configuracion/edit.html.twig', array(
            'configuracion' => $configuracion,
            'form' => $editForm->createView(),
            'menu'=>'configuracion'
            ));

        }else{
          
          $configuracion = new Configuracion();
          $form = $this->createForm('WA\BackendBundle\Form\ConfiguracionType', $configuracion);
          $form->handleRequest($request);

          $auditoria->registralog('Nueva configuracion', $session->get('id_usuario'));
          return $this->render('configuracion/new.html.twig', array(
            'configuracion' => $configuracion,
            'form' => $form->createView(),
            'menu'=>'configuracion'
            ));
        }

      }else{
        $uri = $this->container->get('router')->generate('default_login');
        return new RedirectResponse($uri);
      }

    }

    /**
     * Creates a new Configuracion entity.
     *
     * @Route("/new", name="configuracion_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {

      $response=new Response;

      try{

        $configuracion = new Configuracion();
        $form = $this->createForm('WA\BackendBundle\Form\ConfiguracionType', $configuracion);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        $em->persist($configuracion);
        $em->flush();

        $response->setStatusCode(200);
        
      }catch(\Exception $e){
      //  dump($e->getMessage());exit;
        $response->setStatusCode(500);
      }

      return $response;
      
    }//end action


    /**
     * Displays a form to edit an existing Configuracion entity.
     *
     * @Route("/{id}/edit", name="configuracion_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Configuracion $configuracion)
    {

      $response=new Response;

      try{

        $editForm = $this->createForm('WA\BackendBundle\Form\ConfiguracionType', $configuracion);
        $editForm->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        $em->persist($configuracion);
        $em->flush();


        $response->setStatusCode(200);
      }catch(\Exception $e){
      //  dump($e->getMessage());exit;
        $response->setStatusCode(500);
      }

      return $response;

    }//end action
  }
