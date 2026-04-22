<?php
// Подключение к базе данных
$host = 'localhost';
$dbname = 'u82462';
$username = 'u82462';
$password = '9164341';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к БД: " . $e->getMessage());
}

// Получаем список языков для повторного отображения формы при ошибках
$stmt = $pdo->query("SELECT id, name FROM programming_languages ORDER BY name");
$languages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Проверяем, что форма отправлена
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// ---------- ВАЛИДАЦИЯ ----------
$errors = [];
$fieldErrors = [];

// Примеры для подсказок
$examples = [
    'full_name' => 'Например: Иванов Иван Иванович',
    'phone' => 'Например: +7 (999) 123-45-67',
    'email' => 'Например: ivanov@mail.ru',
    'birth_date' => 'Выберите дату',
    'gender' => 'Выберите один из вариантов',
    'languages' => 'Выберите хотя бы один язык',
    'bio' => 'Не более 65535 символов',
    'contract_agreed' => 'Необходимо подтвердить'
];

// 1. ФИО
$full_name = trim($_POST['full_name'] ?? '');
if (empty($full_name)) {
    $errors['full_name'] = "Поле обязательно для заполнения";
} elseif (strlen($full_name) > 150) {
    $errors['full_name'] = "Не более 150 символов";
} elseif (!preg_match("/^[a-zA-Zа-яА-ЯёЁ\s\-]+$/u", $full_name)) {
    $errors['full_name'] = "Только буквы и пробелы";
} else {
    $lettersOnly = preg_replace("/[^a-zA-Zа-яА-ЯёЁ]/u", '', $full_name);
    if (mb_strlen($lettersOnly) < 2) {
        $errors['full_name'] = "Минимум 2 буквы";
    }
}

// 2. Телефон
$phone = trim($_POST['phone'] ?? '');
if (empty($phone)) {
    $errors['phone'] = "Поле обязательно для заполнения";
} else {
    $digits = preg_replace('/\D/', '', $phone);
    if (strlen($digits) !== 11) {
        $errors['phone'] = "Должно быть ровно 11 цифр";
    } elseif ($digits[0] !== '7') {
        $errors['phone'] = "Номер должен начинаться с 7";
    }
}

// 3. E-mail
$email = trim($_POST['email'] ?? '');
if (empty($email)) {
    $errors['email'] = "Поле обязательно для заполнения";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = "Некорректный адрес";
}

// 4. Дата рождения
$birth_date = trim($_POST['birth_date'] ?? '');
if (empty($birth_date)) {
    $errors['birth_date'] = "Поле обязательно для заполнения";
} else {
    $date = DateTime::createFromFormat('Y-m-d', $birth_date);
    if (!$date || $date->format('Y-m-d') !== $birth_date) {
        $errors['birth_date'] = "Некорректная дата";
    } elseif ($date > new DateTime()) {
        $errors['birth_date'] = "Дата не может быть в будущем";
    }
}

// 5. Пол
$gender = $_POST['gender'] ?? '';
if (!in_array($gender, ['male', 'female'])) {
    $errors['gender'] = "Выберите значение";
}

// 6. Любимые языки программирования
if (empty($_POST['languages']) || !is_array($_POST['languages'])) {
    $errors['languages'] = "Выберите хотя бы один язык";
} else {
    $placeholders = implode(',', array_fill(0, count($_POST['languages']), '?'));
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM programming_languages WHERE id IN ($placeholders)");
    $stmt->execute($_POST['languages']);
    $count = $stmt->fetchColumn();
    
    if ($count != count($_POST['languages'])) {
        $errors['languages'] = "Выбраны некорректные языки";
    }
}

// 7. Биография
$bio = trim($_POST['bio'] ?? '');
if (!empty($bio) && strlen($bio) > 65535) {
    $errors['bio'] = "Слишком длинный текст";
}

// 8. Чекбокс контракта
if (!isset($_POST['contract_agreed'])) {
    $errors['contract_agreed'] = "Необходимо подтвердить";
}

// Функция для вывода значения поля
function old($key, $default = '') {
    if (isset($_POST[$key])) {
        if (is_array($_POST[$key])) return $_POST[$key];
        return htmlspecialchars($_POST[$key]);
    }
    return $default;
}

// Функция для проверки выбранного языка
function isLanguageSelected($langId) {
    return isset($_POST['languages']) && in_array($langId, $_POST['languages']);
}

// Если есть ошибки — выводим форму повторно
if (!empty($errors)) {
    ?>
    <!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Анкета пользователя</title>
        <link rel="stylesheet" href="style.css">
        <style>
            .field-error {
                color: #d32f2f;
                font-size: 0.85rem;
                margin-top: 4px;
                padding-left: 5px;
            }
            .form-group.has-error input,
            .form-group.has-error select,
            .form-group.has-error textarea {
                border-color: #d32f2f !important;
                background-color: #fff8f8;
            }
            .field-hint {
                color: #666;
                font-size: 0.8rem;
                margin-top: 2px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Заполните анкету</h1>
            
            <?php if (!empty($errors)): ?>
                <div class="alert error">
                    <strong>Пожалуйста, исправьте ошибки в форме.</strong>
                </div>
            <?php endif; ?>

            <form action="process.php" method="post" class="application-form">
                <input type="hidden" name="submitted" value="1">
                
                <!-- ФИО -->
                <div class="form-group <?php echo isset($errors['full_name']) ? 'has-error' : ''; ?>">
                    <label for="full_name">ФИО *</label>
                    <input type="text" id="full_name" name="full_name" 
                           value="<?php echo old('full_name'); ?>" maxlength="150" required>
                    <?php if (isset($errors['full_name'])): ?>
                        <div class="field-error">⚠️ <?php echo htmlspecialchars($errors['full_name']); ?>. <?php echo $examples['full_name']; ?></div>
                    <?php else: ?>
                        <div class="field-hint"><?php echo $examples['full_name']; ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-row">
                    <!-- Телефон -->
                    <div class="form-group <?php echo isset($errors['phone']) ? 'has-error' : ''; ?>">
                        <label for="phone">Телефон *</label>
                        <input type="tel" id="phone" name="phone" 
                               value="<?php echo old('phone'); ?>" required>
                        <?php if (isset($errors['phone'])): ?>
                            <div class="field-error">⚠️ <?php echo htmlspecialchars($errors['phone']); ?>. <?php echo $examples['phone']; ?></div>
                        <?php else: ?>
                            <div class="field-hint"><?php echo $examples['phone']; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- E-mail -->
                    <div class="form-group <?php echo isset($errors['email']) ? 'has-error' : ''; ?>">
                        <label for="email">E-mail *</label>
                        <input type="email" id="email" name="email" 
                               value="<?php echo old('email'); ?>" required>
                        <?php if (isset($errors['email'])): ?>
                            <div class="field-error">⚠️ <?php echo htmlspecialchars($errors['email']); ?>. <?php echo $examples['email']; ?></div>
                        <?php else: ?>
                            <div class="field-hint"><?php echo $examples['email']; ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="form-row">
                    <!-- Дата рождения -->
                    <div class="form-group <?php echo isset($errors['birth_date']) ? 'has-error' : ''; ?>">
                        <label for="birth_date">Дата рождения *</label>
                        <input type="date" id="birth_date" name="birth_date" 
                               value="<?php echo old('birth_date'); ?>" required>
                        <?php if (isset($errors['birth_date'])): ?>
                            <div class="field-error">⚠️ <?php echo htmlspecialchars($errors['birth_date']); ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Пол -->
                    <div class="form-group <?php echo isset($errors['gender']) ? 'has-error' : ''; ?>">
                        <label>Пол *</label>
                        <div class="radio-group">
                            <label class="radio-label">
                                <input type="radio" name="gender" value="male" <?php echo old('gender') === 'male' ? 'checked' : ''; ?> required> Мужской
                            </label>
                            <label class="radio-label">
                                <input type="radio" name="gender" value="female" <?php echo old('gender') === 'female' ? 'checked' : ''; ?> required> Женский
                            </label>
                        </div>
                        <?php if (isset($errors['gender'])): ?>
                            <div class="field-error">⚠️ <?php echo htmlspecialchars($errors['gender']); ?>. <?php echo $examples['gender']; ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Языки -->
                <div class="form-group <?php echo isset($errors['languages']) ? 'has-error' : ''; ?>">
                    <label for="languages">Любимый язык программирования *</label>
                    <select id="languages" name="languages[]" multiple size="6" required>
                        <?php foreach ($languages as $language): ?>
                            <option value="<?php echo $language['id']; ?>" 
                                <?php echo isLanguageSelected($language['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($language['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small>Для выбора нескольких языков удерживайте Ctrl (Cmd на Mac)</small>
                    <?php if (isset($errors['languages'])): ?>
                        <div class="field-error">⚠️ <?php echo htmlspecialchars($errors['languages']); ?>. <?php echo $examples['languages']; ?></div>
                    <?php endif; ?>
                </div>

                <!-- Биография -->
                <div class="form-group <?php echo isset($errors['bio']) ? 'has-error' : ''; ?>">
                    <label for="bio">Биография</label>
                    <textarea id="bio" name="bio" rows="5"><?php echo old('bio'); ?></textarea>
                    <?php if (isset($errors['bio'])): ?>
                        <div class="field-error">⚠️ <?php echo htmlspecialchars($errors['bio']); ?>. <?php echo $examples['bio']; ?></div>
                    <?php endif; ?>
                </div>

                <!-- Чекбокс -->
                <div class="form-group checkbox-group <?php echo isset($errors['contract_agreed']) ? 'has-error' : ''; ?>">
                    <label class="checkbox-label">
                        <input type="checkbox" name="contract_agreed" <?php echo isset($_POST['contract_agreed']) ? 'checked' : ''; ?> required>
                        С контрактом ознакомлен(а) *
                    </label>
                    <?php if (isset($errors['contract_agreed'])): ?>
                        <div class="field-error">⚠️ <?php echo htmlspecialchars($errors['contract_agreed']); ?></div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="submit-btn">Сохранить</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// ---------- СОХРАНЕНИЕ В БД ----------
try {
    $pdo->beginTransaction();

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
        1
    ]);

    $application_id = $pdo->lastInsertId();

    $stmt = $pdo->prepare("
        INSERT INTO application_languages (application_id, language_id) 
        VALUES (?, ?)
    ");
    
    foreach ($_POST['languages'] as $language_id) {
        $stmt->execute([$application_id, $language_id]);
    }

    $pdo->commit();

    header('Location: index.php?success=1');
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    die('Ошибка при сохранении данных: ' . $e->getMessage());
}
?>