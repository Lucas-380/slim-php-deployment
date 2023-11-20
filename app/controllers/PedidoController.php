<?php
require_once './models/Pedido.php';
require_once './controllers/UsuarioController.php';
require_once './interfaces/IApiUsable.php';
class PedidoController extends Pedido implements IApiUsable
{
  public function CargarUno($request, $response, $args)
  {
    $parametros = $request->getParsedBody();
    $cliente = $parametros['cliente'];
    $productos = $parametros['productos'];
    $idMesa = $parametros['idMesa'];
    $idMozo = $request->getAttribute('idUsuario');

    $uploadedFiles = $request->getUploadedFiles();

    $mozo = UsuarioController::BuscarMozo($idMozo);
    $mesa = MesaController::BuscarMesa($idMesa);
    $ped = new Pedido();
    $ped->idPedido = $mozo->generarCodigoUnico();
    $ped->estado = 'pendiente';
    $ped->cliente = $cliente;
    $ped->idMesa = $idMesa;
    $ped->idMozo = $mozo->id;
    $ped->disponible = true;
    
    if(PedidoController::guardarImagen($uploadedFiles, $ped)) {
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
    }else{
      $payload = json_encode(array("mensaje" => "Error al guardar la imagen"));
    }
    
    $response->getBody()->write($payload);
    return $response;
  }

  public static function guardarImagen($uploadedFiles, $pedido) {
    if(isset($uploadedFiles['imagen']) && $uploadedFiles['imagen']->getError() === UPLOAD_ERR_OK) {
      $carpetaImg = './ImagenesMesas/';
      $nombreImg = $pedido->idMesa."_".$pedido->idPedido;
      $ruta = $carpetaImg . $nombreImg . ".jpg";

      if (!is_dir($carpetaImg)) {
          mkdir($carpetaImg, 0777, true);
      }

      /** @var UploadedFile $imagen */
      $imagen = $uploadedFiles['imagen'];
      try {
          $imagen->moveTo($ruta);

          $imagenGuardada = [
              'name' => $nombreImg,
              'full_path' => $ruta,
          ];
          $retorno = $imagenGuardada;
      } catch (Exception $e) {
        $retorno = false;
      }
    } else {
      $retorno = false;
    }
    return $retorno;
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

  public function Modificar($request, $response, $args)
  {
    $parametros = $request->getParsedBody();
    $id = $parametros['id'];
    $pedido = Pedido::obtenerPedido($id);

    if($pedido){
      $atributosModificables = ['idMesa', 'idMozo', 'cliente', 'estado', 'fechaDeCreacion', 'tiempoDePreparacion', 'productos', 'precio', 'disponible'];

      foreach ($atributosModificables as $atributoModificado) {
          if(isset($parametros[$atributoModificado])){
            if($atributoModificado === 'productos') {
              if($pedido->estado == 'pendiente'){
                foreach (json_decode($pedido->productos) as $producto) {
                  UsuarioController::TerminarProductoDePedido($producto);
                }

                $arrayProductos = explode(",", $parametros['productos']);
                $arrayProductos = array_map('trim', $arrayProductos);
                $productosAux = array();

                foreach($arrayProductos as $id) {
                  array_push($productosAux, (int)$id);
                  UsuarioController::asignarProductoAEmpleado(Producto::obtenerProductoPorId($id));
                }
                  
                $pedido->productos = json_decode($pedido->productos);
                $pedido->productos = $productosAux;
                $pedido->productos = json_encode($pedido->productos);
              }else{
                $payload = json_encode(array("mensaje" => "Error: No se puede agregar productos porque no esta pendiente"));
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json');
              }
            }else{
              $pedido->{$atributoModificado} = $parametros[$atributoModificado];
            }
          }
      }
      
      $pedido->modificarPedido();
      
      $payload = json_encode(array("mensaje" => "Pedido modificado correctamente"));
    } else {
        $payload = json_encode(array("mensaje" => "Error al modificar Pedido"));
    }

    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
  }

  public function Eliminar($request, $response, $args)
  {
    $parametros = $request->getParsedBody();
    $id = $parametros['id'];
    $pedido = Pedido::obtenerPedido($id);

    if($pedido && Pedido::borrarPedido($pedido->idPedido)){
      $payload = json_encode(array("mensaje" => "Pedido eliminado con exito"));
    }else{
      $payload = json_encode(array("mensaje" => "Error en eliminar Pedido"));
    }

    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
  }  



  public function PrepararPedido($request, $response, $args)
  {
    $parametros = $request->getParsedBody();
    $idPedido = $parametros['idPedido'];
    $pedido = Pedido::obtenerPedido($idPedido);

    $pedido->estado = "En preparacion";
    $pedido->modificarPedido();

    //Comienzan a preparar los pedidos

    for ($i=$pedido->tiempoDePreparacion; $i>0; $i--) {  
      //sleep(60);
      $pedido->tiempoDePreparacion = $pedido->tiempoDePreparacion - 1;
      $pedido->modificarPedido();
    }

    //Termina el tiempo
    foreach (json_decode($pedido->productos) as $producto) {
      UsuarioController::TerminarProductoDePedido($producto);
    }

    // //Modifico estedo de pedido
    $pedido->estado = "listo para servir";
    $pedido->modificarPedido();

    $payload = json_encode(array("mensaje" => "Pedido preparado"));
    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }



  public function ServirPedido($request, $response, $args) {
    //Obtengo el id del mozo desde el token que hace la peticion
    $idUsuario = $request->getAttribute('idUsuario');
    $parametros = $request->getParsedBody();

    $idPedido = $parametros['idPedido'];
    $pedido = Pedido::obtenerPedido($idPedido);

    //verifico que el pedido tenga asignado el id del mozo que intenta servir el pedido
    $mozo = Usuario::obtenerUsuarioPorId($idUsuario);

    if($pedido->idMozo === $mozo->id){
      $pedidos = json_decode($mozo->pedidos_pendiente);
      foreach ($pedidos as $idPedido) {
        if($pedido->idPedido == $idPedido){
          $pedidos = array_filter($pedidos, function ($valor) use ($idPedido) {
            return $valor !== $idPedido;
          });
        }

        $mozo->estado = 'disponible';
        $mozo->pedidos_pendiente = $pedidos;
        $mozo->modificarUsuario();
      }

      $pedido->estado = "servido";
      $pedido->modificarPedido();

      $mesa = MesaController::BuscarMesa($pedido->idMesa);
      MesaController::actualizarEstado($mesa, "Con cliente comiendo");
      UsuarioController::asignarSocio($pedido);

      $payload = json_encode(array("mensaje" => "Pedido servido correctamente"));
    }else{
      $payload = json_encode(array("error" => "No tiene asignado el pedido que quiere servir"));
    }

    $response->getBody()->write($payload);
    return $response;
  }



  public function CobrarPedido($request, $response, $args) {
    $idUsuario = $request->getAttribute('idUsuario');
    $parametros = $request->getParsedBody();
    $idPedido = $parametros['idPedido'];
    $pedido = Pedido::obtenerPedido($idPedido);
    $socio = Usuario::obtenerUsuarioPorId($idUsuario);

    $mesa = MesaController::BuscarMesa($pedido->idMesa);
    MesaController::actualizarEstado($mesa, "Con cliente pagando");

    $pedidos = json_decode($socio->pedidos_pendiente);
    foreach ($pedidos as $idPedido) {
      if($pedido->idPedido == $idPedido){
        $pedidos = array_filter($pedidos, function ($valor) use ($idPedido) {
          return $valor !== $idPedido;
        });
        $socio->estado = 'disponible';
        $socio->pedidos_pendiente = $pedidos;
        $socio->modificarUsuario();
      }
    }

    $pedido->estado = "cobrado";
    $pedido->modificarPedido();

    $payload = json_encode(array("mensaje" => "Pedido cobrado correctamente"));
    $response->getBody()->write($payload);
    return $response;
  }

}