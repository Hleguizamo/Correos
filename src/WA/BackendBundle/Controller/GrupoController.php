<?php

namespace WA\BackendBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use WA\BackendBundle\Entity\Grupo;
use WA\BackendBundle\Entity\IntegranteGrupo;
use WA\BackendBundle\Form\GrupoType;

/**
 * Grupo controller.
 *
 * @Route("/grupo")
 */
class GrupoController extends Controller
{
    /**
     * Lists all Grupo entities.
     *
     */
    public function procesarArchivoAsociadosAction(Request $request){
        $session = $request->getSession();
        $auditoria = $this->get('utilidadesAdministrador');
        /*$permisos = $session->get('permisos');
        $auditoria = $this->get('auditoria');
        if(!$auditoria->seguridad_http('grupo',3,$permisos))
            return $this->redirect($this->generateUrl('admin_login'));*/
        $response = new Response();
        $resp = array();
        if($request->getMethod() == 'POST'){
            $idGrupo = $request->get('grupoId');
                $em = $this->getDoctrine()->getManager();
                $conn = $em->getConnection();
                $errores_proveedor_email=array();
                $errores_asociados = array();
                $files=$request->files->get('form');
                if($files['archivo']){
                    $file = $files['archivo'];
                    $file->move('./uploads/',$file->getClientOriginalName());
                    $filename = './uploads/'.$file->getClientOriginalName();
                    $excelObj = \PHPExcel_IOFactory::load($filename);
                    $sheetData = $excelObj->getActiveSheet()->toArray(null,true,true,true);
                    
                    $update="UPDATE integrante_grupo SET activo = '0' WHERE grupo_id = ".$idGrupo;
                    $conn->query($update);
                    $idsCliente = array();
                    $idsProveedorContacto = array();
                    $idsProveedor = $idsProveedorC = array();
                    $grupo = $em->getRepository('WABackendBundle:Grupo')->find($idGrupo);
                    $contadorCont=$contadorAsociado=$contadorActuCont=$contadorActuAsociado=0;
                    $asociadossArray=array();
                    $sqlAsociados = "SELECT id, nit, codigo FROM cliente";
                    $asociadosEntitie=$conn->query($sqlAsociados)->fetchAll();
                    $clasificacionAsociados=($grupo->getClasificacion()=='Asociados')?'id':'codigo';
                    foreach($asociadosEntitie as $asociadoEntitie){
                        $asociadossArray[$asociadoEntitie[$clasificacionAsociados]][0]=$asociadoEntitie['id'];
                        $asociadossArray[$asociadoEntitie[$clasificacionAsociados]][1]=$asociadoEntitie['nit'];
                        $asociadossArray[$asociadoEntitie[$clasificacionAsociados]][2]=$asociadoEntitie['codigo'];
                    }
                    $destinatarioGrupoAArray=array();
                    $destinatarioGrupoPArray=array();
                    $sqlDestinatariosGrupo = "SELECT * FROM integrante_grupo WHERE grupo_id=".$idGrupo;
                    $destinatariosGrupoEntitie=$conn->query($sqlDestinatariosGrupo)->fetchAll();
                    foreach($destinatariosGrupoEntitie as $destinatarioEntitie){
                        if($destinatarioEntitie['asociado_id']!=""){
                            $destinatarioGrupoAArray[$destinatarioEntitie['asociado_id']]=$destinatarioEntitie['id'];
                        }
                    }
                    //dump($sheetData);exit();
                    foreach($sheetData as $n_fila=>$data){         
                        if($n_fila > 1){
                            $identificador = (isset($data['A']))?trim($data['A']):NULL;

                            if($identificador != ''){
                                if($grupo->getClasificacion() != 'Asociados'){
                                    if(isset($asociadossArray[$identificador]))
                                        $idsCliente[] = $asociadossArray[$identificador][0];
                                }else{
                                  foreach ($asociadossArray as $asociadosA){
                                        if($asociadosA[1]==$identificador)
                                            $idsCliente[] = $asociadosA[0];
                                    }
                                }
                            }
                        }
                    }
                    //** Recorro e inserto los integrantes de tipo sociados **//                    
                    foreach ($idsCliente as $id) {
                        if(!isset($destinatarioGrupoAArray[$id])){
                            $insert="INSERT INTO integrante_grupo (asociado_id, proveedor_id, grupo_id, tipo_integrante_id, activo) VALUES ('".$id."', NULL, '".$idGrupo."', NULL, '1')";
                            $conn->query($insert);
                            $contadorAsociado++;
                        }else{
                            $update="UPDATE integrante_grupo SET activo = '1' WHERE id = ".$destinatarioGrupoAArray[$id];
                            $conn->query($update);
                            $contadorActuAsociado++;
                        }
                    }
                }
                //Respuesta
                $resp['status'] = 1;
                $resp['actualizados'] = $contadorActuAsociado+$contadorActuCont;
                $resp['insertados'] = $contadorAsociado+$contadorCont;
                if($errores_asociados){
                    $resp['erroresAsociados'] = implode('|', $errores_asociados);
                }
                
                
                $Contador = $em->createQuery("SELECT COUNT(ig.id) AS contador FROM WABackendBundle:IntegranteGrupo ig WHERE ig.grupo=".$idGrupo)->getSingleResult();
                $numRegistros = $Contador['contador'];

                if($grupo) {
                    $grupo->setIntegrantes($numRegistros);
                    $em->persist($grupo);
                    $em->flush();
                }
                
        }
        if(is_file($filename)){
            unlink($filename);
        }
        $auditoria->registralog('Proceso archivo de carga de asociados', $session->get('id_usuario'));
        $response->setContent(json_encode($resp));
        return $response;
    }

    /**
     * Lists all Grupo entities.
     *
     */
    public function cargaArchivoAsociadoAction(Request $request){
        $session = $request->getSession();
        return $this->render('grupo/cargarAsociados.html.twig',array('grupoId'=>$request->get('grupoId')));
    }

    /**
     * Lists all Grupo entities.
     *
     */
    public function procesarArchivoAction(Request $request){
        $session = $request->getSession();
        $auditoria = $this->get('utilidadesAdministrador');
        /*$permisos = $session->get('permisos');
        $auditoria = $this->get('auditoria');
        if(!$auditoria->seguridad_http('grupo',3,$permisos))
            return $this->redirect($this->generateUrl('admin_login'));*/
        $response = new Response();
        $resp = array();
        if($request->getMethod() == 'POST'){
            $idGrupo = $request->get('grupoId');
                $em = $this->getDoctrine()->getManager();
                $conn = $em->getConnection();
                $connp = $this->getDoctrine()->getManager('contactosProveedor')->getConnection(); 
                $errores_proveedor_email=array();
                $errores_asociados = array();
                $files=$request->files->get('form');
                if($files['archivo']){
                    $file = $files['archivo'];
                    $file->move('./uploads/',$file->getClientOriginalName());
                    $filename = './uploads/'.$file->getClientOriginalName();
                    $excelObj = \PHPExcel_IOFactory::load($filename);
                    $sheetData = $excelObj->getActiveSheet()->toArray(null,true,true,true);
                    
                    $update="UPDATE integrante_grupo SET activo = '0' WHERE grupo_id = ".$idGrupo;
                    $conn->query($update);
                    $idsCliente = array();
                    $idsProveedorContacto = array();
                    $idsProveedor = $idsProveedorC = array();
                    $grupo = $em->getRepository('WABackendBundle:Grupo')->find($idGrupo);
                    $proveedoresArray=array();
                    $contadorCont=$contadorProve=$contadorActuCont=$contadorActuProve=0;
                    $sqlProveedores = "SELECT c.id, p.id AS idProveedor, p.nit, p.codigo, p.email_representante_legal, c.email FROM proveedores p JOIN contactos c ON c.id_proveedor = p.id ";
                    $proveedoresEntitie=$connp->query($sqlProveedores)->fetchAll();
                    $tipoResgistroProveedor=($request->get('tipoRegistro')=='Email')?'email':'id';
                    foreach($proveedoresEntitie as $proveedorEntitie){
                        $proveedoresArray[$proveedorEntitie[$tipoResgistroProveedor]][0]=$proveedorEntitie['id'];
                        $proveedoresArray[$proveedorEntitie[$tipoResgistroProveedor]][1]=$proveedorEntitie['nit'];
                        $proveedoresArray[$proveedorEntitie[$tipoResgistroProveedor]][2]=$proveedorEntitie['codigo'];
                        $proveedoresArray[$proveedorEntitie[$tipoResgistroProveedor]][3]=$proveedorEntitie['email'];
                        $proveedoresArray[$proveedorEntitie[$tipoResgistroProveedor]][4]=$proveedorEntitie['idProveedor'];
                    }
                    $destinatarioGrupoAArray=array();
                    $destinatarioGrupoPArray=array();
                    /*$sqlDestinatariosGrupo = "SELECT * FROM integrante_grupo WHERE grupo_id=".$idGrupo;
                    $destinatariosGrupoEntitie=$conn->query($sqlDestinatariosGrupo)->fetchAll();
                    foreach($destinatariosGrupoEntitie as $destinatarioEntitie){
                        if($destinatarioEntitie['contacto_proveedor_id']!=""){
                            $destinatarioGrupoPArray[$destinatarioEntitie['contacto_proveedor_id']]['contacto']=$destinatarioEntitie['id'];
                        }
                        if($destinatarioEntitie['proveedor_id']!=""){
                            $destinatarioGrupoPArray[$destinatarioEntitie['proveedor_id']]['proveedor']=$destinatarioEntitie['id'];
                        }
                    }*/


                    /********SE ELIMINAN LOS INTEGRANTES DEL GRUPO ANTES DE INSERTAR LOS NUEVOS DEL ARCHIVO*********/

                    $sqlIntegrantes = "SELECT id, contacto_proveedor_id, proveedor_id FROM integrante_grupo WHERE grupo_id=".$idGrupo;
                    $datosIntegrantes = $conn->query($sqlIntegrantes);

                    $arrayRespaldoContacto = array();
                    $arrayRespaldoProveedor = array();
                    foreach($datosIntegrantes as $datIntegrante){
                        if(is_null($datIntegrante['contacto_proveedor_id'])){
                            $arrayRespaldoProveedor[$datIntegrante['proveedor_id']] =$datIntegrante['id'] ;
                        }else{
                            $arrayRespaldoContacto[$datIntegrante['proveedor_id']][$datIntegrante['contacto_proveedor_id']] = $datIntegrante['id'];
                        }
                    }

                    $eliminacionIntegrantes = "DELETE FROM integrante_grupo WHERE grupo_id=".$idGrupo;
                    $conn->query($eliminacionIntegrantes);

                    /********************/

                    //dump($sheetData);exit();
                    foreach($sheetData as $n_fila=>$data){         
                        if($n_fila > 1){
                            $proveedor_email =(isset($data['A']))?trim($data['A']):NULL;                            
                            if($proveedor_email != ''){
                                if($request->get('tipoRegistro')=='Email'){
                                        
                                    if (filter_var($proveedor_email, FILTER_VALIDATE_EMAIL)) {
                                        if(isset($proveedoresArray[$proveedor_email])){
                                            $idsProveedorContacto[] = $proveedoresArray[$proveedor_email][0];
                                            $idsProveedor[$proveedoresArray[$proveedor_email][0]] = $proveedoresArray[$proveedor_email][4];
                                            $idsProveedorC[] = $proveedoresArray[$proveedor_email][4];
                                        }
                                    }else{
                                        $errores_proveedor_email[] = $proveedor_email;
                                    }
                                    
                                }else{
                                    foreach ($proveedoresArray as $proveedorA){
                                        if(preg_replace('/\D/', '', $proveedorA[1])==$proveedor_email){
                                            $idsProveedorContacto[] = $proveedorA[0];
                                            $idsProveedor[$proveedorA[0]] = $proveedorA[4];
                                            $idsProveedorC[] = $proveedorA[4];
                                        }
                                            
                                    }
                                    $sqlProveedores = "SELECT p.id FROM proveedores p WHERE p.nit LIKE '%".$proveedor_email."%' GROUP BY p.codigo ";
                                    $idproveedoresEntitie=$connp->query($sqlProveedores)->fetchAll();
                                    foreach ($idproveedoresEntitie as $value) {
                                        $idsProveedorC[] = $value['id'];
                                    }
                                }
                            }
                        }
                    }
                    //** Recorro e inserto los integrantes de tipo sociados **// 
                    foreach (array_unique($idsProveedorC) as $id) {
                        if(isset($destinatarioGrupoPArray[$id]['proveedor'])){
                            $update="UPDATE integrante_grupo SET activo = '1' WHERE id = ".$destinatarioGrupoPArray[$id]['proveedor'];
                            $conn->query($update);
                            $contadorActuProve++;
                        }else{
                            if(isset( $arrayRespaldoProveedor[$id] )){

                                $insert="INSERT INTO integrante_grupo (id, asociado_id, proveedor_id, contacto_proveedor_id, grupo_id, tipo_integrante_id, activo) VALUES ('".$arrayRespaldoProveedor[$id]."' , NULL,'".$id."', NULL, '".$idGrupo."', NULL, '1')";
                            }else{

                                $insert="INSERT INTO integrante_grupo (asociado_id, proveedor_id, contacto_proveedor_id, grupo_id, tipo_integrante_id, activo) VALUES ( NULL,'".$id."', NULL, '".$idGrupo."', NULL, '1')";
                            }
                            //$insert="INSERT INTO integrante_grupo (asociado_id, proveedor_id, contacto_proveedor_id, grupo_id, tipo_integrante_id, activo) VALUES ( NULL,'".$id."', NULL, '".$idGrupo."', NULL, '1')";
                            $conn->query($insert);
                            $contadorProve++;

                            $destinatarioGrupoPArray[$id]['proveedor']=$id;
                        }
                    }
                    foreach ($idsProveedorContacto as $id) {
                        if(isset($destinatarioGrupoPArray[$id]['contacto'])){
                            $update="UPDATE integrante_grupo SET activo = '1' WHERE id = ".$destinatarioGrupoPArray[$id]['contacto'];
                            $conn->query($update);
                            $contadorActuCont++;
                        }else{

                            if(isset($arrayRespaldoContacto[ $idsProveedor[$id] ][ $id ] ) ){
                                $insert="INSERT INTO integrante_grupo (id, asociado_id, proveedor_id, contacto_proveedor_id, grupo_id, tipo_integrante_id, activo) VALUES ( '".$arrayRespaldoContacto[ $idsProveedor[$id] ][ $id ]."', NULL,'". $idsProveedor[$id] ."', '".$id."', '".$idGrupo."', NULL, '1')";
                            }else{
                                $insert="INSERT INTO integrante_grupo (asociado_id, proveedor_id, contacto_proveedor_id, grupo_id, tipo_integrante_id, activo) VALUES ( NULL,'". $idsProveedor[$id] ."', '".$id."', '".$idGrupo."', NULL, '1')";
                            }
                            //$insert="INSERT INTO integrante_grupo (asociado_id, proveedor_id, contacto_proveedor_id, grupo_id, tipo_integrante_id, activo) VALUES ( NULL,'". $idsProveedor[$id] ."', '".$id."', '".$idGrupo."', NULL, '1')";
                            $conn->query($insert);
                            $contadorCont++;

                            $destinatarioGrupoPArray[$id]['contacto']=$id;
                        }
                    }
                }
                //Respuesta
                $resp['status'] = 1;
                $resp['actualizados'] = $contadorActuProve+$contadorActuCont;
                $resp['insertados'] = $contadorProve+$contadorCont;
                if($errores_proveedor_email){
                    $resp['errores_proveedor_email'] = implode('|', $errores_proveedor_email);
                }
                if($errores_asociados){
                    $resp['erroresAsociados'] = implode('|', $errores_asociados);
                }
                
                
                $Contador = $em->createQuery("SELECT COUNT(ig.id) AS contador FROM WABackendBundle:IntegranteGrupo ig WHERE ig.grupo=".$idGrupo)->getSingleResult();
                $numRegistros = $Contador['contador'];

                if($grupo) {
                    $grupo->setIntegrantes($numRegistros);
                    $em->persist($grupo);
                    $em->flush();
                }
                
        }
        if(is_file($filename)){
            unlink($filename);
        }
        $auditoria->registralog('Proceso archivo de carga de proveedor', $session->get('id_usuario'));
        $response->setContent(json_encode($resp));
        return $response;
    }
    
    /** 
     * Genera archivo xls con los destinatarios asignados al grupo seleccionado.
     * 
     */
    public function descargaDestinatariosAction(Request $request){
        $session = $request->getSession();
        $auditoria = $this->get('utilidadesAdministrador');
        //echo "entra";exit();
        $grupoId=$request->get('grupoId');
        //$excelService = $this->get('xls.service_xls5');
        $excelService = $this->get('phpexcel')->createPHPExcelObject();
        //$excelService->excelObj->setActiveSheetIndex(0);
        $excelService->setActiveSheetIndex(0);
        $activeSheet = $excelService->getActiveSheet();
        $styleArrayTitulos = array(
            'font'=> array('bold'=> true),
            'alignment' => array('horizontal'   => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
            'fill'      => array('type'         => \PHPExcel_Style_Fill::FILL_SOLID,
                                'startcolor'    => array('argb' => 'FFD6D6D6')
                ),
            'borders'   => array('outline'      => array(
                                                        'style' => \PHPExcel_Style_Border::BORDER_THIN,
                                                        'color' => array('argb' => '00000000')
                )
           )
        );
        $styleArrayResaltado = array(
            'font'      => array('bold'         => true),
            'alignment' => array('horizontal'   => \PHPExcel_Style_Alignment::HORIZONTAL_RIGHT),            
            'borders'   => array('allborders'   => array(
                                                        'style' => \PHPExcel_Style_Border::BORDER_THIN,
                                                        'color' => array('argb' => '00000000')
                )
           )
        );
        $styleArrayBordes = array('borders'=> array('allborders'=> array('style' => \PHPExcel_Style_Border::BORDER_THIN,'color' => array('argb' => '00000000'))));
        
        $em = $this->getDoctrine()->getManager();
        $emp = $this->getDoctrine()->getManager('contactosProveedor');
        //Hoja de estudiantes:
        $columna = "A";
        $fila=1;
        $activeSheet->getColumnDimension('A')->setWidth(10);
        $activeSheet->getColumnDimension('B')->setWidth(30);
        $activeSheet->getColumnDimension('C')->setWidth(30);
        $activeSheet->getColumnDimension('D')->setWidth(25);
        $activeSheet->getColumnDimension('E')->setWidth(25);
        $activeSheet->getColumnDimension('E')->setWidth(25);
        $activeSheet->getColumnDimension('F')->setWidth(25);
        
        /*$entitiesE = $em->createQuery("SELECT a.nit AS aNit, a.drogueria AS Drogueria, a.email AS aEmail, a.asociado AS aNombre, p.nit AS pNit, p.empresa AS Empresa, p.email1 AS pEmail, p.nombre AS pNombre, p.apellido AS pApellido, ig.id AS asignado 
                FROM WABackendBundle:IntegranteGrupo ig 
                LEFT JOIN ig.asociado a 
                LEFT JOIN ig.proveedor p
                WHERE ig.grupo=".$grupoId)->getResult();*/
        
        $entitiesE = $em->createQuery("SELECT a.nit AS aNit,a.codigo AS aCodigo, a.drogueria AS Drogueria, a.email AS aEmail, a.asociado AS aNombre, ig.id AS asignado, ig.proveedorId AS proveedorId,ig.contactoProveedorId AS contactoProveedorId
                FROM WABackendBundle:IntegranteGrupo ig 
                LEFT JOIN ig.asociado a 
                WHERE ig.grupo=".$grupoId)->getResult();
        
        $contactosProveedor = $emp->createQuery("SELECT p.nit, p.codigo, p.nombre, p.representanteLegal, p.emailRepresentanteLegal, c.nombreContacto, c.email, p.id,c.id AS contactoId, car.nombre AS cargoNombre
                FROM WABackendBundle:Proveedores p 
                LEFT JOIN WABackendBundle:Contactos c WITH c.idProveedor = p.id
                LEFT JOIN c.idCargo car ")->getResult();
        $contactosArray = array();
        foreach($contactosProveedor as $contactos){
            $contactosArray[$contactos['id']]['id'] = $contactos['id'];
            $contactosArray[$contactos['id']]['pNit'] = $contactos['nit'];
            $contactosArray[$contactos['id']]['Empresa'] = $contactos['nombre'];
            $contactosArray[$contactos['id']]['pEmail'] = $contactos['email'];
            $contactosArray[$contactos['id']]['pNombre'] = $contactos['nombreContacto'];
            $contactosArray[$contactos['contactoId']]['nombreContacto'] = $contactos['nombreContacto'];
            $contactosArray[$contactos['contactoId']]['email'] = $contactos['email'];
            $contactosArray[$contactos['contactoId']]['cargo'] = $contactos['cargoNombre'];
            //echo $contactos['contactoId'].' - '.$contactos['cargoNombre'];
        }//exit();
        $titulocolum='Nit';
        if ($request->get('clasificacion')=='Droguerías') {
            $titulocolum='Codigo';
        }
        $activeSheet->setCellValue($columna.$fila, $titulocolum);$columna++;
        if ($entitiesE[0]['Drogueria']) {
            $activeSheet->setCellValue($columna.$fila, 'Drogueria');$columna++;
        }else{
            $activeSheet->setCellValue($columna.$fila, 'Empresa');$columna++;
            $activeSheet->setCellValue($columna.$fila, 'Cargo');$columna++;
        }
        $activeSheet->setCellValue($columna.$fila, 'Nombre');$columna++;
        $activeSheet->setCellValue($columna.$fila, 'Email');$columna++;
        $FilaInicial=$fila;
        
        if ($entitiesE) {
            foreach($entitiesE as $p){
                $fila++;
                $columna = "A";
                $valcolum=$p['aNit'];
                if ($request->get('clasificacion')=='Droguerías') {
                    $valcolum=$p['aCodigo'];
                }
                $activeSheet->setCellValue($columna.$fila, $p['aNit']? $valcolum : $contactosArray[$p['proveedorId']]['pNit'] );$columna++;
                if ($p['Drogueria']) {
                    $activeSheet->setCellValue($columna.$fila, $p['Drogueria']);$columna++;
                    $activeSheet->setCellValue($columna.$fila, $p['aNombre']);$columna++;
                    $activeSheet->setCellValue($columna.$fila, $p['aEmail']);$columna++;
                }else{
                    $activeSheet->setCellValue($columna.$fila, $contactosArray[$p['proveedorId']]['Empresa']);$columna++;
                    if (isset($contactosArray[$p['contactoProveedorId']])) {
                        $cargo='';
                        if(isset($contactosArray[$p['contactoProveedorId']]['cargo'])){
                            $cargo=$contactosArray[$p['contactoProveedorId']]['cargo'];
                        }
                        $activeSheet->setCellValue($columna.$fila, $cargo);$columna++;
                        $nombreContacto='';
                        if(isset($contactosArray[$p['contactoProveedorId']]['nombreContacto'])){
                            $nombreContacto=$contactosArray[$p['contactoProveedorId']]['nombreContacto'];
                        }
                        $activeSheet->setCellValue($columna.$fila, $nombreContacto);$columna++;
                        $emailContacto='';
                        if(isset($contactosArray[$p['contactoProveedorId']]['email'])){
                            $emailContacto=$contactosArray[$p['contactoProveedorId']]['email'];
                        }
                        $activeSheet->setCellValue($columna.$fila, $emailContacto);$columna++;
                    }else{
                        $activeSheet->setCellValue($columna.$fila, '');$columna++;
                        $activeSheet->setCellValue($columna.$fila, $contactosArray[$p['proveedorId']]['pNombre']);$columna++;
                        $activeSheet->setCellValue($columna.$fila, $contactosArray[$p['proveedorId']]['pEmail'] );$columna++;
                    }
                }
            }
            $hoja=0;
            $activeSheet->getTabColor()->setRGB('00FF00');
            $excelService->getActiveSheet()->setTitle($entitiesE[0]['aEmail']? 'Asociado' : 'Proveedor');
        }
        /*$response = $excelService->getResponse();
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment;filename=DestinatariosGrupo.xls');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        return $response;*/
        $auditoria->registralog('Descarga destinatarios', $session->get('id_usuario'));
        header('Content-Type: ');
        header('Content-Disposition: attachment;filename="DestinatariosGrupo.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($excelService,'Excel2007');
        $objWriter->save('php://output'); 
        exit();  
    }
    

    /**
     * Lists all Grupo entities.
     *
     */
    public function cargaArchivoAction(Request $request){
        $session = $request->getSession();
        return $this->render('grupo/cargarDestinatarios.html.twig',array('grupoId'=>$request->get('grupoId')));
    }

    /**
     * Lists all Grupo entities.
     *
     * @Route("/proveedorDestinatarioAgregar", name="grupo_proveedorAgregar")
     * @Method("POST")
     */
    
    public function agregarProveedorAction(Request $request){
        $session = $request->getSession();
        $auditoria = $this->get('utilidadesAdministrador');
        $em = $this->getDoctrine()->getManager();
        $emp = $this->getDoctrine()->getManager('contactosProveedor');
        $conexion = $em->getConnection();
        $proveedorId = $request->get('padreId');
        $grupoId = $request->get('grupoId');
        $nitProveedor = $request->get('nit');
        
        $entity = $em->getRepository('WABackendBundle:Grupo')->find($grupoId);
        
        if ($request->get('multiple') == 'true') {
            $busqueda = $this->get('busquedaAdministrador');
            $filtro=$busqueda->busqueda();
            $filtroBusqueda = ($filtro) ? $filtro : ' WHERE p.id != 0 ';
            $OrdenTipo = $request->get('sord');
            $OrdenCampo = $request->get('sidx');
            $provedoresIds = array();
            $selectProvedores = $emp->createQuery("SELECT p.id FROM WABackendBundle:Proveedores p  $filtroBusqueda ORDER BY ".$OrdenCampo." ".$OrdenTipo)->getResult();
            //$selectProvedores = $emCP->createQuery("SELECT c.id FROM WABackendBundle:Proveedores p JOIN WABackendBundle:Contactos c WITH p.id = c.idProveedor  $filtroBusqueda ORDER BY ".$OrdenCampo." ".$OrdenTipo)->getResult();
            foreach ($selectProvedores as $value) {
                $provedoresIds[]=$value['id'];
            }

            $eliminarIntegaratesProvedores = $em->createQuery("DELETE FROM WABackendBundle:IntegranteGrupo ig WHERE ig.grupo=$grupoId AND ig.proveedorId IN(".implode(",", $provedoresIds).")")->getResult();

            $contactosProveedor= $emp->createQuery("SELECT c.id,p.id AS idProveedor FROM WABackendBundle:Contactos c LEFT JOIN c.idProveedor p WHERE c.idProveedor IN(".implode(",", $provedoresIds).")")->getResult();
            //$contactosProveedor= $emCP->createQuery("SELECT c.id,p.id AS idProveedor FROM WABackendBundle:Contactos c LEFT JOIN c.idProveedor p WHERE c.id IN(".implode(",", $provedoresIds).")")->getResult();
            $insert=' INSERT INTO integrante_grupo (activo,grupo_id,proveedor_id,contacto_proveedor_id )';
            //$insert=' INSERT INTO integrante_grupo (activo,grupo_id,proveedor_id )';
            $values=$valuesPro='';
            $count=$countPro=1;
            foreach ($contactosProveedor as $value) {
                if($values!='')
                    $values.=',';
                $values.='(1,'.$grupoId.','.$value['idProveedor'].','.$value['id'].')';
                //$values.='(1,'.$grupoId.','.$value['id'].')';
                if($count==50){
                    $count=0;
                    $conexion->query($insert.' VALUES '.$values);
                    $values='';
                }
                $count++;
            }
            if($values!=''){
                $conexion->query($insert.'VALUES'.$values);
            }
            foreach ($provedoresIds as $value1) {
                if($valuesPro!='')
                    $valuesPro.=',';
                $valuesPro.='(1,'.$grupoId.','.$value1.',NULL)';
                if($countPro==50){
                    $countPro=0;
                    $conexion->query($insert.' VALUES '.$valuesPro);
                    $valuesPro='';
                }
                $countPro++;
            }
            if($valuesPro!=''){
                $conexion->query($insert.'VALUES'.$valuesPro);
            }
        } else {
            $entidad = $em->getRepository('WABackendBundle:IntegranteGrupo')->findOneBy(array('proveedorId' => $proveedorId, 'grupo' => $grupoId));
            if (!$entidad) {
                $contactosProveedor=$emp->getRepository('WABackendBundle:Contactos')->findBy(array('idProveedor' => $proveedorId));
                foreach ($contactosProveedor as $value) {
                    $integrante = new IntegranteGrupo();
                    $integrante->setActivo(1);
                    $integrante->setGrupo($em->getReference('WABackendBundle:Grupo', $grupoId));
                    $integrante->setProveedorId($proveedorId);
                    $integrante->setContactoProveedorId($value->getId());
                    $em->persist($integrante);
                    $em->flush();
                }
                $integrante = new IntegranteGrupo();
                $integrante->setActivo(1);
                $integrante->setGrupo($em->getReference('WABackendBundle:Grupo', $grupoId));
                $integrante->setProveedorId($proveedorId);
                $integrante->setContactoProveedorId(NULL);
                $em->persist($integrante);
                $em->flush();
            }
        }
        $Contador = $em->createQuery("SELECT COUNT(ig.id) AS contador FROM WABackendBundle:IntegranteGrupo ig WHERE ig.grupo=".$grupoId)->getSingleResult();
        $numRegistros = $Contador['contador'];
        
        if($entity) {
            $entity->setIntegrantes($numRegistros);
            $em->persist($entity);
            $em->flush();
        }
        $response = new Response(json_encode(array('resultado' => '1','integrantes'=>$numRegistros)));
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'application/json');
        $auditoria->registralog('Agrega proveedor', $session->get('id_usuario'));
        return $response;
    }

    /**
     * Lists all Grupo entities.
     *
     */

    public function eliminarProveedorAction(Request $request){
        $session = $request->getSession();
        $auditoria = $this->get('utilidadesAdministrador');
        $em = $this->getDoctrine()->getManager();
        $emp = $this->getDoctrine()->getManager('contactosProveedor');
        $proveedorId = $request->get('proveedorId');
        $grupoId = $request->get('grupoId');
        if ($request->get('multiple') == 'true') {
            $busqueda = $this->get('busquedaAdministrador');
            $filtro=$busqueda->busqueda();
            $filtroBusqueda = ($filtro) ? $filtro : ' WHERE p.id != 0 ';
            $OrdenTipo = $request->get('sord');
            $OrdenCampo = $request->get('sidx');
            $provedoresIds = array();
            $selectProvedores = $emp->createQuery("SELECT p.id FROM WABackendBundle:Proveedores p $filtroBusqueda ORDER BY ".$OrdenCampo." ".$OrdenTipo)->getResult();
            //$selectProvedores = $emCP->createQuery("SELECT c.id FROM WABackendBundle:Proveedores p JOIN WABackendBundle:Contactos c WITH c.idProveedor = p.id $filtroBusqueda ORDER BY ".$OrdenCampo." ".$OrdenTipo)->getResult();
            foreach ($selectProvedores as $value) {
                $provedoresIds[]=$value['id'];
            }

            $eliminarIntegaratesProvedores = $em->createQuery("DELETE FROM WABackendBundle:IntegranteGrupo ig WHERE ig.grupo=$grupoId AND ig.proveedorId IN(".implode(",", $provedoresIds).")")->getResult();
        } else {
            
            $contactosProveedor = $emp->createQuery("SELECT c.id FROM WABackendBundle:Contactos c WHERE c.idProveedor =".$proveedorId)->getResult();
            $idContactos = array();
            foreach($contactosProveedor as $contactos){
                $idContactos[]=$contactos['id'];
            }
            
            if(count($idContactos)>0){
                
                $entidad = $em->createQuery("DELETE FROM WABackendBundle:IntegranteGrupo ig WHERE ig.grupo=:grupo AND ig.proveedorId IN(".implode(',',$idContactos).")")
                ->setParameter('grupo', $grupoId)
                ->getResult();
                
            }
            
            $entidad = $em->createQuery('DELETE FROM WABackendBundle:IntegranteGrupo ig WHERE ig.grupo=:grupo AND ig.proveedorId=:proveedor')
                ->setParameter('grupo', $grupoId)
                ->setParameter('proveedor', $proveedorId)
                ->getResult();
        }
        $Contador = $em->createQuery("SELECT COUNT(ig.id) AS contador FROM WABackendBundle:IntegranteGrupo ig WHERE ig.grupo=".$grupoId)->getSingleResult();
        $numRegistros = $Contador['contador'];
        $entity = $em->getRepository('WABackendBundle:Grupo')->find($grupoId);
        if($entity) {
            $entity->setIntegrantes($numRegistros);
            $em->persist($entity);
            $em->flush();
        }
        $response = new Response(json_encode(array('resultado' => '1','integrantes'=>$numRegistros)));
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'application/json');
        $auditoria->registralog('Eliminacion proveedor', $session->get('id_usuario'));
        return $response;
    }

    /**
     * Lists all Grupo entities.
     *
     */

    public function proveedoresXmlAction(Request $request){
        if ($request->isXmlHttpRequest()){
            $session = $request->getSession();
            $auditoria = $this->get('utilidadesAdministrador');
            /*$permisos = $session->get('permisos');
            $auditoria = $this->get('auditoria');
            if(!$auditoria->seguridad_http('grupo',3,$permisos))
                return $this->redirect($this->generateUrl('admin_login'));*/
            $em = $this->getDoctrine()->getManager();
            $emp = $this->getDoctrine()->getManager('contactosProveedor');
            $busqueda = $this->get('busquedaAdministrador');
            $filtro=$busqueda->busqueda();
            $filtroBusqueda = ($filtro) ? $filtro : ' WHERE p.id != 0 ';
            $OrdenTipo = $request->get('sord');
            $OrdenCampo = $request->get('sidx');
            $rows = $request->get('rows');
            $pagina = $request->get('page');
            $paginacion = ($pagina * $rows) - $rows;
            $entities = $emp->createQuery("SELECT p.id AS id, p.nit, p.codigo, p.nombre, p.representanteLegal FROM WABackendBundle:Proveedores p INNER JOIN WABackendBundle:Contactos c WITH c.idProveedor = p.id $filtroBusqueda GROUP BY p.nit ORDER BY ".$OrdenCampo." ".$OrdenTipo);
            
            $entities->setMaxResults($rows);
            $entities->setFirstResult($paginacion);
            $entities = $entities->getResult();

            $Contador = $emp->createQuery("SELECT COUNT(DISTINCT(p.nit)) AS contador FROM WABackendBundle:Proveedores p INNER JOIN WABackendBundle:Contactos c WITH c.idProveedor = p.id $filtroBusqueda")->getSingleResult();
            
            $numRegistros = $Contador['contador'];
            $totalPagina = ceil($numRegistros / $rows);
            $response = new Response();
            $response->setStatusCode(200);
            $response->headers->set('Content-Type', 'text/xml');
            $auditoria->registralog('Listado de proveedores Grupo', $session->get('id_usuario'));
            //$auditoria->registralog('Listado de Costos', $session->get('id_usuario'));
            return $this->render('grupo/proveedor.xml.twig', array(
                    'entities' => $entities,'numRegistros' => $numRegistros,
                    'maxPagina' => $totalPagina,'pagina' => $pagina,'grupoId'=>$request->get('grupoId')), $response);
        }
    }

    public function proveedoresAgregadosXmlAction(Request $request){
        if ($request->isXmlHttpRequest()){
            $session = $request->getSession();
            $auditoria = $this->get('utilidadesAdministrador');
            /*$permisos = $session->get('permisos');
            $auditoria = $this->get('auditoria');
            if(!$auditoria->seguridad_http('grupo',3,$permisos))
                return $this->redirect($this->generateUrl('admin_login'));*/
            $em = $this->getDoctrine()->getManager();
            $emp = $this->getDoctrine()->getManager('contactosProveedor');
            $busqueda = $this->get('busquedaAdministrador');
            $filtro=$busqueda->busqueda();
            $filtroBusqueda = ($filtro) ? $filtro : ' WHERE p.id != 0 ';
            $OrdenTipo = $request->get('sord');
            $OrdenCampo = $request->get('sidx');
            $rows = $request->get('rows');
            $pagina = $request->get('page');
            $paginacion = ($pagina * $rows) - $rows;

            $integrantesGrupo=$em->createQuery('SELECT ig.proveedorId FROM WABackendBundle:IntegranteGrupo ig WHERE ig.grupo='.$request->get('grupoId').' GROUP BY ig.proveedorId')->getResult();
            $idsProveedor='';
            foreach ($integrantesGrupo as $key => $value) {
                if ($key>=1) {
                    $idsProveedor .= ',';
                }
                $idsProveedor .= $value['proveedorId'];
            }
            $condicion='';
            if ($idsProveedor) {
                $condicion='AND p.id IN('.$idsProveedor.')';
            }
            $entities = $emp->createQuery("SELECT p.id AS id, p.nit, p.codigo, p.nombre, p.representanteLegal FROM WABackendBundle:Proveedores p $filtroBusqueda $condicion GROUP BY p.nit ORDER BY ".$OrdenCampo." ".$OrdenTipo);
            
            $entities->setMaxResults($rows);
            $entities->setFirstResult($paginacion);
            $entities = $entities->getResult();

            $Contador = $emp->createQuery("SELECT COUNT(DISTINCT(p.nit)) AS contador FROM WABackendBundle:Proveedores p $filtroBusqueda $condicion")->getSingleResult();
            
            $numRegistros = $Contador['contador'];
            $totalPagina = ceil($numRegistros / $rows);
            $response = new Response();
            $response->setStatusCode(200);
            $response->headers->set('Content-Type', 'text/xml');
            $auditoria->registralog('Listado de proveedores agregados Grupo', $session->get('id_usuario'));
            //$auditoria->registralog('Listado de Costos', $session->get('id_usuario'));
            return $this->render('grupo/proveedor.xml.twig', array(
                    'entities' => $entities,'numRegistros' => $numRegistros,
                    'maxPagina' => $totalPagina,'pagina' => $pagina,'grupoId'=>$request->get('grupoId')), $response);
        }
    }

    /**
     * Lists all Grupo entities.
     *
     */
    
    public function agregarProveedorContactoAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();
        $auditoria = $this->get('utilidadesAdministrador');
        $proveedorId = $request->get('padreId');
        $grupoId = $request->get('grupoId');
        $proveedorContacto = $request->get('proveedorContacto');
        
        $entity = $em->getRepository('WABackendBundle:Grupo')->find($grupoId);
        $integrante = new IntegranteGrupo();
        $integrante->setActivo(1);
        $integrante->setGrupo($em->getReference('WABackendBundle:Grupo', $grupoId));
        $integrante->setProveedorId($proveedorId);
        $integrante->setContactoProveedorId($proveedorContacto);
        //$integrante->setProveedorId($proveedorContacto);
        try{
            $em->persist($integrante);
            $em->flush();
            $Contador = $em->createQuery("SELECT COUNT(ig.id) AS contador FROM WABackendBundle:IntegranteGrupo ig WHERE ig.grupo=".$grupoId)->getSingleResult();
                $numRegistros = $Contador['contador'];
                if($entity) {
                    $entity->setIntegrantes($numRegistros);
                    $em->persist($entity);
                    $em->flush();
                }
            $response = new Response(json_encode(array('resultado' => '1','integrantes'=>$numRegistros)));
            //$auditoria->registralog('Agregar contactos proveedor Grupo', $session->get('id_usuario'));
        }
        catch(\Exception $e){
            $response = new Response(json_encode(array('resultado' => '0','Error'=>$e->getMessage())));
        }
        /*
        
        */
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'application/json');
        
        return $response;
    }

    /**
     * Lists all Grupo entities.
     *
     */

    public function eliminarProveedorContactoAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();
        $auditoria = $this->get('utilidadesAdministrador');
        $proveedorId = $request->get('proveedorId');
        $grupoId = $request->get('grupoId');
        $proveedorContacto = $request->get('proveedorContacto');
        /*$entidad = $em->createQuery('DELETE FROM WABackendBundle:IntegranteGrupo ig WHERE ig.grupo=:grupo AND ig.proveedorId=:proveedor AND ig.contactoProveedorId=:contacto')
            ->setParameter('grupo', $grupoId)
            ->setParameter('proveedor', $proveedorId)
            ->setParameter('contacto', $proveedorContacto)
            ->getResult();*/
        $entidad = $em->createQuery('DELETE FROM WABackendBundle:IntegranteGrupo ig WHERE ig.grupo=:grupo AND ig.contactoProveedorId=:contacto ')
            ->setParameter('grupo', $grupoId)
            ->setParameter('contacto', $proveedorContacto)
            ->getResult();
        $Contador = $em->createQuery("SELECT COUNT(ig.id) AS contador FROM WABackendBundle:IntegranteGrupo ig WHERE ig.grupo=".$grupoId)->getSingleResult();
        $numRegistros = $Contador['contador'];
        $entity = $em->getRepository('WABackendBundle:Grupo')->find($grupoId);
        if($entity) {
            $entity->setIntegrantes($numRegistros);
            $em->persist($entity);
            $em->flush();
        }
        $response = new Response(json_encode(array('resultado' => '1','integrantes'=>$numRegistros)));
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'application/json');
        $auditoria->registralog('Eliminar contactos proveedor Grupo', $session->get('id_usuario'));
        return $response;
    }
    /**
     * Lists all Grupo entities.
     *
     */

    public function proveedorContactosXmlAction(Request $request){
        if ($request->isXmlHttpRequest()){
            $session = $request->getSession();
            $auditoria = $this->get('utilidadesAdministrador');
            $em = $this->getDoctrine()->getManager('contactosProveedor');
            $emCorreos = $this->getDoctrine()->getManager();
            $busqueda = $this->get('busquedaAdministrador');
            $filtro=$busqueda->busqueda();
            $filtroBusqueda = ($filtro) ? 'WHERE '.$filtro : ' WHERE c.id != 0 ';
            $OrdenTipo = $request->get('sord');
            $OrdenCampo = $request->get('sidx');
            $rows = $request->get('rows');
            $pagina = $request->get('page');
            $paginacion = ($pagina * $rows) - $rows;
            
            $entities = $em->createQuery("SELECT c.id AS id, c.nombreContacto, c.ciudad, c.email, c.telefono, c.movil FROM WABackendBundle:Contactos c $filtroBusqueda AND c.idProveedor = ".$request->get('porveedorId')." ORDER BY ".$OrdenCampo." ".$OrdenTipo);
            
            $entities->setMaxResults($rows);
            $entities->setFirstResult($paginacion);
            $entities = $entities->getResult();

            $Contador = $em->createQuery("SELECT COUNT(c.id) AS contador FROM WABackendBundle:Contactos c $filtroBusqueda AND c.idProveedor = ".$request->get('porveedorId'))->getSingleResult();
            $conn = $emCorreos->getConnection();
            $sqlIntegrantes = "SELECT id, contacto_proveedor_id FROM integrante_grupo WHERE grupo_id=".$request->get('grupoId')." AND proveedor_id=".$request->get('porveedorId');
            $datosIntegrantes = $conn->query($sqlIntegrantes);
            $arrayRespaldoContacto = array();
            foreach($datosIntegrantes as $datIntegrante){
                if(!in_array($datIntegrante['contacto_proveedor_id'], $arrayRespaldoContacto)){
                    $arrayRespaldoContacto[$datIntegrante['contacto_proveedor_id']]=$datIntegrante['contacto_proveedor_id'];
                    //echo $datIntegrante['contacto_proveedor_id'].'  -  ';
                }
            }

            
            $numRegistros = $Contador['contador'];
            $totalPagina = ceil($numRegistros / $rows);
            $response = new Response();
            $response->setStatusCode(200);
            $response->headers->set('Content-Type', 'text/xml');
            $auditoria->registralog('Listado contactos proveedor Grupo', $session->get('id_usuario'));
            //$auditoria->registralog('Listado de Costos', $session->get('id_usuario'));
            return $this->render('grupo/proveedorContacto.xml.twig', array(
                    'entities' => $entities,'numRegistros' => $numRegistros,
                    'integranteGrupo'=>$arrayRespaldoContacto,
                    'maxPagina' => $totalPagina,'pagina' => $pagina,'grupoId'=>$request->get('grupoId')), $response);
        }
    }

    /**
     * Lists all Grupo entities.
     *
     */

    public function agregarAsociadoAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();
        $auditoria = $this->get('utilidadesAdministrador');
        $asociadoId = $request->get('padreId');
        $grupoId = $request->get('grupoId');
        if ($request->get('multiple') == 'true') { 
            set_time_limit(500);
            $Group=' GROUP BY a.codigo ';
            $busqueda = $this->get('busquedaAdministrador');
            $filtro=$busqueda->busqueda();
            $filtroBusqueda = ($filtro) ? $filtro.' AND a.id != 0 ' : ' WHERE a.id != 0';
            if($request->get('tipoGrupo')==1){
               $Group=' GROUP BY a.nit ';
            }
            $entities = $em->createQuery("SELECT a.id, ig.id AS asignado FROM WABackendBundle:Cliente a 
                LEFT JOIN WABackendBundle:IntegranteGrupo ig WITH ig.asociado=a.id AND ig.activo=1 AND ig.grupo=".$request->get('grupoId')." ".$filtroBusqueda.' '.$Group)->getResult();
            $batchSize = 200;
            $i=0;
            $insert1='';
            $insert0='INSERT INTO integrante_grupo (asociado_id, proveedor_id, grupo_id, activo) VALUES ';
            $registros=0;
            foreach($entities as $d){
                if(!$d['asignado']){
                    if($insert1!='')
                        $insert1.=', ';
                    $insert1.='(\''.$d['id'].'\', NULL, \''.$grupoId.'\', \'1\')';
                    if (($i % $batchSize) == 0) {
                        $em->getConnection()->query($insert0.$insert1);
                        $insert1='';
                    }
                    $i++;
                }
            }
            if ($i >1) {
                $em->getConnection()->query($insert0.$insert1);
            }
            /*$em->flush();
            $em->clear();*/
            /*$sql = "INSERT IGNORE INTO integrante_grupo (asociado_id, grupo_id, activo)
                SELECT p.id, $grupoId, 1
                FROM cliente p
                LEFT JOIN integrante_grupo ig ON ig.asociado_id = p.id AND ig.grupo_id = '$grupoId' 
                WHERE NOT EXISTS (SELECT id, $grupoId, 1 FROM integrante_grupo WHERE asociado = p.id AND grupo_id = $grupoId) AND $filtroBusqueda $Group";
            
            $em->getConnection()->query($sql)->execute();*/
        } else {
            $entidad = $em->getRepository('WABackendBundle:IntegranteGrupo')->findOneBy(array('asociado' => $asociadoId, 'grupo' => $grupoId));
            if (!$entidad) {
                $integrante = new IntegranteGrupo();
                $integrante->setActivo(1);
                $integrante->setGrupo($em->getReference('WABackendBundle:Grupo', $grupoId));
                $integrante->setAsociado($em->getReference('WABackendBundle:Cliente', $asociadoId));
                $em->persist($integrante);
                $em->flush();
            }
        }
        $Contador = $em->createQuery("SELECT COUNT(ig.id) AS contador FROM WABackendBundle:IntegranteGrupo ig WHERE ig.grupo=".$grupoId)->getSingleResult();
        $numRegistros = $Contador['contador'];
        $entity = $em->getRepository('WABackendBundle:Grupo')->find($grupoId);
        if($entity) {
            $entity->setIntegrantes($numRegistros);
            $em->persist($entity);
            $em->flush();
        }
        $response = new Response(json_encode(array('resultado' => '1','integrantes'=>$numRegistros)));
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'application/json');
        $auditoria->registralog('Agregar asociado Grupo', $session->get('id_usuario'));
        return $response;
    }

    /**
     * Lists all Grupo entities.
     *
     */

    public function eliminarAsociadoAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $padreId = $request->get('padreId');
        $grupoId = $request->get('grupoId');
        $session = $request->getSession();
        $auditoria = $this->get('utilidadesAdministrador');
        if ($request->get('multiple') == 'true') {
            $sql = "DELETE FROM integrante_grupo WHERE grupo_id = '$grupoId' AND asociado_id IS NOT NULL AND proveedor_id IS NULL";
            $em->getConnection()->query($sql);
        } else {
            $entidad = $em->getRepository('WABackendBundle:IntegranteGrupo')->findOneBy(array('asociado' => $padreId, 'grupo' => $grupoId));
            if ($entidad) {
                $em->remove($entidad);
                $em->flush();
            }
        }
        $Contador = $em->createQuery("SELECT COUNT(ig.id) AS contador FROM WABackendBundle:IntegranteGrupo ig WHERE ig.grupo=".$grupoId)->getSingleResult();
        $numRegistros = $Contador['contador'];
        $entity = $em->getRepository('WABackendBundle:Grupo')->find($grupoId);
        if($entity) {
            $entity->setIntegrantes($numRegistros);
            $em->persist($entity);
            $em->flush();
        }
        $response = new Response(json_encode(array('resultado' => '1','integrantes'=>$numRegistros)));
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'application/json');
        $auditoria->registralog('Elimina asociado Grupo', $session->get('id_usuario'));
        return $response;
    }

    /**
     * Lists all Grupo entities.
     *
     */

    public function asociadosXmlAction(Request $request){
        if ($request->isXmlHttpRequest()){
            $session = $request->getSession();
            $auditoria = $this->get('utilidadesAdministrador');
            /*$permisos = $session->get('permisos');
            $auditoria = $this->get('auditoria');
            if(!$auditoria->seguridad_http('grupo',3,$permisos))
                return $this->redirect($this->generateUrl('admin_login'));*/
            $em = $this->getDoctrine()->getManager();
            $busqueda = $this->get('busquedaAdministrador');
            $filtro=$busqueda->busqueda();
            $filtroBusqueda = ($filtro) ? $filtro.' AND a.id != 0 ' : ' WHERE a.id != 0 ';
            $OrdenTipo = $request->get('sord');
            $OrdenCampo = $request->get('sidx');
            $rows = $request->get('rows');
            $pagina = $request->get('page');
            $paginacion = ($pagina * $rows) - $rows;
            
            $Group=' GROUP BY a.codigo ';$distincy=' COUNT(DISTINCT a.codigo) ';
            if($request->get('tipoGrupo')==1){
               $Group=' GROUP BY a.nit ';
               $distincy=' COUNT(DISTINCT a.nit) ';
               $Contador = $em->createQuery("SELECT ".$distincy." AS contador FROM WABackendBundle:Cliente a 
                    LEFT JOIN WABackendBundle:IntegranteGrupo ig WITH ig.asociado=a.id AND ig.activo=1 AND ig.grupo=".$request->get('grupoId')." ".$filtroBusqueda)->getSingleResult();
               $numRegistros = $Contador['contador'];
            }else{
                $Contador = $em->createQuery("SELECT ".$distincy." AS contador FROM WABackendBundle:Cliente a 
                    LEFT JOIN WABackendBundle:IntegranteGrupo ig WITH ig.asociado=a.id AND ig.activo=1 AND ig.grupo=".$request->get('grupoId')." ".$filtroBusqueda)->getSingleResult();
                $numRegistros = $Contador['contador'];
            }
            
            
            $entities = $em->createQuery("SELECT a.id, a.depto,a.ciudad,a.centro,a.codigo,a.ruta,a.drogueria,a.nit,a.asociado,a.email, a.emailAsociado, ig.id AS asignado FROM WABackendBundle:Cliente a 
                LEFT JOIN WABackendBundle:IntegranteGrupo ig WITH ig.asociado=a.id AND ig.activo=1 AND ig.grupo=".$request->get('grupoId')." ".$filtroBusqueda." ".$Group." ORDER BY ".$OrdenCampo." ".$OrdenTipo);          
            $entities->setMaxResults($rows);
            $entities->setFirstResult($paginacion);
            $entities = $entities->getResult();
            
            $totalPagina = ceil($numRegistros / $rows);
            $estados = array('' => 'Inactivo', 1 => 'Activo');
            $response = new Response();
            $response->setStatusCode(200);
            $response->headers->set('Content-Type', 'text/xml');
            $auditoria->registralog('Listado asociado Grupo', $session->get('id_usuario'));
            //$auditoria->registralog('Listado de Costos', $session->get('id_usuario'));
            return $this->render('grupo/asociados.xml.twig', array(
                    'entities' => $entities,'numRegistros' => $numRegistros,
                    'maxPagina' => $totalPagina,'pagina' => $pagina,'tipoGrupo'=>$request->get('tipoGrupo')), $response);
        }
    }

    /**
     * Lists all Grupo entities.
     *
     */

    public function destinatariosAction(Request $request)
    {
        //$auditoria = $this->get('auditoria');
        //$session = $this->getRequest()->getSession();
        //$permisos = $session->get('permisos');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('WABackendBundle:Grupo')->find($request->get('idGrupo'));
        if (!$entity) {
            throw $this->createNotFoundException('El grupo que intenta consultar no tiene Entidad.');
        }
        return $this->render('grupo/destinatarios.html.twig', array(
            /*'permisos' => $permisos,
            'raices' => $session->get('raices'), 
            'permiso_modulo' => $permisos['grupo']['permiso'],
            'rutaNuevo'=>$this->generateUrl('grupo_new'),*/
            'menu'=>'grupos',
            'grupoId'=>$request->get('idGrupo'),
            'tipoGrupo'=>(($entity->getClasificacion()=='Asociados')?1:0)
        ));
    }

    /**
     * Lists all Grupo entities.
     *
     */

    public function xmlAction(Request $request){
        if ($request->isXmlHttpRequest()){
            $session = $request->getSession();
            $auditoria = $this->get('utilidadesAdministrador');
            //$permisos = $session->get('permisos');
            //$auditoria = $this->get('auditoria');
            $busqueda = $this->get('busquedaAdministrador');
            $filtro=$busqueda->busqueda();
            $filtroBusqueda=(($filtro!='')?' WHERE g.administrador='.$session->get('id_usuario').' AND '.$filtro:' WHERE g.administrador='.$session->get('id_usuario'));
            $OrdenTipo = $request->get('sord');
            $OrdenCampo = $request->get('sidx');
            $rows = $request->get('rows');
            $pagina = $request->get('page');
            $paginacion = ($pagina * $rows) - $rows;
            $em = $this->getDoctrine()->getManager();
            $entities = $em->createQuery("SELECT g FROM WABackendBundle:Grupo g ".$filtroBusqueda." ORDER BY ".$OrdenCampo." ".$OrdenTipo);
            $entities->setMaxResults($rows);
            $entities->setFirstResult($paginacion);
            $entities = $entities->getResult();
            $Contador = $em->createQuery("SELECT COUNT(g.id) AS contador FROM WABackendBundle:Grupo g".$filtroBusqueda)->getSingleResult();
            $numRegistros = $Contador['contador'];
            $totalPagina = ceil($numRegistros / $rows);
            $estados = array('' => 'Inactivo', 1 => 'Activo');
            $response = new Response();
            $response->setStatusCode(200);
            $response->headers->set('Content-Type', 'text/xml');
            $auditoria->registralog('Listado Grupos', $session->get('id_usuario'));
            //$auditoria->registralog('Lista los Grupos', $session->get('id_usuario'));
            return $this->render('grupo/index.xml.twig', array(
                    'entities' => $entities,'numRegistros' => $numRegistros,
                    'maxPagina' => $totalPagina,'pagina' => $pagina,
                    'estados' => $estados), $response);
        }
    }

    /**
     * Lists all Grupo entities.
     *
     */
    public function indexAction() {
        $em = $this->getDoctrine()->getManager();

        $grupos = $em->getRepository('WABackendBundle:Grupo')->findAll();

        return $this->render('grupo/index.html.twig', array(
            'grupos' => $grupos,
            'menu'=>'grupos'
        ));
    }

    public function asignadoAction(Request $request) {
        return $this->render('grupo/asignadopro.html.twig', array(
            'menu'=>'grupos',
            'grupoId' => $request->get('grupoId')
        ));
    }

    /**
     * Creates a new Grupo entity.
     *
     */
    public function newAction(Request $request)
    {
        $grupo = new Grupo();
        $form = $this->createForm('WA\BackendBundle\Form\GrupoType', $grupo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $session = $request->getSession();
            $auditoria = $this->get('utilidadesAdministrador');
            $em = $this->getDoctrine()->getManager();
            $grupo->setAdministrador($em->getReference('WABackendBundle:Administrador', $session->get('id_usuario')));
            $grupo->setFechaCreado(new \DateTime());
            $em->persist($grupo);
            $em->flush();
            $auditoria->registralog('Nuevo Grupo', $session->get('id_usuario'));
            return $this->redirectToRoute('grupo_edit', array('id' => $grupo->getId()));
        }

        return $this->render('grupo/new.html.twig', array(
            'grupo' => $grupo,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Grupo entity.
     *
     */
    public function showAction(Grupo $grupo)
    {
        $deleteForm = $this->createDeleteForm($grupo);

        return $this->render('grupo/show.html.twig', array(
            'grupo' => $grupo,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Grupo entity.
     *
     */
    public function editAction(Request $request,Grupo $grupo){
        $editForm = $this->createForm('WA\BackendBundle\Form\GrupoType', $grupo);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $session = $request->getSession();
            $auditoria = $this->get('utilidadesAdministrador');
            $em = $this->getDoctrine()->getManager();
            $em->persist($grupo);
            $em->flush();
            $auditoria->registralog('Edicion Grupo', $session->get('id_usuario'));
            return $this->redirectToRoute('grupo_edit', array('id' => $grupo->getId()));
        }

        return $this->render('grupo/edit.html.twig', array(
            'grupo' => $grupo,
            'edit_form' => $editForm->createView()
        ));
    }
}
