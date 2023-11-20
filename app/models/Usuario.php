<?php

class Usuario
{
    public $id;
    public $nombre;
    public $usuario;
    public $contrasenia;
    public $sector;
    public $pedidos_pendiente;
    public $estado;
    public $fechaDeBaja;

    function generarCodigoUnico($longitud = 5)
    {
        $caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        do {
            $codigo = '';
            for ($i = 0; $i < $longitud; $i++) {
                $codigo .= $caracteres[rand(0, strlen($caracteres) - 1)];
            }
        } while (Usuario::verificarCodigoUnico($codigo));
        return $codigo;
    }

    private static function verificarCodigoUnico($codigoPedido){
        $objAccesoDatos = AccesoDatos::obtenerInstancia();

        $consulta = $objAccesoDatos->prepararConsulta("SELECT COUNT(*) FROM pedidos WHERE idPedido = :idPedido");
        $consulta->bindValue(':idPedido', $codigoPedido, PDO::PARAM_STR);
        $consulta->execute();
        return $consulta->fetchColumn() > 0;
    }

    public function crearUsuario()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();

        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO usuarios (nombre, usuario, contrasenia, sector, pedidos_pendiente, estado) VALUES(:nombre, :usuario, :contrasenia, :sector, :pedidos_pendiente, :estado)");
        $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':sector', $this->sector, PDO::PARAM_STR);
        $consulta->bindValue(':usuario', $this->usuario, PDO::PARAM_STR);
        $consulta->bindValue(':contrasenia', $this->contrasenia, PDO::PARAM_STR);
        $consulta->bindValue(':pedidos_pendiente', json_encode($this->pedidos_pendiente), PDO::PARAM_STR);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);

        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, nombre, usuario, contrasenia, sector, pedidos_pendiente, estado, fechaDeBaja FROM usuarios");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Usuario');
    }

    public static function obtenerUsuario($usuario)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, nombre, usuario, contrasenia, sector, pedidos_pendiente, estado, fechaDeBaja FROM usuarios WHERE usuario = :usuario");
        $consulta->bindValue(':usuario', $usuario, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Usuario');
    }

    public static function obtenerUsuarioPorId($id_usuario)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, nombre, usuario, contrasenia, sector, pedidos_pendiente, estado, fechaDeBaja FROM usuarios WHERE id = :id");
        $consulta->bindValue(':id', $id_usuario, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Usuario');
    }

    public function modificarUsuario()
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE usuarios SET nombre = :nombre, usuario = :usuario, contrasenia = :contrasenia, sector = :sector, pedidos_pendiente = :pedidos_pendiente, estado = :estado, fechaDeBaja = :fechaDeBaja WHERE id = :id");
        $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':usuario', $this->usuario, PDO::PARAM_STR);
        $consulta->bindValue(':contrasenia', $this->contrasenia, PDO::PARAM_STR);
        $consulta->bindValue(':sector', $this->sector, PDO::PARAM_STR);
        $consulta->bindValue(':pedidos_pendiente', json_encode($this->pedidos_pendiente), PDO::PARAM_STR);
        $consulta->bindValue(':fechaDeBaja', $this->fechaDeBaja, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function borrarUsuario($id)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE usuarios SET fechaDeBaja = :fechaDeBaja WHERE id = :id");
        $fecha = (new DateTime('now', new DateTimeZone('America/Argentina/Buenos_Aires')))->format('Y/m/d');
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->bindValue(':fechaDeBaja', $fecha, PDO::PARAM_STR);
        return $consulta->execute();
    }

    public static function obtenerUsuariosPorSector($sector)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, nombre, usuario, contrasenia, sector, pedidos_pendiente, estado, fechaDeBaja FROM usuarios WHERE sector = :sector AND pedidos_pendiente = 0");

        $consulta->bindValue(':sector', $sector, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Usuario');
    }


    public static function BuscarEmpleadoDisponible($sector) {
        $empleados = Usuario::obtenerUsuariosPorSector($sector);
        foreach ($empleados as $empleado) {
            if($empleado->estado == "disponible" && $empleado->fechaDeBaja == null) {
                return $empleado;
            }
        }
        return Usuario::BuscarEmpleadosConMenosPedidos($empleados);
    }

    private static function BuscarEmpleadosConMenosPedidos($empleados){
        $empleadoConMenosPedidos = $empleados[0];
        
        foreach ($empleados as $empleado) {
            if(count(json_decode($empleadoConMenosPedidos->pedidos_pendiente)) > count(json_decode($empleado->pedidos_pendiente))){
                $empleadoConMenosPedidos = $empleado;
            }
        }
        return $empleadoConMenosPedidos;
    }

    public static function TraerUsuarioPorSesion($username, $contrasenia){
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, nombre, usuario, contrasenia, sector, pedidos_pendiente, estado, fechaDeBaja FROM usuarios WHERE usuario = :usuario AND contrasenia = :contrasenia" );
        $consulta->bindValue(':usuario', $username, PDO::PARAM_STR);
        $consulta->bindValue(':contrasenia', $contrasenia, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Usuario');
    }

}