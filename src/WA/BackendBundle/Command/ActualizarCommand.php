<?php
namespace WA\BackendBundle\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
class ActualizarCommand extends ContainerAwareCommand{
    protected function configure(){
        parent::configure();
        $this->setName('actualiza:asociados')->setDescription('Actualizacion de Clientes.');
    }
    protected function execute(InputInterface $input, OutputInterface $output){
        set_time_limit(0);
        ini_set('memory_limit', '2048M');
        $output->writeln("<question>INICIO DE LA TAREA:</question>");
        $em = $this->getContainer()->get('doctrine')->getManager();
        $emConect=$em->getConnection();
        $ruta='/home/planos/';
        if (filesize($ruta.'maestro_drog.txt') > 0){
          $output->writeln('<error>Se abre archivo ['.$ruta.'maestro_drog.txt] en modo lectura (r) '.date('Y-m-d H:i:s').': </error>');
          if ($fp = fopen($ruta.'maestro_drog.txt', "r")){
            $Asociados=array();
            $drogueriasSql='SELECT * FROM cliente';
            $drogueriasQuery=$emConect->query($drogueriasSql);
            foreach($drogueriasQuery as $drogueria){
                $Asociados[$drogueria['codigo']]['id']=$drogueria['id'];
            }
            $Columnas = 'INSERT INTO cliente (zona, codigo, bloqueo_d, drogueria, nit, bloqueo_r, asociado, nit_dv, direcion, cod_mun, ciudad, ruta, un_geogra, depto, telefono, centro, p_s, p_carga, diskette, email, email_asociado, tipo_cliente, cupo_asociado, centro_costo) ';
            $Valores = 'VALUES ';
            $registros = 0;
            $ins=$contU = 0;
            $sql="";
            $codDocumento=array();
            while($datos = fgetcsv($fp, 1000, ",")){
              if(isset($Asociados[(int)$datos[1]]['id'])){
                  $output->writeln("Actualiza asociado [".(int)$datos[1]."]");
                  $cupo=(int)$datos[29];
                  $sql .= "UPDATE cliente "
                      ."SET zona = '".(int) str_replace(" ","",trim($datos[0]))."', "
                      ."bloqueo_d = '".$datos[2] ."', "
                      ."drogueria = '".str_replace('"','&quot;',utf8_encode(preg_replace('([^A-Za-z0-9 .#()°-])', '',$datos[3])))."', "
                      ."nit = '".$datos[4]."', "
                      ."bloqueo_r = '".str_replace('"','&quot;',utf8_encode($datos[5]))."', "
                      ."asociado = '".utf8_encode($datos[6])."', "
                      ."nit_dv = '".$datos[7]."', "
                      ."direcion = '".utf8_encode($datos[8])."', "
                      ."cod_mun = '".$datos[9]."', "
                      ."ciudad = '".utf8_encode($datos[10])."', "
                      ."ruta = '".$datos[11]."', "
                      ."un_geogra = '".$datos[12]."', "
                      ."depto = '".utf8_encode($datos[13])."', "
                      ."telefono = '".$datos[14]."', "
                      ."centro = '".$datos[15]."', "
                      ."p_s = '".$datos[16]."', "
                      ."p_carga = '".$datos[17]."', "
                      ."diskette = '".$datos[18]."', "
                      ."email = '".utf8_encode($datos[19])."', "
                      ."email_asociado = '".utf8_encode($datos[20])."', "
                      ."tipo_cliente = '".$datos[21]."', "
                      ."cupo_asociado = '".$cupo."', "
                      ."centro_costo = '".(int)$datos[33]."' "
                      ."WHERE id = ".$Asociados[(int)$datos[1]]['id']."; ";
                  $contU++;
                  if($contU==1){
                      $emConect->query($sql);
                      $contU=0;
                      $sql="";
                  }
              }else{
                if($ins > 0){
                  $Valores.=', ';
                }
                $Valores.='(
                 "' . (int) str_replace(" ", "", trim($datos[0])) . '",
                  "' . $datos[1] . '",
                   "' . $datos[2] . '",
                    "' . str_replace('"', '&quot;', utf8_encode(preg_replace('([^A-Za-z0-9 .#()°-])', '',$datos[3]))) . '",
                     "' . $datos[4] . '",
                      "' . str_replace('"', '&quot;', utf8_encode($datos[5])) . '",
                       "' . utf8_encode($datos[6]) . '",
                        "' . utf8_encode($datos[7]) . '",
                         "' . utf8_encode($datos[8]) . '",
                          "' . utf8_encode($datos[9]) . '",
                           "' . utf8_encode($datos[10]) . '",
                            "' . utf8_encode($datos[11]) . '",
                             "' . utf8_encode($datos[12]) . '",
                              "' . utf8_encode($datos[13]) . '",
                               "' . utf8_encode($datos[14]) . '",
                                "' . $datos[15] . '",
                                 "' . $datos[16] . '",
                                  "' . $datos[17] . '",
                                    "' . $datos[18] . '",
                                     "' . $datos[19] . '",
                                      "' . $datos[20] . '",
                                       "' . $datos[21] . '",
                                        "' . (int) $datos[29] . '",
                                              "' . (int) $datos[33] . '")';
                  $ins++;
                  if ($ins == 200){
                    $emConect->query($Columnas . $Valores);
                    $Valores = "VALUES ";
                    $ins = 0;
                  }
                $registros++;
              }
              $codDocumento[]=(int)$datos[1];
            }
            $drogueriaCodigo=array();
            /*$codigosSql='SELECT * FROM cliente';
            $codigoQuery=$emConect->query($codigosSql);
            foreach($codigoQuery as $codigo){
                $drogueriaCodigo[]=(int)$codigo['codigo'];
            }
            $result=array_diff($drogueriaCodigo,$codDocumento);
            foreach ($result as $borrar) {
              $borrarSql='DELETE FROM cliente WHERE codigo ='.$borrar;
              $borrarQuery=$emConect->query($borrarSql);
            }*/
            fclose($fp);
            $output->writeln('<error>Se cierra archivo ['.$ruta.'maestro_drog.txt] abierto en modo lectura (r) '.date('Y-m-d H:i:s').': </error>');
          }
          if ($ins > 0){
            $emConect->query($Columnas . $Valores);
            $Valores = "VALUES ";
            $ins = 0;
          }
          //Actualiza
          if($contU>0){
              $emConect->query($sql);
          }
          $output->writeln('<info>Se insertaron '.$registros.' registros<info>');
        }else{
          $output->writeln("No hay un archivo para importar información.");
        }
        $output->writeln("<question>FIN DE LA TAREA</question>");
    }
}
?>
