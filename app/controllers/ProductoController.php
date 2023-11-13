<?php
require_once './models/Producto.php';

class ProductoController extends Producto
{
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $nombre = $parametros['nombre'];
        $sector = $parametros['sector'];
        $tiempoDePreparacion = $parametros['tiempoDePreparacion'];
        $precio = $parametros['precio'];
        
        // Creamos el producto
        $prd = new Producto();
        $prd->nombre = $nombre;
        $prd->sector = $sector;
        $prd->tiempoDePreparacion = $tiempoDePreparacion;
        $prd->precio = $precio;
        $prd->crearProducto();

        $payload = json_encode(array("mensaje" => "Producto creado con exito"));

        $response->getBody()->write($payload);
        return $response;
    }

    public function TraerUno($request, $response, $args)
    {
        $prd = $args['producto'] ?? null;

        $producto = Producto::obtenerProducto($prd);
        $payload = json_encode($producto);

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Producto::obtenerTodos();
        $payload = json_encode(array("listaProductos" => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
}