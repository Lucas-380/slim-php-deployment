<?php
require_once './models/Usuario.php';
require_once './utils/AutentificadorJWT.php';


class LoginController
{  
    public function Login($request, $response, $args){
        $parametros = $request->getParsedBody();

        $username = $parametros['username'];
        $contrasenia = $parametros['contrasenia'];
        $usuario = Usuario::TraerUsuarioPorSesion($username, $contrasenia);

        if($usuario && $usuario->fechaDeBaja == null){ 
            $datos = array('id' => $usuario->id, 'sector'=> $usuario->sector);
            $token = AutentificadorJWT::CrearToken($datos);
            $payload = json_encode(array('jwt' => $token));
        } else {
            $payload = json_encode(array('error' => 'Usuario o contrasenia incorrectos'));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}