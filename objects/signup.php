<?php

require_once './vendor/autoload.php';

use Conn\Database;

$db = Database::connection();

$data = [
    'firstname' => $_POST['firstname'],
    'lastname' => $_POST['lastname'],
    'email' => $_POST['email'],
    'pass' => $_POST['pass'],
    'pass_conf' => $_POST['pass_conf']
];

$stmt = $db->prepare("SELECT * FROM `users` WHERE `email` =:email");
$stmt->execute(['email' => $data['email']]);
$res = $stmt->fetch(PDO::FETCH_ASSOC);

if ($res) {
    $response = [
        'status' => false,
        'type' => 1,
        'message' => 'Такой email уже существует',
        'fields' => ['email']
    ];
    echo json_encode($response);
    die();
}

$errors = [];

if (trim($_POST['firstname']) == '') {
    $errors[] = 'firstname';
}
if (trim($_POST['lastname']) == '') {
    $errors[] = 'lastname';
}
if (trim($_POST['email']) == ''|| !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'email';
}
if (trim($_POST['pass']) == '') {
    $errors[] = 'pass';
}
if (trim($_POST['pass_conf']) == '') {
    $errors[] = 'pass_conf';
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

if (trim($_POST['pass']) === trim($_POST['pass_conf'])) {
    $data['pass'] = md5($data['pass']);

    $stmt = $db->prepare("INSERT INTO users VALUES (NULL, :firstname, :lastname, :password, :email, false )");
    $stmt->bindValue(':firstname', $data['firstname']);
    $stmt->bindValue(':lastname', $data['lastname']);
    $stmt->bindValue(':email', $data['email']);
    $stmt->bindValue(':password', $data['pass']);
    $res = $stmt->execute();

    $response = [
        'status' => true,
        'message' => 'Регистрация прошла успешно!',
    ];
    echo json_encode($response);
} else {
    $response = [
        'status' => false,
        'message' => 'Пароли не совпадают',
    ];
    echo json_encode($response);
}