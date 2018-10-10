<?php
namespace WA\BackendBundle\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
class AntiguosCommand extends ContainerAwareCommand{
    protected function configure(){
        parent::configure();
        $this->setName('antiguos:eliminar')->setDescription('Elimina correos antiguos.');
    }
    protected function execute(InputInterface $input, OutputInterface $output){
        $em = $this->getContainer()->get('doctrine')->getEntityManager();
        $emCon = $this->getContainer()->get('doctrine')->getManager();
        $conexionDb=$emCon->getConnection();
        $fecha = date('Y-m-d H:m:s');
        //descontar los dias
        $fechaBorrar = strtotime('-100 day', strtotime($fecha));
        $fechaBorrar = date ( 'Y-m-d H:i:s' , $fechaBorrar );
        //echo $fechaBorrar;exit();
        $envios = $em->createQuery("SELECT e FROM WABackendBundle:Envio e WHERE e.fechaCreado < :fecha")->setParameter('fecha', $fechaBorrar)->execute();
        $output->writeln('<comment>Consulta  SELECT e FROM WABackendBundle:Envio e WHERE e.fechaCreado < '.$fechaBorrar.'</comment>');
        $cont=0;
        foreach($envios as $envio){
            $directorioRaiz1="./adjuntos/".$envio->getId();
            if(is_dir($directorioRaiz1)){
                $directorioRaiz = opendir("./adjuntos/".$envio->getId().'/');
                while($archivo = readdir($directorioRaiz)){
                    if(is_file("./adjuntos/".$envio->getId().'/'.$archivo)){
                        unlink("./adjuntos/".$envio->getId().'/'.$archivo); 
                    }else{
                        if(is_dir("./adjuntos/".$envio->getId().'/'.$archivo)){
                            $otroAdm = opendir("./adjuntos/".$envio->getId().'/'.$archivo.'/');
                            
                            if(is_dir("./adjuntos/".$envio->getId().'/'.$archivo.'/csv')){
                                $otroAdm = opendir("./adjuntos/".$envio->getId().'/'.$archivo.'/csv/');
                                while($archivoCombinacion = readdir($otroAdm)){
                                    if(is_file("./adjuntos/".$envio->getId().'/'.$archivo.'/csv/'.$archivoCombinacion)){
                                        unlink("./adjuntos/".$envio->getId().'/'.$archivo.'/csv/'.$archivoCombinacion);
                                    }
                                }
                                rmdir("./adjuntos/".$envio->getId().'/'.$archivo.'/csv');
                            }
                            
                            if(is_dir("./adjuntos/".$envio->getId().'/'.$archivo.'/otros')){
                                $otroAdm = opendir("./adjuntos/".$envio->getId().'/'.$archivo.'/otros');
                                while($archivoAdjunto = readdir($otroAdm)){
                                    if(is_file("./adjuntos/".$envio->getId().'/'.$archivo.'/otros/'.$archivoAdjunto)){
                                        unlink("./adjuntos/".$envio->getId().'/'.$archivo.'/otros/'.$archivoAdjunto);
                                    }
                                }
                                rmdir("./adjuntos/".$envio->getId().'/'.$archivo.'/otros');
                                
                            }
                            rmdir("./adjuntos/".$envio->getId().'/'.$archivo);
                        }
                    }
                }
                rmdir("./adjuntos/".$envio->getId());
            }
            $sqlEliminaLog1="DELETE FROM columnas_datos_envio WHERE envio_id = '".$envio->getId()."'";
            $Eliminar1=$conexionDb->prepare($sqlEliminaLog1);
            $Eliminar1->execute();

            $sqlEliminaLog2="DELETE FROM datos_envio WHERE envio_id = '".$envio->getId()."'";
            $Eliminar2=$conexionDb->prepare($sqlEliminaLog2);
            $Eliminar2->execute();

            $sqlEliminaLog3="DELETE FROM copia_envio WHERE envio_id = '".$envio->getId()."'";
            $Eliminar3=$conexionDb->prepare($sqlEliminaLog3);
            $Eliminar3->execute();

            $sqlEliminaLog4="DELETE FROM envio_lectura WHERE envio_id = '".$envio->getId()."'";
            $Eliminar4=$conexionDb->prepare($sqlEliminaLog4);
            $Eliminar4->execute();

            $sqlEliminaLog5="DELETE FROM envio_integrante WHERE envio_id = '".$envio->getId()."'";
            $Eliminar5=$conexionDb->prepare($sqlEliminaLog5);
            $Eliminar5->execute();

            $sqlEliminaLog6="DELETE FROM envio WHERE id = '".$envio->getId()."'";
            $Eliminar6=$conexionDb->prepare($sqlEliminaLog6);
            $Eliminar6->execute();

            /*$em->createQuery("DELETE FROM WABackendBundle:ColumnasDatosEnvio cde WHERE cde.envioId = :envio")->setParameter('envio', $envio->getId())->execute();
            $em->createQuery("DELETE FROM WABackendBundle:DatosEnvio de WHERE de.envioId = :envio")->setParameter('envio', $envio->getId())->execute();
            $em->createQuery("DELETE FROM WABackendBundle:Adjunto cde WHERE cde.envio = :envio")->setParameter('envio', $envio->getId())->execute();
            $em->createQuery("DELETE FROM WABackendBundle:CopiaEnvio ce WHERE ce.envio = :envio")->setParameter('envio', $envio->getId())->execute();
            $em->createQuery("DELETE FROM WABackendBundle:EnvioLectura el WHERE el.envioId = :envio")->setParameter('envio', $envio->getId())->execute();
            $em->createQuery("DELETE FROM WABackendBundle:EnvioIntegrante ei WHERE ei.envio = :envio")->setParameter('envio', $envio->getId())->execute();
            $em->createQuery("DELETE FROM WABackendBundle:Envio e WHERE e.id = :envio")->setParameter('envio', $envio->getId())->execute();*/
            $output->writeln('<comment>Elimine el envio '.$envio->getId().' y todas sus dependencias</comment>');
            $cont++;
        }
        $output->writeln('<info>Tarea Terminada eliminando '.$cont.' envios.</info>');
    }
}
?>