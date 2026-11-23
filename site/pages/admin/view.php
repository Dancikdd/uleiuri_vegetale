<?php
/**
 * Șablonul HTML (View) pentru Panoul de Administrare.
 */
?>

<section class="pagina admin-dashboard">
    <header class="admin-heading">
        <div>
            <span class="admin-eyebrow">Panou de control</span>
            <h1>Administrare</h1>
            <p>Gestionează catalogul de produse și categoriile magazinului.</p>
        </div>
        <nav class="admin-shortcuts" aria-label="Scurtături administrare">
            <a href="#admin-categorii">Categorii</a>
            <a href="#admin-produse">Produse</a>
            <a href="#admin-solicitari">Solicitări</a>
            <a href="#admin-utilizatori">Utilizatori</a>
        </nav>
    </header>

    <?php if ($flash): ?>
        <p class="mesaj <?= ($flash['type'] ?? '') === 'error' ? 'mesaj-eroare' : 'mesaj-succes' ?>" role="status">
            <?= htmlspecialchars($flash['text'] ?? '', ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php endif; ?>
    <?php if ($generalError): ?>
        <p class="mesaj mesaj-eroare" role="alert"><?= htmlspecialchars($generalError, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if (!$eroareCitire): ?>
        <div class="admin-stats" aria-label="Rezumatul platformei">
            <article class="admin-stat"><span>Categorii</span><strong><?= $numarCategorii ?></strong></article>
            <article class="admin-stat"><span>Total produse</span><strong><?= $numarProduse ?></strong></article>
            <article class="admin-stat"><span>Produse active</span><strong><?= $numarActive ?></strong></article>
            <article class="admin-stat"><span>Solicitări</span><strong><?= $numarSolicitari ?></strong></article>
            <article class="admin-stat"><span>Utilizatori</span><strong><?= $numarUtilizatori ?></strong></article>
        </div>
    <?php endif; ?>

    <?php if ($eroareCitire): ?>
        <p class="mesaj mesaj-eroare" role="alert"><?= htmlspecialchars($eroareCitire, ENT_QUOTES, 'UTF-8') ?></p>
    <?php else: ?>
        <div class="admin-sections">
            <!-- ── Secțiunea Categorii ── -->
            <section class="admin-panel" id="admin-categorii">
                <div class="admin-panel-heading">
                    <div><span class="admin-eyebrow">Organizare catalog</span><h2>Categorii</h2></div>
                    <div class="admin-panel-tools"><span class="admin-count"><?= $numarCategorii ?> total</span><button class="admin-add-link" type="button" data-open-dialog="category-dialog">+ Adaugă</button></div>
                </div>
                <div class="admin-search-row">
                    <label class="sr-only" for="category-search">Caută categorii</label>
                    <input class="admin-search" id="category-search" type="search" placeholder="Caută categorie..." data-table-search="categories-table">
                    <span class="admin-search-count" data-search-count="categories-table"><?= $numarCategorii ?> categorii</span>
                </div>
                <?php if (!$categorii): ?><p class="admin-empty">Nu există categorii încă.</p><?php endif; ?>
                <div class="tabel-responsiv">
                    <table class="admin-table" id="categories-table">
                        <thead><tr><th>Denumire</th><th>Elemente</th><th></th></tr></thead>
                        <tbody>
                            <?php foreach ($categorii as $categorie): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($categorie['name'], ENT_QUOTES, 'UTF-8') ?></strong><small><?= htmlspecialchars($categorie['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></small></td>
                                    <td><span class="admin-count"><?= (int) $categorie['item_count'] ?></span></td>
                                    <td>
                                        <div class="admin-actions">
                                            <a class="admin-text-link" href="administrare.php?edit_category=<?= (int) $categorie['id'] ?>#admin-categorii">Modifică</a>
                                            <form action="administrare.php#admin-categorii" method="post" onsubmit="return confirm('Ștergi această categorie?')">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(token_csrf(), ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="action" value="category_delete">
                                                <input type="hidden" name="category_id" value="<?= (int) $categorie['id'] ?>">
                                                <button class="admin-action-button action-delete" type="submit">Șterge</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <p class="admin-empty" data-search-empty="categories-table" hidden>Nu am găsit categorii potrivite.</p>
                <dialog class="admin-modal" id="category-dialog" aria-labelledby="category-dialog-title" data-auto-open="<?= $formType === 'category' ? 'true' : 'false' ?>" data-reset-url="administrare.php#admin-categorii">
                    <div class="admin-modal-heading">
                        <div><span class="admin-eyebrow">Organizare catalog</span><h2 id="category-dialog-title"><?= $categoryForm['id'] ? 'Modifică categoria' : 'Adaugă o categorie' ?></h2></div>
                        <button class="admin-modal-close" type="button" data-close-dialog aria-label="Închide fereastra">&times;</button>
                    </div>
                    <form class="formular admin-form" action="administrare.php" method="post">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(token_csrf(), ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="action" value="category_save">
                        <input type="hidden" name="id" value="<?= (int) $categoryForm['id'] ?>">
                        <?php if (isset($formErrors['id']) && $formType === 'category'): ?><small class="mesaj-eroare"><?= htmlspecialchars($formErrors['id'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?>
                        <label for="category_name">Denumire</label>
                        <input type="text" id="category_name" name="name" required maxlength="100" value="<?= htmlspecialchars($categoryForm['name'], ENT_QUOTES, 'UTF-8') ?>">
                        <?php if (isset($formErrors['name']) && $formType === 'category'): ?><small class="mesaj-eroare"><?= htmlspecialchars($formErrors['name'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?>
                        <label for="category_description">Descriere</label>
                        <textarea id="category_description" name="description" rows="3"><?= htmlspecialchars($categoryForm['description'], ENT_QUOTES, 'UTF-8') ?></textarea>
                        <?php if (isset($formErrors['description']) && $formType === 'category'): ?><small class="mesaj-eroare"><?= htmlspecialchars($formErrors['description'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?>
                        <div class="admin-form-actions"><button class="buton" type="submit"><?= $categoryForm['id'] ? 'Salvează modificările' : 'Adaugă categoria' ?></button><?php if ($categoryForm['id']): ?><a href="administrare.php#admin-categorii">Anulează</a><?php else: ?><button class="admin-cancel-button" type="button" data-close-dialog>Anulează</button><?php endif; ?></div>
                    </form>
                </dialog>
            </section>

            <!-- ── Secțiunea Produse ── -->
            <section class="admin-panel admin-products" id="admin-produse">
                <div class="admin-panel-heading">
                    <div><span class="admin-eyebrow">Catalog magazin</span><h2>Produse</h2></div>
                    <div class="admin-panel-tools"><span class="admin-count"><?= $numarProduse ?> total</span><button class="admin-add-link" type="button" data-open-dialog="item-dialog">+ Adaugă</button></div>
                </div>
                <div class="admin-search-row">
                    <label class="sr-only" for="item-search">Caută produse</label>
                    <input class="admin-search" id="item-search" type="search" placeholder="Caută după produs sau categorie..." data-table-search="items-table">
                    <span class="admin-search-count" data-search-count="items-table"><?= $numarProduse ?> produse</span>
                </div>
                <div class="tabel-responsiv">
                    <table class="admin-table" id="items-table">
                        <thead><tr><th>Produs</th><th>Categorie</th><th>Preț</th><th>Stoc</th><th>Statut</th><th>Acțiuni</th></tr></thead>
                        <tbody>
                            <?php foreach ($produse as $produs): ?>
                                <tr>
                                    <td>
                                        <div class="admin-product-ident">
                                            <img src="../<?= htmlspecialchars(cale_imagine_produs($produs['image_path'] ?? null, $produs['name']), ENT_QUOTES, 'UTF-8') ?>" alt="" loading="lazy">
                                            <div><strong><?= htmlspecialchars($produs['name'], ENT_QUOTES, 'UTF-8') ?></strong><small><?= htmlspecialchars($produs['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></small></div>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($produs['category_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td class="admin-price"><?= number_format((float) $produs['price'], 2) ?> lei</td>
                                    <td><?= (int) $produs['quantity_available'] ?></td>
                                    <td><span class="admin-status <?= (int) $produs['is_active'] === 1 ? 'este-activ' : 'este-inactiv' ?>"><?= (int) $produs['is_active'] === 1 ? 'Activ' : 'Inactiv' ?></span></td>
                                    <td>
                                        <div class="admin-actions">
                                            <a class="admin-text-link" href="administrare.php?edit_item=<?= (int) $produs['id'] ?>#admin-produse">Modifică</a>
                                            <form action="administrare.php#admin-produse" method="post">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(token_csrf(), ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="action" value="item_toggle">
                                                <input type="hidden" name="item_id" value="<?= (int) $produs['id'] ?>">
                                                <input type="hidden" name="is_active" value="<?= (int) $produs['is_active'] === 1 ? 0 : 1 ?>">
                                                <button class="admin-action-button" type="submit"><?= (int) $produs['is_active'] === 1 ? 'Dezactivează' : 'Activează' ?></button>
                                            </form>
                                            <form action="administrare.php#admin-produse" method="post">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(token_csrf(), ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="action" value="item_delete">
                                                <input type="hidden" name="item_id" value="<?= (int) $produs['id'] ?>">
                                                <button class="admin-action-button action-delete" type="submit">Șterge</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php if (!$produse): ?><p class="admin-empty">Nu există produse încă.</p><?php endif; ?>
                </div>
                <p class="admin-empty" data-search-empty="items-table" hidden>Nu am găsit produse potrivite.</p>

                <dialog class="admin-modal admin-modal-large" id="item-dialog" aria-labelledby="item-dialog-title" data-auto-open="<?= $formType === 'item' ? 'true' : 'false' ?>" data-reset-url="administrare.php#admin-produse">
                    <div class="admin-modal-heading">
                        <div><span class="admin-eyebrow">Catalog magazin</span><h2 id="item-dialog-title"><?= $itemForm['id'] ? 'Modifică produsul' : 'Adaugă un produs' ?></h2></div>
                        <button class="admin-modal-close" type="button" data-close-dialog aria-label="Închide fereastra">&times;</button>
                    </div>
                    <?php if (!$categorii): ?><p class="admin-empty">Adaugă mai întâi o categorie.</p><?php endif; ?>
                    <form class="formular admin-form" action="administrare.php#admin-produse" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(token_csrf(), ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="action" value="item_save">
                        <input type="hidden" name="id" value="<?= (int) $itemForm['id'] ?>">
                        <div class="admin-form-grid">
                            <div><label for="item_name">Denumire</label><input type="text" id="item_name" name="name" required maxlength="150" value="<?= htmlspecialchars($itemForm['name'], ENT_QUOTES, 'UTF-8') ?>"><?php if (isset($formErrors['name']) && $formType === 'item'): ?><small class="mesaj-eroare"><?= htmlspecialchars($formErrors['name'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?></div>
                            <div><label for="category_id">Categorie</label><select id="category_id" name="category_id" required><option value="">Alege categoria</option><?php foreach ($categorii as $categorie): ?><option value="<?= (int) $categorie['id'] ?>" <?= (int) $itemForm['category_id'] === (int) $categorie['id'] ? 'selected' : '' ?>><?= htmlspecialchars($categorie['name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select><?php if (isset($formErrors['category_id']) && $formType === 'item'): ?><small class="mesaj-eroare"><?= htmlspecialchars($formErrors['category_id'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?></div>
                            <div><label for="price">Preț (lei)</label><input type="number" id="price" name="price" min="0" max="99999999.99" step="0.01" required value="<?= htmlspecialchars((string) $itemForm['price'], ENT_QUOTES, 'UTF-8') ?>"><?php if (isset($formErrors['price']) && $formType === 'item'): ?><small class="mesaj-eroare"><?= htmlspecialchars($formErrors['price'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?></div>
                            <div><label for="quantity_available">Cantitate disponibilă</label><input type="number" id="quantity_available" name="quantity_available" min="0" step="1" required value="<?= htmlspecialchars((string) $itemForm['quantity_available'], ENT_QUOTES, 'UTF-8') ?>"><?php if (isset($formErrors['quantity_available']) && $formType === 'item'): ?><small class="mesaj-eroare"><?= htmlspecialchars($formErrors['quantity_available'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?></div>
                            <div class="admin-description-field"><label for="item_description">Descriere</label><textarea id="item_description" name="description" rows="3"><?= htmlspecialchars($itemForm['description'], ENT_QUOTES, 'UTF-8') ?></textarea><?php if (isset($formErrors['description']) && $formType === 'item'): ?><small class="mesaj-eroare"><?= htmlspecialchars($formErrors['description'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?></div>
                            <div class="admin-image-field"><label for="item_image">Imagine produs (JPG, PNG sau WebP, maximum 2 MB)</label><input type="file" id="item_image" name="image" accept="image/jpeg,image/png,image/webp"><?php if (isset($formErrors['image']) && $formType === 'item'): ?><small class="mesaj-eroare"><?= htmlspecialchars($formErrors['image'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?><?php if (!empty($itemForm['image_path'])): ?><span class="admin-current-image">Imaginea curentă</span><img class="admin-image-preview" src="../<?= htmlspecialchars(cale_imagine_produs($itemForm['image_path'], $itemForm['name']), ENT_QUOTES, 'UTF-8') ?>" alt="Imagine curentă: <?= htmlspecialchars($itemForm['name'], ENT_QUOTES, 'UTF-8') ?>"><?php endif; ?></div>
                        </div>
                        <?php if (isset($formErrors['id']) && $formType === 'item'): ?><small class="mesaj-eroare"><?= htmlspecialchars($formErrors['id'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?>
                        <div class="admin-form-actions"><button class="buton" type="submit" <?= !$categorii ? 'disabled' : '' ?>><?= $itemForm['id'] ? 'Salvează modificările' : 'Adaugă produsul' ?></button><?php if ($itemForm['id']): ?><a href="administrare.php#admin-produse">Anulează</a><?php else: ?><button class="admin-cancel-button" type="button" data-close-dialog>Anulează</button><?php endif; ?></div>
                    </form>
                </dialog>
            </section>

            <!-- ── Secțiunea Solicitări (Comenzi) ── -->
            <section class="admin-panel admin-data-panel" id="admin-solicitari">
                <div class="admin-panel-heading">
                    <div><span class="admin-eyebrow">Comenzi primite</span><h2>Toate comenzile</h2></div>
                    <span class="admin-count"><?= $numarSolicitari ?> total</span>
                </div>
                <form class="admin-export-form" action="export_csv.php" method="get" target="_blank">
                    <fieldset class="admin-export-fieldset">
                        <legend class="sr-only">Exportă raport CSV</legend>
                        <div class="admin-export-fields">
                            <div class="admin-export-field">
                                <label for="export_data_start">De la</label>
                                <input type="date" id="export_data_start" name="data_start" required>
                            </div>
                            <div class="admin-export-field">
                                <label for="export_data_sfarsit">Până la</label>
                                <input type="date" id="export_data_sfarsit" name="data_sfarsit" required>
                            </div>
                            <button class="admin-export-button" type="submit">📥 Exportă CSV</button>
                        </div>
                    </fieldset>
                </form>
                <?php if ($eroareAdminData): ?>
                    <p class="mesaj mesaj-eroare" role="alert"><?= htmlspecialchars($eroareAdminData, ENT_QUOTES, 'UTF-8') ?></p>
                <?php elseif (!$solicitariAdmin): ?>
                    <p class="admin-empty">Nu există comenzi înregistrate.</p>
                <?php else: ?>
                    <?php $eticheteStatus = ['pending' => 'În așteptare', 'approved' => 'Aprobată', 'rejected' => 'Respinsă', 'cancelled' => 'Anulată']; ?>
                    <div class="tabel-responsiv">
                        <table class="admin-table admin-request-table">
                            <thead><tr><th>Comandă</th><th>Utilizator</th><th>Produse comandate</th><th>Total</th><th>Statut</th><th>Acțiuni</th></tr></thead>
                            <tbody>
                                <?php foreach ($solicitariAdmin as $solicitare): ?>
                                    <tr>
                                        <td><strong>#<?= (int) $solicitare['id'] ?></strong><small><?= htmlspecialchars($solicitare['created_at'], ENT_QUOTES, 'UTF-8') ?></small></td>
                                        <td><strong><?= htmlspecialchars($solicitare['user_name'], ENT_QUOTES, 'UTF-8') ?></strong><small><?= htmlspecialchars($solicitare['user_email'], ENT_QUOTES, 'UTF-8') ?></small></td>
                                        <td><?= htmlspecialchars($solicitare['products'] ?? 'Fără produse', ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="admin-price"><?= number_format((float) $solicitare['total'], 2) ?> lei</td>
                                        <td><span class="admin-status status-<?= htmlspecialchars($solicitare['status'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($eticheteStatus[$solicitare['status']] ?? $solicitare['status'], ENT_QUOTES, 'UTF-8') ?></span></td>
                                        <td>
                                            <?php if ($solicitare['status'] === 'pending'): ?>
                                                <div class="admin-actions">
                                                    <form action="administrare.php#admin-solicitari" method="post">
                                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(token_csrf(), ENT_QUOTES, 'UTF-8') ?>">
                                                        <input type="hidden" name="action" value="request_status">
                                                        <input type="hidden" name="request_id" value="<?= (int) $solicitare['id'] ?>">
                                                        <input type="hidden" name="status" value="approved">
                                                        <button class="admin-action-button" type="submit">Aprobă</button>
                                                    </form>
                                                    <form action="administrare.php#admin-solicitari" method="post">
                                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(token_csrf(), ENT_QUOTES, 'UTF-8') ?>">
                                                        <input type="hidden" name="action" value="request_status">
                                                        <input type="hidden" name="request_id" value="<?= (int) $solicitare['id'] ?>">
                                                        <input type="hidden" name="status" value="rejected">
                                                        <button class="admin-action-button action-delete" type="submit">Respinge</button>
                                                    </form>
                                                    <form action="administrare.php#admin-solicitari" method="post" onsubmit="return confirm('Anulezi această comandă?')">
                                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(token_csrf(), ENT_QUOTES, 'UTF-8') ?>">
                                                        <input type="hidden" name="action" value="request_status">
                                                        <input type="hidden" name="request_id" value="<?= (int) $solicitare['id'] ?>">
                                                        <input type="hidden" name="status" value="cancelled">
                                                        <button class="admin-action-button action-delete" type="submit">Anulează</button>
                                                    </form>
                                                </div>
                                            <?php endif; ?>
                                            <form action="administrare.php#admin-solicitari" method="post" onsubmit="return confirm('Ștergi definitiv această comandă și produsele ei?')">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(token_csrf(), ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="action" value="request_delete">
                                                <input type="hidden" name="request_id" value="<?= (int) $solicitare['id'] ?>">
                                                <button class="admin-action-button action-delete" type="submit">Șterge comanda</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>

            <!-- ── Secțiunea Utilizatori ── -->
            <section class="admin-panel admin-data-panel" id="admin-utilizatori">
                <div class="admin-panel-heading">
                    <div><span class="admin-eyebrow">Conturi înregistrate</span><h2>Toți utilizatorii</h2></div>
                    <span class="admin-count"><?= count($utilizatori) ?> total</span>
                </div>
                <?php if ($eroareAdminData): ?>
                    <p class="mesaj mesaj-eroare" role="alert"><?= htmlspecialchars($eroareAdminData, ENT_QUOTES, 'UTF-8') ?></p>
                <?php elseif (!$utilizatori): ?>
                    <p class="admin-empty">Nu există utilizatori înregistrați.</p>
                <?php else: ?>
                    <div class="tabel-responsiv">
                        <table class="admin-table admin-users-table">
                            <thead><tr><th>Utilizator</th><th>Rol</th><th>Comenzi</th><th>Cont creat</th></tr></thead>
                            <tbody>
                                <?php foreach ($utilizatori as $utilizator): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($utilizator['name'], ENT_QUOTES, 'UTF-8') ?></strong><small><?= htmlspecialchars($utilizator['email'], ENT_QUOTES, 'UTF-8') ?></small></td>
                                        <td><?= htmlspecialchars(ROLURI[$utilizator['role']] ?? $utilizator['role'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><span class="admin-count"><?= (int) $utilizator['request_count'] ?></span></td>
                                        <td><?= htmlspecialchars($utilizator['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    <?php endif; ?>
</section>

<script src="../js/admin-dashboard.js" defer></script>
