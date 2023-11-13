<?php

class Pedido{
    public $idPedido; //id del pedido
    public $idMesa;
    public $idMozo; //mozo encargado
    public $cliente;
    public $estado;
    public $fechaCreacion;
    public $tiempoDePreparacion;
    public $productos;
    public $precio;

    public function crearPedido()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        
        $idsProductos =  array();
        foreach ($this->productos as $producto) {
            array_push($idsProductos, $producto->id);
        }

        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO pedidos (idPedido, idMesa, idMozo, cliente, estado, fechaCreacion, tiempoDePreparacion, productos, precio) VALUES (:idPedido, :idMesa, :idMozo, :cliente, :estado, :fechaCreacion, :tiempoDePreparacion, :productos, :precio)");
        $consulta->bindValue(':idPedido', $this->idPedido, PDO::PARAM_INT);
        $consulta->bindValue(':idMesa', $this->idMesa, PDO::PARAM_INT);
        $consulta->bindValue(':idMozo', $this->idMozo, PDO::PARAM_INT);
        $consulta->bindValue(':cliente', $this->cliente, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $consulta->bindValue(':fechaCreacion', $this->fechaCreacion, PDO::PARAM_STR);
        $consulta->bindValue(':tiempoDePreparacion', $this->tiempoDePreparacion, PDO::PARAM_STR);
        $consulta->bindValue(':productos', json_encode($idsProductos), PDO::PARAM_STR);
        $consulta->bindValue(':precio', $this->precio, PDO::PARAM_STR);

        $consulta->execute();

    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT idPedido, idMesa, idMozo, cliente, estado, fechaCreacion, tiempoDePreparacion, productos, precio FROM pedidos");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
    }

    public static function obtenerPedido($codigoPedido)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT idPedido, idMesa, idMozo, cliente, estado, fechaCreacion, tiempoDePreparacion, productos, precio FROM pedidos WHERE idPedido = :idPedido");
        $consulta->bindValue(':idPedido', $codigoPedido, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchObject('Pedido');
    }

    public static function ArmarPedido($pedido) {
        $ped = new Pedido();
        $ped->idPedido = $pedido->idPedido;
        $ped->estado = $pedido->estado;
        $ped->cliente = $pedido->cliente;
        $ped->idMesa = $pedido->idMesa;
        $ped->idMozo = $pedido->idMozo;
        $ped->productos = array();

        $ArrayProd = json_decode($pedido->productos);

        foreach ($ArrayProd as $producto => $id) {
            array_push($ped->productos, Producto::obtenerProductoPorId($id));
        }

        $ped->fechaCreacion = $pedido->fechaCreacion;
        $ped->tiempoDePreparacion = $pedido->tiempoDePreparacion;
        return $ped;
    }

    public static function CalcularTiempoEstimado($productosPedidos){
        $tiempoEstimado = 0;

        for ($i=0; $i < count($productosPedidos); $i++) {
            $tiempo = (Producto::obtenerProductoPorId($productosPedidos[$i]->id))->tiempoDePreparacion;
            if($tiempo > $tiempoEstimado){
                $tiempoEstimado = $tiempo;
            }
        }
        return $tiempoEstimado;
    }


    public function modificarPedido()
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE pedidos SET idMesa = :idMesa, idMozo = :idMozo, cliente = :cliente, estado = :estado, fechaCreacion = :fechaCreacion, tiempoDePreparacion = :tiempoDePreparacion, productos = :productos, precio = :precio WHERE idPedido = :idPedido");
        $consulta->bindValue(':idPedido', $this->idPedido, PDO::PARAM_STR);
        $consulta->bindValue(':idMesa', $this->idMesa, PDO::PARAM_INT);
        $consulta->bindValue(':idMozo', $this->idMozo, PDO::PARAM_INT);
        $consulta->bindValue(':cliente', $this->cliente, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $consulta->bindValue(':fechaCreacion', $this->fechaCreacion, PDO::PARAM_STR);
        $consulta->bindValue(':tiempoDePreparacion', $this->tiempoDePreparacion, PDO::PARAM_STR);
        $consulta->bindValue(':productos', $this->productos, PDO::PARAM_STR);
        $consulta->bindValue(':precio', $this->precio, PDO::PARAM_STR);
        $consulta->execute();
    }
}