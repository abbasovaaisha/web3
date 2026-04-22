<?php
session_start();

// Подключение к базе данных
$host = 'localhost';
$dbname = 'u82462';
$username = 'u82462';
$password = '9164341';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    header('Location: index.php?error=' . urlencode('Ошибка подключения к БД'));
    exit;
}

// Функция для редиректа с ошибкой
function redirectWithError($message) {
    header('Location: index.php?error=' . urlencode($message));
    exit;
}

// Проверяем, что форма отправлена методом POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// ---------- ВАЛИДАЦИЯ ----------
$errors = [];

// 1. ФИО
$full_name = trim($_POST['full_name'] ?? '');
if (empty($full_name)) {
    $errors[] = "Поле 'ФИО' обязательно для заполнения";
} elseif (strlen($full_name) > 150) {
    $errors[] = "Поле 'ФИО' не должно превышать 150 символов";
} elseif (!preg_match("/^[a-zA-Zа-яА-ЯёЁ\s\-]+$/u", $full_name)) {
    $errors[] = "Поле 'ФИО' может содержать только буквы и пробелы";
}

// 2. Телефон
$phone = trim($_POST['phone'] ?? '');
if (empty($phone)) {
    $errors[] = "Поле 'Телефон' обязательно для заполнения";
} elseif (!preg_match("/^[\+]?[0-9\(\)\-\s]+$/", $phone)) {
    $errors[] = "Поле 'Телефон' содержит недопустимые символы";
}

// 3. E-mail
$email = trim($_POST['email'] ?? '');
if (empty($email)) {
    $errors[] = "Поле 'E-mail' обязательно для заполнения";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Поле 'E-mail' содержит некорректный адрес";
}

// 4. Дата рождения
$birth_date = trim($_POST['birth_date'] ?? '');
if (empty($birth_date)) {
    $errors[] = "Поле 'Дата рождения' обязательно для заполнения";
} else {
    $date = DateTime::createFromFormat('Y-m-d', $birth_date);
    if (!$date || $date->format('Y-m-d') !== $birth_date) {
        $errors[] = "Поле 'Дата рождения' содержит некорректную дату";
    } elseif ($date > new DateTime()) {
        $errors[] = "Дата рождения не может быть в будущем";
    }
}

// 5. Пол
$gender = $_POST['gender'] ?? '';
if (!in_array($gender, ['male', 'female'])) {
    $errors[] = "Выберите корректное значение пола";
}

// 6. Любимые языки программирования
if (empty($_POST['languages']) || !is_array($_POST['languages'])) {
    $errors[] = "Выберите хотя бы один язык программирования";
} else {
    // Проверяем, что все выбранные ID существуют в базе
    $placeholders = implode(',', array_fill(0, count($_POST['languages']), '?'));
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM programming_languages WHERE id IN ($placeholders)");
    $stmt->execute($_POST['languages']);
    $count = $stmt->fetchColumn();
    
    if ($count != count($_POST['languages'])) {
        $errors[] = "Выбраны некорректные языки программирования";
    }
}

// 7. Биография (необязательное поле, но если есть - проверяем длину)
$bio = trim($_POST['bio'] ?? '');
if (!empty($bio) && strlen($bio) > 65535) {
    $errors[] = "Поле 'Биография' слишком длинное";
}

// 8. Чекбокс контракта
if (!isset($_POST['contract_agreed'])) {
    $errors[] = "Необходимо подтвердить ознакомление с контрактом";
}

// Если есть ошибки - возвращаемся на форму
if (!empty($errors)) {
    // Сохраняем данные формы в сессии для повторного отображения
    $_SESSION['form_data'] = $_POST;
    redirectWithError(implode('. ', $errors));
}

// ---------- СОХРАНЕНИЕ В БД ----------
try {
    $pdo->beginTransaction();

    // Вставка основной заявки
    $stmt = $pdo->prepare("
        INSERT INTO applications (full_name, phone, email, birth_date, gender, bio, contract_agreed) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $full_name,
        $phone,
        $email,
        $birth_date,
        $gender,
        $bio,
        1 // contract_agreed всегда 1, так как проверено выше
    ]);

    $application_id = $pdo->lastInsertId();

    // Вставка выбранных языков
    $stmt = $pdo->prepare("
        INSERT INTO application_languages (application_id, language_id) 
        VALUES (?, ?)
    ");
    
    foreach ($_POST['languages'] as $language_id) {
        $stmt->execute([$application_id, $language_id]);
    }

    $pdo->commit();

    // Очищаем данные формы в сессии
    unset($_SESSION['form_data']);
    
    // Перенаправляем с сообщением об успехе
    header('Location: index.php?success=1');
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    redirectWithError('Ошибка при сохранении данных: ' . $e->getMessage());
}
?>