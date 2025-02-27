<?php
require_once 'db_connect.php';

// Sprawdź, czy użytkownik jest zalogowany i jest adminem
if (!isset($_SESSION['user_id']) || $_SESSION['username'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$komunikat = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $nazwa = $_POST['name'];
    $cena = $_POST['price'];
    $opis = $_POST['description'];
    $kategoria = $_POST['category'];

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $obraz = $_FILES['image'];
        $nazwa_obrazu = basename($obraz['name']);
        $sciezka_obrazu = __DIR__ . '/' . $nazwa_obrazu;

        if (move_uploaded_file($obraz['tmp_name'], $sciezka_obrazu)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO products (id, name, price, image, description, category) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$id, $nazwa, $cena, $nazwa_obrazu, $opis, $kategoria]);
                $komunikat = "Perfumy dodane pomyślnie!";
            } catch (PDOException $e) {
                $komunikat = "Błąd dodawania perfum: " . $e->getMessage();
            }
        } else {
            $komunikat = "Błąd przesyłania obrazu.";
        }
    } else {
        $komunikat = "Proszę przesłać obraz.";
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dodaj perfumy</title>
  <link rel="stylesheet" href="css.css">
  <style>
    .form-container {
      max-width: 600px;
      margin: 20px auto;
      padding: 30px;
      background: rgba(255, 255, 255, 0.95);
      border-radius: 15px;
      box-shadow: 0 6px 25px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
    }
    body.dark-mode .form-container {
      background: rgba(30, 30, 30, 0.95);
      box-shadow: 0 6px 25px rgba(0, 0, 0, 0.3);
    }
    .form-container:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }
    body.dark-mode .form-container:hover {
      box-shadow: 0 10px 30px rgba(147, 112, 219, 0.3);
    }
    .form-container label {
      display: block;
      margin: 15px 0 5px;
      font-weight: 600;
      color: #2d2d2d;
    }
    body.dark-mode .form-container label {
      color: #e0e0e0;
    }
    .form-container input, .form-container textarea, .form-container select {
      width: calc(100% - 20px);
      margin: 0 10px 15px 10px;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 8px;
      font-family: 'Poppins', sans-serif;
      font-size: 1rem;
      transition: all 0.3s ease;
    }
    .form-container input:focus, .form-container textarea:focus, .form-container select:focus {
      border-color: #007bff;
      box-shadow: 0 0 10px rgba(0, 123, 255, 0.3);
    }
    .form-container button {
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
    .form-container button:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(0, 123, 255, 0.5);
    }
    .message {
      color: green;
      margin: 0 10px 15px 10px;
      text-align: center;
      font-family: 'Poppins', sans-serif;
      font-weight: 600;
    }
    .error {
      color: red;
    }
  </style>
</head>
<body>
  <header class="header">
    <a href="index.php" class="back-button">← Powrót do sklepu</a>
    <h1>Dodaj nowe perfumy</h1>
    <div class="header-right">
      <button class="theme-toggle" aria-label="Przełącz tryb ciemny">
        <img src="dark.png" alt="Tryb ciemny" class="theme-icon">
      </button>
    </div>
  </header>

  <div class="container">
    <div class="form-container">
      <?php if ($komunikat): ?>
        <p class="<?php echo strpos($komunikat, 'Błąd') === false ? 'message' : 'error'; ?>">
          <?php echo htmlspecialchars($komunikat); ?>
        </p>
      <?php endif; ?>
      <form method="POST" enctype="multipart/form-data">
        <label for="id">ID produktu:</label>
        <input type="text" id="id" name="id" required>

        <label for="name">Nazwa:</label>
        <input type="text" id="name" name="name" required>

        <label for="price">Cena (zł):</label>
        <input type="number" step="0.01" id="price" name="price" required>

        <label for="image">Obraz:</label>
        <input type="file" id="image" name="image" accept="image/*" required>

        <label for="description">Opis:</label>
        <textarea id="description" name="description" required></textarea>

        <label for="category">Kategoria:</label>
        <select id="category" name="category" required>
          <option value="men">Męskie</option>
          <option value="women">Damskie</option>
          <option value="unisex">Unisex</option>
        </select>

        <button type="submit">Dodaj perfumy</button>
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