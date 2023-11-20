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
        $username = $parametros['username'];
        $contrasenia = $parametros['contrasenia'];

        if($sector != null && $nombre != null)
        {
          $usr = new Usuario();
          $usr->nombre = $nombre;
          $usr->sector = $sector;
          $usr->usuario = $username;
          $usr->contrasenia = $contrasenia;
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

    public function Modificar($request, $response, $args)
    {
      $parametros = $request->getParsedBody();
      $id = $parametros['id'];
      $usuario = Usuario::obtenerUsuarioPorId($id);
  
      if($usuario && $usuario->pedidos_pendiente == []){
        $atributosModificables = ['nombre', 'usuario', 'contrasenia', 'sector', 'fechaDeBaja'];

        $usuario->pedidos_pendiente = json_decode($usuario->pedidos_pendiente);
        $usuario->estado = $usuario->estado;

        foreach ($atributosModificables as $atributoModificado) {
            if(isset($parametros[$atributoModificado])){
                $usuario->{$atributoModificado} = $parametros[$atributoModificado];
            }
        }

        $usuario->modificarUsuario();
        
        $payload = json_encode(array("mensaje" => "Usuario modificado correctamente"));
      } else {
          $payload = json_encode(array("mensaje" => "Error al modificar usuario"));
      }
  
      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
    }

    public function Eliminar($request, $response, $args)
    {
      $parametros = $request->getParsedBody();
      $id = $parametros['id'];
      $usuario = Usuario::obtenerUsuarioPorId($id);

      if($usuario && Usuario::borrarUsuario($usuario->id)){
        $payload = json_encode(array("mensaje" => "Usuario eliminado con exito"));
      }else{
        $payload = json_encode(array("mensaje" => "Error en eliminar usuario"));
      }

      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
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
      //$mozo->estado = $estado;
      $mozo->modificarUsuario();
    }

    public static function asignarProductoAEmpleado($productoPedido) {

      $empleado = Usuario::BuscarEmpleadoDisponible($productoPedido->sector);

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

      $empleados = Usuario::obtenerUsuariosPorSector($productoPedido->sector);

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

    public static function asignarSocio($pedido){
      $socio = Usuario::BuscarEmpleadoDisponible('socio');

      $socio->estado = 'ocupado';
      $socio->pedidos_pendiente = json_decode($socio->pedidos_pendiente);
      array_push($socio->pedidos_pendiente, $pedido->idPedido);

      $socio->modificarUsuario();
    }

    public function listarPendientes($request, $response, $args){
      $idUsuario = $request->getAttribute('idUsuario');
      $empleado = Usuario::obtenerUsuarioPorId($idUsuario);
      $arrayProductos = array();

      $productosPendiente = json_decode($empleado->pedidos_pendiente);
      foreach ($productosPendiente as $producto) {
        array_push($arrayProductos, Producto::obtenerProductoPorId($producto));
      }

      $payload = json_encode($arrayProductos);
      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');

    }
}