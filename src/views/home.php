<div class="container-fluid">
  <div class="row">
    <nav class="col-sm-3 col-md-2 d-none d-sm-block bg-light sidebar mt-3">
      <ul class="nav nav-pills flex-column">
        <li class="nav-item">
          <a class="nav-link active" href="#">Übersicht <span class="sr-only">(current)</span></a>
        </li>
      </ul>
    </nav>

    <main class="col-sm-9 ml-sm-auto col-md-10 pt-3" role="main">
      <h1>Übersicht</h1>
      <hr />
      <section class="row text-center placeholders pl-3 pr-3">
          <div class="list-group ">
            <?php
            if(empty($actions)) :
            ?>
            <em>Zur Zeit sind keine Produkte verliehen!</em>
            <?php
            else:
                foreach($actions as $action): ?>
                <a href="/product/return/<?= $action->get('product')->getId() ?>" class="list-group-item">
                    <?= $action->get('product')->get('name') ?> von <?= is_null($action->get('customer')->get('name')) ? $action->get('customer')->get('internal_id') : $action->get('customer')->get('name') ?> <small>seit <?= ago(strtotime($action->get('rentDate'))) ?></small>
                </a>
            <?php
                endforeach;
            endif;
            ?>
        </div>
      </section>
    </main>
  </div>
</div>