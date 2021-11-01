<?php
require_once './vendor/autoload.php';

use Firebase\JWT\JWT;

use Conn\Database;
$db = Database::connection();

$data = [
    'email' => $_POST['email'],
    'pass' => $_POST['pass']
];

$errors = [];

if (trim($_POST['email']) == '') {
    $errors[] = 'email';
}
if (trim($_POST['pass']) == '') {
    $errors[] = 'pass';
}

if (!empty($errors)) {
    $response = [
        'status' => false,
        'type' => 1,
        'message' => 'Проверьте правильность полей',
        'fields' => $errors
    ];
    echo json_encode($response);
    die();
}

$data['pass'] = md5($data['pass']);

$stmt = $db->prepare("SELECT * FROM `users` WHERE `email` =:email AND `password` =:password");
$stmt->bindValue(':email', $data['email']);
$stmt->bindValue(':password', $data['pass']);
$stmt->execute();
$res = $stmt->fetch(PDO::FETCH_ASSOC);

if ($res) {
    $secret_key = $_ENV['KEY'];
    $dateTime = time(); // issued at
    $activate = $dateTime; //not before in seconds
    $activateTime = $dateTime + 10800; // expire time in seconds

    $token = array (
        'iat' => $dateTime,
        'nbf' => $activate,
        'exp' => $activateTime,
        'data' => array(
            'id' => $res['id'],
            'firstname' => $res['firstname']
        )
    );

    $jwt = JWT::encode($token, $secret_key);

    $response = [
        'status' => true,
        'jwt' => $jwt
    ];
    echo json_encode($response);
} else {
    $response = [
        'status' => false,
        'message' => 'Не верный логин или пароль'
    ];
    echo json_encode($response);
}