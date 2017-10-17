<?php
$available = $product->isAvailable();
?>
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
            <h1>Produkte &#187; <?= $product->get('name') ?></h1>
            <a href="/products">Zurück</a>
            <hr>
            <!-- /.col-lg-3 -->
            <div class="row">
                <div class="col col-6">
                    <div class="card">
                        <?php if($available): ?>
                            <span class="badge badge-success">Verfügbar</span>
                        <?php else: ?>
                            <span class="badge badge-danger">Verliehen</span>
                        <?php endif; ?>
                        <div id="carouselExampleControls" class="carousel slide" data-ride="carousel">
                            <div class="carousel-inner">
                                <div class="carousel-item active">
                                    <img class="d-block w-100" src="http://via.placeholder.com/900x400" alt="First slide">
                                </div>
                                <div class="carousel-item">
                                    <img class="d-block w-100" src="http://via.placeholder.com/900x400" alt="Second slide">
                                </div>
                                <div class="carousel-item">
                                    <img class="d-block w-100" src="http://via.placeholder.com/900x400" alt="Third slide">
                                </div>
                            </div>
                            <a class="carousel-control-prev" href="#carouselExampleControls" role="button" data-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="sr-only">Previous</span>
                            </a>
                            <a class="carousel-control-next" href="#carouselExampleControls" role="button" data-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="sr-only">Next</span>
                            </a>
                        </div>
                        <div class="card-body">
                            <h3 class="card-title"><?= $product->get('name') ?></h3>
                            <h5 class="card-type font-italic"><?= $product->get('type') ?></h5>
                            <p class="card-text">
                                <?= $product->get('description') ?>
                            </p>
                            <?php if(!is_null($product->get('note'))): ?>
                            <div class="alert alert-info" role="alert">
                                <strong>Anmerkung:</strong> <?= $product->get('note') ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="btn-group" role="group" aria-label="Basic example">
                            <?php if($available): ?>
                                <a href="/product/rent/<?= $product->get('id')?>" class="btn btn-success">Ausleihen</a>
                            <?php else: ?>
                                <a href="/product/return/<?= $product->get('id')?>" class="btn btn-success">Zurückgeben</a>
                            <?php endif; ?>
                            <a href="/product/claim/<?= $product->get('id')?>" class="btn btn-default">Reservieren</a>
                            <a href="/product/edit/<?= $product->get('id')?>" class="btn btn-primary">Bearbeiten</a>
                        </div>
                    </div>
                </div>
                <div class="col col-6">
                    <!-- /.card -->
                    <div class="card card-outline-secondary">
                        <div class="card-header">
                            Historie
                        </div>
                        <div class="card-body">
                            <?php foreach($rentHistory as $rentAction):
                                if(!$rentAction->isProductReturned()) :
                            ?>
                                <small class="text-muted font-weight-bold">
                                    Ausgeliehen von <?= $rentAction->get('customer')->get('name') ?> seit dem <?= date('d.m.Y', strtotime($rentAction->get('rentDate'))) ?>
                                </small>
                            <?php else: ?>
                                <small class="text-muted">
                                    Ausgeliehen von <?= $rentAction->get('customer')->get('name') ?> vom <?= date('d.m.Y', strtotime($rentAction->get('rentDate'))) ?> bis zum <?= date('d.m.Y', strtotime($rentAction->get('returnDate'))) ?>
                                </small>
                            <?php endif; ?>
                            <hr>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
</div>
</div>