<?php
require_once './models/Pedido.php';
require_once './controllers/UsuarioController.php';

class PedidoController extends Pedido
{
  public function CargarPedido($request, $response, $args)
  {
    $parametros = $request->getParsedBody();
    $cliente = $parametros['cliente'];
    $productos = $parametros['productos'];
    $idMesa = $parametros['idMesa'];
    $idMozo = $parametros['idMozo'];

    $mozo = UsuarioController::BuscarMozo($idMozo);
    $mesa = MesaController::BuscarMesa($idMesa);

    $ped = new Pedido();
    $ped->idPedido = $mozo->generarCodigoUnico();
    $ped->estado = 'pendiente';
    $ped->cliente = $cliente;
    $ped->idMesa = $idMesa;
    $ped->idMozo = $mozo->id;
    
    $arrayProductos = explode(",", $productos);
    $arrayProductos = array_map('trim', $arrayProductos);
    $productosAux = array();
    foreach($arrayProductos as $nombre) {
      $productoPedido = Producto::obtenerProducto($nombre);
        array_push($productosAux, $productoPedido);
        //asignar empleado para el producto
        UsuarioController::asignarProductoAEmpleado($productoPedido);
        $ped->precio += $productoPedido->precio;
    }

    $ped->productos = $productosAux;
    $ped->tiempoDePreparacion = Pedido::CalcularTiempoEstimado($productosAux);
    $ped->fechaCreacion = new DateTime('now', new DateTimeZone('America/Argentina/Buenos_Aires'));
    $ped->fechaCreacion = $ped->fechaCreacion->format('Y-m-d H:i:s');
    $ped->crearPedido();
  
    UsuarioController::actualizarMozo($mozo, "ocupado", $ped);
    MesaController::actualizarEstado($mesa, "con cliente esperando pedido");
    $payload = json_encode(array("mensaje" => "Pedido creado con exito - codigo de pedido " . $ped->idPedido));
    
    $response->getBody()->write($payload);
    return $response;
  }

  public function TraerUno($request, $response, $args)
  {
    $pedidoAux = $args['codigoPedido'] ?? null;
    $pedido = Pedido::obtenerPedido($pedidoAux);
    $payload = json_encode(Pedido::ArmarPedido($pedido));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerTodos($request, $response, $args)
  {
    $lista = Pedido::obtenerTodos();
    $payload = json_encode(array("ListaPedidos" => $lista));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function PrepararPedido($request, $response, $args)
  {
    $parametros = $request->getParsedBody();
    $idPedido = $parametros['idPedido'];
    $pedido = Pedido::obtenerPedido($idPedido);

    //Comienzan a preparar los pedidos
    sleep($pedido->tiempoDePreparacion);
    //Termina el tiempo
    foreach (json_decode($pedido->productos) as $producto) {
      UsuarioController::TerminarProductoDePedido($producto);
    }

    // //Modifico estedo de pedido
    $pedido->estado = "listo para servir";
    $pedido->modificarPedido();

    // hago entrega del pedido / cambio de estado de la mesa / el id del pedido se va del pendiente del mozo y pasa al socio para que dsp lo cobre


    $payload = json_encode(array("mensaje" => "Pedido entregado"));
    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

}