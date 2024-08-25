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
        $seriesData = $this->fetchSeriesData($title);
        if ($seriesData === null || count($seriesData['results']) === 0) {
            return null;
        }
        $seriesId = $seriesData['results'][0]['id'];

        $showData = $this->fetchShowData($seriesId);
        if ($showData === null) {
            return null;
        }
        $totalSeasons = $showData['number_of_seasons'];

        $randomSeason = rand(1, $totalSeasons);

        $seasonData = $this->fetchSeasonData($seriesId, $randomSeason);
        if ($seasonData === null) {
            return null;
        }
        $totalEpisodes = count($seasonData['episodes']);

        $randomEpisode = rand(0, $totalEpisodes - 1);
        $episode = $seasonData['episodes'][$randomEpisode];

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

$randomEpisode = $client->getRandomEpisode('Drake y Josh');

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
    <style>
        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .card {
            /* background-color: #fff; */
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.4);
            overflow: hidden;
            max-width: 50%;
            width: 100%;
            margin: 20px;
        }

        .card img {
            width: 100%;
            height: auto;
        }

        .card-body {
            padding: 20px;
            text-align: center;
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
    <div class="card">
        <?php if ($randomEpisode !== null): ?>
            <img src="<?php echo $randomEpisode['image']; ?>" alt="Imagen del episodio">
            <div class="card-body">
                <h1><?php echo "Season " . $randomEpisode['season'] . " - Episode " . $randomEpisode['episode']; ?></h1>
                <h2><?php echo $randomEpisode['title']; ?></h2>
                <p><?php echo $randomEpisode['overview']; ?></p>
                <button id="reload">Random Episode!</button>
            </div>
        <?php else: ?>
            <div class="card-body">
                <p>No se pudo obtener un episodio aleatorio.</p>
            </div>
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
