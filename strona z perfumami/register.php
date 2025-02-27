<?php
require_once 'db_connect.php';

$komunikat = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nazwa_uzytkownika = trim($_POST['username']);
    $haslo = $_POST['password'];
    $powtorz_haslo = $_POST['password_repeat'];

    if ($haslo !== $powtorz_haslo) {
        $komunikat = "Hasła nie są zgodne.";
    } elseif (strlen($haslo) < 6) {
        $komunikat = "Hasło musi mieć co najmniej 6 znaków.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$nazwa_uzytkownika]);
        if ($stmt->fetch()) {
            $komunikat = "Ta nazwa użytkownika jest już zajęta.";
        } else {
            $hash_hasla = password_hash($haslo, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            try {
                $stmt->execute([$nazwa_uzytkownika, $hash_hasla]);
                $komunikat = "Rejestracja udana! Możesz się teraz zalogować.";
            } catch (PDOException $e) {
                $komunikat = "Błąd podczas rejestracji: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Rejestracja</title>
  <link rel="stylesheet" href="css.css">
  <style>
    .register-container {
      max-width: 400px;
      margin: 50px auto;
      padding: 30px;
      background: rgba(255, 255, 255, 0.95);
      border-radius: 15px;
      box-shadow: 0 6px 25px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
    }
    body.dark-mode .register-container {
      background: rgba(30, 30, 30, 0.95);
      box-shadow: 0 6px 25px rgba(0, 0, 0, 0.3);
    }
    .register-container:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }
    body.dark-mode .register-container:hover {
      box-shadow: 0 10px 30px rgba(147, 112, 219, 0.3);
    }
    .register-container label {
      display: block;
      margin: 15px 0 5px;
      font-weight: 600;
      color: #2d2d2d;
    }
    body.dark-mode .register-container label {
      color: #e0e0e0;
    }
    .register-container input {
      width: calc(100% - 20px);
      margin: 0 10px 15px 10px;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 8px;
      font-family: 'Poppins', sans-serif;
      font-size: 1rem;
      transition: all 0.3s ease;
    }
    .register-container input:focus {
      border-color: #007bff;
      box-shadow: 0 0 10px rgba(0, 123, 255, 0.3);
    }
    .register-container button {
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
    .register-container button:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(0, 123, 255, 0.5);
    }
    .register-container p {
      text-align: center;
      margin: 15px 10px 0 10px;
      color: #555;
      font-family: 'Poppins', sans-serif;
    }
    body.dark-mode .register-container p {
      color: #bbb;
    }
    .register-container a {
      color: #007bff;
      text-decoration: none;
    }
    .register-container a:hover {
      text-decoration: underline;
    }
    .message {
      color: green;
      margin: 0 10px 15px 10px;
      text-align: center;
      font-family: 'Poppins', sans-serif;
      font-weight: 600;
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
    <h1>Rejestracja</h1>
    <div class="header-right">
      <button class="theme-toggle" aria-label="Przełącz tryb ciemny">
        <img src="dark.png" alt="Tryb ciemny" class="theme-icon">
      </button>
    </div>
  </header>

  <div class="container">
    <div class="register-container">
      <?php if ($komunikat): ?>
        <p class="<?php echo strpos($komunikat, 'Błąd') === false && strpos($komunikat, 'zajęta') === false && strpos($komunikat, 'zgodne') === false ? 'message' : 'error'; ?>">
          <?php echo htmlspecialchars($komunikat); ?>
        </p>
      <?php endif; ?>
      <form method="POST">
        <label for="username">Nazwa użytkownika:</label>
        <input type="text" id="username" name="username" required>

        <label for="password">Hasło:</label>
        <input type="password" id="password" name="password" required>

        <label for="password_repeat">Powtórz hasło:</label>
        <input type="password" id="password_repeat" name="password_repeat" required>

        <button type="submit">Zarejestruj się</button>
      </form>
      <p>Masz już konto? <a href="login.php">Zaloguj się</a></p>
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