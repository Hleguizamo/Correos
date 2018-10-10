<?php
namespace WA\BackendBundle\Services;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
use WA\BackendBundle\Entity\LogAdministrador;
class Util {

    /**
     * Contenedor de servicios
     */
    private $container;

    /**
     * Costructor de la clase
     */
    public function __construct($container) {
        $this->container = $container;
    }

    /**
     * Registra en la tabla de logs las 
     * actividades de los usuarios.
     * @param string $actividad Actividad que realizo el usuario
     * @param string $rol Rol del usuario
     */
    public function registralog($actividad,$id) {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $conexion = $this->container->get('Doctrine')->getManager()->getConnection();
        $ip = $request->getClientIp();
        if($ip==null){
            $ip='';
        }
        $fecha = new \DateTime('now');
        $sql='INSERT INTO log_administrador (actividad, ip, administrador_id, fecha_ingreso) VALUES(?,?,?,?) ';
        $aux=$conexion->prepare($sql);
        $aux->execute(array($actividad,$ip,$id,date('Y-m-d H:i:s')));

    }


    /**
     * Verifica los permisos del usuario.
     * @param string $controlador Permiso al que se intenta eccesar
     * @param int $nivel Nivel de permiso.
     * Permisos:
     * 1 Adminsitrador total, listado, creacion, edicion, eliminacion etc..
     * 2 Administrador lectura y edicion.
     * 3 Administrador consulta solo lectura de los datos.
     */
    public function seguridad_http($contolador, $nivel, $permisos) {
        if(!isset($permisos[$contolador]['tipo'])) {
            return false;
        }else {
            if ($permisos[$contolador]['tipo'] <= $nivel) {
                return $permisos[$contolador]['tipo'];
            } else {
                return false;
            }
        }
        return true;
    }
    

    
    /**
     * Limpia una cadena de texto de caracteres especiales y espacios
     * @param string $cadena URL o cadena de texto que se desea limpiar
     * @param unknown_type $separador Separador para los espacios
     * @return string cadena de texto limpia
     * @author Emmanuel Camacho <desarrollo1@waplicaciones.co>
     */
    public function getSlug($cadena, $separador = '_') {
    	$slug = iconv('UTF-8', 'ASCII//TRANSLIT', $cadena);
    	$slug = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $slug);
    	$slug = strtolower(trim($slug, $separador));
    	$slug = preg_replace("/[\/_|+ -]+/", $separador, $slug);
    	return $slug;
    }
    
    /**
     * Valida la sesion del usuario
     * @author Emmanuel Camacho <desarrollo1@waplicaciones.co>
     */
    public function validarSesion(){
    	$valid = '';
    	$sesion = $this->container->get('session');
    	$lastTime = $sesion->get('last_time');
    	$lifetime = $sesion->getMetadataBag()->getLifetime();
    	if(time() < ($lastTime + $lifetime)){
    		$sesion->set('last_time', time());
    		$valid = true;
    	}else{
    		$sesion->invalidate();
    		$valid = false;
    	}
    	return $valid;
    }
    
    /**
     * Encripta el texto enviado
     * @param string $raw texto a encriptar 
     * @param string $salt (opcional) salt
     * @return string valor encriptado
     */
    public function encodePassword($raw, $salt = ''){
    	$iteraciones = $this->container->getParameter('wa.clave_iteraciones');
    	$encoder = new MessageDigestPasswordEncoder('sha512',true,$iteraciones);
    	return $encoder->encodePassword($raw, $salt);
    }
    
    /**
     * Valida si un password es valido
     * @param string $encoded string codificado
     * @param string $raw string a comparar
     * @param string $salt (opcional) salt
     * @return bool true si es valido false en caso contrario
     * @author Emmanuel Camacho <ecamacho@waplicaciones.co>
     */
    public function isPasswordValid($encoded, $raw, $salt = ''){
    	$iteraciones = $this->container->getParameter('wa.clave_iteraciones');
    	$encoder = new MessageDigestPasswordEncoder('sha512',true,$iteraciones);
    	return $encoder->isPasswordValid($encoded, $raw, $salt);
    }
    
    
    
    
    public function encrypt($string) {
        $key="ticket";
        $result = '';
        for($i=0; $i<strlen($string); $i++) {
           $char = substr($string, $i, 1);
           $keychar = substr($key, ($i % strlen($key))-1, 1);
           $char = chr(ord($char)+ord($keychar));
           $result.=$char;
        }
        return base64_encode($result);
    }
    public function decrypt($string) {
        $key="ticket";
        $result = '';
        $string = base64_decode($string);
        for($i=0; $i<strlen($string); $i++) {
           $char = substr($string, $i, 1);
           $keychar = substr($key, ($i % strlen($key))-1, 1);
           $char = chr(ord($char)-ord($keychar));
           $result.=$char;
        }
        return $result;
    }


    function generarClave($longitud = 8, $especiales = false) {
      $clave = "";
      $semilla = array();
      $semilla[] = array('a', 'e', 'i', 'o', 'u');
      $semilla[] = array('b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'm', 'n', 'p', 'q', 'r', 's', 't', 'v', 'w', 'x', 'y', 'z');
      $semilla[] = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
      $semilla[] = array('A', 'E', 'I', 'O', 'U');
      $semilla[] = array('B', 'C', 'D', 'F', 'G', 'H', 'J', 'K', 'L', 'M', 'N', 'P', 'Q', 'R', 'S', 'T', 'V', 'W', 'X', 'Y', 'Z');
      $semilla[] = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
      if ($especiales) {
          $semilla[] = array('$', '#', '%', '&', '@', '-', '?', '¿', '!', '¡', '+', '-', '*');
      }
      for ($bucle = 0; $bucle < $longitud; $bucle++) {
          $valor = mt_rand(0, count($semilla) - 1);
          $posicion = mt_rand(0, count($semilla[$valor]) - 1);
          $clave.=$semilla[$valor][$posicion];
      }
      return $clave;
    }

}

?>
