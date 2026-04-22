<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toimittajat - Tietokantaohjelma</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container">

        <!-- Main view: table -->
        <div id="mainView">
            <header class="page-header">
                <h1>Toimittajat</h1>
            </header>

            <div class="top-actions">
                <button class="button button--primary admin-only" onclick="addSupplier()">Lisää uusi toimittaja</button>
                <button class="button button--secondary admin-only" onclick="showXmlView()">Lisää tarvikkeita</button>
                <div class="filter-field">
                    <label for="supplierFilter">Suodata</label>
                    <input type="text" id="supplierFilter" placeholder="Etsi toimittajan nimellä" oninput="filterSuppliers()">
                </div>
            </div>

            <div class="table-container">
                <table class="data-table" id="supplierTable">
                    <thead>
                        <tr>
                            <th>Nimi</th>
                            <th>Osoite</th>
                            <th>Tarvikkeita</th>
                            <th>Toiminnot</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Details view: show / edit supplier -->
        <div id="detailsView" class="hidden">
            <div class="details-view-header">
                <button class="button button--ghost back-button" onclick="backToMain()">← Takaisin</button>
                <h1 id="detailsTitle">Toimittaja</h1>
            </div>

            <div class="details-content">
                <div class="details-card" id="supplierInfoView">
                    <h3>Tiedot</h3>
                    <div class="details-row"><span>Nimi</span><span id="viewName"></span></div>
                    <div class="details-row"><span>Osoite</span><span id="viewAddress"></span></div>
                    <div class="details-row"><span>Tarvikkeita</span><span id="viewCount"></span></div>
                </div>

                <div class="details-card" id="supplierInfoEdit">
                    <h3>Muokkaa toimittajaa</h3>
                    <div class="details-row">
                        <label for="editName">Nimi</label>
                        <input type="text" id="editName">
                    </div>
                    <div class="details-row">
                        <label for="editAddress">Osoite</label>
                        <input type="text" id="editAddress">
                    </div>
                </div>

                <div class="details-card hidden" id="supplierProductsCard">
                    <h3>Toimitukset työkohteisiin</h3>
                    <div class="table-container">
                        <table class="data-table" id="productsTable">
                            <thead>
                                <tr>
                                    <th>Tarvike</th>
                                    <th>Työkohde</th>
                                    <th>Asiakas</th>
                                    <th>Määrä</th>
                                </tr>
                            </thead>
                            <tbody id="productsTableBody">
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="details-actions">
                    <button class="button button--primary" id="saveSupplierBtn" onclick="saveSupplier()" style="display: none;">Tallenna</button>
                    <button class="button button--danger" id="deleteSupplierBtn" onclick="deleteSupplier()" style="display: none;">Poista</button>
                </div>
            </div>
        </div>

        <!-- XML upload view -->
        <div id="xmlView" class="hidden">
            <div class="details-view-header">
                <button class="button button--ghost back-button" onclick="backToMain()">← Takaisin</button>
                <h1>Lisää tarvikkeita</h1>
            </div>

            <div class="details-content">
                <div class="details-card">
                    <h3>XML-tuonti</h3>
                    <div class="details-row">
                        <label for="xmlFile">Valitse XML-tiedosto</label>
                        <input type="file" id="xmlFile" accept=".xml">
                    </div>
                    <div class="details-row">
                        <span></span>
                        <button class="button button--primary" onclick="uploadXml()">Lataa XML</button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        let suppliers = [];
        let activeSupplierId = null;
        let editMode = false;

        async function init() {
            try {
                const response = await fetch('methods/toimittajat_methods.php');
                const data = await response.json();
                if (!data.success) {
                    alert('Datan haku epäonnistui');
                    return;
                }
                suppliers = data.suppliers;
                renderSupplierRows();
            } catch (e) {
                console.error(e);
            }
            applyRoleRestrictions();
        }

        init();

        function applyRoleRestrictions() {
            if (IS_ADMIN) return;
            document.querySelectorAll('.admin-only').forEach(el => el.style.display = 'none');
        }

        function renderSupplierRows() {
            const tbody = document.querySelector('#supplierTable tbody');
            tbody.innerHTML = '';
            const filter = document.getElementById('supplierFilter').value.toLowerCase();

            suppliers
                .filter(s => (s.nimi || '').toLowerCase().includes(filter))
                .forEach(s => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${s.nimi || '-'}</td>
                        <td>${s.osoite || '-'}</td>
                        <td>${s.tarvike_maara}</td>
                        <td class="actions-cell">
                            <button class="button button--secondary" onclick="showSupplier(${s.toimittaja_id})">Näytä</button>
                            <button class="button button--ghost admin-only" onclick="editSupplier(${s.toimittaja_id})">Muokkaa</button>
                        </td>
                    `;
                    tbody.appendChild(row);
                });
        }

        function filterSuppliers() {
            renderSupplierRows();
            applyRoleRestrictions();
        }

        function switchToDetailsView(mode) {
            document.getElementById('mainView').classList.add('hidden');
            document.getElementById('xmlView').classList.add('hidden');
            document.getElementById('detailsView').classList.remove('hidden');
            editMode = mode === 'edit';
            document.getElementById('supplierInfoView').classList.toggle('hidden', editMode);
            document.getElementById('supplierInfoEdit').classList.toggle('hidden', !editMode);
            document.getElementById('saveSupplierBtn').style.display = editMode ? 'inline-flex' : 'none';
            document.getElementById('deleteSupplierBtn').style.display = (editMode && activeSupplierId) ? 'inline-flex' : 'none';
        }

        function backToMain() {
            document.getElementById('detailsView').classList.add('hidden');
            document.getElementById('xmlView').classList.add('hidden');
            document.getElementById('mainView').classList.remove('hidden');
            activeSupplierId = null;
        }

        function showXmlView() {
            document.getElementById('mainView').classList.add('hidden');
            document.getElementById('detailsView').classList.add('hidden');
            document.getElementById('xmlView').classList.remove('hidden');
        }

        function showSupplier(id) {
            const s = suppliers.find(x => Number(x.toimittaja_id) === Number(id));
            if (!s) return;
            activeSupplierId = id;
            switchToDetailsView('view');
            document.getElementById('detailsTitle').textContent = `Toimittaja: ${s.nimi}`;
            document.getElementById('viewName').textContent = s.nimi || '-';
            document.getElementById('viewAddress').textContent = s.osoite || '-';
            document.getElementById('viewCount').textContent = s.tarvike_maara;
            loadProducts(id);
        }

        async function loadProducts(toimittajaId) {
            const card = document.getElementById('supplierProductsCard');
            const tbody = document.getElementById('productsTableBody');
            card.classList.add('hidden');
            tbody.innerHTML = '';
            try {
                const response = await fetch(`methods/toimittajat_methods.php?toimittaja_id=${toimittajaId}`);
                const data = await response.json();
                if (!data.success || !data.products.length) return;
                data.products.forEach(row => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${row.tarvike_nimi || '-'}</td>
                        <td>${row.kohde_nimi || '-'}</td>
                        <td>${row.asiakas_nimi || '-'}</td>
                        <td>${parseFloat(row.maara).toLocaleString('fi-FI')} ${row.yksikko || ''}</td>
                    `;
                    tbody.appendChild(tr);
                });
                card.classList.remove('hidden');
            } catch (e) {
                console.error(e);
            }
        }

        function editSupplier(id) {
            const s = suppliers.find(x => Number(x.toimittaja_id) === Number(id));
            if (!s) return;
            activeSupplierId = id;
            switchToDetailsView('edit');
            document.getElementById('detailsTitle').textContent = `Muokkaa toimittajaa: ${s.nimi}`;
            document.getElementById('editName').value = s.nimi || '';
            document.getElementById('editAddress').value = s.osoite || '';
        }

        function addSupplier() {
            activeSupplierId = null;
            switchToDetailsView('edit');
            document.getElementById('detailsTitle').textContent = 'Lisää uusi toimittaja';
            document.getElementById('editName').value = '';
            document.getElementById('editAddress').value = '';
        }

        async function postSupplier(payload, method) {
            const response = await fetch('methods/toimittajat_methods.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ ...payload, real_method: method })
            });
            return await response.json();
        }

        async function deleteSupplier() {
            if (!activeSupplierId) return;
            const s = suppliers.find(x => Number(x.toimittaja_id) === Number(activeSupplierId));
            if (!confirm(`Haluatko varmasti poistaa toimittajan "${s?.nimi}"?`)) return;
            try {
                const result = await postSupplier({ toimittaja_id: activeSupplierId }, 'DELETE');
                if (result.success) {
                    window.location.reload();
                } else {
                    alert('Poisto epäonnistui: ' + (result.error || 'Toimittajalla voi olla tarvikkeita.'));
                }
            } catch (e) {
                console.error(e);
            }
        }

        async function saveSupplier() {
            const nimi = document.getElementById('editName').value.trim();
            const osoite = document.getElementById('editAddress').value.trim();

            if (!nimi) {
                alert('Nimi on pakollinen.');
                return;
            }

            const payload = { nimi, osoite: osoite || null };
            const method = activeSupplierId ? 'PUT' : 'POST';
            if (activeSupplierId) payload.toimittaja_id = activeSupplierId;

            try {
                const result = await postSupplier(payload, method);
                if (result.success) {
                    window.location.reload();
                } else {
                    alert('Tallennus epäonnistui.');
                }
            } catch (e) {
                console.error(e);
            }
        }

        async function uploadXml() {
            const fileInput = document.getElementById('xmlFile');
            if (!fileInput.files || !fileInput.files.length) {
                alert('Valitse ensin XML-tiedosto.');
                return;
            }

            const formData = new FormData();
            formData.append('xmlFile', fileInput.files[0]);
            formData.append('real_method', 'XML_IMPORT');

            try {
                const response = await fetch('methods/tarvikkeet_methods.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (result.success) {
                    alert(
                        `XML tuotu onnistuneesti:\n` +
                        `${result.inserted} uutta tarviketta lisätty\n` +
                        `${result.updated} tarviketta päivitetty\n` +
                        `${result.unchanged} tarviketta muuttumatta`
                    );
                    fileInput.value = '';
                    backToMain();
                } else {
                    alert('XML-tuonti epäonnistui: ' + (result.error || 'Tuntematon virhe'));
                }
            } catch (e) {
                console.error(e);
                alert('Yhteysvirhe palvelimeen.');
            }
        }
    </script>
    <script src="sort.js"></script>
    <script>makeSortable('supplierTable');</script>
</body>
</html>
