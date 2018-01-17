<div class="container-fluid">
    <div class="row">
        <nav class="col-sm-3 col-md-2 bg-light sidebar pt-3">
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
          <ul class="nav nav-pills flex-column mt-4">
              <li class="nav-item">
                  Kategorien
              </li>
              <?php
                if(isset($categories)): foreach($categories as $c): ?>
                  <li class="nav-item">
                    <a class="nav-link" href="/products/category/<?= urlencode($c['name']) ?>"><?= $c['name'] ?></a>
                  </li>
              <?php endforeach; endif; ?>
          </ul>
        </nav>
        <main class="col-sm-9 ml-sm-auto col-md-10 pt-3" role="main">
          <h1>Produkte (<?= $totals ?>)</h1>
          <hr />
          <section class="row text-center placeholders pl-3 pr-3">
            <?php foreach($products as $product): ?>
            <div class="card col-lg-3 col-md-4 col-sm-6">
                <img class="card-img-top" src="<?= $product->getFrontImage()->get('src') ?>" alt="Card image cap">
                <div class="card-body">
                    <?php if($product->isAvailable()): ?>
                        <span class="badge badge-success">Verfügbar</span>
                    <?php else: ?>
                        <span class="badge badge-danger">Verliehen</span>
                    <?php endif; ?>
                    <h4 class="card-title"><?= $product->get('name') ?></h4>
                    <p class="card-text">
                        <?= $product->get('type') ?>
                    </p>
                    <a href="/product/<?= $product->get('id') ?>" class="btn btn-primary">Details</a>
                </div>
            </div>
            <?php endforeach;?>
          </section>
          <section>
              <?php
                if(isset($paginator)) echo $paginator->render();
              ?>
          </section>
        </main>
    </div>
</div>