<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tarvikkeet - Tietokantaohjelma</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container">
        <div id="mainView">
            <header class="page-header">
                <h1>Tarvikkeet</h1>
            </header>

            <div class="top-actions">
                <button class="button button--primary" onclick="addAccessory()">Lisää tarvike</button>
                <div class="filter-field">
                    <label for="accessoryFilter">Suodata</label>
                    <input type="text" id="accessoryFilter" placeholder="Etsi tarvikkeen nimellä" oninput="filterAccessories()">
                </div>
            </div>

            <div class="table-container">
                <table class="data-table" id="accessoryTable">
                    <thead>
                        <tr>
                            <th>Nimi</th>
                            <th>Merkki</th>
                            <th>Toimittaja</th>
                            <th>Varastossa</th>
                            <th>ALV</th>
                            <th>Kokonaishinta</th>
                            <th>Toiminnot</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="detailsView" class="hidden">
            <div class="details-view-header">
                <button class="button button--ghost back-button" onclick="backToMain()">← Takaisin</button>
                <h1 id="detailsTitle">Tarvike</h1>
            </div>

            <div class="details-content">
                <div class="details-card" id="accessoryInfoView">
                    <h3>Tarvikkeen tiedot</h3>
                    <div class="details-row"><span>Nimi</span><span id="viewName"></span></div>
                    <div class="details-row"><span>Merkki</span><span id="viewBrand"></span></div>
                    <div class="details-row"><span>Toimittaja</span><span id="viewSupplier"></span></div>
                    <div class="details-row"><span>Yksikkö</span><span id="viewUnit"></span></div>
                    <div class="details-row"><span>Varastossa</span><span id="viewStock"></span></div>
                    <div class="details-row"><span>ALV</span><span id="viewVat"></span></div>
                    <div class="details-row"><span>Hankintahinta</span><span id="viewPurchasePrice"></span></div>
                    <div class="details-row"><span>ALV-osuus</span><span id="viewVatAmount"></span></div>
                    <div class="details-row"><span>Kokonaishinta</span><span id="viewTotalPrice"></span></div>
                </div>

                <div class="details-card" id="accessoryInfoEdit">
                    <h3>Muokkaa tarviketta</h3>
                    <div class="details-row">
                        <label for="editName">Nimi</label>
                        <input type="text" id="editName">
                    </div>
                    <div class="details-row">
                        <label for="editBrand">Merkki</label>
                        <input type="text" id="editBrand">
                    </div>
                    <div class="details-row">
                        <label for="editSupplier">Toimittaja</label>
                        <select id="editSupplier">
                            <option value="">Valitse toimittaja</option>
                        </select>
                    </div>
                    <div class="details-row">
                        <label for="editUnit">Yksikkö</label>
                        <input type="text" id="editUnit" placeholder="esim. kpl, m, kg">
                    </div>
                    <div class="details-row">
                        <label for="editStock">Varastossa</label>
                        <input type="number" id="editStock" min="0">
                    </div>
                    <div class="details-row">
                        <label for="editVat">ALV</label>
                        <select id="editVat">
                            <option value="10">10%</option>
                            <option value="14">14%</option>
                            <option value="24">24%</option>
                            <option value="25.5">25,5%</option>
                        </select>
                    </div>
                    <div class="details-row">
                        <label for="editPurchasePrice">Hankintahinta (€)</label>
                        <input type="number" id="editPurchasePrice" min="0" step="0.01">
                    </div>
                </div>

                <div class="details-actions">
                    <button class="button button--primary" id="saveAccessoryBtn" onclick="saveAccessory()">Tallenna</button>
                    <button class="button button--danger" id="deleteAccessoryBtn" onclick="deleteAccessory()" style="display: none;">Poista</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let accessories = [];
        let suppliers = [];
        let activeAccessoryId = null;
        let editMode = false;

        async function init() {
            try {
                const response = await fetch('methods/tarvikkeet_methods.php');
                const data = await response.json();
                if (!data.success) {
                    alert('Datan haku epäonnistui: ' + (data.error || ''));
                    return;
                }
                accessories = data.accessories;
                suppliers = data.suppliers;
                renderAccessoryRows();
            } catch (e) {
                console.error(e);
            }
        }

        init();

        function formatCurrency(value) {
            return `${parseFloat(value).toFixed(2).replace('.', ',')} €`;
        }

        function renderAccessoryRows() {
            const tbody = document.querySelector('#accessoryTable tbody');
            tbody.innerHTML = '';
            const filter = document.getElementById('accessoryFilter').value.toLowerCase();

            accessories
                .filter(item => (item.nimi || '').toLowerCase().includes(filter))
                .forEach(item => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${item.nimi || '-'}</td>
                        <td>${item.merkki || '-'}</td>
                        <td>${item.toimittaja_nimi || '-'}</td>
                        <td>${item.varastossa}</td>
                        <td>${item.alv_prosentti}%</td>
                        <td>${formatCurrency(item.kokonaishinta)}</td>
                        <td class="actions-cell">
                            <button class="button button--secondary" onclick="showAccessory(${item.tarvike_id})">Näytä</button>
                            <button class="button button--ghost" onclick="editAccessory(${item.tarvike_id})">Muokkaa</button>
                        </td>
                    `;
                    tbody.appendChild(row);
                });
        }

        function filterAccessories() {
            renderAccessoryRows();
        }

        function switchToDetailsView(mode) {
            document.getElementById('mainView').classList.add('hidden');
            document.getElementById('detailsView').classList.remove('hidden');
            editMode = mode === 'edit';
            document.getElementById('accessoryInfoView').classList.toggle('hidden', editMode);
            document.getElementById('accessoryInfoEdit').classList.toggle('hidden', !editMode);
            document.getElementById('saveAccessoryBtn').style.display = editMode ? 'inline-flex' : 'none';
            document.getElementById('deleteAccessoryBtn').style.display = (editMode && activeAccessoryId) ? 'inline-flex' : 'none';
        }

        function backToMain() {
            document.getElementById('mainView').classList.remove('hidden');
            document.getElementById('detailsView').classList.add('hidden');
            activeAccessoryId = null;
        }

        function populateSupplierDropdown(selectedId) {
            const select = document.getElementById('editSupplier');
            select.innerHTML = '<option value="">Valitse toimittaja</option>';
            suppliers.forEach(s => {
                const option = document.createElement('option');
                option.value = s.toimittaja_id;
                option.textContent = s.nimi;
                if (Number(s.toimittaja_id) === Number(selectedId)) {
                    option.selected = true;
                }
                select.appendChild(option);
            });
        }

        function showAccessory(id) {
            const item = accessories.find(a => Number(a.tarvike_id) === Number(id));
            if (!item) return;
            activeAccessoryId = id;
            switchToDetailsView('view');
            document.getElementById('detailsTitle').textContent = `Tarvike: ${item.nimi}`;
            document.getElementById('viewName').textContent = item.nimi || '-';
            document.getElementById('viewBrand').textContent = item.merkki || '-';
            document.getElementById('viewSupplier').textContent = item.toimittaja_nimi || '-';
            document.getElementById('viewUnit').textContent = item.yksikko || '-';
            document.getElementById('viewStock').textContent = item.varastossa;
            document.getElementById('viewVat').textContent = `${item.alv_prosentti}%`;
            document.getElementById('viewPurchasePrice').textContent = formatCurrency(item.hankintahinta);
            const vatAmount = item.hankintahinta * (item.alv_prosentti / 100);
            document.getElementById('viewVatAmount').textContent = formatCurrency(vatAmount);
            document.getElementById('viewTotalPrice').textContent = formatCurrency(item.kokonaishinta);
        }

        function editAccessory(id) {
            const item = accessories.find(a => Number(a.tarvike_id) === Number(id));
            if (!item) return;
            activeAccessoryId = id;
            switchToDetailsView('edit');
            document.getElementById('detailsTitle').textContent = `Muokkaa tarviketta: ${item.nimi}`;
            document.getElementById('editName').value = item.nimi || '';
            document.getElementById('editBrand').value = item.merkki || '';
            document.getElementById('editUnit').value = item.yksikko || '';
            document.getElementById('editStock').value = item.varastossa;
            document.getElementById('editVat').value = item.alv_prosentti;
            document.getElementById('editPurchasePrice').value = item.hankintahinta;
            populateSupplierDropdown(item.toimittaja_id);
        }

        function addAccessory() {
            activeAccessoryId = null;
            switchToDetailsView('edit');
            document.getElementById('detailsTitle').textContent = 'Lisää uusi tarvike';
            document.getElementById('editName').value = '';
            document.getElementById('editBrand').value = '';
            document.getElementById('editUnit').value = '';
            document.getElementById('editStock').value = 0;
            document.getElementById('editVat').value = 24;
            document.getElementById('editPurchasePrice').value = '';
            populateSupplierDropdown('');
        }

        async function postAccessory(payload, method) {
            const response = await fetch('methods/tarvikkeet_methods.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ ...payload, real_method: method })
            });
            return await response.json();
        }

        async function deleteAccessory() {
            if (!activeAccessoryId) return;
            const item = accessories.find(a => Number(a.tarvike_id) === Number(activeAccessoryId));
            if (!confirm(`Haluatko varmasti poistaa tarvikkeen "${item?.nimi}"?`)) return;
            try {
                const result = await postAccessory({ tarvike_id: activeAccessoryId }, 'DELETE');
                if (result.success) {
                    window.location.reload();
                } else {
                    alert('Poisto epäonnistui.');
                }
            } catch (e) {
                console.error(e);
            }
        }

        async function saveAccessory() {
            const nimi = document.getElementById('editName').value.trim();
            const merkki = document.getElementById('editBrand').value.trim();
            const toimittaja_id = document.getElementById('editSupplier').value;
            const yksikko = document.getElementById('editUnit').value.trim();
            const varastossa = parseInt(document.getElementById('editStock').value, 10);
            const alv_prosentti = parseFloat(document.getElementById('editVat').value);
            const hankintahinta = parseFloat(document.getElementById('editPurchasePrice').value);

            if (!nimi || !toimittaja_id || isNaN(varastossa) || isNaN(hankintahinta)) {
                alert('Täytä kaikki pakolliset kentät (nimi, toimittaja, varastossa, hankintahinta).');
                return;
            }

            const payload = {
                nimi,
                merkki: merkki || null,
                toimittaja_id: parseInt(toimittaja_id),
                yksikko: yksikko || null,
                varastossa,
                alv_prosentti,
                hankintahinta
            };

            const method = activeAccessoryId ? 'PUT' : 'POST';
            if (activeAccessoryId) payload.tarvike_id = activeAccessoryId;

            try {
                const result = await postAccessory(payload, method);
                if (result.success) {
                    window.location.reload();
                } else {
                    alert('Tallennus epäonnistui.');
                }
            } catch (e) {
                console.error(e);
            }
        }
    </script>
    <script src="sort.js"></script>
    <script>makeSortable('accessoryTable');</script>
</body>
</html>
