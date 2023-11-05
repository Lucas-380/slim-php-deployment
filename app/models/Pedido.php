<?php

class Pedido{
    public $id;
    public $codigoPedido;
    public $estado;
    public $codigoMesa;
    public $cliente;
    public $productos;
    public $fechaCreacion;
    //public $mozoEncargado;

    public function crearPedido()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO pedidos (codigoPedido, estado, codigoMesa, cliente, productos, fechaCreacion) VALUES('$this->codigoPedido','$this->estado','$this->codigoMesa','$this->cliente','$this->productos','$this->fechaCreacion')");
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, codigoPedido, estado, codigoMesa, cliente, productos, fechaCreacion FROM pedidos");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
    }

    public static function obtenerPedido($cliente)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, codigoPedido, estado, codigoMesa, cliente, productos, fechaCreacion FROM pedidos WHERE cliente = :cliente");
        $consulta->bindValue(':cliente', $cliente, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Pedido');
    }

}