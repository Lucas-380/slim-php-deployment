<?php
require_once './interfaces/IApiUsable.php';
require_once './models/Producto.php';

class ProductoController extends Producto implements IApiUsable
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
        $prd->disponible = true;
        $prd->crearProducto();

        $payload = json_encode(array("mensaje" => "Producto creado con exito"));

        $response->getBody()->write($payload);
        return $response;
    }

    public function TraerUno($request, $response, $args)
    {
        $prd = $args['producto'] ?? null;

        $producto = Producto::obtenerProducto($prd);
        $producto->disponible = (bool)$producto->disponible;

        $payload = json_encode($producto);

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Producto::obtenerTodos();

        foreach ($lista as $prod) {
            $prod->disponible = (bool)$prod->disponible;
        }

        $payload = json_encode(array("listaProductos" => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function Modificar($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $id = $parametros['id'];
        $producto = Producto::obtenerProductoPorId($id);
    
        if($producto){
          $atributosModificables = ['nombre', 'sector', 'tiempoDePreparacion', 'precio', 'disponible'];
  
          foreach ($atributosModificables as $atributoModificado) {
              if(isset($parametros[$atributoModificado])){
                  $producto->{$atributoModificado} = $parametros[$atributoModificado];
              }
          }
  
          $producto->modificarProducto();
          $payload = json_encode(array("mensaje" => "Producto modificado correctamente"));
        } else {
            $payload = json_encode(array("mensaje" => "Error al modificar producto"));
        }
    
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function Eliminar($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $id = $parametros['id'];
        $producto = Producto::obtenerProductoPorId($id);
  
        if($producto && Producto::borrarProducto($producto->id)){
          $payload = json_encode(array("mensaje" => "Producto eliminado con exito"));
        }else{
          $payload = json_encode(array("mensaje" => "Error en eliminar Producto"));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }



    //--------------------------------------------------------------CSV

    public static function GuardarProductosCSV($request, $response, $args){
        $path = "productos.csv";
        $param = $request->getQueryParams();
        $productosArray = array();
        $productos = Producto::obtenerTodos();

        foreach($productos as $prod){
            $producto = array($prod->id, $prod->nombre, $prod->sector, $prod->tiempoDePreparacion, $prod->precio, $prod->disponible);
            $productosArray[] = $producto;
        }
  
        $archivo = fopen($path, "w");
        $encabezado = array("id", "nombre", "sector", "tiempoDePreparacion", "precio", "disponible");
        fputcsv($archivo, $encabezado);
        foreach($productosArray as $fila){
            fputcsv($archivo, $fila);
        }
        fclose($archivo);
        $retorno = json_encode(array("mensaje"=>"Productos guardados correctamente"));
           
        $response->getBody()->write($retorno);
        return $response;
    }
  
    public static function CargarProductosCSV($request, $response, $args){
        $path = "productos.csv";
        $archivo = fopen($path, "r");
        $encabezado = fgets($archivo);
    
        while(($linea = fgets($archivo)) !== false){
            // Verificar si la línea está vacía antes de procesarla
            if(trim($linea) === '') {
                continue; // Saltar la línea vacía y pasar a la siguiente iteración
            }
    
            $datos = str_getcsv($linea);
    
            $producto = new Producto();
            $producto->id = $datos[0];
            $producto->nombre = $datos[1];
            $producto->sector = $datos[2];
            $producto->tiempoDePreparacion = $datos[3];
            $producto->precio = $datos[4];
            $producto->disponible = (int)$datos[5];
            if(Producto::obtenerProductoPorId($producto->id) == false){
                $producto->crearProducto();
            }
        }
        fclose($archivo);
                
        $retorno = json_encode(array("mensaje"=>"Productos cargados Correctamente"));
        $response->getBody()->write($retorno);
        return $response;
    }
}