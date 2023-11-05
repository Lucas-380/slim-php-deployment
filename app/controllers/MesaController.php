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
        $mesaAux = $args['codigoMesa'] ?? null;

        $mesa = Mesa::obtenerMesa($mesaAux);
        $payload = json_encode($mesa);

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerMesas($request, $response, $args)
    {
        $lista = Mesa::obtenerTodos();
        $payload = json_encode(array("listaProductos" => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }


}