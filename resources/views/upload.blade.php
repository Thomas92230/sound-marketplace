<!DOCTYPE html>
<html>
<head>
    <title>Upload - Music Marketplace</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-10">
<div class="max-w-lg mx-auto bg-white p-8 rounded-lg shadow">
    <h1 class="text-2xl font-bold mb-6">Ajouter une musique</h1>

    <form action="/upload" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-4">
            <label class="block mb-2">Titre du morceau</label>
            <input type="text" name="title" class="w-full border p-2 rounded" required>
        </div>

        <div class="mb-4">
            <label class="block mb-2">Nom de l'artiste</label>
            <input type="text" name="artist_name" class="w-full border p-2 rounded" required>
        </div>

        <div class="mb-4">
            <label class="block mb-2">Prix (en centimes, ex: 990 pour 9.90€)</label>
            <input type="number" name="price_cents" class="w-full border p-2 rounded" required>
        </div>

        <div class="mb-6">
            <label class="block mb-2">Fichier MP3</label>
            <input type="file" name="track" accept="audio/mpeg" class="w-full" required>
        </div>

        <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">
            Envoyer sur S3
        </button>
    </form>
</div>
</body>
</html>
