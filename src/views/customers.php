<div class="container-fluid">
    <div class="row">
        <nav class="col-sm-3 col-md-2 d-none d-sm-block bg-light sidebar pt-3">
          <ul class="nav nav-pills flex-column">

          </ul>
        </nav>
        <main class="col-sm-9 ml-sm-auto col-md-10 pt-3" role="main">
          <h1>Kunden</h1>
          <section class="row text-center placeholders">
            <?php foreach($customers as $customer): ?>
            <div class="card" style="width: 10rem;">
                <img class="card-img-top" src="http://via.placeholder.com/100x100" alt="Card image cap">
                <div class="card-body">
                    <h4 class="card-title"><?= $customer->get('name') ?></h4>
                    <p class="card-text">
                        <?= $customer->get('internal_id') ?>
                    </p>
                    <a href="/customers/<?= $customer->get('id') ?>" class="btn btn-primary">Details</a>
                </div>
            </div>
            <?php endforeach;?>
          </section>
        </main>
    </div>
</div>