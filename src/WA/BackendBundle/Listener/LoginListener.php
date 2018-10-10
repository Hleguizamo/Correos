<?php

namespace WA\BackendBundle\Listener;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;

use WA\BackendBundle\Entity\Administrador;

class LoginListener implements AuthenticationSuccessHandlerInterface, AuthenticationFailureHandlerInterface, LogoutSuccessHandlerInterface {
    private $container;
    private $em;
    /**
     * Constructor
     * @param container   $container
     */
    public function __construct($container){
        $this->container = $container;
        $this->em = $this->container->get('doctrine')->getManager();
        /*
        $em= $this->container->get('doctrine')->getManager();
        $admin=$em->getRepository('SIPAdministradorBundle:Administradores')->findAll();
        var_dump($admin);exit();
        */
    }
    /**
     * This is called when an interactive authentication attempt succeeds. This
     * is called by authentication listeners inheriting from AbstractAuthenticationListener.
     * @param Request        $request
     * @param TokenInterface $token
     * @return Response The response to return
     */
    function onAuthenticationSuccess(Request $request, TokenInterface $token){    
        //echo "entra";exit();
        if($token->getUser()->getActivo()=='1'){

            $user=$token->getUser();

            $conn = $this->em->getConnection();
            $session = $request->getSession();
            $sql = 'SELECT m.id, m.titulo, m.controlador, m.icono, m.menu_padre_id as menuPadre, pm.permiso FROM menu m 
            INNER JOIN permisos_menu pm ON pm.menu_id = m.id WHERE pm.administrador_id = ' . $token->getUser()->getId() . ' AND pm.permiso!=0 GROUP BY 1';
            $userAccessMenu = $conn->query($sql)->fetchAll();
            $permisos = array();
            $primer_permiso = null;
            foreach ($userAccessMenu as $e){
                if (!$primer_permiso){
                    if ($e['controlador'] != 'none'){
                        $primer_permiso = $e['controlador'];
                    }
                }
                $permisos[$e['controlador']]['controlador'] = $e['controlador'];
                $permisos[$e['controlador']]['titulo'] = $e['titulo'];
                $permisos[$e['controlador']]['permiso'] = $e['permiso'];
                $permisos[$e['controlador']]['icono'] = $e['icono'];
                $permisos[$e['controlador']]['raiz'] = $e['menuPadre'];
                $permisos[$e['controlador']]['tipo'] = 1;
            }
            if ($primer_permiso) {
                $session->set('administrador',$user);
                $session->set('id_usuario', $token->getUser()->getId());
                $session->set('nombreAdmin', $token->getUser()->getNombre());
                $session->set('emailAdmin', $token->getUser()->getEmail());
                $session->set('emailClave', $token->getUser()->getClaveEmail());
                $session->set('autenticado',true);
                $session->set('permisos', $permisos);
                $session->set('last_time', time());
     
                $uri = $this->container->get('router')->generate($primer_permiso);
            } else {
                $error = 'El usuario no tiene permisos asignados';
                $uri = $this->container->get('router')->generate('default_login');
            }
        }else{
            $error = 'Cuenta innactiva';
            $uri = $this->container->get('router')->generate('default_login');
        }
        return new RedirectResponse($uri);
    }

    /**
     * This is called when an interactive authentication attempt fails. This is
     * called by authentication listeners inheriting from
     * AbstractAuthenticationListener.
     *
     * @param Request                 $request
     * @param AuthenticationException $exception
     *
     * @return Response The response to return, never null
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception){

        $usuario = $request->request->get('_username');
        $request->getSession()->getFlashBag()->add('error', $exception->getMessage());
        $uri = $this->container->get('router')->generate('default_login');
        $response = new RedirectResponse($uri);
        return $response;

        return new RedirectResponse($request);
    }

    /**
     * Creates a Response object to send upon a successful logout.
     *
     * @param Request $request
     *
     * @return Response never null
     */
    public function onLogoutSuccess(Request $request)
    {
        if ($this->container->get('security.context')->getToken()){

            $session=$request->getSession();

            $util=$this->container->get('utilidadesAdministrador');
            $util->registralog('1.6 Finaliza sesion. ',$session->get('administradorId'));

            
            $session->invalidate();
         
        }

        $uri = $this->container->get('router')->generate('default_login');
        $response = new RedirectResponse($uri);
        return $response;
    }

}
