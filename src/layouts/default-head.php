<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>MMT Verleihmanager</title>

    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="/src/css/bootstrap.min.css" rel="stylesheet">
    <link href="/src/css/bootstrap.datepicker.min.css" rel="stylesheet">
    <link href="/src/css/style.css" rel="stylesheet">

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
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
              <div class="login display-flex text-light mr-2">
                  <?php if($this->isUserSignedIn()): ?>
                    <div class="dropdown">
                      <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Hallo <?= $this->getCurrentUser()->get('name') ?>!
                      </button>
                      <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <!--<a class="dropdown-item" href="#">Action</a>
                        <a class="dropdown-item" href="#">Another action</a>
                        <a class="dropdown-item" href="#">Something else here</a>-->
                        <form class="dropdown-item" action="/logout" method="post">
                            <button class="btn btn-danger w-100">Logout</button>
                        </form>
                      </div>
                    </div>
              <?php else: ?>
                  <a class="btn btn-primary my-2 mr-2 my-sm-0" href="/login">Login</a>
              <?php endif; ?>
              </div>
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