<div class="container-fluid">
    <div class="row">
        <nav class="col-sm-3 col-md-2 d-none d-sm-block bg-light sidebar mt-3">
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
            <div class="row">
                <form class="ml-4" method='post'>
                    <div class="form-group">
                        <label for="exampleInputEmail1">Name</label>
                        <input type="text" class="form-control" readonly value="<?= $product->get('name') ?> (<?= $product->get('invNr') ?>)">
                    </div>
                    <div class="form-group">
                        <label for="exampleInputPassword1">FHS Nummer</label>
                        <input type="text" class="form-control" placeholder="FHS Nummer eingeben" name='internal_id' value="<?= $request->getParam('internal_id') ?>">
                    </div>
                    <div class="form-group">
                        <label for="exampleInputPassword1">Voraussichtliches Rückgabedatum</label>
                        <input type="text" class="form-control" name='expectedReturnDate' placeholder="Rückgabedatum eingeben" value="<?= $request->getParam('expectedReturnDate')  ?>">
                    </div>

                    <button type="submit" name="submit" class="btn btn-primary">Leihen</button>
                </form>
            </div>
        </div>
    </div>
</main>
</div>
</div>