<?php
// Error Handling
error_reporting(-1);
ini_set('display_errors', 1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteContext;

require __DIR__ . '/../vendor/autoload.php';

require_once './db/AccesoDatos.php';

require_once './controllers/UsuarioController.php';
require_once './controllers/ProductoController.php';
require_once './controllers/MesaController.php';
require_once './controllers/PedidoController.php';

// Instantiate App
$app = AppFactory::create();

// Set base path
$app->setBasePath('/slim-php-deployment/app');

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Add parse body
$app->addBodyParsingMiddleware();

// Routes
$app->post('[/]', \UsuarioController::class . ':CargarUno');
$app->get('/usuarios', \UsuarioController::class . ':TraerTodos');
$app->get('/usuarios/{nombre}', \UsuarioController::class . ':TraerUno');

$app->get('/productos', \ProductoController::class . ':TraerTodos');
$app->post('/productos/nuevo', \ProductoController::class . ':CargarUno');
$app->get('/productos/{producto}', \ProductoController::class . ':TraerUno');

$app->get('/mesas', \MesaController::class . ':TraerMesas');
$app->post('/mesas/nuevo', \MesaController::class . ':CargarMesa');
$app->get('/mesas/{codigoMesa}', \MesaController::class . ':TraerUnaMesa');


$app->post('/pedidos/{id_mozo}', \PedidoController::class . ':CargarPedido');
$app->get('/pedidos', \PedidoController::class . ':TraerTodos');

$app->run();
