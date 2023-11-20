<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Slim\Psr7\Response;

class AuthMiddleware
{
    private $sector;
    
    public function __construct($sector) {
        $this->sector = $sector;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {   
        try {
            $header = $request->getHeaderLine('Authorization');
            if($header == ""){
                throw new Exception();
            }
            $token = trim(explode("Bearer", $header)[1]);


            if(AutentificadorJWT::VerificarSector($token, $this->sector)){
                $idUsuario = AutentificadorJWT::ObtenerData($token)->id;
                $request = $request->withAttribute('idUsuario', $idUsuario);
                $response = $handler->handle($request);
            }else{
                $response = new Response();
                $payload = json_encode(array('mensaje' => 'ERROR: Solo disponible para los usuarios del sector: '. $this->sector));
                $response->getBody()->write($payload);
            }
        } catch (Exception $e) {
            $response = new Response();
            $payload = json_encode(array('mensaje' => 'ERROR: Hubo un error con el TOKEN'));
            $response->getBody()->write($payload);
        }
        

        return $response->withHeader('Content-Type', 'application/json');
    }
}