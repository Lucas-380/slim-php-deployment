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
        $codigoMesa = $parametros['codigoMesa'];

        $mozo = UsuarioController::BuscarMozo($args['id_mozo']);// ?? null;
        //validar que el mozo existe y este desocupado para asignarle el pedido / si no hay mozo, inicia un tiempo de retraso
        if($mozo != null) {
          UsuarioController::actualizarEstado($mozo, "tomando pedidos");
          // Creamos el pedido
          $ped = new Pedido();
          $ped->estado = 'pendiente';
          $ped->cliente = $cliente;
          $ped->codigoMesa = $codigoMesa;
          $ped->productos = $productos;
          $ped->fechaCreacion = new DateTime('now', new DateTimeZone('America/Argentina/Buenos_Aires'));
          $ped->fechaCreacion = $ped->fechaCreacion->format('Y-m-d H:i:s'); //con esta fecha valido el tiempo estimado de espera por el pedido
          $pedidoGuardado = Pedido::obtenerPedido($cliente);
          
          //Una vez que se cumpla el tiempo estimado de espera se obtiene el pedido
          //El codigo del pedido debe darselo el mozo - Todavia no se me ocurre bien como hacerlo
          if($pedidoGuardado == false || ($ped->cliente != $pedidoGuardado->cliente)){
            $ped->codigoPedido = rand(10000, 99999);
          }else{
            $ped->codigoPedido = $pedidoGuardado->codigoPedido;
          }
        }
        
        $ped->crearPedido();
        UsuarioController::actualizarListadoDePendientes($mozo, $ped);

        $payload = json_encode(array("mensaje" => "Pedido creado con exito - codigo de pedido " . $ped->codigoPedido));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        // Buscamos usuario por nombre del producto
        $pedidoAux = $args['codigoMesa'] ?? null;

        $pedido = Pedido::obtenerPedido($pedidoAux);
        $payload = json_encode($pedido);

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
      $lista = Pedido::obtenerTodos();
      $payload = json_encode(array("listaProductos" => $lista));

      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }
}