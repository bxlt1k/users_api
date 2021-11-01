<?php

require_once 'vendor/autoload.php';

use Firebase\JWT\JWT;
use Conn\Database;
use App\Users;
use App\Activate;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\HttpFoundation\Request;

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Method: GET, POST, DELETE, PUT, PATCH, OPTIONS, UPDATE');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');

try {
    $routeUsers = new Route('/users');
    $routeUsersId = new Route('/users/{id}', [], ['id' => '\\d+']);
    $routeUsersActivation = new Route("/users/activation");
    $routeSignUp = new Route('/signup');
    $routeSignIn = new Route('/signin');
    $routeAuth = new Route('/auth');

    $routes = new RouteCollection();
    $routes->add('getUsers', $routeUsers);
    $routes->add('getUser', $routeUsersId);
    $routes->add('userActivation', $routeUsersActivation);
    $routes->add('signup', $routeSignUp);
    $routes->add('signin', $routeSignIn);
    $routes->add('auth', $routeAuth);


    $context = new RequestContext();
    $context->fromRequest(Request::createFromGlobals());

    $matcher = new UrlMatcher($routes, $context);
    $parameters = $matcher->match($context->getPathInfo());
} catch (Exception $e) {
    Users::jsonResponse('The request is incorrect', 404);
    return;
}

$db = Database::connection();

if ($parameters['_route'] === 'signup') {
    require_once 'objects/signup.php';
    return;
}

if ($parameters['_route'] === 'signin') {
    require_once 'objects/signin.php';
    return;
}

if ($parameters['_route'] === 'auth') {
    $decoded = JWT::decode($_POST['token'], $_ENV['KEY'], array('HS256'));
    Users::getUser($db, $decoded->data->id);
    return;
}

if ($parameters['_route'] === 'userActivation') {
    $token = $_GET['token'];
    if (!$token) {
        Users::jsonResponse('The request is incorrect', 404);
    }
    Activate::confirmEmail($token);
    return;
}

$data = file_get_contents('php://input');
$data = json_decode($data, true);

switch ($context->getMethod()) {
    case 'GET':
        if (!$parameters['id']) {
            Users::getUsers($db, $_GET);
        } else {
            Users::getUser($db, $parameters['id']);
        }
        break;
    case 'POST':
        if (!$parameters['id']) {
            Users::addUser($db, $data);
        } else {
            http_response_code(404);
        }
        break;
    case 'DELETE':
        if ($parameters['id']) {
            Users::deleteUser($db, $parameters['id']);
        } else {
            http_response_code(404);
        }
        break;
    case 'PUT':
        if ($parameters['id']) {
            $data['id'] = $parameters['id'];
            Users::updateUser($db, $data);
        } else {
            http_response_code(404);
        }
        break;
}