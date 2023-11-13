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

require_once './middlewares/AuthMiddleware.php';
require_once './middlewares/ValidarSectorMW.php';
require_once './middlewares/ValidarPedidoMW.php';

// Instantiate App
$app = AppFactory::create();

// Set base path
$app->setBasePath('/slim-php-deployment/app');

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Add parse body
$app->addBodyParsingMiddleware();

// Routes
$app->group('/usuarios', function (RouteCollectorProxy $group) {
    $group->get('[/]', \UsuarioController::class . ':TraerTodos');
    $group->post('[/]', \UsuarioController::class . ':CargarUno')
        ->add(new ValidarSectorMW());;
    $group->get('/{nombre}', \UsuarioController::class . ':TraerUno');
})->add(new AuthMiddleware('socio'));

$app->group('/productos', function (RouteCollectorProxy $group) {
    $group->get("[/]", \ProductoController::class . ':TraerTodos');
    $group->post('[/]', \ProductoController::class . ':CargarUno')
        ->add(new AuthMiddleware('socio'))
        ->add(new ValidarSectorMW());
    $group->get('/{producto}', \ProductoController::class . ':TraerUno');
});

$app->group('/mesas', function (RouteCollectorProxy $group) {
    $group->get('[/]', \MesaController::class . ':TraerMesas');
    $group->post('[/]', \MesaController::class . ':CargarMesa')->add(new AuthMiddleware('socio'));
    $group->get('/{idMesa}', \MesaController::class . ':TraerUnaMesa');
});

$app->group('/pedidos', function (RouteCollectorProxy $group) {
    $group->get('[/]', \PedidoController::class . ':TraerTodos');
    $group->get('/{codigoPedido}', \PedidoController::class . ':TraerUno');
    $group->post('[/]', \PedidoController::class . ':CargarPedido')
        ->add(new AuthMiddleware('mozo'))
        ->add(new ValidarPedidoMW());

    $group->post('/{id_mozo}/prepararPedido', \PedidoController::class . ':PrepararPedido');
});

$app->run();