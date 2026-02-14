<?php
require '../../vendor/autoload.php';

use Application\Mail;
use Application\Page;

$dsn = "pgsql:host=" . getenv('DB_PROD_HOST') . ";dbname=" . getenv('DB_PROD_NAME');
$pdo = new PDO($dsn, getenv('DB_USER'), getenv('DB_PASS'), [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$mail = new Mail($pdo);
$page = new Page();

switch ($_SERVER['REQUEST_METHOD']) {

    case 'GET':
        $page->list($mail->getAllMail());
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['subject'], $data['body'])) {
            $page->badRequest();
            break;
        }

        $id = $mail->createMail($data['subject'], $data['body']);
        http_response_code(201);
        $page->item(['id' => $id]);
        break;

    default:
        $page->badRequest();
}
