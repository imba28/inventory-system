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
            <h1>Produkte &#187; <?= $product->get('name') ?> &#187; Verleihen</h1>
            <a href="/products/<?= $product->get('id') ?>">Zurück</a>
            <hr>
            <!-- /.col-lg-3 -->
            <div class="row">
                <form class="ml-4" method='post'>
                    <div class="form-group">
                        <label for="exampleInputPassword1">Rückgabedatum</label>
                        <input type="text" class="form-control datepicker" name='returnDate' placeholder="Rückgabedatum eingeben (leer = jetzt)" value="<?= $request->getParam('expectedReturnDate')  ?>">
                    </div>
                    <button type="submit" name="submit" class="btn btn-primary">Zurückgeben</button>
                </form>
            </div>
        </div>
    </div>
</main>
</div>
</div>