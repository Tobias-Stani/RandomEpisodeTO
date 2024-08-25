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

$randomEpisode = $client->getRandomEpisode('The Office');

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Episodio Random de The Office</title>
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-image: url('<?php echo $randomEpisode['image']; ?>');
            background-size: cover;
            background-position: center;
        }

        .overlay {
            background-color: rgba(0, 0, 0, 0.5);
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            color: #ffff;
            max-width: 80%;
            width: 100%;
            /* Para asegurar que el contenedor no sea más pequeño que el contenido */
            box-sizing: border-box;
            margin: 0 auto;
        }

        h1,
        h2,
        p {
            margin: 10px 0;
        }

        footer {
            position: fixed;
            bottom: 10px;
            width: 100%;
            text-align: center;
            color: white;
        }
    </style>

</head>

<body>
    <div class="overlay">
        <?php if ($randomEpisode !== null): ?>
            <h1><?php echo "Season " . $randomEpisode['season'] . " - Episode " . $randomEpisode['episode']; ?></h1>
            <h2><?php echo $randomEpisode['title']; ?></h2>
            <p><?php echo $randomEpisode['overview']; ?></p>
            <button id="reload">Random Episode!</button>
        <?php else: ?>
            <p>No se pudo obtener un episodio aleatorio.</p>
        <?php endif; ?>
    </div>

    <footer>
        <p>
            Made with love by 🫶: <a href="https://www.linkedin.com/in/tobias-stanislavsky" target="_blank">TS</a>
        </p>
    </footer>

    <script>
        const reload = document.getElementById("reload");

        reload.addEventListener("click", (_) => {
            location.reload();
        });
    </script>

</body>

</html>