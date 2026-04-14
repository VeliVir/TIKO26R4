<?php
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Placeholder credentials
    $valid_username = 'seppo.tarsky@tuni.fi';
    $valid_password = '1234';
    
    if ($username === $valid_username && $password === $valid_password) {
        $_SESSION['user'] = $username;
        header('Location: main.php');
        exit();
    } else {
        $error = 'Virheellinen käyttäjänimi tai salasana';
    }
}
?>

<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kirjaudu sisään - Sähkötärskyn tietokantasofta</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="login-style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h1>Sähkötärskyn tietokantasofta</h1>
                <p>Kirjaudu sisään jatkaaksesi</p>
            </div>

            <?php if ($error): ?>
                <div class="error-message">
                    <span class="error-icon">⚠️</span>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="login-form">
                <div class="form-group">
                    <label for="username">Käyttäjänimi</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        placeholder="Kirjoita käyttäjänimi"
                        required
                        autocomplete="username"
                    >
                </div>

                <div class="form-group">
                    <label for="password">Salasana</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Kirjoita salasana"
                        required
                        autocomplete="current-password"
                    >
                </div>

                <button type="submit" class="login-button">Kirjaudu sisään</button>
            </form>
        </div>
    </div>
</body>
</html>
