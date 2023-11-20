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
require_once './controllers/LoginController.php';

require_once './middlewares/AuthMiddleware.php';
require_once './middlewares/ValidarProductoMW.php';
require_once './middlewares/ValidarPedidoMW.php';
require_once './middlewares/ValidarPreparacionDePedido.php';

require_once './utils/AutentificadorJWT.php';

// Instantiate App
$app = AppFactory::create();

// Set base path
$app->setBasePath('/slim-php-deployment/app');

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Add parse body
$app->addBodyParsingMiddleware();

// Routes

// JWT en login
$app->group('/auth', function (RouteCollectorProxy $group){
    $group->post('[/]', \LoginController::class . ':Login');
});
  
$app->post('[/]', \MesaController::class . ':VerTiempoRestante');

$app->group('/usuarios', function (RouteCollectorProxy $group) {
    $group->get('[/]', \UsuarioController::class . ':TraerTodos');
    $group->get('/{nombre}', \UsuarioController::class . ':TraerUno');
    $group->post('[/]', \UsuarioController::class . ':CargarUno');
    $group->put('/modificar', \UsuarioController::class . ':Modificar');
    $group->delete('/baja', \UsuarioController::class . ':Eliminar');
})->add(new AuthMiddleware('socio'));


$app->group('/productos', function (RouteCollectorProxy $group) {
    $group->get("[/]", \ProductoController::class . ':TraerTodos');
    $group->post('[/]', \ProductoController::class . ':CargarUno')
        ->add(new ValidarProductoMW())
        ->add(new AuthMiddleware('socio'));
    $group->put('/modificar', \ProductoController::class . ':Modificar')
        ->add(new ValidarProductoMW())
        ->add(new AuthMiddleware('socio'));
    $group->delete('/baja', \ProductoController::class . ':Eliminar')
        ->add(new AuthMiddleware('socio'));

    $group->get('/guardar', \ProductoController::class . ':GuardarProductosCSV');
    $group->get('/cargar', \ProductoController::class . ':CargarProductosCSV');
});

$app->group('/mesas', function (RouteCollectorProxy $group) {
    $group->get('[/]', \MesaController::class . ':TraerTodos');
    $group->get('/{idMesa}', \MesaController::class . ':TraerUno');
    $group->post('[/]', \MesaController::class . ':CargarUno')->add(new AuthMiddleware('socio'));
    $group->put('/modificar', \MesaController::class . ':Modificar')->add(new AuthMiddleware('socio'));
    $group->delete('/baja', \MesaController::class . ':Eliminar')->add(new AuthMiddleware('socio'));

    $group->put('/cerrar', \MesaController::class . ':Cerrar')->add(new AuthMiddleware('socio'));
});

$app->group('/pedidos', function (RouteCollectorProxy $group) {
    $group->get('[/]', \PedidoController::class . ':TraerTodos')->add(new AuthMiddleware('socio'));
    $group->get('/{codigoPedido}', \PedidoController::class . ':TraerUno')->add(new AuthMiddleware('socio'));
    $group->post('[/]', \PedidoController::class . ':CargarUno')
        ->add(new ValidarPedidoMW())
        ->add(new AuthMiddleware('mozo'));
    $group->put('/modificar', \PedidoController::class . ':Modificar')->add(new AuthMiddleware('mozo'));
    $group->delete('/baja', \PedidoController::class . ':Eliminar')->add(new AuthMiddleware('socio'));

    $group->post('/prepararPedido', \PedidoController::class . ':PrepararPedido')
        ->add(new ValidarPreparacionDePedido())
        ->add(new AuthMiddleware('mozo'));
    $group->post('/servirPedido', \PedidoController::class . ':ServirPedido')
        ->add(new AuthMiddleware('mozo'));
    $group->post('/cobrarPedido', \PedidoController::class . ':CobrarPedido')
    ->add(new AuthMiddleware('socio'));
});

//LISTADO DE PRODUCTOS PENDIENTES SEGUN SU SECTOR
$app->group('/choperas', function (RouteCollectorProxy $group) {
    $group->get('/listaDePendientes', \UsuarioController::class . ':listarPendientes');
})->add(new AuthMiddleware('choperas'));

$app->group('/cocina', function (RouteCollectorProxy $group) {
    $group->get('/listaDePendientes', \UsuarioController::class . ':listarPendientes');
})->add(new AuthMiddleware('cocina'));

$app->group('/tragos', function (RouteCollectorProxy $group) {
    $group->get('/listaDePendientes', \UsuarioController::class . ':listarPendientes');
})->add(new AuthMiddleware('tragos'));

$app->group('/candybar', function (RouteCollectorProxy $group) {
    $group->get('/listaDePendientes', \UsuarioController::class . ':listarPendientes');
})->add(new AuthMiddleware('candy bar'));


$app->run();