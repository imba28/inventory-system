<div class="container-fluid">
    <div class="row">
        <nav class="col-sm-3 col-md-2 d-none d-sm-block bg-light sidebar pt-3">
            <ul class="nav nav-pills flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="/inventur">Übersicht</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/inventur/registered">Vorhandene Produkte</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="/inventur/missing">Fehlende Produkte <span class="sr-only">(current)</span></a>
                </li>
            </ul>
        </nav>
        <main class="col-sm-9 ml-sm-auto col-md-10 pt-3" role="main">
            <h1>Inventur &#187; fehlende Produkte</h1>
            <hr />
            <section class="placeholders inventurMissing">
                <?php foreach($products as $product):
                    $available = $product->isAvailable();
                ?>
                    <article class="bg-light mb-2 p-3 rounded">
                        <div class="media inventurMissing__product">
                            <a href="/products/<?= $product->getId() ?>" class="d-block">
                                <img class="align-self-start mr-3 rounded" src="<?= $product->getFrontImage()->get('src') ?>" alt="Generic placeholder image">
                            </a>
                            <div class="media-body">
                                <?php if(!$available): ?>
                                    <span class="badge badge-warning">Verliehen</span>
                                <?php endif ?>
                                <a href="/products/<?= $product->getId() ?>" class="d-block">
                                    <h5 class="mt-0 mb-0"><?= $product->get('name') ?> <small><?= $product->get('type') ?></small></h5>
                                </a>
                                <h6>Inventarnummer <?= $product->get('invNr') ?></h6>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </section>
        </main>
    </div>
</div>