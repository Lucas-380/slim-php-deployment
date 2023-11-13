<?php
require_once './models/Mesa.php';
require_once './controllers/PedidoController.php';

class MesaController extends Mesa
{
    public function CargarMesa($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $mesas = $parametros['cargarMesa'];
        
        if($mesas > 0) {
          for ($i = 0; $i < $mesas; $i++) {
            $mesa = new Mesa();
            $mesa->estado = 'cerrado';
            $mesa->crearProducto();
            $payload = json_encode(array("mensaje" => "mesa creada con exito"));
            $response->getBody()->write($payload);
          }
        }
        
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerUnaMesa($request, $response, $args)
    {
        // Buscamos usuario por nombre del producto
        $mesaAux = $args['idMesa'] ?? null;

        $mesa = Mesa::obtenerMesa($mesaAux);
        $payload = json_encode($mesa);

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerMesas($request, $response, $args)
    {
        $lista = Mesa::obtenerTodos();
        $payload = json_encode(array("ListaMesas" => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public static function BuscarMesa($id_mesa) {
      $mesa = Mesa::obtenerMesa($id_mesa);
      if($mesa != false) {
        return $mesa;
      }else{
        return null;
      }
    }

    public static function actualizarEstado($mesa, $estado) {
      $mesa->estado = $estado;
      $mesa->modificarUsuario();
    }

}