<?php

namespace WA\BackendBundle\Controller;

use symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use WA\BackendBundle\Entity\Envio;


class ArchivosAdjuntosController extends Controller
{

  /**
  * Renderiza el formulario para la carga de archivos.
  * @param object $request Objeto peticion de Symfony 3, entidad Envio
  * @return 
  * @author Julián casas <j.casas@waplicaciones.com.co>
  * @since 3
  * @category Correos\ArchivosAdjuntos
  */
  public function formSubirArchivosAction( Request $request ,Envio $envio){

    $session=$request->getSession();

    if( $session->get('autenticado') ){
      $auditoria = $this->get('utilidadesAdministrador');

      $session->set('envio',$envio->getId());
      $hayExcepcion=$this->verificarCarpetas($session, $session->get('administrador'), $envio );

      $data=$this->archivosDisponibles( $session );

      $peso = $this->pesoArchivos( $session );
      $auditoria->registralog('Carga archivo', $session->get('id_usuario'));
      return $this->render('envio/adminArchivos.html.twig',array(
        'envio'=>$envio,
        'hayExcepcion'=>$hayExcepcion,
        'peso' => $peso
        ));

    }else{

      $uri = $this->container->get('router')->generate('default_login');
      return new RedirectResponse($uri);

    }

  }// end action

  /**
  * Carga el archivo csv "combinacion.csv"
  * @param object $request Objeto peticion de Symfony 3
  * @return 
  * @author Julián casas <j.casas@waplicaciones.com.co>
  * @since 3
  * @category Correos\ArchivosAdjuntos
  */
  public function csvAction(Request $request){

    set_time_limit ( 0 );
    $em = $this->getDoctrine()->getManager();
    $fs = new Filesystem();
    $archivo='combinacion.csv';
    $auditoria = $this->get('utilidadesAdministrador');

    $session=$request->getSession();
    $ruta = $session->get('rutaCSV');

    $response = new Response;
    $response->headers->set('Content-Type', 'application/json');
    $json = array();
           
    $response->setStatusCode( 200 );

    try{

      //Obtener el archivo
      $file = $request->files->get('csv');

      //verificar si hay archivo existente
      if( $fs->exists($ruta.$archivo) )
        $fs->remove($ruta.$archivo);

      //Mover el archivo a la carpeta
      $fs->copy( $file->getRealPath() , $ruta.$archivo );
      $json['status'] = 1;
      
      /*********Asignar estado de combinacion al envio********/
      
      $envio = $em->getRepository('WABackendBundle:Envio')->findOneById($request->get('idEnvio'));
      if($envio){
          $envio->setCombinacion(1);
          $em->persist($envio);
          $em->flush();
      
        $auditoria->registralog('Procesa convinacion Csv', $session->get('id_usuario'));
        /*******Procesar Sabana*******/
        $sabanaInicial = true;
        //$this->procesarSabana("./uploader/files/".$session->get('id_usuario').'-A/'.$archivo,$entity->getId(),$session,$sabanaInicial);
        $this->procesarSabana($ruta.$archivo,$envio->getId(),$session,$sabanaInicial);
      
      }
      
    }catch( \IOExceptionInterface $e ){
      $json['descripcion']=$e->getMessage();
      $response->setStatusCode( 500 );
      $json['status'] = 2;

    }catch(\UnexpectedValueException $e){
      $json['descripcion']=$e->getMessage();
      $json['status'] = 3;
      $response->setStatusCode( 500 );
    } catch( \Exception $e ){
      $json['descripcion']=$e->getMessage();
      $json['status'] = 3;
      $response->setStatusCode( 500 );
    }

    $response->setContent(json_encode($this->utf8_converter($json)));
    return $response;

  }

  function utf8_converter($array){
    array_walk_recursive($array, function(&$item, $key){
        if(!mb_detect_encoding($item, 'utf-8', true)){
                $item = utf8_encode($item);
        }
    });
 
    return $array;
}

  /**
  * Carga demás archivos adjuntos.
  * @param object $request Objeto peticion de Symfony 3
  * @return 
  * @author Julián casas <j.casas@waplicaciones.com.co>
  * @since 3
  * @category Correos\ArchivosAdjuntos
  */
  public function otrosAction(Request $request){

    set_time_limit ( 0 );

    $fs = new Filesystem();
   
    $session=$request->getSession();
    $ruta = $session->get('rutaOtros');
    $auditoria = $this->get('utilidadesAdministrador');

    $response = new Response;
    $response->headers->set('Content-Type', 'application/json');
    $json = array();
           
    $response->setStatusCode( 200 );
    try{

      $peso = $this->pesoArchivos( $session );

      //Obtener el archivo
      $files = $request->files->get('adjuntos');
      $contaArchivos=0;

      //Mover los archivos 
      foreach ($files as  $file){

        $peso+= number_format(( filesize ( $file->getRealPath() )  / 1000000 ),1,'.','');  
        if( $peso <= 2 ){
          $contaArchivos++;
          $fs->copy( $file->getRealPath(), $ruta.$file->getClientOriginalName() );
        }else{
          continue;
        }

      }// end foreach
      $auditoria->registralog('Carga otros archivos adjuntos', $session->get('id_usuario'));
      $this->pesoArchivos( $session );
      
      $json['status'] = 1;
    }catch( IOExceptionInterface $e ){

      $response->setStatusCode( 500 );
      $json['status'] = 2;

    }catch( \Exception $e ){

      $json['status'] = 2;
      $response->setStatusCode( 500 );

    }

    $response->setContent(json_encode($json));
    return $response;

  }

  /**
  * Verifica la existencia de la carpeta donde se guardarán los archivos a cargar.
  * @param object $request Objeto peticion de Symfony 3
  * @return 
  * @author Julián casas <j.casas@waplicaciones.com.co>
  * @since 3
  * @category Correos\ArchivosAdjuntos
  */
  private function verificarCarpetas($session, $administrador, $envio){

    //verificar existencia de directorio
    $fs = new Filesystem();
    $ruta=$this->get('kernel')->getRootDir().'/../web';
    try{

      if( !$fs->exists($ruta.'/adjuntos') )
        $fs->mkdir($ruta.'/adjuntos');

      //verificar existencia de la carpeta del administrador
      $fecha = $administrador->getFechaCreado();
      $fecha=$fecha->format('Ymd');
      $carpeta = $envio->getId().'/'.$fecha.$administrador->getId();

      if( !$fs->exists($ruta.'/adjuntos/'.$carpeta) ){

        $fs->mkdir($ruta.'/adjuntos/'.$carpeta.'/csv/');
        $fs->mkdir($ruta.'/adjuntos/'.$carpeta.'/otros/');

      }

      $session->set('rutaCSV',$ruta.'/adjuntos/'.$carpeta.'/csv/');
      $session->set('rutaOtros',$ruta.'/adjuntos/'.$carpeta.'/otros/');

      return false;

    }catch( IOExceptionInterface $e ){

      return true;

    }catch( \Exception $e ){

      return true;
    }

  }

  /**
  * Recupera y renderiza a la vista los archivos cargados disponibles.
  * @param object $request Objeto peticion de Symfony 3
  * @return 
  * @author Julián casas <j.casas@waplicaciones.com.co>
  * @since 3
  * @category Correos\ArchivosAdjuntos
  */
  public function listarAction( Request $request ){

    $session = $request->getSession();
    $auditoria = $this->get('utilidadesAdministrador');
    $data=$this->archivosDisponibles( $session );

    $csv=$data['csv'];
    $otros=$data['otros'];
    $peso=0;
    foreach ($otros as  $value) 
      $peso+=(int)$value['peso'];
    
    $auditoria->registralog('Listado de archivos cargados', $session->get('id_usuario'));
    return $this->render( 'archivosadmin/listaArchivos.html.twig',array( 'csv'=>$csv, 'otros'=>$otros, 'peso'=>$peso ));

  }// end action

  /**
  * Recupera la ruta y el nombre de los archivos que el Administrador ha cargado al sistema.
  * @param object $request Objeto peticion de Symfony 3
  * @return 2 array. Uno con información del csv y otro con ifnormación de los archivos adjuntos.
  * @author Julián casas <j.casas@waplicaciones.com.co>
  * @since 3
  * @category Correos\ArchivosAdjuntos
  */
  private function archivosDisponibles( $session ){

    $rutaCSV = $session->get('rutaCSV');
    $rutaOtros = $session->get('rutaOtros');

    $infoCSV = $infoOtros = array();

    if( !is_null( $rutaCSV ) && $rutaCSV != '' ){

      $fs = new Filesystem();
      $archivo='combinacion.csv';

      try{

        //verificar si hay archivo existente
        if( $fs->exists( $rutaCSV.$archivo ) ){
          $infoCSV['archivo'] = $archivo;
          $infoCSV['peso'] =  filesize ( $rutaCSV.$archivo );
        }else{
          $infoCSV['archivo'] =' No se pudo recuperar el archivo. ';
        }

      }catch( \Exception $e ){
        $infoCSV['error'] =' No se pudo recuperar el archivo. ';
      }
    }

    if( !is_null( $rutaOtros ) && $rutaOtros != '' ){

      $rep  = opendir($rutaOtros);    //Abrimos el directorio

      while ($arc = readdir($rep)) {  //Leemos el arreglo de archivos contenidos en el directorio: readdir recibe como parametro el directorio abierto

        if($arc != '..' && $arc !='.' && $arc !=''){
          $infoOtros[$arc]['nombre'] = $arc; 
          $infoOtros[$arc]['peso'] = filesize ( $rutaOtros.$arc );
        }
      }
      closedir($rep);         //Cerramos el directorio
      clearstatcache();     //Limpia la caché de estado de un archivo

    }

    return array( 'csv'=>$infoCSV, 'otros'=>$infoOtros );
   
  }//end function

  /**
  * Elimina archivos 
  * @param object $request Objeto peticion de Symfony 3
  * @return 
  * @author Julián casas <j.casas@waplicaciones.com.co>
  * @since 3
  * @category Correos\ArchivosAdjuntos
  */
  public function eliminarAction(Request $request){

    $session = $request->getSession();
    $auditoria = $this->get('utilidadesAdministrador');
    $archivo = $request->get('archivo');
    $opcion = $request->get('opcion');

    if( $opcion == 1 ){
      $ruta = $session->get('rutaCSV');
    }else{
      $ruta = $session->get('rutaOtros');
    }

    $fs = new Filesystem();

    $response = new Response;
    
    try{

      $auditoria->registralog('Eliminacion de archivos adjuntos', $session->get('id_usuario'));
      $fs->remove( $ruta.$archivo );
      $response->setStatusCode( 200 );
      $this->pesoArchivos( $session );
    }catch(\Exception $e){
      $response->setStatusCode( 500 );
    }

    return $response;
  }

  /**
  * Calcula el peso total en MB de los archivos disponibles.
  * @param object $request Objeto peticion de Symfony 3
  * @return 
  * @author Julián casas <j.casas@waplicaciones.com.co>
  * @since 3
  * @category Correos\ArchivosAdjuntos
  */
  private function pesoArchivos( $session ){

    $rutaOtros = $session->get('rutaOtros');
    $peso = 0;
    $contaArchivos = 0;
    if( !is_null( $rutaOtros ) && $rutaOtros != '' ){

      $rep  = opendir($rutaOtros);    //Abrimos el directorio

      while ($arc = readdir($rep)) {  //Leemos el arreglo de archivos contenidos en el directorio: readdir recibe como parametro el directorio abierto
        if($arc != '..' && $arc !='.' && $arc !=''){
          $peso+=(int) filesize ( $rutaOtros.$arc );  
          $contaArchivos++;
        }
      }
      closedir($rep);         //Cerramos el directorio
      clearstatcache();     //Limpia la caché de estado de un archivo

    }

    $em = $this->getDoctrine()->getManager();
    $envioId = $session->get('envio');

    $qb = $em->createQueryBuilder();
    $q = $qb->update('WABackendBundle:Envio', 'e')
            ->set('e.adjuntos',':adjuntos')
            ->where('e.id = :envio')
            ->setParameter(':envio', $envioId)
            ->setParameter(':adjuntos', $contaArchivos)
            ->getQuery();
    $p = $q->execute();

    return $peso = ( $peso >0 )? number_format(($peso / 1000000),1,'.','') :0;

  }
  
  
  /**
     * Procesa y registra la sabana de datos enviada por el usuario.
     * @param type $ruta
     * @param type $envio
     * @author Alejandro Ardila Ardila <j.restrepo@waplicaciones.co>
     * @category BackendBundle\ArchivosAdjuntos
     */
    protected function procesarSabana($ruta, $envio,$session,$inicial) {
        ini_set('memory_limit', '2048M');
        set_time_limit(0);
        $em = $this->getDoctrine()->getManager();
        if($inicial){
            $Eliminar0="DELETE FROM `columnas_datos_envio` WHERE `envio_id` = $envio";
            $em->getConnection()->query($Eliminar0);
            $Eliminar1="DELETE FROM `datos_envio` WHERE `envio_id` = $envio";
            $em->getConnection()->query($Eliminar1);
        }
        /*$em->createQuery("DELETE FROM WABackendBundle:ColumnasDatosEnvio c WHERE c.envio = :envio")->setParameter('envio', $envio)->execute(); 
        $em->createQuery("DELETE FROM WABackendBundle:DatosEnvio c WHERE c.envio = :envio")->setParameter('envio', $envio)->execute();*/
        
        $cont = 1;$batchSize = 20;
        $insert1="INSERT INTO `columnas_datos_envio` (`fecha_creado` ,`envio_id` ,`datos` ,`creador_id` ,`fecha_modificado` ,`identificador`)
                        VALUES ";
        $sql="";
        $fpB = fopen($ruta, "r");
    
        if ($fpB){
            while ($data = fgetcsv($fpB, 4000, ";")){
              if($cont === 1){
                  $v=array();
                  
                  foreach($data as $d){
                      $v[]=  utf8_encode(stripcslashes($d));
                  }              

                  $SQLC=" INSERT INTO `datos_envio` (`fecha_creado` ,`codigo_destinatario` , `tipo_usuario` ,`envio_id` ,`creador_id` ,`datos` ,`fecha_modificado` ,`identificador`)
                      VALUES ( '".date('Y-m-d H:i:s')."', 1 , NULL , '".$envio."', '".$session->get('id_usuario')."', '".serialize($v)."' , '".date('Y-m-d H:i:s')."', '".$data[0]."')";
                  $em->getConnection()->query($SQLC);
                  unset($v);
              }else{
                  $v=array();
                  foreach($data as $d){
                      $v[]=  utf8_encode(stripcslashes($d));
                  }
                  
                  if($sql!="")
                      $sql.=",";
                  $sql.=" ( '".date('Y-m-d H:i:s')."', '".$envio."', '".serialize($v)."', '".$session->get('id_usuario')."', '".date('Y-m-d H:i:s')."', '".$data[0]."')";
                  if (($cont % $batchSize) == 0) {                   
                      $em->getConnection()->query($insert1.$sql);
                      $sql="";
                  }
                  unset($v);
              }
              $cont++;
            }  
            if($sql!="")
                $em->getConnection()->query($insert1.$sql);
        }
        /*$em->flush();
        $em->clear();*/
    }


}//end class
