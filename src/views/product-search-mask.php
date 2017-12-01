<div class="container-fluid">
    <div class="row">
        <nav class="col-sm-3 col-md-2 d-none d-sm-block bg-light sidebar pt-3">
            <ul class="nav nav-pills flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="/products/add">Produkt einpflegen</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="/products/rent">Produkt verleihen</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/products/return">Produkt zurücknehmen</a>
                </li>
            </ul>
        </nav>
        <main class="col-sm-9 ml-sm-auto col-md-10 pt-3" role="main">
            <h1>Produkt suchen</h1>
            <hr />
            <section class="placeholders">
                <form method="post">
                    <div class="row mb-4">
                        <div class="col-sm-6 col-lg-4">
                            <input type="hidden" name="submit" value="1" />
                            <input type="text" class="form-control" name="search" placeholder="Nach Name, Inventarnummer oder Kategorie suchen..." value="<?= isset($string) ? $string : '' ?>"/>
                        </div>
                    </div>
                </form>
            </section>
            <?php if(isset($products)): ?>
            <section class="text-center placeholders pl-3 pr-3 row">
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
                        <?php
                        if(isset($buttons)) {
                            foreach($buttons as $button) {
                                $href = str_replace('__id__', $product->getId(), $button->get('href'));
                                echo "<a href=\"{$href}\" class=\"btn btn-{$button->get('style')}\">{$button->get('text')}</a>";
                            }
                        }
                        else { ?>
                        <a href="/product/<?= $product->get('id') ?>" class="btn btn-primary">Details</a>
                        <?php
                        }
                        ?>
                    </div>
                </div>
                <?php endforeach ?>
            </section>
            <?php endif ?>
        </main>
    </div>
</div>