<div class="container-fluid">
  <div class="row">
    <nav class="col-sm-3 col-md-2 d-none d-sm-block bg-light sidebar mt-3">
      <ul class="nav nav-pills flex-column">
        <li class="nav-item">
          <a class="nav-link active" href="#">Übersicht <span class="sr-only">(current)</span></a>
        </li>
      </ul>
    </nav>

    <main class="col-sm-9 ml-sm-auto col-md-10 pt-3" role="main">
      <h1>Übersicht</h1>
      <hr />
      <section class="row text-center placeholders pl-3 pr-3">
          <div class="list-group ">
            <?php foreach($actions as $action): ?>
                <a href="/product/return/<?= $action->get('product')->getId() ?>" class="list-group-item">
                    <?= $action->get('product')->get('name') ?> von <?= $action->get('customer')->get('name') ?> <small>seit <?= date('d.m.Y', strtotime($action->get('rentDate'))) ?></small>
                </a>
            <?php endforeach; ?>
        </div>
      </section>
    </main>
  </div>
</div>