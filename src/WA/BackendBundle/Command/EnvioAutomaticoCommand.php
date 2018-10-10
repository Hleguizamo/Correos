<?php
namespace WA\BackendBundle\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use WA\BackendBundle\Entity\EnvioIntegrante;
class EnvioAutomaticoCommand extends ContainerAwareCommand{
    protected function configure(){
        parent::configure();
        $this->setName('correo:programado')->setDescription('Envio automatico de correos.');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output){
        //$output->writeln("entro a la tarea.");exit();
        set_time_limit(0);
        ini_set('memory_limit', '2048M');
        
        $em = $this->getContainer()->get('doctrine')->getEntityManager();
        $emp = $this->getContainer()->get('doctrine')->getEntityManager('contactosProveedor');
        

        //$entity = $em->getRepository('WABackendBundle:Envio')->find($request->get('envioId'));
        $envios = $em->getRepository('WABackendBundle:Envio')->findBy(array('programado' => 1));
        

        foreach($envios as $entity){
            
            $adjuntos=array();
            $na=0;
            if($entity->getAdjuntos()>0){
                
                //$ruta=$this->get('kernel')->getRootDir().'/../web/adjuntos/'.$entity->getId();
                
                if (is_dir($this->getContainer()->get('kernel')->getRootDir().'/../web/adjuntos/'.$entity->getId())) {
                    //if ($dh = opendir("./adjuntos/".$entity->getId())) {
                    if ($dh = opendir($this->getContainer()->get('kernel')->getRootDir().'/../web/adjuntos/'.$entity->getId())) {
                        while (($file = readdir($dh)) !== false) {
                            if(!is_dir($file)){
                                //$adjuntos[$na]="/adjuntos/".$entity->getId().'/'.$file;
                                $adjuntos[$na]= $this->getContainer()->get('kernel')->getRootDir().'/../web/adjuntos/'.$entity->getId().'/'.$file;
                                $output->writeln("archivo ".$adjuntos[$na]);
                                $na++;
                            }else{
                                $output->writeln("es un directorio "."/adjuntos/".$entity->getId()."/".$file);
                                if(is_dir($this->getContainer()->get('kernel')->getRootDir().'/../web/adjuntos/'.$entity->getId().'/'.$file)){
                                    
                                    if(is_dir($this->getContainer()->get('kernel')->getRootDir().'/../web/adjuntos/'.$entity->getId().'/'.$file.'/otros')){
                                        if($carpetaAdjuntos = opendir($this->getContainer()->get('kernel')->getRootDir().'/../web/adjuntos/'.$entity->getId().'/'.$file.'/otros/')){

                                            while (($archivoAdmin = readdir($carpetaAdjuntos)) !== false) {
                                                if(!is_dir($archivoAdmin)){
                                                    $adjuntos[$na]= $this->getContainer()->get('kernel')->getRootDir().'/../web/adjuntos/'.$entity->getId().'/'.$file.'/otros/'.$archivoAdmin;
                                                    $output->writeln("archivo ".$adjuntos[$na]);
                                                    $na++;
                                                }
                                            }
                                        }
                                    }
                                    
                                    
                                }
                            }
                        }
                        closedir($dh);
                    }else{$output->writeln("no se pudo abrir el directorio "."/adjuntos/".$entity->getId());}
                }else{$output->writeln("no es un directorio "."./adjuntos/".$entity->getId());}
            }
            $contenido=$entity->getContenido();

            $destinatario=array();
            
            if($entity->getGrupo()->getAgruparProveedor() == 1  && $entity->getGrupo()->getClasificacion() == "No Aplica"){
                $groupBy = " GROUP BY pr.nit ";
            }else{
                $groupBy = "";
            }
            
            
            $sql="SELECT ig.id, ig.asociado_id, ig.proveedor_id, ig.grupo_id, ei.id AS enviado 
                FROM integrante_grupo ig 
                LEFT JOIN envio_integrante ei ON ig.id = ei.integrante_grupo_id AND ei.envio_id=".$entity->getId()." 
                WHERE ig.activo=1 AND ig.grupo_id=".$entity->getGrupo()->getId().$groupBy;
            $idsDestinatarios= $em->getConnection()->query($sql)->fetchAll();
            
            foreach($idsDestinatarios as $iddestino){
                
                    $output->writeln("Destinatario [".$iddestino['id']."]");
                    $entityDestinatario = $em->getRepository('WABackendBundle:IntegranteGrupo')->findOneById($iddestino['id']);
                    
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
                            $output->writeln("Destinatario [".$destinatarioEntitie['asociado']."] email [".$destinatarioEntitie['emailAsociado']."]");
                            
                            $destinatario['email']=$destinatarioEntitie['emailAsociado'];
                            $destinatario['nombre']=$destinatarioEntitie['asociado'];
                            $destinatario['Codigo']=$destinatarioEntitie['codigo'];
                            $destinatario['Nit']=$destinatarioEntitie['nit'];
                            $destinatario['id']=$destinatarioEntitie['id'];
                            $destinatario['tipo']='Drogueria';
                        }else{
                            $output->writeln("Destinatario [".$destinatarioEntitie['drogueria']."] email [".$destinatarioEntitie['email']."]");
                            
                            $destinatario['email']=$destinatarioEntitie['email'];
                            $destinatario['nombre']=$destinatarioEntitie['drogueria'];
                            $destinatario['Codigo']=$destinatarioEntitie['codigo'];
                            $destinatario['Nit']=$destinatarioEntitie['nit'];
                            $destinatario['id']=$destinatarioEntitie['id'];
                            $destinatario['tipo']='Asociado';
                        }

                    }else{
                        
                        
                        if($entity->getGrupo()->getAgruparProveedor() == 1 && $entity->getGrupo()->getClasificacion() == "No Aplica"){
                            $nitProveedores = $entityDestinatario->getProveedor()->getNit();

                            $proveedores = $em->getRepository('WABackendBundle:Proveedor')->findBy(array('nit' => $nitProveedores));

                            foreach($proveedores as $proveedorDestino){

                                $destinatario[$proveedorDestino->getId()]['email']=$proveedorDestino->getEmail1();
                                $destinatario[$proveedorDestino->getId()]['email1']=$proveedorDestino->getEmail2();
                                $destinatario[$proveedorDestino->getId()]['nombre']=$proveedorDestino->getNombre().' '.$proveedorDestino->getApellido();
                                $destinatario[$proveedorDestino->getId()]['Codigo']=$proveedorDestino->getCodigo();
                                $destinatario[$proveedorDestino->getId()]['Nit']=$proveedorDestino->getNit();
                                $destinatario[$proveedorDestino->getId()]['id']=$proveedorDestino->getId();
                                $destinatario['tipo']='Proveedor';
                            }
                        }else{

                            /*$Query = $em->createQuery('SELECT p FROM WABackendBundle:Proveedor p WHERE p.id = :proveedorId');
                            $Query->setParameter('proveedorId', $entityDestinatario->getProveedor()->getId());*/
                            
                            $Query = $emp->createQuery('SELECT p.nit, c.nombreContacto AS nombre, p.nombre AS empresa, c.telefono AS telefono, c.movil AS celular, c.email AS email1, p.codigo, c.id FROM WABackendBundle:Proveedores p JOIN WABackendBundle:Contactos c WITH c.idProveedor = p.id WHERE c.id = :contactoId');
                            $Query->setParameter('contactoId', $entityDestinatario->getProveedorId());
                            
                            $destinatarioEntitie = $Query->getArrayResult();
                            $destinatarioEntitie=$destinatarioEntitie[0];
                            $permitidos=array('nit','nombre','apellido','empresa','cargo','direccion','telefono',
                            'fax','celular','email1','email2','clasificacion');
                            
                            foreach($permitidos as $campo){
                                if(isset($destinatarioEntitie[$campo]))
                                    $contenido = str_replace("//$campo//", utf8_decode($destinatarioEntitie[$campo]), $contenido);
                            }
                            $output->writeln("Destinatario [".$destinatarioEntitie['nombre']."] email [".$destinatarioEntitie['email1']."]");
                            $destinatario['email']=$destinatarioEntitie['email1'];
                            //$destinatario['email1']=$destinatarioEntitie['email2'];
                            $destinatario['nombre']=$destinatarioEntitie['nombre'];
                            $destinatario['Codigo']=$destinatarioEntitie['codigo'];
                            $destinatario['Nit']=$destinatarioEntitie['nit'];
                            $destinatario['id']=$destinatarioEntitie['id'];
                            $destinatario['tipo']='Proveedor';

                        }
                        
                        
                        
                        
                    }

                if($entity->getCombinacion()==1){
                    $columna=$entity->getColumnaCombinacion();

                    $idEnvio=$entity->getId();            
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
                            //Crear link para descargar el archivo.
                            /*$linkArchivo='<a style="color:#E01F69;font-size:24px;" href="'.$this->get('router')->generate('envio_xls', array('envio_id'=>$entity->getId(),'tipo_usuario'=>$usuarioTipo,'id_usuario'=>$destinatario['id'],'llave'=>md5($entity->getId().$usuarioTipo.$destinatario['id'])), true).'" target="_blank">Clic aqu&iacute; para descargar Archivo</a>';*/
                            
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
                    $message = new \Swift_Message();
                    
                    $container = $this->getContainer();
                    $mailer = $container->get('mailer');
                    
                    $message->setSubject($entity->getAsunto());

                    //$message->setFrom($session->get('correo'),$session->get('nombre'))->setContentType('text/html');
                    $message->setFrom($entity->getAdministrador()->getEmail(),$entity->getAdministrador()->getNombre())->setContentType('text/html');

                    //$message->addTo('alejandroardilaardila@gmail.com','Alejandro Ardila Ardila');
                    //$message->addTo($destinatario['email'],$destinatario['nombre']);
                    
                    if($entityDestinatario->getAsociado()){
                        //var_dump($destinatario);exit();
                        $message->addTo($destinatario['email'],$destinatario['nombre']);
                    }else{
                        if($entity->getGrupo()->getAgruparProveedor() == 1 && $entity->getGrupo()->getClasificacion() == "No Aplica"){
                            //var_dump($destinatario);exit();
                            $arrayDestino = array();
                            foreach($destinatario as $destino){
                                //echo $destino['email'];
                                if(isset($destino['email'])){

                                    if(filter_var($destino['email'], FILTER_VALIDATE_EMAIL)){
                                        $arrayDestino[$destino['email']] = $destino['nombre'];
                                        //$message->addTo($destino['email'],$destino['nombre']);
                                    }
                                }
                                if(isset($destino['email1'])){
                                    if(filter_var($destino['email1'], FILTER_VALIDATE_EMAIL))
                                        //$message->addTo($destino['email1'],$destino['nombre']);
                                        $arrayDestino[$destino['email']] = $destino['nombre'];
                                }

                            }
                            //var_dump($arrayDestino);exit();
                            $message->setTo($arrayDestino);

                        }else{
                            if(filter_var($destinatario['email'], FILTER_VALIDATE_EMAIL)){
                                $message->addTo($destinatario['email'],$destinatario['nombre']);
                            }

                            if(isset($destinatario['email1'])){
                                if(filter_var($destinatario['email1'], FILTER_VALIDATE_EMAIL))
                                    $message->addTo($destinatario['email1'],$destinatario['nombre']);
                            }
                        }

                    }
                    
                    
                    
                    /*if(isset($destinatario['email1'])){
                        if(filter_var($destinatario['email1'], FILTER_VALIDATE_EMAIL))
                            $message->addTo($destinatario['email1'],$destinatario['nombre']);
                    }*/
                    $ccEntity = $em->createQuery('SELECT a FROM WABackendBundle:Administrador a WHERE a.seguimiento=1 AND a.activo=1')->execute();
                    foreach ($ccEntity as $admin){
                        $message->addBcc($admin->getEmail(),$admin->getNombre());
                    }
                    /*if($destinatario['tipo']=='Proveedor')
                        $copia_envio = $em->getRepository('WABackendBundle:CopiaEnvio')->findOneBy(array('proveedor'=>$destinatario['id'],'envio'=>$entity->getId()));
                    else
                        $copia_envio = $em->getRepository('WABackendBundle:CopiaEnvio')->findOneBy(array('asociado'=>$destinatario['id'],'envio'=>$entity->getId()));
                    if($copia_envio)
                        $message->addCc($entity->getAdministrador()->getEmail(),$entity->getAdministrador()->getNombre());*/

                    if($entity->getCopiaAdministrador()){
                        $admin_copia = $em->getRepository('WABackendBundle:Administrador')->findOneById($entity->getCopiaAdministrador());
                        $message->addBcc($admin_copia->getEmail(),$admin_copia->getNombre());                
                    }
                    
                    /*$template= str_replace('../../../uploads/images', $this->getContainer()->getParameter('wa.app_url').'/uploads/images',
                            $this->getContainer()->get('templating')->render('WABackendBundle:Envio:email_send.html.twig',array(
                        'mensaje'=>$contenido,'destinatario'=>$destinatario,'destinatarioId'=>$entityDestinatario->getId(),
                        'idEnvio'=>$entity->getId(),'ruta'=>$this->getContainer()->getParameter('wa.app_url'))));*/
                    
                    $template= str_replace('../../../uploads/images', $this->getContainer()->getParameter('wa.app_url').'/uploads/images',
                        $this->renderView('envio/email_send.html.twig'
                        ,array(
                          'mensaje'=>$contenido,'destinatario'=>$destinatario,'destinatarioId'=>$entityDestinatario->getId(),
                          'idEnvio'=>$entity->getId(),'ruta'=>$this->getContainer()->getParameter('wa.app_url')
                        )));

                    $message->setBody($template);

                    foreach ($adjuntos as $path) {
                        $attachment = \Swift_Attachment::fromPath($path);
                        $message->attach($attachment);
                        
                        //$output->writeln("Se adjunta archivo ".$path);
                    }

                    try{
                        //$this->container->get('mailer')->send($message);
                        $mailer->send($message);
                        
                        $spool = $mailer->getTransport()->getSpool();
                        $transport = $container->get('swiftmailer.transport.real');

                        $spool->flushQueue($transport);                       
                        
                        $resultado=1;

                        
                        if($entity->getGrupo()->getAgruparProveedor() == 1 && $entity->getGrupo()->getClasificacion() == "No Aplica"){


                            foreach($proveedores as $proveedorDestino){

                                $integrantes = $em->getRepository('WABackendBundle:IntegranteGrupo')->findOneBy(array('grupo' => $entity->getGrupo()->getId() , 'proveedor' => $proveedorDestino->getId()));


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
                            $Enviado = $em->getRepository('WABackendBundle:EnvioIntegrante')->findOneBy(array('envio' => $entity->getId(), 'integranteGrupo' => $entityDestinatario->getId()));

                            if(!$Enviado){
                                $Enviado = new EnvioIntegrante();
                                $Enviado->setEnvio($em->getReference('WABackendBundle:Envio', $entity->getId()));
                                $Enviado->setIntegranteGrupo($em->getReference('WABackendBundle:IntegranteGrupo', $entityDestinatario->getId()));
                                $Enviado->setReenviado(0);
                            }else{
                                $Enviado->setReenviado($Enviado->getReenviado() + 1);
                            }


                            $Enviado->setEnviado(0);

                            $Enviado->setFechaEnvio(new \DateTime());
                            $em->persist($Enviado);
                            $em->flush();
                        }  
                            

                        $envios_num = $entity->getCantidadEnviados()+1;
                        $sql="UPDATE envio e SET e.cantidad_enviados = '".$envios_num."' WHERE e.id=".$entity->getId();
                        $em->getConnection()->query($sql);

                        $output->writeln("El email ha sido enviado");

                    }catch(\Exception $e){
                        $resultado=2;
                    }

                /*}else{
                    $output->writeln("El email de destino [".$destinatario['email']."] no pasó la validacion de PHP");
                }*/

            }

            $entity->setProgramado(0);
            
            $em->persist($entity);
            $em->flush();
        }

        $output->writeln("Fin de la tarea.");
    }
}
?>