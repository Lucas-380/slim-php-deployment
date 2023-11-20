<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Slim\Psr7\Response;

class ValidarPedidoMW
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {   
        $parametros = $request->getParsedBody();
        $idUsuario = $request->getAttribute('idUsuario');
        
        $mozo = UsuarioController::BuscarMozo($idUsuario);
        $mesa = MesaController::BuscarMesa($parametros['idMesa']);
        $productos = $parametros['productos'];

        $arrayProductos = explode(",", $productos);
        $arrayProductos = array_map('trim', $arrayProductos);
        
        $valido = false;

        foreach($arrayProductos as $nombre) {
            if(Producto::obtenerProducto($nombre)) {
                if($mesa != null && $mesa->disponible != 0 && $mesa->estado == "cerrado" && $mozo != null && $mozo->fechaDeBaja == null && $mozo->estado != "ocupado") {
                    $valido = true;
                } else {
                    $response = new Response();
                    $payload = json_encode(array("mensaje" => "La mesa o el mozo no esta disponible"));
                    $response->getBody()->write($payload);
                }
            }else{
                $response = new Response();
                $payload = json_encode(array("mensaje" => "El producto pedido no esta disponible"));
                $response->getBody()->write($payload);
                break;
            }
        }
        if($valido == true){
            $response = $handler->handle($request);
        }
        
        return $response->withHeader('Content-Type', 'application/json');
    }
}