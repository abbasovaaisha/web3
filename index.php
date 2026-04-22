<?php
/**
 * Подключение к базе данных
 */
function connectToDatabase() {
    static $db = null;
    if ($db === null) {
        $host = 'localhost';
        $user = 'u82462';
        $pass = '9164341';
        $name = 'u82462';
        $dsn = "mysql:host=$host;dbname=$name;charset=utf8mb4";
        try {
            $db = new PDO($dsn, $user, $pass);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            exit('Ошибка подключения к БД: ' . $e->getMessage());
        }
    }
    return $db;
}

/**
 * Получить список языков из таблицы programming_languages
 */
function getLanguageList() {
    $pdo = connectToDatabase();
    $stmt = $pdo->query("SELECT id, name FROM programming_languages ORDER BY name");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Белые списки для валидации
$allowedLanguages = [
    'Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python',
    'Java', 'Haskell', 'Clojure', 'Prolog', 'Scala', 'Go'
];
$allowedGenders = ['male', 'female'];

// Инициализация данных формы
$formInput = [
    'full_name' => '',
    'phone'     => '',
    'email'     => '',
    'birth_date'=> '',
    'gender'    => '',
    'bio'       => '',
    'contract_agreed' => false,
    'languages' => []
];

$errorList = [];
$successMessage = '';

// Примеры правильного заполнения
$fieldExamples = [
    'full_name' => 'Пример: Иванов Иван Иванович',
    'phone'     => 'Пример: +7 999 123-45-67',
    'email'     => 'Пример: ivanov@mail.ru',
    'birth_date'=> 'Выберите дату',
    'gender'    => 'Выберите вариант',
    'languages' => 'Выберите хотя бы один язык',
    'bio'       => 'До 10000 символов',
    'contract_agreed' => 'Требуется подтверждение'
];

// Обработка отправки формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Загрузка данных из POST
    $formInput['full_name']   = trim($_POST['full_name'] ?? '');
    $formInput['phone']       = trim($_POST['phone'] ?? '');
    $formInput['email']       = trim($_POST['email'] ?? '');
    $formInput['birth_date']  = trim($_POST['birth_date'] ?? '');
    $formInput['gender']      = $_POST['gender'] ?? '';
    $formInput['bio']         = trim($_POST['bio'] ?? '');
    $formInput['contract_agreed'] = isset($_POST['contract_agreed']);
    $formInput['languages']   = $_POST['languages'] ?? [];

    // --- Валидация ---
    // ФИО
    if ($formInput['full_name'] === '') {
        $errorList['full_name'] = 'Поле обязательно для заполнения.';
    } elseif (!preg_match('/^[a-zA-Zа-яА-ЯёЁ\s\-]+$/u', $formInput['full_name'])) {
        $errorList['full_name'] = 'Допускаются только буквы, пробелы и дефис.';
    } elseif (strlen($formInput['full_name']) > 150) {
        $errorList['full_name'] = 'Максимальная длина — 150 символов.';
    } else {
        preg_match_all('/[a-zA-Zа-яА-ЯёЁ]/u', $formInput['full_name'], $letters);
        if (count($letters[0]) < 2) {
            $errorList['full_name'] = 'В имени должно быть не менее двух букв.';
        }
    }

    // Телефон (ровно 11 цифр, первая 7)
    if ($formInput['phone'] === '') {
        $errorList['phone'] = 'Поле обязательно для заполнения.';
    } else {
        $digits = preg_replace('/\D/', '', $formInput['phone']);
        if (strlen($digits) !== 11) {
            $errorList['phone'] = 'Номер должен содержать ровно 11 цифр.';
        } elseif ($digits[0] !== '7') {
            $errorList['phone'] = 'Номер должен начинаться с 7.';
        }
    }

    // Email
    if ($formInput['email'] === '') {
        $errorList['email'] = 'Поле обязательно для заполнения.';
    } elseif (!filter_var($formInput['email'], FILTER_VALIDATE_EMAIL)) {
        $errorList['email'] = 'Некорректный адрес электронной почты.';
    }

    // Дата рождения
    if ($formInput['birth_date'] === '') {
        $errorList['birth_date'] = 'Поле обязательно для заполнения.';
    } else {
        $dateObj = DateTime::createFromFormat('Y-m-d', $formInput['birth_date']);
        if (!$dateObj || $dateObj->format('Y-m-d') !== $formInput['birth_date']) {
            $errorList['birth_date'] = 'Некорректный формат даты.';
        } elseif ($dateObj > new DateTime('today')) {
            $errorList['birth_date'] = 'Дата не может быть в будущем.';
        }
    }

    // Пол
    if ($formInput['gender'] === '') {
        $errorList['gender'] = 'Выберите пол.';
    } elseif (!in_array($formInput['gender'], $allowedGenders)) {
        $errorList['gender'] = 'Недопустимое значение.';
    }

    // Языки программирования
    if (empty($formInput['languages'])) {
        $errorList['languages'] = 'Необходимо выбрать хотя бы один язык.';
    } else {
        foreach ($formInput['languages'] as $lang) {
            if (!in_array($lang, $allowedLanguages)) {
                $errorList['languages'] = 'Выбран недопустимый язык.';
                break;
            }
        }
    }

    // Биография
    if (strlen($formInput['bio']) > 10000) {
        $errorList['bio'] = 'Текст слишком длинный (максимум 10000 символов).';
    }

    // Чекбокс
    if (!$formInput['contract_agreed']) {
        $errorList['contract_agreed'] = 'Необходимо подтвердить ознакомление с контрактом.';
    }

    // Сохранение в БД, если ошибок нет
    if (empty($errorList)) {
        try {
            $pdo = connectToDatabase();
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                INSERT INTO applications 
                (full_name, phone, email, birth_date, gender, bio, contract_agreed)
                VALUES (:fn, :ph, :em, :bd, :gen, :bio, :ca)
            ");
            $stmt->execute([
                ':fn'  => $formInput['full_name'],
                ':ph'  => $formInput['phone'],
                ':em'  => $formInput['email'],
                ':bd'  => $formInput['birth_date'],
                ':gen' => $formInput['gender'],
                ':bio' => $formInput['bio'],
                ':ca'  => $formInput['contract_agreed'] ? 1 : 0
            ]);
            $applicationId = $pdo->lastInsertId();

            // Получаем соответствие название языка → id
            $languageMap = [];
            $langRecords = getLanguageList();
            foreach ($langRecords as $lang) {
                $languageMap[$lang['name']] = $lang['id'];
            }

            // Вставка связей
            $linkStmt = $pdo->prepare("
                INSERT INTO application_languages (application_id, language_id) 
                VALUES (?, ?)
            ");
            foreach ($formInput['languages'] as $langName) {
                if (isset($languageMap[$langName])) {
                    $linkStmt->execute([$applicationId, $languageMap[$langName]]);
                }
            }

            $pdo->commit();
            $successMessage = 'Данные успешно сохранены!';

            // Очистка формы
            $formInput = [
                'full_name' => '',
                'phone'     => '',
                'email'     => '',
                'birth_date'=> '',
                'gender'    => '',
                'bio'       => '',
                'contract_agreed' => false,
                'languages' => []
            ];
        } catch (Exception $e) {
            $pdo->rollBack();
            $errorList['database'] = 'Ошибка сохранения: ' . $e->getMessage();
        }
    }
}

// Получаем список языков для формы
$languageOptions = getLanguageList();
if (empty($languageOptions)) {
    $languageOptions = array_map(function($name) {
        return ['id' => $name, 'name' => $name];
    }, $allowedLanguages);
}

// Подключаем шаблон формы (переименован в anketa.php)
require 'anketa.php';
?>