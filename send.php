<?php
/**
 * send.php — принимает данные формы заявки с сайта АВИЛ
 * и отправляет их на почту через встроенную функцию PHP mail().
 *
 * Ничего платить не нужно, никаких сторонних сервисов —
 * просто положите этот файл в ту же папку, что и index.html,
 * на любой хостинг с поддержкой PHP.
 */

// Разрешаем запросы только методом POST (от формы)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

// Куда отправлять письма
$to = 'avers161snab@yandex.ru';

// Забираем и очищаем данные из формы
function clean($value) {
    return htmlspecialchars(trim($value ?? ''), ENT_QUOTES, 'UTF-8');
}

$name      = clean($_POST['name']      ?? '');
$phone     = clean($_POST['phone']     ?? '');
$email     = clean($_POST['email']     ?? '');
$org       = clean($_POST['org']       ?? '');
$direction = clean($_POST['direction'] ?? '');
$comment   = clean($_POST['comment']   ?? '');

// Простая проверка: обязательные поля должны быть заполнены
if ($name === '' || $phone === '') {
    http_response_code(400);
    exit('Заполните обязательные поля: имя и телефон.');
}

// Тема и текст письма
$subject = '=?UTF-8?B?' . base64_encode('Заявка с сайта АВИЛ — ' . $name) . '?=';

$body = "Новая заявка с сайта АВИЛ:\n\n"
      . "Имя: $name\n"
      . "Телефон: $phone\n"
      . "E-mail: $email\n"
      . "Организация: $org\n"
      . "Интересующее направление: $direction\n"
      . "Комментарий: $comment\n";

// Заголовки письма (кодировка UTF-8, чтобы русский текст не превращался в кракозябры)
$headers = "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "From: Сайт АВИЛ <no-reply@" . ($_SERVER['HTTP_HOST'] ?? 'avil-site.ru') . ">\r\n";
if ($email !== '') {
    $headers .= "Reply-To: $email\r\n";
}

// Отправка письма
$sent = mail($to, $subject, $body, $headers);

// Отвечаем сайту: получилось или нет
header('Content-Type: application/json; charset=utf-8');
if ($sent) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Не удалось отправить письмо. Проверьте, что на хостинге включена функция mail().']);
}
