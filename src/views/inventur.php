<?php
$total = $inventur->getTotalCount();
$missing = count($inventur->getMissingItems());
$percentage = floor((($total - $missing) / $total) * 100);
?>
<div class="container-fluid">
    <div class="row">
        <nav class="col-sm-3 col-md-2 d-none d-sm-block bg-light sidebar pt-3">
            <ul class="nav nav-pills flex-column">
                <li class="nav-item">
                    <a class="nav-link active" href="#">Übersicht <span class="sr-only">(current)</span></a>
                </li>
                <?php if($inventur->isStarted()): ?>
                <li class="nav-item">
                    <a class="nav-link" href="/inventur/registered">Vorhandene Produkte</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/inventur/missing">Fehlende Produkte</a>
                </li>
                <?php endif ?>
            </ul>
        </nav>
        <main class="col-sm-9 ml-sm-auto col-md-10 pt-3" role="main">
            <h1>Inventur</h1>
            <hr />
            <section class="placeholders">
                <?php if(!is_null($lastInventur)): ?>
                <div class="mb-3">
                    <small>
                        Letzte Inventur durchgeführt vor <?= ago($lastInventur->get('finishDate')) ?> von <strong><?= $lastInventur->get('user')->get('name') ?></strong>
                    </small>
                </div>
                <?php
                endif;

                if($inventur->isStarted()):
                ?>
                <form method="post">
                    <?php
                    if($inventur->isReady()):
                    ?>
                    <button class="btn btn-primary d-block mt-2" name="action" value="end">
                        Inventur beenden
                    </button>
                    <?php
                    else:
                    ?>
                    <div class="row mb-4">
                        <div class="col-sm-6 col-lg-2">
                            <input type="hidden" name="action" value="scan_product" />
                            <input type="text" class="form-control" name="invNr" placeholder="Inventar Nummer eingeben..."/>
                        </div>
                    </div>
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: <?= $percentage ?>%" aria-valuenow="<?= $percentage ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <strong>Du hast <?= $total - $missing ?> von <?= $total ?></strong> Produkten erfasst.
                    <?php
                    endif;
                    ?>
                </form>
                <?php
                else:
                ?>
                <form method="post">
                    <button class="btn btn-primary d-block" name="action" value="start">
                        Inventur starten
                    </button>
                </form>
                <?php
                endif;
                ?>
            </section>
        </main>
    </div>
</div>