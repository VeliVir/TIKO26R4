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
        <header class="page-header">
            <h1>Toimittajat</h1>
        </header>

        <div class="table-container" style="padding: 30px; max-width: 640px; margin: 0 auto;">
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

    <script>
        function uploadXml() {
            const fileInput = document.getElementById('xmlFile');
            if (!fileInput.files || !fileInput.files.length) {
                alert('Valitse ensin XML-tiedosto.');
                return;
            }
            const fileName = fileInput.files[0].name;
            alert(`XML-tiedosto ${fileName} valittu. Tässä on paikkamerkki-tuki lataukselle.`);
        }
    </script>
</body>
</html>
