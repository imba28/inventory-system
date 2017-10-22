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
            <h1>Produkt &#187; <?= $product->get('name') ?> &#187; Verleihen</h1>
            <a href="/products/<?= $product->get('id') ?>">Zurück</a>
            <hr>
            <!-- /.col-lg-3 -->
            <div class="row pl-3 pr-3">
                <p>
                    Das Produkt `<?= $product->get('name') ?>`(<?= $product->get('invNr') ?>) ist bereits verliehen.
                    <?php if(!is_null($action->get('expectedReturnDate'))): ?>
                    <small class="d-block mt-3">Es wird voraussichtlich am <?= date('d.m.Y', strtotime($action->get('expectedReturnDate'))) ?> wieder verfügbar sein.</small>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
</main>
</div>
</div>