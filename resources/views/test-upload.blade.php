<!DOCTYPE html>
<html>
<head>
    <title>Test Upload</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <h1>Test Upload Simple</h1>
    
    <form id="uploadForm" enctype="multipart/form-data">
        <div>
            <label>Titre:</label>
            <input type="text" name="title" value="Test Song" required>
        </div>
        <div>
            <label>Artiste:</label>
            <input type="text" name="artist_name" value="Test Artist" required>
        </div>
        <div>
            <label>Prix (centimes):</label>
            <input type="number" name="price_cents" value="100" min="50" required>
        </div>
        <div>
            <label>Fichier MP3:</label>
            <input type="file" name="track" accept=".mp3" required>
        </div>
        <button type="submit">Upload</button>
    </form>
    
    <div id="result"></div>
    
    <script>
        document.getElementById('uploadForm').onsubmit = function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('/test-upload', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('result').innerHTML = JSON.stringify(data, null, 2);
            })
            .catch(error => {
                document.getElementById('result').innerHTML = 'Erreur: ' + error;
            });
        };
    </script>
</body>
</html>