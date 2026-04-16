<?php
require_once 'db/db_connection.php';

$sql_asiakkaat = "SELECT asiakas_id, 
                         (etunimi || ' ' || sukunimi) AS nimi,
                         puhelinnro,
                         sahkoposti,
                         osoite
                  FROM Asiakas 
                  ORDER BY sukunimi, etunimi ASC";

$result_asiakkaat = pg_query($yhteys, $sql_asiakkaat);
$asiakkaat_data = pg_fetch_all($result_asiakkaat);

if (!$asiakkaat_data) {
    $asiakkaat_data = [];
}

$sql_kohteet = "SELECT kohde_id, asiakas_id, nimi, osoite FROM Tyokohde";
$result_kohteet = pg_query($yhteys, $sql_kohteet);
$kohteet_data = pg_fetch_all($result_kohteet);

if (!$kohteet_data) {
    $kohteet_data = [];
}
?>
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
        <!-- Main List View -->
        <div id="mainView">
            <header class="page-header">
                <h1>Asiakkaat</h1>
            </header>

            <div class="top-actions">
                <button type="button" class="button button--primary" onclick="addCustomer()">Lisää uusi asiakas</button>
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
                </div>

                <div class="details-card" id="customerInfoEdit">
                    <h3>Muokkaa asiakasta</h3>
                    <div class="details-row"><label for="editName">Nimi</label><input type="text" id="editName"></div>
                    <div class="details-row"><label for="editPhone">Puhelin</label><input type="text" id="editPhone"></div>
                    <div class="details-row"><label for="editEmail">Sähköposti</label><input type="email" id="editEmail"></div>
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
                        <button class="button button--secondary" onclick="addLocation()">Lisää työkohde</button>
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
                    <button class="button button--primary" id="saveCustomerBtn" onclick="saveCustomer()">Tallenna</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const customers = <?php echo json_encode($asiakkaat_data); ?>;
        const locations = <?php echo json_encode($kohteet_data); ?>;

        let activeCustomerId = null;
        let editMode = false;

        function renderCustomerRows() {
            const tbody = document.querySelector('#customerTable tbody');
            tbody.innerHTML = '';
            const filter = document.querySelector('#customerFilter').value.toLowerCase();

            customers
                .filter(customer => customer.nimi.toLowerCase().includes(filter))
                .forEach(customer => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${customer.nimi}</td>
                        <td>${customer.puhelinnro}</td>
                        <td>${customer.sahkoposti}</td>
                        <td class="actions-cell">
                            <button class="button button--secondary" onclick="showCustomer(${customer.asiakas_id})">Näytä</button>
                            <button class="button button--ghost" onclick="editCustomer(${customer.asiakas_id})">Muokkaa</button>
                        </td>
                    `;
                    tbody.appendChild(row);
                });
        }

        function filterCustomers() {
            renderCustomerRows();
        }

        function switchToDetailsView(mode) {
            document.getElementById('mainView').classList.add('hidden');
            document.getElementById('detailsView').classList.remove('hidden');
            editMode = mode === 'edit';
            document.getElementById('customerInfoView').classList.toggle('hidden', editMode);
            document.getElementById('customerInfoEdit').classList.toggle('hidden', !editMode);
            document.getElementById('saveCustomerBtn').style.display = editMode ? 'inline-flex' : 'none';
            document.getElementById('locationActions').style.display = editMode ? 'flex' : 'none';
            // Hide add location card when switching views
            document.getElementById('addLocationCard').classList.add('hidden');
        }

        function backToMain() {
            document.getElementById('mainView').classList.remove('hidden');
            document.getElementById('detailsView').classList.add('hidden');
            activeCustomerId = null;
        }

        function showCustomer(id) {
            const customer = customers.find(item => item.asiakas_id == id);
            if (!customer) return;
            activeCustomerId = id;
            switchToDetailsView('view');
            document.getElementById('detailsTitle').textContent = `Asiakas: ${customer.nimi}`;
            document.getElementById('viewName').textContent = customer.nimi;
            document.getElementById('viewPhone').textContent = customer.puhelinnro;
            document.getElementById('viewEmail').textContent = customer.sahkoposti;
            document.getElementById('editName').value = customer.nimi;
            document.getElementById('editPhone').value = customer.puhelinnro;
            document.getElementById('editEmail').value = customer.sahkoposti;
            renderLocations(locations);
        }

        function editCustomer(id) {
            const customer = customers.find(item => item.asiakas_id == id);
            if (!customer) return;
            activeCustomerId = id;
            switchToDetailsView('edit');
            document.getElementById('detailsTitle').textContent = `Muokkaa asiakasta: ${customer.nimi}`;
            document.getElementById('editName').value = customer.nimi;
            document.getElementById('editPhone').value = customer.puhelinnro;
            document.getElementById('editEmail').value = customer.sahkoposti;
            renderLocations(locations);
        }

        function addCustomer() {
            activeCustomerId = null;
            switchToDetailsView('edit');
            document.getElementById('detailsTitle').textContent = 'Lisää uusi asiakas';
            document.getElementById('editName').value = '';
            document.getElementById('editPhone').value = '';
            document.getElementById('editEmail').value = '';
            renderLocations([]);
        }

        function saveCustomer() {
            const name = document.getElementById('editName').value.trim();
            const phone = document.getElementById('editPhone').value.trim();
            const email = document.getElementById('editEmail').value.trim();

            if (!name || !phone || !email) {
                alert('Täytä kaikki kentät ennen tallentamista.');
                return;
            }

            if (activeCustomerId) {
                const customer = customers.find(item => item.id == activeCustomerId);
                if (!customer) return;
                customer.nimi = name;
                customer.puhelinnro = phone;
                customer.sahkoposti = email;
            } else {
                /*customers.push({
                    id: Date.now(),
                    name,
                    phone,
                    email,
                    locations: []
                });*/
            }

            renderCustomerRows();
            backToMain();
        }

        function renderLocations(allLocations) {
            if (!allLocations) return;
            const tbody = document.querySelector('#locationTable tbody');
            tbody.innerHTML = '';

            const filteredLocations = allLocations.filter(loc => loc.asiakas_id == activeCustomerId);

            if (filteredLocations.length === 0) {
                const row = document.createElement('tr');
                row.innerHTML = '<td colspan="3" class="empty-row">Ei sijainteja</td>';
                tbody.appendChild(row);
                return;
            }

            filteredLocations.forEach(location => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${location.nimi}</td>
                    <td>${location.osoite}</td>
                    <td class="actions-cell">
                        <button class="button button--ghost" onclick="viewLocation(${location.kohde_id})">Näytä</button>
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

        function saveNewLocation() {
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

            const customer = customers.find(c => c.id === activeCustomerId);
            if (!customer) {
                alert('Virhe: Asiakasta ei löytynyt.');
                return;
            }

            // Add new location to customer's locations
            customer.locations.push({
                name: name,
                address: address
            });

            // Hide the add location card
            document.getElementById('addLocationCard').classList.add('hidden');

            // Re-render locations
            renderLocations(customer.locations);
        }

        function cancelAddLocation() {
            document.getElementById('addLocationCard').classList.add('hidden');
        }

        function viewLocation(locationId) {
            window.location.href = `kohteet.php?location=${locationId}`;
        }

        renderCustomerRows();
    </script>
</body>
</html>
