<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SIMUS.MJN - Inicio</title>
  <style>
    body { 
      margin:0;
      font-family:monospace, sans-serif;
      background: #fdfdff;
    }
    header  { /*encabezado*/
      background:#a095f3;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1) ;
      padding: 15px  40px;
      display:  flex;
      align-items: center;
      justify-content: space-between;
  } 
    header h1 { 
      color:#6d5fe0;
      margin: 0;
    } 

    nav a {
      margin-left: 20px;
      text-decoration: none;
      color: #771c1c;
      font-weight: bold;
    }

    nav a:hover {
      color: #6d5fe0;
    }

    .hero {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 50px;
      background: #f7f7fb;
    }

    .hero-text {
      flex: 1;
      padding-right: 30px;
    }

    .hero-text h2 {
      color: #6d5fe0;
      font-size: 22px;
      margin-bottom: 15px;
    }

    .hero-text p {
      font-size: 15px;
      color: #444;
      line-height: 1.6;
    }

    .hero-image {
      flex: 1;
      height: 400px;
      background: url('imgs/fondojuego.png'); /* reservado para la imagen de los juegos */
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 18px;
      color: #333;
      box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }

    .section-mascota {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 50px;
      background: white;
    }

    .mascota-img {
      flex: 0.5;
      height: 500px;
      background: url('imgs/bremi.png') no-repeat center center; /* reservado para de la mascota */
      background-size: cover;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 18px;
      color: #333;
      box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }

    .mascota-text  {
      flex: 1;
      text-align: center;
      padding-left: 30px;
  }

    .mascota-text h3 {
      color: #6d5fe0;
      font-size: 22px;
      margin-bottom: 10px;
    }

    .mascota-text p {
      font-size: 15px;
      color: #444;
      margin-bottom: 20px;
    }

    .btn-container {
      display: flex;
      justify-content: center;
      gap: 15px;
    }

    .btn {
      padding: 12px 20px;
      background: #8a7cf7;
      color: white;
      border-radius: 8px;
      text-decoration: none;
      font-weight: bold;
      transition: background 0.3s ease;
    }

    .btn:hover {
      background: #6d5fe0;
    }
  </style>
</head>
<body>

  <header>
    <h1>SIMUS.MJN</h1>
    <nav>
      <a href="todoenuno.php">Tienda</a>
      <a href="FAQ.php">Contacto</a>
      <a href="nosotros.html">Nosotros</a>
      <a href="login.php">login</a>
    </nav>
  </header>

  <section class="hero">
    <div class="hero-text">
      <h2>¿QUÉ PUEDE HACER SIMUS.MJN?</h2>
      <p>
        Es una página web que busca dar a conocer softwares interactivos, divertidos, inclusivos y accesibles. Nuestro software más emblemático contiene una serie de actividades con las que se puede interactuar de forma que
        ayude a la comunicación, juegos interactivos básicos de aprendizaje en niños de 7 a 10 años.
      </p>
      <p>
        El cursor puede moverse con la mano o con el seguimiento de la nariz.

        Las acciones de clic se realizan con un gesto (índice y pulgar) o con un parpadeo.

        Una tabla de diálogos interactiva permite expresar emociones, necesidades y frases cotidianas a través de audio.

        Además, el sistema integra juegos educativos que refuerzan memoria, lógica y asociaciones, logrando que aprender y comunicarse sea también una experiencia divertida.

      </p>
    </div>
    <div class="hero-image">
      
    </div>
  </section>

  <section class="section-mascota">
    <div class="mascota-img">
    </div>
    <div class="mascota-text">
      <h3>¡HOLA! SOY BREMI</h3>
      <p>
        Soy la mascota que te ayudará a navegar por la página web de SIMUS.MJN
      </p>
      <div class="btn-container">
        <a href=" tienda.php" class="btn">Tienda de juegos</a>
      </div>
    </div>
  </section>
  <script>
  // ver si usuario inicio sesion
  function checkAuth() {
      const token = localStorage.getItem('token_sesion');
      
      if(!token) {
          alert('Debes iniciar sesión para acceder a esta página');
          window.location.href = 'login.php';
          return false;
      }
      return true;
  }

  // Cerrar sesión
  function logout() {
      if(confirm('seguro de cerrar sesion?')) {
          localStorage.removeItem('token_sesion');
          localStorage.removeItem('nombre');
          window.location.href = 'login.php';
      }
  }

  // autenticacion del token cuando pasa de pestañas
  document.addEventListener('DOMContentLoaded', function() {
      if (!checkAuth()) {
          return;
      }

      const login = document.querySelector('nav a[href="login.php"]');
      if (login) {
          login.textContent = 'Cerrar Sesión';
          login.href = '#';
          login.onclick = function(e) {
              e.preventDefault();
              logout();
          };
      }
  });
  </script>
</body>
</html>
