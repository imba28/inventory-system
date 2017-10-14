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
                <a class="nav-link" href="/products/return">Produkt zurücknehmen</a>
              </li>
          </ul>
        </nav>
        <main class="col-sm-9 ml-sm-auto col-md-10 pt-3" role="main">
          <h1>Produkte</h1>
          <hr />
          <section class="row text-center placeholders">
            <?php foreach($products as $product): ?>
            <div class="card" style="width: 20%">
                <img class="card-img-top" src="http://via.placeholder.com/100x100" alt="Card image cap">
                <div class="card-body">
                    <?php if($product->isAvailable()): ?>
                        <span class="badge badge-success">Verfügbar</span>
                    <?php else: ?>
                        <span class="badge badge-danger">Verliehen</span>
                    <?php endif; ?>
                    <h4 class="card-title"><?= $product->get('name') ?></h4>
                    <p class="card-text">
                        <?= $product->get('details') ?>
                    </p>
                    <a href="/product/<?= $product->get('id') ?>" class="btn btn-primary">Details</a>
                </div>
            </div>
            <?php endforeach;?>
          </section>
        </main>
    </div>
</div>