<?php

namespace WA\BackendBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function loginAction()
    {	
        return $this->render('WABackendBundle:Default:login.html.twig');
    }

     public function logoutAction(Request $request) {

        $request = 
        $session = $request->getSession();
        
        $session->set('autenticado',false);        
        $session->invalidate();
        return $this->redirect($this->generateUrl('default_login'));
    }
}
