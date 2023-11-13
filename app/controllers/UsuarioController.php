<?php
require_once './models/Usuario.php';
require_once './interfaces/IApiUsable.php';
require_once './models/producto.php';

class UsuarioController extends Usuario implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $nombre = $parametros['nombre'];
        $sector = $parametros['sector'];

        if($sector != null && $nombre != null)
        {
          $usr = new Usuario();
          $usr->nombre = $nombre;
          $usr->sector = $sector;
          
          $usr->pedidos_pendiente = array();


          $usr->estado = 'disponible';
          $usr->crearUsuario();
          $payload = json_encode(array("mensaje" => "Usuario creado con exito"));
        }

        $response->getBody()->write($payload);
        return $response;
    }

    public function TraerUno($request, $response, $args)
    {
        $usr = $args['nombre'] ?? null;
        $usuario = Usuario::obtenerUsuario($usr);
        $payload = json_encode($usuario);
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Usuario::obtenerTodos();
        $payload = json_encode(array("listaUsuario" => $lista));
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    
    public static function BuscarMozo($id_mozo) {
      $mozo = Usuario::obtenerUsuarioPorId($id_mozo);
      if($mozo != false) {
        $mozo->pedidos_pendiente = json_decode($mozo->pedidos_pendiente, true);
        return $mozo;
      }else{
        return null;
      }
    }


    public static function actualizarMozo($mozo, $estado, $pedido) {
      array_push($mozo->pedidos_pendiente, $pedido->idPedido);
      $mozo->estado = $estado;
      $mozo->modificarUsuario();
    }

    public static function asignarProductoAEmpleado($productoPedido) {
      switch ($productoPedido->sector) {
        case 'cocina':
          $empleado = Usuario::BuscarEmpleadoDisponible('cocina');
          break;
        case 'choperas':
          $empleado = Usuario::BuscarEmpleadoDisponible('choperas');
          break;
        case 'tragos':
          $empleado = Usuario::BuscarEmpleadoDisponible('tragos');
          break;
        case 'candy bar':
          $empleado = Usuario::BuscarEmpleadoDisponible('candy bar');
          break;
      }

      $empleado->pedidos_pendiente = json_decode($empleado->pedidos_pendiente);
      array_push($empleado->pedidos_pendiente, $productoPedido->id);
      $empleado->modificarUsuario();
    }

    public static function TerminarProductoDePedido($idProducto) {
      $producto = Producto::obtenerProductoPorId($idProducto);

      if(UsuarioController::terminarProducto($producto) == true) {
        return true;
      }else{
        return false;
      }
    }

    private static function terminarProducto($productoPedido) {
      $retorno = false;

      //$empleados = Usuario::obtenerUsuariosPorSector($productoPedido->sector);

      switch ($productoPedido->sector) {
        case 'cocina':
          $empleados = Usuario::obtenerUsuariosPorSector('cocina');
          break;
        case 'choperas':
          $empleados = Usuario::obtenerUsuariosPorSector('choperas');
          break;
        case 'tragos':
          $empleados = Usuario::obtenerUsuariosPorSector('tragos');
          break;
        case 'candy bar':
          $empleados = Usuario::obtenerUsuariosPorSector('candy bar');
          break;
      }

      foreach ($empleados as $empleado) {
        $empleado->pedidos_pendiente = json_decode($empleado->pedidos_pendiente);
        $indiceAEliminar = array_search($productoPedido->id, $empleado->pedidos_pendiente);
        if($indiceAEliminar !== false ) {
          unset($empleado->pedidos_pendiente[$indiceAEliminar]);
          $empleado->pedidos_pendiente = array_values($empleado->pedidos_pendiente);
          
          $empleado->modificarUsuario();
          $retorno = true;
          break;
        }
      }

      return $retorno;
    }


}