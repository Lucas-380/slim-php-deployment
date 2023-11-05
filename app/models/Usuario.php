<?php

class Usuario
{
    public $id;
    public $nombre;
    public $rol;
    public $pedidos_pendiente;
    public $estado;
    // public $codigoCliente;

    public function crearUsuario()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $pedidosPendientes = count($this->pedidos_pendiente);
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO usuarios (nombre, rol, pedidos_pendiente, estado) VALUES('$this->nombre','$this->rol','$pedidosPendientes','$this->estado')");
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, nombre, rol, pedidos_pendiente, estado FROM usuarios");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Usuario');
    }

    public static function obtenerUsuario($usuario)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, nombre, rol, pedidos_pendiente, estado FROM usuarios WHERE nombre = :nombre");
        $consulta->bindValue(':nombre', $usuario, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Usuario');
    }

    public static function obtenerUsuarioPorId($id_usuario)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, nombre, rol, pedidos_pendiente, estado FROM usuarios WHERE id = :id");
        $consulta->bindValue(':id', $id_usuario, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Usuario');
    }

    public function modificarUsuario()
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE usuarios SET nombre = :nombre, rol = :rol, estado = :estado, pedidos_pendiente = :pedidos_pendiente WHERE id = :id");
        $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':rol', $this->rol, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        if(is_array($this->pedidos_pendiente)){
            $consulta->bindValue(':pedidos_pendiente', count($this->pedidos_pendiente), PDO::PARAM_STR);
        }else{
            $consulta->bindValue(':pedidos_pendiente', $this->pedidos_pendiente, PDO::PARAM_STR);    
        }
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function validarUsuario($rol) {
        $rol = strtolower($rol);
        if($rol == 'bartender' || $rol == 'cervecero' || $rol == 'cocinero' || $rol == 'mozo' || $rol == 'socio')
        {
            return $rol;
        }
        return null;
    }
}