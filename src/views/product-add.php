<div class="container-fluid">
    <div class="row">
        <nav class="col-sm-3 col-md-2 d-none d-sm-block bg-light sidebar pt-3">
            <ul class="nav nav-pills flex-column">
                <li class="nav-item">
                    <a class="nav-link active" href="/products/add">Produkt einpflegen</a>
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
            <h1>Produkte &#187; <?= $request->getParam('name') ?></h1>
            <a href="/products">Zurück</a>
            <hr>
            <!-- /.col-lg-3 -->
            <div class="row">
                <form class="ml-4 col-md-6" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="exampleInputEmail1">Name</label>
                        <input type="text" class="form-control" placeholder="Produktname eingeben" name="name" value="<?= $request->getParam('name') ?>">
                    </div>
                    <div class="form-group">
                        <label for="exampleInputEmail1">Inventarnummer</label>
                        <input type="text" class="form-control" placeholder="Inventarnummer eingeben" name="invNr" value="<?= $request->getParam('invNr') ?>">
                    </div>
                    <div class="form-group">
                        <label for="exampleInputPassword1">Type</label>
                        <input type="text" class="form-control" placeholder="Type" name="type" value="<?= $request->getParam('type') ?>">
                    </div>
                    <div class="form-group">
                        <label for="exampleInputPassword1">Beschreibung</label>
                        <textarea class="form-control" name="description" placeholder="Beschreibung eingeben"><?= $request->getParam('description') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="exampleInputPassword1">Anmerkung</label>
                        <textarea class="form-control" name="note" placeholder="Anmerkung eingeben"><?= $request->getParam('note') ?></textarea>
                    </div>
                    <div class="form-group">
                        <ul class="productimages row">
                            <?php
                            if(isset($product)):
                                foreach($product->getImages() as $image): ?>
                                <li class="col col-md-3">
                                    <button type="button" class="btn btn-danger btn-sm">x</button>
                                    <img src="<?= $image->get('src') ?>" alt="<?= $image->get('title') ?>" class="d-block w-100">
                                    <p>
                                        <?= $image->get('title') ?>
                                    </p>
                                </li>
                            <?php
                                endforeach;
                            endif;
                            ?>
                        </ul>
                        <input type="file" name="add-productImage[]" multiple>
                    </div>
                    <button type="submit" name="submit" class="btn btn-primary">Erstellen</button>
                </form>
            </div>
        </div>
    </div>
</main>
</div>
</div>