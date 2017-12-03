<div class="container-fluid">
  <div class="row">
    <nav class="col-sm-3 col-md-2 d-none d-sm-block bg-light sidebar pt-3">
        <ul class="nav nav-pills flex-column">
            <li class="nav-item">
                <a class="nav-link" href="/products/add">Produkt einpflegen</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/products/rent">Produkt verleihen</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="/products/return">Produkt zurücknehmen</a>
            </li>
        </ul>
    </nav>
    <main class="col-sm-9 ml-sm-auto col-md-10 pt-3" role="main">
      <h1>Produkte &#187; Verleih</h1>
      <hr />
      <div class="row">
      <section class="text-center pl-3 pr-3 col-sm-12 col-lg-6">
          <h3>Momentan verliehen</h3>
          <div class="list-group">
            <?php
            if(empty($actions)) :
            ?>
            <em>Zur Zeit sind keine Produkte verliehen!</em>
            <?php
            else:
                foreach($actions as $action): ?>
                <div class="list-group-item">
                    <a href="/product/return/<?= $action->get('product')->getId() ?>"> <?= $action->get('product')->get('name') ?></a>
                    von <?= is_null($action->get('customer')->get('name')) ? $action->get('customer')->get('internal_id') : $action->get('customer')->get('name') ?> <small>seit <?= ago(strtotime($action->get('rentDate'))) ?></small>
                </div>
            <?php
                endforeach;
            endif;
            ?>
        </div>
        </section>
        <section class="col-sm-12 col-lg-6">
            <h3>Beliebte Produkte</h3>
            <?php foreach($productsArray as $p): ?>
            <div class="list-group-item">
                <a href="/product/<?= $p['product']->getId() ?>">
                    <?= $p['product']->get('name') ?>
                </a> wurde <b><?= $p['frequency'] ?></b> mal verliehen.
            </div>
            <?php endforeach ?>
        </section>
    </div>
    </main>
  </div>
</div>