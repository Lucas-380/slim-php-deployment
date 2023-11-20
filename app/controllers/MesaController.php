<?php
require_once './interfaces/IApiUsable.php';
require_once './models/Mesa.php';
require_once './controllers/PedidoController.php';

class MesaController extends Mesa implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
      $parametros = $request->getParsedBody();
      $mesas = $parametros['cargarMesa'];
      
      if($mesas > 0) {
        for ($i = 0; $i < $mesas; $i++) {
          $mesa = new Mesa();
          $mesa->estado = 'cerrado';
          $mesa->disponible = true;
          $mesa->crearProducto();
          $payload = json_encode(array("mensaje" => "mesa creada con exito"));
          $response->getBody()->write($payload);
        }
      }
      
      return $response
        ->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
      $mesaAux = $args['idMesa'] ?? null;

      $mesa = Mesa::obtenerMesa($mesaAux);
      $payload = json_encode($mesa);

      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
      $lista = Mesa::obtenerTodos();
      $payload = json_encode(array("ListaMesas" => $lista));

      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }

    public function Modificar($request, $response, $args)
    {
      $parametros = $request->getParsedBody();
      $id = $parametros['id'];
      $mesa = Mesa::obtenerMesa($id);
  
      if($mesa){
        $atributosModificables = ['estado', 'disponible'];
  
        foreach ($atributosModificables as $atributoModificado) {
          if(isset($parametros[$atributoModificado])){
              $mesa->{$atributoModificado} = $parametros[$atributoModificado];
            }
        }
  
        $mesa->modificarMesa();
        
        $payload = json_encode(array("mensaje" => "Mesa modificada correctamente"));
      } else {
        $payload = json_encode(array("mensaje" => "Error al modificar Mesa"));
      }
      
      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
    }

    public function Eliminar($request, $response, $args)
    {
      $parametros = $request->getParsedBody();
      $id = $parametros['id'];
      $mesa = Mesa::obtenerMesa($id);

      if($mesa && Mesa::borrarMesa($mesa->id)){
        $payload = json_encode(array("mensaje" => "Mesa eliminada correctamente"));
      }else{
        $payload = json_encode(array("mensaje" => "Error en eliminar Mesa"));
      }

      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
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
      $mesa->modificarMesa();
    }

    public function VerTiempoRestante($request, $response, $args){
      $parametros = $request->getParsedBody();
      $idMesa = $parametros['idMesa'];
      $idPedido = $parametros['idPedido'];

      $pedido = PedidoController::obtenerPedido($idPedido);

      if($pedido->idMesa == $idMesa){
        $payload = json_encode(array("mensaje" => "Tiempo restante para su pedido: ".($pedido->tiempoDePreparacion)." minutos"));
      }else{
        $payload = json_encode(array("mensaje" => "Los datos ingresados no coinciden"));
      }

      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json'); 
    }

    public function Cerrar($request, $response, $args) {
      $parametros = $request->getParsedBody();
      $idMesa = $parametros['idMesa'];

      $mesa = Mesa::obtenerMesa($idMesa);

      if($mesa->estado == "Con cliente pagando") {
        $mesa->estado = 'cerrado';
        $mesa->modificarMesa();
        $payload = json_encode(array("mensaje" => "Mesa ".($mesa->id)." liberada."));
      }else{
        $payload = json_encode(array("mensaje" => "La mesa esta cerrada o todavia no se puede cerrar"));
      }

      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json'); 
    }
}