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
        
        $mozo = UsuarioController::BuscarMozo($parametros['idMozo']);
        $mesa = MesaController::BuscarMesa($parametros['idMesa']);
        $productos = $parametros['productos'];

        $arrayProductos = explode(",", $productos);
        $arrayProductos = array_map('trim', $arrayProductos);
        
        foreach($arrayProductos as $nombre) {
            if(Producto::obtenerProducto($nombre) != null) {
                if($mesa != null && $mesa->estado == "cerrado" && $mozo != null && $mozo->estado != "ocupado") {
                  $response = $handler->handle($request);
                } else {
                    $response = new Response();
                    $payload = json_encode(array("mensaje" => "La mesa o el mozo no esta disponible"));
                    $response->getBody()->write($payload);
                }
            }else{
                $response = new Response();
                $payload = json_encode(array("mensaje" => "El producto pedido no esta disponible"));
                $response->getBody()->write($payload);
            }
        }
        

        return $response->withHeader('Content-Type', 'application/json');
    }
}