<?php
require '../../../vendor/autoload.php';

use Application\Mail;
use Application\Page;

$dsn = "pgsql:host=" . getenv('DB_PROD_HOST') . ";dbname=" . getenv('DB_PROD_NAME');
$pdo = new PDO($dsn, getenv('DB_USER'), getenv('DB_PASS'), [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$mail = new Mail($pdo);
$page = new Page();

$uri = $_SERVER['REQUEST_URI'];
$parts = explode('/', trim($uri, '/'));
$id = end($parts);

$item = $mail->getMail($id);

if (!$item) {
    $page->notFound();
    exit;
}

switch ($_SERVER['REQUEST_METHOD']) {

    case 'GET':
        $page->item($item);
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['subject'], $data['body'])) {
            $page->badRequest();
            break;
        }

        $mail->updateMail($id, $data['subject'], $data['body']);
        $page->item($mail->getMail($id));
        break;

    case 'DELETE':
        $mail->deleteMail($id);
        echo json_encode(["deleted" => true]);
        break;

    default:
        $page->badRequest();
}
