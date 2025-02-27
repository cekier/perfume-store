<?php
require_once 'db_connect.php';

$productId = isset($_GET['id']) ? $_GET['id'] : null;

if ($productId) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT reviewer, text FROM reviews WHERE product_id = ?");
    $stmt->execute([$productId]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $product = null;
}

$komunikat_recenzji = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_text'])) {
    if (!isset($_SESSION['user_id'])) {
        $komunikat_recenzji = "Musisz być zalogowany, aby dodać recenzję.";
    } else {
        $tekst_recenzji = trim($_POST['review_text']);
        if (empty($tekst_recenzji)) {
            $komunikat_recenzji = "Recenzja nie może być pusta.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO reviews (product_id, reviewer, text) VALUES (?, ?, ?)");
            try {
                $stmt->execute([$productId, $_SESSION['username'], $tekst_recenzji]);
                $komunikat_recenzji = "Recenzja dodana pomyślnie!";
                $stmt = $pdo->prepare("SELECT reviewer, text FROM reviews WHERE product_id = ?");
                $stmt->execute([$productId]);
                $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $komunikat_recenzji = "Błąd podczas dodawania recenzji: " . $e->getMessage();
            }
        }
    }
}

$cart = isset($_COOKIE['cart']) ? json_decode($_COOKIE['cart'], true) : [];
if (!is_array($cart)) $cart = [];
?>

<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Szczegóły perfum</title>
  <link rel="stylesheet" href="css.css">
  <style>
    .review-form {
      margin-top: 30px;
      padding: 25px;
      background: rgba(255, 255, 255, 0.95);
      border-radius: 15px;
      box-shadow: 0 6px 25px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
    }
    body.dark-mode .review-form {
      background: rgba(30, 30, 30, 0.95);
      box-shadow: 0 6px 25px rgba(0, 0, 0, 0.3);
    }
    .review-form:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }
    body.dark-mode .review-form:hover {
      box-shadow: 0 10px 30px rgba(147, 112, 219, 0.3);
    }
    .review-form label {
      display: block;
      margin: 0 10px 10px 10px;
      font-weight: 600;
      color: #2d2d2d;
    }
    body.dark-mode .review-form label {
      color: #e0e0e0;
    }
    .review-form textarea {
      width: calc(100% - 20px);
      margin: 0 10px 15px 10px;
      padding: 12px;
      border: 1px solid #ddd;
      border-radius: 8px;
      font-family: 'Poppins', sans-serif;
      font-size: 1rem;
      resize: vertical;
      transition: all 0.3s ease;
      background: #fff;
    }
    body.dark-mode .review-form textarea {
      background: #2d2d2d;
      border-color: #444;
      color: #e0e0e0;
    }
    .review-form textarea:focus {
      border-color: #007bff;
      box-shadow: 0 0 10px rgba(0, 123, 255, 0.3);
      background: #fff;
      outline: none;
    }
    body.dark-mode .review-form textarea:focus {
      border-color: #9370DB;
      box-shadow: 0 0 10px rgba(147, 112, 219, 0.5);
      background: #2d2d2d;
      outline: none;
    }
    .review-form button {
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
    .review-form button:hover {
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
    <h1>Szczegóły perfum</h1>
    <div class="header-right">
      <button class="theme-toggle" aria-label="Przełącz tryb ciemny">
        <img src="dark.png" alt="Tryb ciemny" class="theme-icon">
      </button>
    </div>
  </header>

  <div class="container">
    <div class="product-detail">
      <?php if ($product): ?>
        <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="detail-image" />
        <div class="detail-info">
          <h1><?php echo htmlspecialchars($product['name']); ?></h1>
          <p class="price"><?php echo number_format($product['price'], 2); ?> zł</p>
          <p class="description"><?php echo htmlspecialchars($product['description']); ?></p>
          <button class="button add-to-cart" onclick="addToCart('<?php echo htmlspecialchars($product['id']); ?>')">Dodaj do koszyka</button>
        </div>
      <?php else: ?>
        <div class="detail-info">
          <p>Produkt nie znaleziony.</p>
        </div>
      <?php endif; ?>
    </div>

    <div class="reviews">
      <h2>Recenzje</h2>
      <?php if ($product && $reviews): ?>
        <?php foreach ($reviews as $review): ?>
          <div class="review cart-item">
            <div class="cart-item-info">
              <span class="cart-item-name reviewer"><?php echo htmlspecialchars($review['reviewer']); ?></span>
              <span class="cart-item-price review-text"><?php echo htmlspecialchars($review['text']); ?></span>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>

      <?php if ($product): ?>
        <div class="review-form">
          <?php if ($komunikat_recenzji): ?>
            <p class="<?php echo strpos($komunikat_recenzji, 'Błąd') === false && strpos($komunikat_recenzji, 'Musisz') === false && strpos($komunikat_recenzji, 'pusta') === false ? 'message' : 'error'; ?>">
              <?php echo htmlspecialchars($komunikat_recenzji); ?>
            </p>
          <?php endif; ?>
          <form method="POST">
            <label for="review_text">Dodaj swoją recenzję:</label>
            <textarea id="review_text" name="review_text" rows="4" required placeholder="Wpisz swoją opinię..."></textarea>
            <button type="submit">Wyślij recenzję</button>
          </form>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <script>
    const product = <?php echo json_encode($product); ?>;
    let cart = <?php echo json_encode($cart); ?>;

    function addToCart(productId) {
      if (product && product.id === productId) {
        cart.push(product);
        saveCartToCookie();
        alert(`${product.name} został dodany do koszyka!`);
      } else {
        console.error('Produkt nie znaleziony lub błędne ID:', productId);
      }
    }

    function saveCartToCookie() {
      try {
        const cartJson = JSON.stringify(cart);
        document.cookie = `cart=${encodeURIComponent(cartJson)}; path=/; max-age=${60 * 60 * 24 * 30}`;
      } catch (e) {
        console.error('Błąd zapisu koszyka do ciasteczka:', e);
      }
    }

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

    const detailImage = document.querySelector('.detail-image');
    if (detailImage) {
      detailImage.addEventListener('mousemove', (e) => {
        const rect = detailImage.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        const centerX = rect.width / 2;
        const centerY = rect.height / 2;
        const offsetX = (x - centerX) / centerX;
        const offsetY = (y - centerY) / centerY;

        detailImage.style.setProperty('--cursor-x', `${x}px`);
        detailImage.style.setProperty('--cursor-y', `${y}px`);
        detailImage.style.setProperty('--shadow-x', `${offsetX * 10}px`);
        detailImage.style.setProperty('--shadow-y', `${offsetY * 10}px`);
      });

      detailImage.addEventListener('mouseleave', () => {
        detailImage.style.setProperty('--cursor-x', '50%');
        detailImage.style.setProperty('--cursor-y', '50%');
        detailImage.style.setProperty('--shadow-x', '0px');
        detailImage.style.setProperty('--shadow-y', '0px');
      });
    }
  </script>
</body>
</html>