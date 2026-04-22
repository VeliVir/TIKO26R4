<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asiakkaat - Tietokantaohjelma</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container">
        <div id="mainView">
            <header class="page-header">
                <h1>Asiakkaat</h1>
            </header>

            <div class="top-actions">
                <button type="button" class="button button--primary admin-only" onclick="addCustomer()">Lisää uusi asiakas</button>
                <div class="filter-field">
                    <label for="customerFilter">Suodata</label>
                    <input type="text" id="customerFilter" placeholder="Etsi asiakkaan nimellä" oninput="filterCustomers()">
                </div>
            </div>

            <div class="table-container">
                <table class="data-table" id="customerTable">
                    <thead>
                        <tr>
                            <th>Nimi</th>
                            <th>Puhelin</th>
                            <th>Sähköposti</th>
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
                <h1 id="detailsTitle">Asiakas</h1>
            </div>

            <div class="details-content">
                <div class="details-card" id="customerInfoView">
                    <h3>Asiakastiedot</h3>
                    <div class="details-row"><span>Nimi</span><span id="viewName"></span></div>
                    <div class="details-row"><span>Puhelin</span><span id="viewPhone"></span></div>
                    <div class="details-row"><span>Sähköposti</span><span id="viewEmail"></span></div>
                    <div class="details-row"><span>Osoite</span><span id="viewAddress"></span></div>
                </div>

                <div class="details-card" id="customerInfoEdit">
                    <h3>Muokkaa asiakasta</h3>
                    <div class="details-row"><label for="editFirstName">Etunimi</label><input type="text" id="editFirstName"></div>
                    <div class="details-row"><label for="editLastName">Sukunimi</label><input type="text" id="editLastName"></div>
                    <div class="details-row"><label for="editPhone">Puhelin</label><input type="text" id="editPhone"></div>
                    <div class="details-row"><label for="editEmail">Sähköposti</label><input type="email" id="editEmail"></div>
                    <div class="details-row"><label for="editAddress">Osoite</label><input type="text" id="editAddress"></div>
                </div>

                <div class="details-card full-width">
                    <h3>Sijainnit</h3>
                    <div class="table-container small">
                        <table class="data-table" id="locationTable">
                            <thead>
                                <tr>
                                    <th>Nimi</th>
                                    <th>Osoite</th>
                                    <th>Toiminnot</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                    <div class="location-actions" id="locationActions">
                        <button class="button button--secondary admin-only" onclick="addLocation()">Lisää työkohde</button>
                    </div>
                </div>

                <div class="details-card full-width hidden" id="addLocationCard">
                    <h3>Lisää uusi työkohde</h3>
                    <div class="details-row"><label for="newLocationName">Nimi</label><input type="text" id="newLocationName" placeholder="Kohteen nimi"></div>
                    <div class="details-row"><label for="newLocationAddress">Osoite</label><input type="text" id="newLocationAddress" placeholder="Kohteen osoite"></div>
                    <div class="location-form-actions">
                        <button class="button button--primary" onclick="saveNewLocation()">Tallenna työkohde</button>
                        <button class="button button--ghost" onclick="cancelAddLocation()">Peruuta</button>
                    </div>
                </div>

                <div class="details-actions" id="detailsActions">
                    <button class="button button--primary admin-only" id="saveCustomerBtn" onclick="saveCustomer()">Tallenna</button>
                    <button class="button button--danger admin-only" id="deleteCustomerBtn" onclick="deleteCustomer()" style="display: none;">Poista</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let customers = [];
        let locations = [];
        let activeCustomerId = null;
        let editMode = false;

        async function init() {
            try {
                const response = await fetch('methods/asiakkaat_methods.php');
                const data = await response.json();
                if (!data.success) {
                    alert('Datan haku epäonnistui: ' + (data.error));
                    return;
                }
                customers = data.customers;
                locations = data.locations;
                renderCustomerRows();
            } catch (e) {
                console.error(e);
                alert('Yhteysvirhe');
            }
            applyRoleRestrictions();
        }

        init();

        function applyRoleRestrictions() {
            if (IS_ADMIN) return;
            document.querySelectorAll('.admin-only').forEach(el => el.style.display = 'none');
        }

        function renderCustomerRows() {
            const tbody = document.querySelector('#customerTable tbody');
            tbody.innerHTML = '';
            const filter = document.getElementById('customerFilter').value.toLowerCase();

            customers
                .filter(c => c.nimi.toLowerCase().includes(filter))
                .forEach(c => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${c.nimi}</td>
                        <td>${c.puhelinnro || '-'}</td>
                        <td>${c.sahkoposti || '-'}</td>
                        <td class="actions-cell">
                            <button class="button button--secondary" onclick="showCustomer(${c.asiakas_id})">Näytä</button>
                            <button class="button button--ghost admin-only" onclick="editCustomer(${c.asiakas_id})">Muokkaa</button>
                        </td>
                    `;
                    tbody.appendChild(row);
                });
        }

        function filterCustomers() {
            renderCustomerRows();
            applyRoleRestrictions();
        }

        function switchToDetailsView(mode) {
            document.getElementById('mainView').classList.add('hidden');
            document.getElementById('detailsView').classList.remove('hidden');
            editMode = mode === 'edit';
            document.getElementById('customerInfoView').classList.toggle('hidden', editMode);
            document.getElementById('customerInfoEdit').classList.toggle('hidden', !editMode);
            document.getElementById('saveCustomerBtn').style.display = editMode ? 'inline-flex' : 'none';
            document.getElementById('deleteCustomerBtn').style.display = (editMode && activeCustomerId) ? 'inline-flex' : 'none';
            document.getElementById('locationActions').style.display = editMode ? 'flex' : 'none';
            document.getElementById('addLocationCard').classList.add('hidden');
        }

        function backToMain() {
            document.getElementById('mainView').classList.remove('hidden');
            document.getElementById('detailsView').classList.add('hidden');
            activeCustomerId = null;
        }

        function showCustomer(id) {
            const c = customers.find(x => x.asiakas_id == id);
            if (!c) return;
            activeCustomerId = id;
            switchToDetailsView('view');
            document.getElementById('detailsTitle').textContent = `Asiakas: ${c.nimi}`;
            document.getElementById('viewName').textContent = c.nimi;
            document.getElementById('viewPhone').textContent = c.puhelinnro || '-';
            document.getElementById('viewEmail').textContent = c.sahkoposti || '-';
            document.getElementById('viewAddress').textContent = c.osoite || '-';
            renderLocations(locations);
        }

        function editCustomer(id) {
            const c = customers.find(x => x.asiakas_id == id);
            if (!c) return;
            activeCustomerId = id;
            switchToDetailsView('edit');
            document.getElementById('detailsTitle').textContent = `Muokkaa asiakasta: ${c.nimi}`;
            document.getElementById('editFirstName').value = c.etunimi || '';
            document.getElementById('editLastName').value = c.sukunimi || '';
            document.getElementById('editPhone').value = c.puhelinnro || '';
            document.getElementById('editEmail').value = c.sahkoposti || '';
            document.getElementById('editAddress').value = c.osoite || '';
            renderLocations(locations);
        }

        function addCustomer() {
            activeCustomerId = null;
            switchToDetailsView('edit');
            document.getElementById('detailsTitle').textContent = 'Lisää uusi asiakas';
            document.getElementById('editFirstName').value = '';
            document.getElementById('editLastName').value = '';
            document.getElementById('editPhone').value = '';
            document.getElementById('editEmail').value = '';
            document.getElementById('editAddress').value = '';
            renderLocations([]);
        }

        async function deleteCustomer() {
            if (!activeCustomerId) return;
            const c = customers.find(x => x.asiakas_id == activeCustomerId);
            if (!confirm(`Haluatko varmasti poistaa asiakkaan "${c?.nimi}"? Kaikki asiakkaan kohteet, sopimukset ja laskut poistetaan.`)) return;
            try {
                const response = await fetch('methods/asiakkaat_methods.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ asiakas_id: activeCustomerId, real_method: 'DELETE' })
                });
                const result = await response.json();
                if (result.success) {
                    window.location.reload();
                } else {
                    alert('Poisto epäonnistui: ' + (result.error || ''));
                }
            } catch (e) {
                console.error(e);
                alert('Yhteysvirhe palvelimeen.');
            }
        }

        async function saveCustomer() {
            const etunimi = document.getElementById('editFirstName').value.trim();
            const sukunimi = document.getElementById('editLastName').value.trim();
            const phone = document.getElementById('editPhone').value.trim();
            const email = document.getElementById('editEmail').value.trim();
            const address = document.getElementById('editAddress').value.trim();

            if (!etunimi || !sukunimi || !phone || !email) {
                alert('Täytä etu- ja sukunimi, puhelin sekä sähköposti.');
                return;
            }

            const payload = {
                etunimi,
                sukunimi,
                puhelinnro: phone,
                sahkoposti: email,
                osoite: address || null
            };

            const method = activeCustomerId ? 'PUT' : 'POST';
            if (activeCustomerId) payload.asiakas_id = activeCustomerId;

            try {
                const response = await fetch('methods/asiakkaat_methods.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ ...payload, real_method: method })
                });
                const result = await response.json();
                if (result.success) {
                    window.location.reload();
                } else {
                    alert('Tallennus epäonnistui: ' + (result.error || 'Tuntematon virhe'));
                }
            } catch (e) {
                console.error(e);
                alert('Yhteysvirhe palvelimeen.');
            }
        }

        function renderLocations(allLocations) {
            const tbody = document.querySelector('#locationTable tbody');
            tbody.innerHTML = '';

            if (!allLocations || !activeCustomerId) {
                tbody.innerHTML = '<tr><td colspan="3" class="empty-row">Ei sijainteja</td></tr>';
                return;
            }

            const filtered = allLocations.filter(loc => loc.asiakas_id == activeCustomerId);
            if (filtered.length === 0) {
                tbody.innerHTML = '<tr><td colspan="3" class="empty-row">Ei sijainteja</td></tr>';
                return;
            }

            filtered.forEach(loc => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${loc.nimi}</td>
                    <td>${loc.osoite || '-'}</td>
                    <td class="actions-cell">
                        <button class="button button--ghost" onclick="viewLocation(${loc.kohde_id})">Näytä</button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        function addLocation() {
            document.getElementById('addLocationCard').classList.remove('hidden');
            document.getElementById('newLocationName').value = '';
            document.getElementById('newLocationAddress').value = '';
            document.getElementById('newLocationName').focus();
        }

        async function saveNewLocation() {
            const name = document.getElementById('newLocationName').value.trim();
            const address = document.getElementById('newLocationAddress').value.trim();

            if (!name || !address) {
                alert('Täytä sekä nimi että osoite.');
                return;
            }

            if (!activeCustomerId) {
                alert('Virhe: Ei aktiivista asiakasta.');
                return;
            }

            try {
                const response = await fetch('methods/kohteet_methods.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ nimi: name, osoite: address, asiakas_id: activeCustomerId })
                });
                const result = await response.json();
                if (result.success) {
                    window.location.reload();
                } else {
                    alert('Tallennus epäonnistui: ' + (result.error || ''));
                }
            } catch (e) {
                console.error(e);
                alert('Yhteysvirhe palvelimeen.');
            }
        }

        function cancelAddLocation() {
            document.getElementById('addLocationCard').classList.add('hidden');
        }

        function viewLocation(locationId) {
            window.location.href = `kohteet.php?location=${locationId}`;
        }
    </script>
    <script src="sort.js"></script>
    <script>
        makeSortable('customerTable');
        makeSortable('locationTable');
    </script>
</body>
</html>
