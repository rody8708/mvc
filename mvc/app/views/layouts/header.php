<!DOCTYPE html>
<html lang="en" <?= !empty($darkMode) ? 'class="dark-mode"' : '' ?>>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MVC</title>

  <!-- 🧩 Estilos principales -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
  
  <!-- 🎨 Estilos propios -->
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/dark-mode.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/main.css">
</head>
<body <?= !empty($darkMode) ? 'class="dark-mode"' : '' ?>>

