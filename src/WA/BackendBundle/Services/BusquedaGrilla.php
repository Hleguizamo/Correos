<?php

namespace WA\BackendBundle\Services;

class BusquedaGrilla {

    /**
     * Contenedor de servicios
     */
    private $container;

    /**
     * Constructor de la clase.
     */
    public function __construct($container) {
        $this->container = $container;
    }

    /**
     * Toma los parametros de la grilla 
     * y los transforma en sentencia SQL
     */
    public function busqueda() {

        $request=$this->container->get('request_stack')->getCurrentRequest();
        $Sql = "";
        if ($request->get('_search') == true) {
            if ($request->get('filters')) {
                $qwery = array();
                $qopers = array(
                    'eq' => " = ",
                    'ne' => " <> ",
                    'lt' => " < ",
                    'le' => " <= ",
                    'gt' => " > ",
                    'ge' => " >= ",
                    'bw' => " LIKE ",
                    'bn' => " NOT LIKE ",
                    'in' => " IN ",
                    'ni' => " NOT IN ",
                    'ew' => " LIKE ",
                    'en' => " NOT LIKE ",
                    'cn' => " LIKE ",
                    'nc' => " NOT LIKE ");
                if ($request->get('filters')) {
                    $jsona = json_decode($request->get('filters'), true);

                    if (is_array($jsona)) {
                        $gopr = $jsona['groupOp'];
                        $rules = $jsona['rules'];
                        $i = 0;
 
                        foreach ($rules as $key => $val) {
                            $field = $val['field'];
                            $op = $val['op'];
                            $v = $val['data'];
                                                       
                            if ($op && ($v||$v==="0") ) {
                                $qwery[$i][0] = $field;
                                $qwery[$i][1] = $qopers[$op];
                                $qwery[$i][2] = $this->ValoSQL($op, $v);
                                $qwery[$i][3] = $gopr;
                                $i++;
                               
                            }
                        }
                    }
                }
 
                foreach ($qwery as $p) {
                    if ($Sql != "")
                        $Sql.=" " . $p[3] . " ";
                    $Sql.=" " . $p[0] . " " . $p[1] . " '" . $p[2] . "'";
                }
                $Sql = ' WHERE '.$Sql.'';
            }
        }

        return $Sql;
    }

    /**
     * Valida el tipo de busqueda.
     * Si es = ó contiene un valor.
     * @param string $oper Tipo de operacion
     * @param string $valor Valor a comparar
     */
    function ValoSQL($oper, $val) {
        if ($oper == 'bw' || $oper == 'bn')
            return addslashes($val) . "%";
        else if ($oper == 'ew' || $oper == 'en')
            return "%" . addcslashes($val);
        else if ($oper == 'cn' || $oper == 'nc')
            return "%" . addslashes($val) . "%";
        else
            return addslashes($val);
    }

    /**
     * Retorna condicional para filtrado de busqueda
     * @param array $filtro Filtro de la busqueda codificada en JSON de los parametros de la busqueda.
     * @return string Contiene el elemento WHERE de SQL para realizar el filtro de la busqueda.
     * @since 1.0
     * @author Sergio Barbosa <sbarbosa115@gmail.com>
     */
    public function busquedaSinWhere() {
        $busqueda = $this->container->get('request')->get('_search');
        $filtro = stripslashes($this->container->get('request')->get('filters'));
        $consulta = '';
        if ($busqueda == 'true') {
            $arrayBusqueda = json_decode($filtro);
            $arrayGeneral = $arrayBusqueda->{'rules'};
            $cont = 1;
            $opcion = null;
            foreach ($arrayGeneral as $var) {
                foreach ($var as $a => $b) {
                    switch ($a) {
                        case 'field':
                            $consulta .= $b;
                            break;
                        case 'op':
                            $opcion = $b;
                            break;
                        case 'data':
                            $consulta .= $this->valorSql($opcion, trim(addslashes($b)));
                            if ($cont < count($arrayGeneral))
                                $consulta .= ' AND ';

                            $cont += 1;
                            break;
                        default:
                            throw $this->createNotFoundException('Error al crear JSON');
                            break;
                    }
                }
            }
        }else {
            $consulta = false;
        }
        return $consulta;
    }

    /**
     * Arma el elemento de busqueda para SQL.
     * @param string $opcion Contiene el elemento de busqueda enviado por JQGrid.
     * @param string $data Contiene el elemento por el cual se desea filtrar.
     * @return string Contiene el elemento WHERE de SQL para realizar el filtro de la busqueda.
     * @since 1.0
     * @author Sergio Barbosa <sbarbosa115@gmail.com>
     */
    protected function valorSql($opcion, $data) {

        switch ($opcion) {
            case 'eq':
                if ($data == 'null' || $data == 'NULL') {
                    return " IS NULL ";
                } else {
                    return ' = \'' . $data . '\'';
                }
                break;
            case 'ne':
                return ' <> \'' . $data . '\'';
                break;
            case 'lt':
                return ' < \'' . $data . '\'';
                break;
            case 'le':
                return ' <= \'' . $data . '\'';
                break;
            case 'gt':
                return ' > \'' . $data . '\'';
                break;
            case 'bw':
                return ' LIKE \'%' . $data . '%\'';
                break;
            case 'bn':
                return ' NOT LIKE \'' . $data . '%\'';
                break;
            case 'in':
                return ' LIKE \'' . $data . '%\'';
                break;
            case 'ni':
                return ' NOT LIKE \'' . $data . '%\'';
                break;
            case 'ew':
                return ' LIKE \'%' . $data . '\'';
                break;
            case 'en':
                return ' NOT LIKE \'%' . $data . '\'';
                break;
            case 'cn':
                return ' LIKE \'%' . $data . '%\'';
                break;
            case 'nc':
                return ' NOT LIKE \'%' . $data . '%\'';
                break;
            case 'nn':
                return " IS NOT NULL ";
                break;
            case 'nu':
                return " IS NULL ";
                break;
        }
    }
    public function fecha_acttual(){
        $arrayMeses=array('Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');
        $arrayDias = array('Domingo','Lunes','Martes','Miercoles','Jueves','Viernes','Sabado');
        return $arrayDias[date('w')].", ".date('d')." de ".$arrayMeses[date('m')-1]." de ".date('Y');
    }
}

?>
