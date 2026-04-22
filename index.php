<?php
// Подключение к БД для получения списка языков
$host = 'localhost';
$dbname = 'u82462';
$username = 'u82462';
$password = '9164341';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->query("SELECT id, name FROM programming_languages ORDER BY name");
    $languages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

$successMessage = '';
if (isset($_GET['success'])) {
    $successMessage = '<div class="alert success">✅ Данные успешно сохранены!</div>';
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Анкета пользователя</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Заполните анкету</h1>
        <?php echo $successMessage; ?>
        
        <!-- Чистая форма для первого входа -->
        <form action="process.php" method="post" class="application-form">
            <input type="hidden" name="submitted" value="1">
            
            <div class="form-group">
                <label for="full_name">ФИО *</label>
                <input type="text" id="full_name" name="full_name" maxlength="150" required>
                <div class="field-hint">Например: Иванов Иван Иванович</div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="phone">Телефон *</label>
                    <input type="tel" id="phone" name="phone" required>
                    <div class="field-hint">Например: +7 (999) 123-45-67</div>
                </div>
                
                <div class="form-group">
                    <label for="email">E-mail *</label>
                    <input type="email" id="email" name="email" required>
                    <div class="field-hint">Например: ivanov@mail.ru</div>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="birth_date">Дата рождения *</label>
                    <input type="date" id="birth_date" name="birth_date" required>
                </div>
                
                <div class="form-group">
                    <label>Пол *</label>
                    <div class="radio-group">
                        <label class="radio-label">
                            <input type="radio" name="gender" value="male" required> Мужской
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="gender" value="female" required> Женский
                        </label>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="languages">Любимый язык программирования *</label>
                <select id="languages" name="languages[]" multiple size="6" required>
                    <?php foreach ($languages as $language): ?>
                        <option value="<?php echo $language['id']; ?>">
                            <?php echo htmlspecialchars($language['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small>Для выбора нескольких языков удерживайте Ctrl (Cmd на Mac)</small>
            </div>

            <div class="form-group">
                <label for="bio">Биография</label>
                <textarea id="bio" name="bio" rows="5"></textarea>
            </div>

            <div class="form-group checkbox-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="contract_agreed" required>
                    С контрактом ознакомлен(а) *
                </label>
            </div>

            <button type="submit" class="submit-btn">Сохранить</button>
        </form>
    </div>
</body>
</html>