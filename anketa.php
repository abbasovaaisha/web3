<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Анкета – Задание 3</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .nav-links {
            margin-top: 30px;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        .nav-links a {
            display: inline-block;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            padding: 10px 25px;
            border-radius: 6px;
            font-weight: bold;
            transition: background 0.2s;
        }
        .nav-links a:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Заполните анкету</h1>

        <?php if ($successMessage !== ''): ?>
            <div class="alert success">✅ <?= htmlspecialchars($successMessage) ?></div>
        <?php endif; ?>

        <?php if (isset($errorList['database'])): ?>
            <div class="alert error">❌ <?= htmlspecialchars($errorList['database']) ?></div>
        <?php endif; ?>

        <form method="post" action="" class="application-form">
            <!-- ФИО -->
            <div class="form-group <?= isset($errorList['full_name']) ? 'has-error' : '' ?>">
                <label for="full_name">ФИО *</label>
                <input type="text" id="full_name" name="full_name" 
                       value="<?= htmlspecialchars($formInput['full_name']) ?>" maxlength="150" required>
                <?php if (isset($errorList['full_name'])): ?>
                    <div class="field-error">⚠️ <?= htmlspecialchars($errorList['full_name']) ?> <?= $fieldExamples['full_name'] ?></div>
                <?php else: ?>
                    <div class="field-hint"><?= $fieldExamples['full_name'] ?></div>
                <?php endif; ?>
            </div>

            <!-- Телефон -->
            <div class="form-group <?= isset($errorList['phone']) ? 'has-error' : '' ?>">
                <label for="phone">Телефон *</label>
                <input type="tel" id="phone" name="phone" 
                       value="<?= htmlspecialchars($formInput['phone']) ?>" required>
                <?php if (isset($errorList['phone'])): ?>
                    <div class="field-error">⚠️ <?= htmlspecialchars($errorList['phone']) ?> <?= $fieldExamples['phone'] ?></div>
                <?php else: ?>
                    <div class="field-hint"><?= $fieldExamples['phone'] ?></div>
                <?php endif; ?>
            </div>

            <!-- Email -->
            <div class="form-group <?= isset($errorList['email']) ? 'has-error' : '' ?>">
                <label for="email">E-mail *</label>
                <input type="email" id="email" name="email" 
                       value="<?= htmlspecialchars($formInput['email']) ?>" required>
                <?php if (isset($errorList['email'])): ?>
                    <div class="field-error">⚠️ <?= htmlspecialchars($errorList['email']) ?> <?= $fieldExamples['email'] ?></div>
                <?php else: ?>
                    <div class="field-hint"><?= $fieldExamples['email'] ?></div>
                <?php endif; ?>
            </div>

            <!-- Дата рождения -->
            <div class="form-group <?= isset($errorList['birth_date']) ? 'has-error' : '' ?>">
                <label for="birth_date">Дата рождения *</label>
                <input type="date" id="birth_date" name="birth_date" 
                       value="<?= htmlspecialchars($formInput['birth_date']) ?>" required>
                <?php if (isset($errorList['birth_date'])): ?>
                    <div class="field-error">⚠️ <?= htmlspecialchars($errorList['birth_date']) ?></div>
                <?php endif; ?>
            </div>

            <!-- Пол -->
            <div class="form-group <?= isset($errorList['gender']) ? 'has-error' : '' ?>">
                <label>Пол *</label>
                <div class="radio-group">
                    <label class="radio-label">
                        <input type="radio" name="gender" value="male" <?= $formInput['gender'] === 'male' ? 'checked' : '' ?> required> Мужской
                    </label>
                    <label class="radio-label">
                        <input type="radio" name="gender" value="female" <?= $formInput['gender'] === 'female' ? 'checked' : '' ?>> Женский
                    </label>
                </div>
                <?php if (isset($errorList['gender'])): ?>
                    <div class="field-error">⚠️ <?= htmlspecialchars($errorList['gender']) ?> <?= $fieldExamples['gender'] ?></div>
                <?php endif; ?>
            </div>

            <!-- Языки -->
            <div class="form-group <?= isset($errorList['languages']) ? 'has-error' : '' ?>">
                <label for="languages">Любимые языки программирования *</label>
                <select id="languages" name="languages[]" multiple size="6" required>
                    <?php foreach ($languageOptions as $langOption): ?>
                        <?php $selected = in_array($langOption['name'], $formInput['languages']); ?>
                        <option value="<?= htmlspecialchars($langOption['name']) ?>" <?= $selected ? 'selected' : '' ?>>
                            <?= htmlspecialchars($langOption['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small>Для выбора нескольких пунктов удерживайте Ctrl (или Cmd на Mac)</small>
                <?php if (isset($errorList['languages'])): ?>
                    <div class="field-error">⚠️ <?= htmlspecialchars($errorList['languages']) ?> <?= $fieldExamples['languages'] ?></div>
                <?php endif; ?>
            </div>

            <!-- Биография -->
            <div class="form-group <?= isset($errorList['bio']) ? 'has-error' : '' ?>">
                <label for="bio">Биография</label>
                <textarea id="bio" name="bio" rows="5"><?= htmlspecialchars($formInput['bio']) ?></textarea>
                <?php if (isset($errorList['bio'])): ?>
                    <div class="field-error">⚠️ <?= htmlspecialchars($errorList['bio']) ?> <?= $fieldExamples['bio'] ?></div>
                <?php endif; ?>
            </div>

            <!-- Чекбокс -->
            <div class="form-group checkbox-group <?= isset($errorList['contract_agreed']) ? 'has-error' : '' ?>">
                <label class="checkbox-label">
                    <input type="checkbox" name="contract_agreed" <?= $formInput['contract_agreed'] ? 'checked' : '' ?> required>
                    С контрактом ознакомлен(а) *
                </label>
                <?php if (isset($errorList['contract_agreed'])): ?>
                    <div class="field-error">⚠️ <?= htmlspecialchars($errorList['contract_agreed']) ?></div>
                <?php endif; ?>
            </div>

            <button type="submit" class="submit-btn">Сохранить</button>
        </form>

        <div class="nav-links">
            <a href="view.php">📋 Просмотр сохранённых анкет</a>
            <a href="podg.html">📋 Страница подготовки </a>
        </div>
    </div>
</body>
</html>