<?php
require_once 'db_connect.php';

// Pobierz wszystkie produkty
$stmt = $pdo->query("SELECT * FROM products");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Pobierz koszyk z ciasteczek
$cart = isset($_COOKIE['cart']) ? json_decode($_COOKIE['cart'], true) : [];
if (!is_array($cart)) $cart = [];

// Sprawdź, czy użytkownik już widział powitanie
$showWelcome = !isset($_COOKIE['welcome_shown']) || $_COOKIE['welcome_shown'] !== 'true';
?>

<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sklep z perfumami</title>
  <link rel="stylesheet" href="css.css">
  <style>
    /* Ekran powitalny */
    .welcome-screen {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: #f8f9fa; /* Jasne tło w trybie jasnym jako baza */
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 2000;
      opacity: 0;
      transform: translateY(-100%);
      transition: opacity 0.5s ease-out, transform 0.8s ease-out;
    }

    .welcome-screen.active {
      opacity: 1;
      transform: translateY(0);
    }

    .welcome-screen::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: radial-gradient(circle at 20% 20%, rgba(255, 182, 193, 0.4), transparent 70%), /* Miętowy róż */
                  radial-gradient(circle at 80% 80%, rgba(173, 216, 230, 0.4), transparent 70%), /* Błękit */
                  radial-gradient(circle at 50% 50%, rgba(255, 245, 157, 0.4), transparent 90%); /* Jasna żółć */
      opacity: 1;
      pointer-events: none;
    }

    .welcome-screen h1 {
      font-size: 3.5rem;
      color: #2d2d2d;
      text-align: center;
      text-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
      font-family: 'Poppins', sans-serif;
      animation: pulse 2s infinite ease-in-out;
      position: relative;
      z-index: 1;
    }

    body.dark-mode .welcome-screen {
      background: #000000; /* Czarne tło w trybie ciemnym jako baza */
    }

    body.dark-mode .welcome-screen::after {
      background: radial-gradient(circle at 20% 20%, rgba(139, 0, 139, 0.15), transparent 60%),
                  radial-gradient(circle at 80% 80%, rgba(0, 0, 139, 0.15), transparent 60%),
                  radial-gradient(circle at 50% 50%, rgba(75, 0, 130, 0.15), transparent 80%);
    }

    body.dark-mode .welcome-screen h1 {
      color: #e0e0e0;
      text-shadow: 0 0 10px rgba(147, 112, 219, 0.3);
    }

    @keyframes pulse {
      0% { transform: scale(1); }
      50% { transform: scale(1.05); }
      100% { transform: scale(1); }
    }
  </style>
</head>
<body>
  <!-- Ekran powitalny – wyświetlany tylko przy pierwszym wejściu -->
  <?php if ($showWelcome): ?>
    <div class="welcome-screen" id="welcomeScreen">
      <h1>Ekskluzywna kolekcja perfum</h1>
    </div>
  <?php endif; ?>

  <header class="header">
    <h1>Ekskluzywna kolekcja perfum</h1>
    <nav class="navigation">
      <ul>
        <li><a href="#all" data-category="all">Wszystkie</a></li>
        <li><a href="#men" data-category="men">Męskie</a></li>
        <li><a href="#women" data-category="women">Damskie</a></li>
        <li><a href="#unisex" data-category="unisex">Unisex</a></li>
        <li class="separator"></li>
        <?php if (isset($_SESSION['user_id'])): ?>
          <?php if ($_SESSION['username'] === 'admin'): ?>
            <li><a href="add_perfume.php">Dodaj perfumy</a></li>
          <?php endif; ?>
          <li><a href="logout.php">Wyloguj się (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a></li>
        <?php else: ?>
          <li><a href="login.php">Zaloguj się</a></li>
          <li><a href="register.php">Zarejestruj się</a></li>
        <?php endif; ?>
      </ul>
    </nav>
    <div class="header-right">
      <button class="theme-toggle" aria-label="Przełącz tryb ciemny">
        <img src="dark.png" alt="Tryb ciemny" class="theme-icon">
      </button>
      <button class="hamburger" aria-label="Przełącz menu" onclick="toggleMenu()">☰</button>
    </div>
  </header>

  <main class="container">
    <section class="products" id="product-list">
      <?php foreach ($products as $product): ?>
        <article class="card" data-category="<?php echo htmlspecialchars($product['category']); ?>">
          <a href="perfume.php?id=<?php echo htmlspecialchars($product['id']); ?>" class="card-link">
            <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" loading="lazy">
            <h2><?php echo htmlspecialchars($product['name']); ?></h2>
            <p class="price"><?php echo number_format($product['price'], 2); ?> zł</p>
          </a>
          <button class="button" onclick="addToCart('<?php echo htmlspecialchars($product['id']); ?>')">Dodaj do koszyka</button>
        </article>
      <?php endforeach; ?>
    </section>

    <aside class="cart" id="cart">
      <h2>Twój koszyk (<span id="cart-items-count">0</span>)</h2>
      <ul id="cart-items"></ul>
      <div class="cart-total">
        <p>Razem: <span id="cart-total-amount">0</span> zł</p>
        <button class="button checkout-button">Zamów</button>
      </div>
    </aside>
  </main>

  <script>
    const products = <?php echo json_encode($products); ?>;
    let cart = <?php echo json_encode($cart); ?>;
    if (!Array.isArray(cart)) cart = [];
    console.log('Initial cart on index:', cart);

    window.onload = function() {
      updateCart();
      // Aktywuj ekran powitalny i ukryj po 2 sekundach, zapisując ciasteczko
      const welcomeScreen = document.getElementById('welcomeScreen');
      if (welcomeScreen) {
        welcomeScreen.classList.add('active');
        setTimeout(() => {
          welcomeScreen.classList.remove('active');
          document.cookie = 'welcome_shown=true; path=/; max-age=' + (60 * 60 * 24 * 30); // 30 dni
        }, 2000); // 2 sekundy
      }
    };

    function addToCart(productId) {
      const product = products.find(p => p.id === productId);
      cart.push(product);
      updateCart();
      saveCartToCookie();
      console.log('Cart after adding on index:', cart);
    }

    function updateCart() {
      const cartItems = document.getElementById("cart-items");
      cartItems.innerHTML = "";
      cart.forEach((item, index) => {
        const cartItem = document.createElement("li");
        cartItem.classList.add("cart-item");
        cartItem.innerHTML = `
          <div class="cart-item-info">
            <span class="cart-item-name">${item.name}</span>
            <span class="cart-item-price">${item.price} zł</span>
          </div>
          <button class="remove-button" data-index="${index}">✕</button>
        `;
        cartItems.appendChild(cartItem);
      });

      document.querySelectorAll('.remove-button').forEach(button => {
        button.addEventListener('click', (e) => {
          const index = parseInt(e.target.getAttribute('data-index'));
          removeFromCart(index);
        });
      });

      document.getElementById("cart-items-count").textContent = cart.length;
      const total = cart.reduce((sum, item) => sum + parseFloat(item.price), 0);
      document.getElementById("cart-total-amount").textContent = total.toFixed(2);
    }

    function removeFromCart(index) {
      cart.splice(index, 1);
      updateCart();
      saveCartToCookie();
    }

    function saveCartToCookie() {
      try {
        const cartJson = JSON.stringify(cart);
        document.cookie = `cart=${encodeURIComponent(cartJson)}; path=/; max-age=${60 * 60 * 24 * 30}`;
        console.log('Cookie saved on index:', document.cookie);
      } catch (e) {
        console.error('Error saving cart to cookie on index:', e);
      }
    }

    function toggleMenu() {
      const nav = document.querySelector('.navigation');
      nav.classList.toggle('active');
    }

    document.querySelectorAll('.navigation a').forEach(link => {
      link.addEventListener('click', (e) => {
        const href = link.getAttribute('href');
        if (href !== 'add_perfume.php' && href !== 'login.php' && href !== 'logout.php' && href !== 'register.php') {
          e.preventDefault();
          const category = link.getAttribute('data-category');
          const cards = document.querySelectorAll('.card');
          cards.forEach(card => {
            const cardCategory = card.getAttribute('data-category');
            if (category === 'all' || cardCategory === category) {
              card.style.display = 'block';
            } else {
              card.style.display = 'none';
            }
          });
        }
      });
    });

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

    document.querySelectorAll('.card').forEach(card => {
      card.addEventListener('mousemove', (e) => {
        const rect = card.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        const centerX = rect.width / 2;
        const centerY = rect.height / 2;
        const offsetX = (x - centerX) / centerX;
        const offsetY = (y - centerY) / centerY;

        card.style.setProperty('--cursor-x', `${x}px`);
        card.style.setProperty('--cursor-y', `${y}px`);
        card.style.setProperty('--shadow-x', `${offsetX * 10}px`);
        card.style.setProperty('--shadow-y', `${offsetY * 10}px`);
      });

      card.addEventListener('mouseleave', () => {
        card.style.setProperty('--cursor-x', '50%');
        card.style.setProperty('--cursor-y', '50%');
        card.style.setProperty('--shadow-x', '0px');
        card.style.setProperty('--shadow-y', '0px');
      });
    });
  </script>
</body>
</html>