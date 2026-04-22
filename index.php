<?php
// Подключение к базе данных
$host = 'localhost';
$dbname = 'u82462';
$username = 'u82462';
$password = '9164341';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Получаем список языков для формы
    $stmt = $pdo->query("SELECT id, name FROM programming_languages ORDER BY name");
    $languages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

// Функция для сохранения значений полей после отправки формы с ошибкой
function old($key) {
    return isset($_POST[$key]) ? htmlspecialchars($_POST[$key]) : '';
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
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert success">✅ Данные успешно сохранены!</div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert error">❌ <?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <form action="process.php" method="post" class="application-form">
            <div class="form-group">
                <label for="full_name">ФИО *</label>
                <input type="text" id="full_name" name="full_name" value="<?php echo old('full_name'); ?>" maxlength="150" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="phone">Телефон *</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo old('phone'); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">E-mail *</label>
                    <input type="email" id="email" name="email" value="<?php echo old('email'); ?>" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="birth_date">Дата рождения *</label>
                    <input type="date" id="birth_date" name="birth_date" value="<?php echo old('birth_date'); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Пол *</label>
                    <div class="radio-group">
                        <label class="radio-label">
                            <input type="radio" name="gender" value="male" <?php echo old('gender') === 'male' ? 'checked' : ''; ?> required> Мужской
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="gender" value="female" <?php echo old('gender') === 'female' ? 'checked' : ''; ?> required> Женский
                        </label>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="languages">Любимый язык программирования *</label>
                <select id="languages" name="languages[]" multiple size="6" required>
                    <?php foreach ($languages as $language): ?>
                        <option value="<?php echo $language['id']; ?>" 
                            <?php echo (isset($_POST['languages']) && in_array($language['id'], $_POST['languages'])) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($language['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small>Для выбора нескольких языков удерживайте Ctrl (Cmd на Mac)</small>
            </div>

            <div class="form-group">
                <label for="bio">Биография</label>
                <textarea id="bio" name="bio" rows="5"><?php echo old('bio'); ?></textarea>
            </div>

            <div class="form-group checkbox-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="contract_agreed" <?php echo isset($_POST['contract_agreed']) ? 'checked' : ''; ?> required>
                    С контрактом ознакомлен(а) *
                </label>
            </div>

            <button type="submit" class="submit-btn">Сохранить</button>
        </form>
    </div>
</body>
</html>