<?php
// Tämä sivu on asiakkaiden hallintaan. Dataa voidaan myöhemmin ladata tietokannasta.
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
        // Tässä jotain placeholdereita asiakkaille. Nämä voidaan myöhemmin hakea tietokannasta.
        const customers = [
            {
                id: 1,
                name: 'Seppo Tärsky',
                phone: '040 123 4567',
                email: 'seppo.tarsky@tuni.fi',
                locations: [
                    { name: 'Helsingin toimisto', address: 'Mannerheimintie 10, Helsinki' },
                    { name: 'Tampereen varasto', address: 'Hatanpään valtatie 18, Tampere' }
                ]
            },
            {
                id: 2,
                name: 'Marko Junkkari',
                phone: '050 987 6543',
                email: 'marko.junkkari@tuni.fi',
                locations: [
                    { name: 'Turun pääkonttori', address: 'Aurakatu 5, Turku' }
                ]
            },
            {
                id: 3,
                name: 'Matti Meikäläinen',
                phone: '044 321 7689',
                email: 'matti.meikalainen@example.com',
                locations: [
                    { name: 'Oulun toimipiste', address: 'Isokatu 12, Oulu' }
                ]
            }
        ];

        let activeCustomerId = null;
        let editMode = false;

        function renderCustomerRows() {
            const tbody = document.querySelector('#customerTable tbody');
            tbody.innerHTML = '';
            const filter = document.querySelector('#customerFilter').value.toLowerCase();

            customers
                .filter(customer => customer.name.toLowerCase().includes(filter))
                .forEach(customer => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${customer.name}</td>
                        <td>${customer.phone}</td>
                        <td>${customer.email}</td>
                        <td class="actions-cell">
                            <button class="button button--secondary" onclick="showCustomer(${customer.id})">Näytä</button>
                            <button class="button button--ghost" onclick="editCustomer(${customer.id})">Muokkaa</button>
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
            const customer = customers.find(item => item.id === id);
            if (!customer) return;
            activeCustomerId = id;
            switchToDetailsView('view');
            document.getElementById('detailsTitle').textContent = `Asiakas: ${customer.name}`;
            document.getElementById('viewName').textContent = customer.name;
            document.getElementById('viewPhone').textContent = customer.phone;
            document.getElementById('viewEmail').textContent = customer.email;
            document.getElementById('editName').value = customer.name;
            document.getElementById('editPhone').value = customer.phone;
            document.getElementById('editEmail').value = customer.email;
            renderLocations(customer.locations);
        }

        function editCustomer(id) {
            const customer = customers.find(item => item.id === id);
            if (!customer) return;
            activeCustomerId = id;
            switchToDetailsView('edit');
            document.getElementById('detailsTitle').textContent = `Muokkaa asiakasta: ${customer.name}`;
            document.getElementById('editName').value = customer.name;
            document.getElementById('editPhone').value = customer.phone;
            document.getElementById('editEmail').value = customer.email;
            renderLocations(customer.locations);
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
                const customer = customers.find(item => item.id === activeCustomerId);
                if (!customer) return;
                customer.name = name;
                customer.phone = phone;
                customer.email = email;
            } else {
                customers.push({
                    id: Date.now(),
                    name,
                    phone,
                    email,
                    locations: []
                });
            }

            renderCustomerRows();
            backToMain();
        }

        function renderLocations(locations) {
            const tbody = document.querySelector('#locationTable tbody');
            tbody.innerHTML = '';
            if (locations.length === 0) {
                const row = document.createElement('tr');
                row.innerHTML = '<td colspan="3" class="empty-row">Ei sijainteja</td>';
                tbody.appendChild(row);
                return;
            }

            locations.forEach((location, index) => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${location.name}</td>
                    <td>${location.address}</td>
                    <td class="actions-cell">
                        <button class="button button--ghost" onclick="viewLocation(${location.id})">Näytä</button>
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
