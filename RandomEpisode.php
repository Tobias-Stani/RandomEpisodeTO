<?php

class TMDbApiClient
{
    private $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function getRandomEpisode(string $title): ?array
    {
        if (empty($title)) {
            return null;
        }

        // Paso 1: Obtener el ID de la serie
        $seriesData = $this->fetchSeriesData($title);
        if ($seriesData === null || count($seriesData['results']) === 0) {
            return null;
        }
        $seriesId = $seriesData['results'][0]['id'];

        // Paso 2: Obtener la cantidad de temporadas
        $showData = $this->fetchShowData($seriesId);
        if ($showData === null) {
            return null;
        }
        $totalSeasons = $showData['number_of_seasons'];

        // Paso 3: Seleccionar una temporada aleatoria
        $randomSeason = rand(1, $totalSeasons);

        // Paso 4: Obtener los episodios de la temporada seleccionada
        $seasonData = $this->fetchSeasonData($seriesId, $randomSeason);
        if ($seasonData === null) {
            return null;
        }
        $totalEpisodes = count($seasonData['episodes']);

        // Paso 5: Seleccionar un episodio aleatorio
        $randomEpisode = rand(0, $totalEpisodes - 1);
        $episode = $seasonData['episodes'][$randomEpisode];

        // Paso 6: Retornar los datos del episodio
        return [
            'title' => $episode['name'],
            'season' => $randomSeason,
            'episode' => $episode['episode_number'],
            'overview' => $episode['overview'],
            'image' => 'https://image.tmdb.org/t/p/w1280' . $episode['still_path']
        ];
    }

    private function fetchSeriesData(string $title): ?array
    {
        $url = "https://api.themoviedb.org/3/search/tv?query=" . urlencode($title) . "&api_key=" . $this->apiKey;
        return $this->fetchData($url);
    }

    private function fetchShowData(int $seriesId): ?array
    {
        $url = "https://api.themoviedb.org/3/tv/$seriesId?api_key=" . $this->apiKey;
        return $this->fetchData($url);
    }

    private function fetchSeasonData(int $seriesId, int $season): ?array
    {
        $url = "https://api.themoviedb.org/3/tv/$seriesId/season/$season?api_key=" . $this->apiKey;
        return $this->fetchData($url);
    }

    private function fetchData(string $url): ?array
    {
        $result = file_get_contents($url);
        if ($result === false) {
            return null;
        }
        $data = json_decode($result, true);
        return $data === null ? null : $data;
    }
}

// Ejemplo de uso
$apiKey = '65bcbdb68a231d56f86ff81adb62daf8';  // Reemplaza con tu clave API real
$client = new TMDbApiClient($apiKey);

$title = $_GET['title'] ?? '';
$randomEpisode = $client->getRandomEpisode($title);

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Episodio Random</title>
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
        <style>
    body {
        margin: 0;
        padding: 0;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        align-items: center;
        background-color: #282c34;
        color: white;
    }

    .container {
        width: 100%;
        max-width: 80%;
        padding: 20px;
        box-sizing: border-box;
        display: flex;
        flex-direction: column;
        align-items: center;
        flex-grow: 1; /* Permite que el contenedor crezca para ocupar el espacio restante */
    }

    .form-container {
        width: 100%;
        max-width: 600px;
        margin-bottom: 20px;
        padding-top: 5%;
        box-sizing: border-box;
    }

    .card {
        background-color: rgba(0, 0, 0, 0.8);
        padding: 20px;
        border-radius: 10px;
        text-align: center;
        margin: 20px 0;
        width: 100%;
        max-width: 600px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        box-sizing: border-box;
    }

    .card img {
        width: 100%;
        border-radius: 10px;
    }

    .card h1,
    .card h2,
    .card p {
        margin: 10px 0;
    }

    footer {
        width: 100%;
        text-align: center;
        padding: 10px 0;
        background-color: #222;
        color: white;
    }

    @media (max-width: 768px) {
        .container, .form-container, .card {
            max-width: 90%;
        }
    }

    @media (max-width: 480px) {
        .container, .form-container, .card {
            max-width: 95%;
        }
    }
</style>

</head>

<body>
    <div class="container">
        <form class="form-container" method="get">
            <input type="text" name="title" placeholder="Ingresa el nombre de la serie" value="<?php echo htmlspecialchars($title); ?>">
            <button type="submit">Buscar Episodio Random</button>
        </form>

        <?php if ($randomEpisode !== null): ?>
            <div class="card">
                <img src="<?php echo $randomEpisode['image']; ?>" alt="Imagen del episodio">
                <h1><?php echo "Temporada " . $randomEpisode['season'] . " - Episodio " . $randomEpisode['episode']; ?></h1>
                <h2><?php echo $randomEpisode['title']; ?></h2>
                <p><?php echo $randomEpisode['overview']; ?></p>
                <button id="reload">Random Episode!</button>
            </div>
        <?php else: ?>
            <?php if (!empty($title)): ?>
                <p>No se pudo obtener un episodio aleatorio. Intenta con otro título.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <footer>
        <p>
            Made with love by 🫶: <a href="https://www.linkedin.com/in/tobias-stanislavsky" target="_blank">TS</a>
        </p>
    </footer>

</body>

<script>
        const reload = document.getElementById("reload");

        reload.addEventListener("click", (_) => {
            location.reload();
        });
    </script>

</html>
