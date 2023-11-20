<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Slim\Psr7\Response;

class ValidarPreparacionDePedido
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {   
        $idUsuario = $request->getAttribute('idUsuario');
        $parametros = $request->getParsedBody();
        $idPedido = $parametros['idPedido'];
        $pedido = PedidoController::obtenerPedido($idPedido);

        if ($pedido && $pedido->estado == 'pendiente') {
            if($pedido->idMozo == $idUsuario){
                $response = $handler->handle($request);
            }else{
                $response = new Response();
                $payload = json_encode(array("mensaje" => "Permiso denegado - Este pedido corresponde a otro mozo"));
                $response->getBody()->write($payload);
            }
        } else {
            $response = new Response();
            $payload = json_encode(array("mensaje" => "Permiso denegado - El pedido ya esta en preparacion o no existe"));
            $response->getBody()->write($payload);
        }
        return $response->withHeader('Content-Type', 'application/json');
    }
}