<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>MMT Verleihmanager</title>

    <link href="/src/css/bootstrap.min.css" rel="stylesheet">
    <link href="/src/css/bootstrap.datepicker.min.css" rel="stylesheet">
    <link href="/src/css/style.css" rel="stylesheet">

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="/src/js/bootstrap.min.js"></script>
    <script src="/src/js/bootstrap.datepicker.min.js"></script>
    <script src="/src/js/main.js"></script>
    <script src="/src/js/tinymce.min.js"></script>
  </head>
  <body>
      <nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
          <a class="navbar-brand" href="/"><?= \App\Configuration::get('site_name')?></a>
          <button class="navbar-toggler d-lg-none" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
              <span class="navbar-toggler-icon"></span>
          </button>

          <div class="collapse navbar-collapse" id="navbarsExampleDefault">
              <ul class="navbar-nav mr-auto">
                  <?= \App\Menu::getItems() ?>
              </ul>
              <form class="form-inline mt-2 mt-md-0" method="post" action="/products/search">
                  <input class="form-control mr-sm-2" type="text" placeholder="Produkte suchen" aria-label="Suchen" name="search_string">
                  <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Suchen</button>
              </form>
          </div>
      </nav>
      <div style="height:55px"></div>
      <div class="system-status">
        <?= \App\System::getStatus() ?>
      </div>