<?php

namespace App;

use PDO;

header('Content-type: json/application');

class Users
{
    public static function jsonResponse($res, int $code)
    {
        http_response_code($code);
        echo json_encode($res);
    }

    private static function checkId(PDO $db, string $id)
    {
        $stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$res) {
            self::jsonResponse('User not found', 404);
            die();
        }
    }

    private static function checkData(?array $data)
    {
        if (!isset($data)) {
            self::jsonResponse('The input data is incorrect', 400);
            die();
        }
        if (!isset($data['firstName'])) {
            self::jsonResponse('The \'firstName\' field is incorrect', 400);
            die();
        }
        if (!isset($data['lastName'])) {
            self::jsonResponse('The \'lastName\' field is incorrect', 400);
            die();
        }
        if (!isset($data['email'])) {
            self::jsonResponse('The \'email\' field is incorrect', 400);
            die();
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            self::jsonResponse('The \'email\' field is incorrect', 400);
            die();
        }
    }

    public static function getUsers(PDO $db, array $get)
    {
        $page = $get['page'] ?? 1;
        $limit = 4;
        $offset = ($page - 1) * $limit;

        $stmt = $db->prepare("SELECT * FROM users WHERE id > 0 limit $offset, $limit");
        $stmt->execute();
        $users = [];

        while ($res = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $users[] = $res;
        }
        self::jsonResponse($users, 200);
    }

    public static function getUser(PDO $db, string $id)
    {
        $stmt = $db->prepare("SELECT * FROM users WHERE id = $id");
        $stmt->execute();
        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$res) {
            self::jsonResponse('User not found', 404);
            die();
        }
        self::jsonResponse($res, 200);
    }

    public static function addUser(PDO $db, ?array $data)
    {
        self::checkData($data);

        $stmt = $db->prepare("INSERT INTO users VALUES (NULL, :firstName, :lastName, :email, FALSE)");
        $stmt->execute($data);

        $id = $db->lastInsertId();

        $token = Activate::generateToken($id);
        Activate::sendMessage($data['email'], $token);

        http_response_code(201);
        echo $id;
    }

    public static function updateUser(PDO $db, array $data)
    {
        self::checkData($data);
        self::checkId($db, $data['id']);

        $stmt = $db->prepare("UPDATE users SET firstName = :firstName, lastName = :lastName, email = :email WHERE id = :id");
        $stmt->execute($data);

        http_response_code(202);
    }

    public static function deleteUser(PDO $db, string $id)
    {
        $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);

        http_response_code(204);
    }
}
