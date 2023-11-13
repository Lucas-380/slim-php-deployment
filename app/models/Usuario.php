<?php

class Usuario
{
    public $id;
    public $nombre;
    public $sector;
    public $pedidos_pendiente;
    public $estado;

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

        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO usuarios (nombre, sector, pedidos_pendiente, estado) VALUES(:nombre, :sector, :pedidos_pendiente, :estado)");
        $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':sector', $this->sector, PDO::PARAM_STR);
        $consulta->bindValue(':pedidos_pendiente', json_encode($this->pedidos_pendiente), PDO::PARAM_STR);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);

        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, nombre, sector, pedidos_pendiente, estado FROM usuarios");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Usuario');
    }

    public static function obtenerUsuario($usuario)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, nombre, sector, pedidos_pendiente, estado FROM usuarios WHERE nombre = :nombre");
        $consulta->bindValue(':nombre', $usuario, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Usuario');
    }

    public static function obtenerUsuarioPorId($id_usuario)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, nombre, sector, pedidos_pendiente, estado FROM usuarios WHERE id = :id");
        $consulta->bindValue(':id', $id_usuario, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Usuario');
    }

    public function modificarUsuario()
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE usuarios SET nombre = :nombre, sector = :sector, estado = :estado, pedidos_pendiente = :pedidos_pendiente WHERE id = :id");
        $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':sector', $this->sector, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $consulta->bindValue(':pedidos_pendiente', json_encode($this->pedidos_pendiente), PDO::PARAM_STR);
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function obtenerUsuariosPorSector($sector)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, nombre, sector, pedidos_pendiente, estado FROM usuarios WHERE sector = :sector AND pedidos_pendiente = 0");

        $consulta->bindValue(':sector', $sector, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Usuario');
    }


    public static function BuscarEmpleadoDisponible($sector) {
        $empleados = Usuario::obtenerUsuariosPorSector($sector);
        foreach ($empleados as $empleado) {
            if($empleado->estado == "disponible") {
                return $empleado;
            }
        }
        Usuario::BuscarEmpleadosConMenosPedidos($empleados);
    }

    private static function BuscarEmpleadosConMenosPedidos($empleados){
        $empleadoConMenosPedidos = $empleados[0];

        foreach ($empleados as $empleado) {
            if(count($empleadoConMenosPedidos) > count($empleado->pedidosPendiente)){
                $empleadoConMenosPedidos = $empleado;
            }
        }
        return $empleadoConMenosPedidos;
    }

}