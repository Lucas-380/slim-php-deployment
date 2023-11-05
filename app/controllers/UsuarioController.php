<?php
require_once './models/Usuario.php';
require_once './interfaces/IApiUsable.php';

class UsuarioController extends Usuario implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $nombre = $parametros['nombre'];
        $rol = Usuario::validarUsuario($parametros['rol']);
        // Creamos el usuario
        if($rol != null)
        {
          $usr = new Usuario();
          $usr->nombre = $nombre;
          $usr->rol = $rol;
          $usr->pedidos_pendiente = 0;
          $usr->estado = 'disponible';
          $usr->crearUsuario();
          $payload = json_encode(array("mensaje" => "Usuario creado con exito"));
        }else{
          $payload = json_encode(array("mensaje" => "El usuario no se pudo crear - compruebe el rol"));  
        }

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        // Buscamos usuario por nombre
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
        return $mozo;
      }else{
        return null;
      }
    }

    public static function actualizarEstado($mozo) {
      $mozo->estado = "tomando pedidos";
      $mozo->modificarUsuario();
    }

    public static function actualizarListadoDePendientes($mozo, $pedido) {
      if(is_array($mozo->pedidos_pendiente)) {
        array_push($mozo->pedidos_pendiente, $pedido);
      }else{
        $mozo->pedidos_pendiente++;
      }
      $mozo->modificarUsuario();
    }
}