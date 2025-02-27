<?php
require_once 'db_connect.php';

$komunikat = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nazwa_uzytkownika = trim($_POST['username']);
    $haslo = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$nazwa_uzytkownika]);
    $uzytkownik = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($uzytkownik && password_verify($haslo, $uzytkownik['password'])) {
        $_SESSION['user_id'] = $uzytkownik['id'];
        $_SESSION['username'] = $uzytkownik['username'];
        header("Location: index.php");
        exit();
    } else {
        $komunikat = "Błędna nazwa użytkownika lub hasło.";
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Logowanie</title>
  <link rel="stylesheet" href="css.css">
  <style>
    .login-container {
      max-width: 400px;
      margin: 50px auto;
      padding: 30px;
      background: rgba(255, 255, 255, 0.95);
      border-radius: 15px;
      box-shadow: 0 6px 25px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
    }
    body.dark-mode .login-container {
      background: rgba(30, 30, 30, 0.95);
      box-shadow: 0 6px 25px rgba(0, 0, 0, 0.3);
    }
    .login-container:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }
    body.dark-mode .login-container:hover {
      box-shadow: 0 10px 30px rgba(147, 112, 219, 0.3);
    }
    .login-container label {
      display: block;
      margin: 15px 0 5px;
      font-weight: 600;
      color: #2d2d2d;
    }
    body.dark-mode .login-container label {
      color: #e0e0e0;
    }
    .login-container input {
      width: calc(100% - 20px);
      margin: 0 10px 15px 10px;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 8px;
      font-family: 'Poppins', sans-serif;
      font-size: 1rem;
      transition: all 0.3s ease;
    }
    .login-container input:focus {
      border-color: #007bff;
      box-shadow: 0 0 10px rgba(0, 123, 255, 0.3);
    }
    .login-container button {
      width: calc(100% - 20px);
      margin: 0 10px;
      background: linear-gradient(90deg, #007bff, #0056b3);
      color: #fff;
      border: none;
      padding: 12px 20px;
      border-radius: 8px;
      font-family: 'Poppins', sans-serif;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    .login-container button:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(0, 123, 255, 0.5);
    }
    .error {
      color: #ff4d4d;
      margin: 0 10px 15px 10px;
      text-align: center;
      font-family: 'Poppins', sans-serif;
      font-weight: 600;
    }
  </style>
</head>
<body>
  <header class="header">
    <a href="index.php" class="back-button">← Powrót do sklepu</a>
    <h1>Logowanie</h1>
    <div class="header-right">
      <button class="theme-toggle" aria-label="Przełącz tryb ciemny">
        <img src="dark.png" alt="Tryb ciemny" class="theme-icon">
      </button>
    </div>
  </header>

  <div class="container">
    <div class="login-container">
      <?php if ($komunikat): ?>
        <p class="error"><?php echo htmlspecialchars($komunikat); ?></p>
      <?php endif; ?>
      <form method="POST">
        <label for="username">Nazwa użytkownika:</label>
        <input type="text" id="username" name="username" required>

        <label for="password">Hasło:</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Zaloguj się</button>
      </form>
    </div>
  </div>

  <script>
    const themeToggle = document.querySelector('.theme-toggle');
    const themeIcon = document.querySelector('.theme-icon');

    window.addEventListener('load', () => {
      const darkModeCookie = document.cookie.split('; ').find(row => row.startsWith('dark-mode='));
      const isDarkMode = darkModeCookie && darkModeCookie.split('=')[1] === 'true';
      document.body.classList.toggle('dark-mode', isDarkMode);
      themeIcon.src = isDarkMode ? 'light.png' : 'dark.png';
      themeIcon.alt = isDarkMode ? 'Tryb jasny' : 'Tryb ciemny';
    });

    themeToggle.addEventListener('click', () => {
      const isDarkMode = document.body.classList.toggle('dark-mode');
      themeIcon.src = isDarkMode ? 'light.png' : 'dark.png';
      themeIcon.alt = isDarkMode ? 'Tryb jasny' : 'Tryb ciemny';
      document.cookie = `dark-mode=${isDarkMode}; path=/; max-age=${60 * 60 * 24 * 30}`;
    });

    window.addEventListener('scroll', () => {
      const header = document.querySelector('.header');
      if (window.scrollY > 50) {
        header.classList.add('scrolled');
      } else {
        header.classList.remove('scrolled');
      }
    });
  </script>
</body>
</html>