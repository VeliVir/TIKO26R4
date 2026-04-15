<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kohteet - Tietokantaohjelma</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container">
        <!-- Main List View -->
        <div id="mainView">
            <header class="page-header">
                <h1>Kohteet</h1>
            </header>

            <div class="top-actions">
                <button type="button" class="button button--primary" onclick="addLocation()">Lisää uusi kohde</button>
                <div class="filter-field">
                    <label for="locationFilter">Suodata</label>
                    <input type="text" id="locationFilter" placeholder="Etsi kohteen nimellä" oninput="filterLocations()">
                </div>
            </div>

            <div class="table-container">
                <table class="data-table" id="locationTable">
                    <thead>
                        <tr>
                            <th>Nimi</th>
                            <th>Osoite</th>
                            <th>Asiakas</th>
                            <th>Toiminnot</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Details View -->
        <div id="detailsView" class="hidden">
            <div class="details-view-header">
                <button class="button button--ghost back-button" onclick="backToMain()">← Takaisin</button>
                <h1 id="detailsTitle">Kohde</h1>
            </div>

            <div class="details-content">
                <div class="details-card" id="locationInfoView">
                    <h3>Kohteen tiedot</h3>
                    <div class="details-row"><span>Nimi</span><span id="viewName"></span></div>
                    <div class="details-row"><span>Osoite</span><span id="viewAddress"></span></div>
                    <div class="details-row"><span>Asiakas</span><span id="viewCustomer"></span></div>
                </div>

                <div class="details-card" id="locationInfoEdit">
                    <h3>Muokkaa kohdetta</h3>
                    <div class="details-row"><label for="editName">Nimi</label><input type="text" id="editName"></div>
                    <div class="details-row"><label for="editAddress">Osoite</label><input type="text" id="editAddress"></div>
                    <div class="details-row">
                        <label for="editCustomer">Asiakas</label>
                        <select id="editCustomer">
                            <option value="">Valitse asiakas</option>
                        </select>
                    </div>
                </div>

                <div class="details-actions" id="detailsActions">
                    <button class="button button--primary" id="saveLocationBtn" onclick="saveLocation()">Tallenna</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const locations = [
            {
                id: 1,
                name: 'Helsingin toimisto',
                address: 'Mannerheimintie 10, Helsinki',
                customer: 'Seppo Tärsky'
            },
            {
                id: 2,
                name: 'Tampereen varasto',
                address: 'Hatanpään valtatie 18, Tampere',
                customer: 'Seppo Tärsky'
            },
            {
                id: 3,
                name: 'Turun pääkonttori',
                address: 'Aurakatu 5, Turku',
                customer: 'Laura Lehtonen'
            },
            {
                id: 4,
                name: 'Oulun toimipiste',
                address: 'Isokatu 12, Oulu',
                customer: 'Matti Meikäläinen'
            },
            {
                id: 5,
                name: 'Jyväskylän tehdas',
                address: 'Tehtaankatu 15, Jyväskylä',
                customer: 'Seppo Tärsky'
            },
            {
                id: 6,
                name: 'Espoon logistiikkakeskus',
                address: 'Logistiikkatie 8, Espoo',
                customer: 'Laura Lehtonen'
            }
        ];

        const customers = [
            'Seppo Tärsky',
            'Laura Lehtonen',
            'Matti Meikäläinen'
        ];

        let activeLocationId = null;
        let editMode = false;

        function renderLocationRows() {
            const tbody = document.querySelector('#locationTable tbody');
            tbody.innerHTML = '';
            const filter = document.querySelector('#locationFilter').value.toLowerCase();

            locations
                .filter(location => location.name.toLowerCase().includes(filter))
                .forEach(location => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${location.name}</td>
                        <td>${location.address}</td>
                        <td>${location.customer}</td>
                        <td class="actions-cell">
                            <button class="button button--secondary" onclick="showLocation(${location.id})">Näytä</button>
                            <button class="button button--ghost" onclick="editLocation(${location.id})">Muokkaa</button>
                        </td>
                    `;
                    tbody.appendChild(row);
                });
        }

        function filterLocations() {
            renderLocationRows();
        }

        function switchToDetailsView(mode) {
            document.getElementById('mainView').classList.add('hidden');
            document.getElementById('detailsView').classList.remove('hidden');
            editMode = mode === 'edit';
            document.getElementById('locationInfoView').classList.toggle('hidden', editMode);
            document.getElementById('locationInfoEdit').classList.toggle('hidden', !editMode);
            document.getElementById('saveLocationBtn').style.display = editMode ? 'inline-flex' : 'none';
        }

        function backToMain() {
            document.getElementById('mainView').classList.remove('hidden');
            document.getElementById('detailsView').classList.add('hidden');
            activeLocationId = null;
            // Clear URL parameters
            window.history.replaceState({}, document.title, window.location.pathname);
        }

        function showLocation(id) {
            const location = locations.find(item => item.id === id);
            if (!location) return;
            activeLocationId = id;
            switchToDetailsView('view');
            document.getElementById('detailsTitle').textContent = `Kohde: ${location.name}`;
            document.getElementById('viewName').textContent = location.name;
            document.getElementById('viewAddress').textContent = location.address;
            document.getElementById('viewCustomer').textContent = location.customer;
            document.getElementById('editName').value = location.name;
            document.getElementById('editAddress').value = location.address;
            populateCustomerDropdown(location.customer);
        }

        function editLocation(id) {
            const location = locations.find(item => item.id === id);
            if (!location) return;
            activeLocationId = id;
            switchToDetailsView('edit');
            document.getElementById('detailsTitle').textContent = `Muokkaa kohdetta: ${location.name}`;
            document.getElementById('editName').value = location.name;
            document.getElementById('editAddress').value = location.address;
            populateCustomerDropdown(location.customer);
        }

        function addLocation() {
            activeLocationId = null;
            switchToDetailsView('edit');
            document.getElementById('detailsTitle').textContent = 'Lisää uusi kohde';
            document.getElementById('editName').value = '';
            document.getElementById('editAddress').value = '';
            populateCustomerDropdown('');
        }

        function populateCustomerDropdown(selectedCustomer) {
            const select = document.getElementById('editCustomer');
            select.innerHTML = '<option value="">Valitse asiakas</option>';
            customers.forEach(customer => {
                const option = document.createElement('option');
                option.value = customer;
                option.textContent = customer;
                if (customer === selectedCustomer) {
                    option.selected = true;
                }
                select.appendChild(option);
            });
        }

        function saveLocation() {
            const name = document.getElementById('editName').value.trim();
            const address = document.getElementById('editAddress').value.trim();
            const customer = document.getElementById('editCustomer').value;

            if (!name || !address || !customer) {
                alert('Täytä kaikki kentät ennen tallentamista.');
                return;
            }

            if (activeLocationId) {
                const location = locations.find(item => item.id === activeLocationId);
                if (!location) return;
                location.name = name;
                location.address = address;
                location.customer = customer;
            } else {
                locations.push({
                    id: Date.now(),
                    name,
                    address,
                    customer
                });
            }

            renderLocationRows();
            backToMain();
        }

        // Check for URL parameters on page load
        function checkUrlParameters() {
            const urlParams = new URLSearchParams(window.location.search);
            const locationId = urlParams.get('location');
            if (locationId) {
                showLocation(parseInt(locationId));
            }
        }

        renderLocationRows();
        checkUrlParameters();
    </script>
</body>
</html>
