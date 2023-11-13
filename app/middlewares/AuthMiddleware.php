<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Slim\Psr7\Response;

class AuthMiddleware
{
    private $rol;

    public function __construct($rol) {
        $this->rol = $rol;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {   
        $queryParams = $request->getQueryParams();
        $user = $queryParams['user'] ?? null;

        if ($user === $this->rol) {
            $response = $handler->handle($request);
        } else {
            $response = new Response();
            $payload = json_encode(array("mensaje" => "Permiso denegado - Su usuario no tiene este nivel de permisos"));
            $response->getBody()->write($payload);
        }

        return $response->withHeader('Content-Type', 'application/json');
    }
}