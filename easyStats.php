<?php

# get correct id for plugin
$thisfile = basename(__FILE__, ".php");

# register plugin
register_plugin(
    $thisfile, //Plugin id
    'easyStats 📊',     //Plugin name
    '1.0',         //Plugin version
    'multicolor',  //Plugin author
    'http://bit.ly/donate-multicolor-plugins', //author website
    ' this plugin shows statistics by counting only unique IP addresses on a website ', //Plugin description
    'pages', //page type - on which admin tab to display
    'easyStatsView'  //main function (administration)
);

# activate filter 
add_action('theme-footer', 'makeEasyStats');

# add a link in the admin tab 'theme'
add_action('pages-sidebar', 'createSideMenu', array($thisfile, 'easyStats 📊'));



function easyStatsView()
{


    $visitorsFile =  GSDATAOTHERPATH . 'easyStats/visitors.xml';

    // Pobranie adresu IP odwiedzającego
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    $ipAddress = hash('sha256', $ipAddress);

    // Aktualna data i czas
    $currentTimestamp = time();

    // Odczyt bieżących odwiedzających z pliku tymczasowego
    $currentVisitors = [];




    // Odczyt istniejących odwiedzających z pliku XML
    $allVisitors = [];
    $visitors7Days = [];
    $visitors30Days = [];
    $visitors24Hours = [];

    if (file_exists($visitorsFile)) {
        $xml = simplexml_load_file($visitorsFile);
        foreach ($xml->visitor as $visitor) {
            $visitorIp = (string) $visitor->ip;
            $visitorTimestamp = (int) $visitor->timestamp;
            $allVisitors[] = $visitorIp;

            if ($currentTimestamp - $visitorTimestamp <= 7 * 24 * 60 * 60) {
                $visitors7Days[] = $visitorIp;
            }

            if ($currentTimestamp - $visitorTimestamp <= 30 * 24 * 60 * 60) {
                $visitors30Days[] = $visitorIp;
            }

            if ($currentTimestamp - $visitorTimestamp <= 24 * 60 * 60) {
                $visitors24Hours[] = $visitorIp;
            }
        }
    }

    // Pobranie liczby unikalnych odwiedzających
    $uniqueAllVisitors = count(array_unique($allVisitors));
    $uniqueVisitors7Days = count(array_unique($visitors7Days));
    $uniqueVisitors30Days = count(array_unique($visitors30Days));
    $uniqueVisitors24Hours = count(array_unique($visitors24Hours));

    // Pobranie liczby obecnie odwiedzających
    $currentVisitorsCount = count($currentVisitors);

    // Wyświetlenie informacji

    echo "

    <div style='width:100%;background:#fafafa;border:solid 1px #ddd; padding:15px;margin-bottom:20px;'>
    <h3>Easy Stats</h3>
    <b>this plugin shows statistics by counting only unique IP addresses on a website</b>
    </div>

    <div class='bg-light border p-2'><h2>Stats</h2></div>
    ";
    echo '<table class="table">';

    echo "<tr><td>Unique user from all the time: $uniqueAllVisitors</tr></td>";
    echo "<tr><td>Unique user from last 30 days: $uniqueVisitors30Days</tr></td>";
    echo "<tr><td>Unique user from last 7 days: $uniqueVisitors7Days</tr></td>";
    echo "<tr><td>Unique user from last 24hours: $uniqueVisitors24Hours </tr></td>";
    echo '</table>';




    echo ' 
<canvas style="margin:20px;0" id="statisticsChart" width="400" height="200"></canvas>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

';




    $pagesFile = GSDATAOTHERPATH . 'easyStats/pagesCount.xml';


    // Pobranie bieżącego adresu URL
    $currentUrl = $_SERVER['REQUEST_URI'];

    // Pobranie adresu IP odwiedzającego
    $ipAddress = $_SERVER['REMOTE_ADDR'];


    // Aktualna data i czas
    $currentTimestamp = time();

    // Data 30 dni wstecz
    $thirtyDaysAgo = strtotime('-30 days');

    // Odczyt istniejących odwiedzanych stron z pliku XML
    $pages = [];
    if (file_exists($pagesFile)) {
        $xml = simplexml_load_file($pagesFile);
        foreach ($xml->page as $page) {
            $pageUrl = (string) $page->url;
            $pageVisits = (int) $page->visits;
            $pageUniqueVisitors = explode(',', (string) $page->unique_visitors);
            $pageTimestamps = explode(',', (string) $page->timestamps);
            $pages[$pageUrl] = [
                'visits' => $pageVisits,
                'unique_visitors' => $pageUniqueVisitors,
                'timestamps' => $pageTimestamps
            ];
        }
    }


    // Sortowanie stron według liczby odwiedzin w odwrotnej kolejności
    uasort($pages, function ($a, $b) {
        return $b['visits'] <=> $a['visits'];
    });

    // Pobranie listy 100 najczęściej odwiedzanych stron z ostatnich 30 dni
    $top100Pages = $pages;




    // Wyświetlenie listy 100 najczęściej odwiedzanych stron
    echo "<div class='col-md-12 bg-light border p-2'><h2> Most popular views: </h2></div>";

    echo '<table class="table">';
    foreach ($top100Pages as $url => $pageData) {
        echo "<tr><td><b>" . $url . "</b>- unique Views: " . count($pageData['unique_visitors']) . "</td></tr>";
    }

    echo "</table>

    


<!-- online script -->

<script>
  const ctx = document.getElementById('statisticsChart');

  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: ['Unique user All the time', 'Unique user from last 30 days:', 'Unique user from last 7 days:','Unique user from last 24hours'],
      datasets: [{
        label: 'Views on website',
        data: [$uniqueAllVisitors,$uniqueVisitors30Days,$uniqueVisitors7Days,$uniqueVisitors24Hours],
        borderWidth: 1
      }]
    },
    options: {
      scales: {
        y: {
          beginAtZero: true
        }
      }
    }
  });
</script>


";

    echo '<div id="paypal" style="margin-top:10px; background: #fafafa; border:solid 1px #ddd; padding: 10px;box-sizing: border-box; text-align: center;">
<p style="margin-bottom:10px;">If you want to see new plugins, buy me a ☕ :) </p>
<a href="https://www.paypal.com/donate/?hosted_button_id=TW6PXVCTM5A72"><img alt="" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" border="0"></a>
</div>';
}


function makeEasyStats()
{


    $http_response_code = http_response_code();

    if ($http_response_code !== 404) {
 

        $visitorsFile = GSDATAOTHERPATH . 'easyStats/visitors.xml';

        if (!is_dir(GSDATAOTHERPATH . 'easyStats/')) {
            mkdir(GSDATAOTHERPATH . 'easyStats/', 0755);
            file_put_contents(GSDATAOTHERPATH . 'easyStats/.htaccess', 'Deny from All');
        };


        // Pobranie adresu IP odwiedzającego
        $ipAddress = $_SERVER['REMOTE_ADDR'];

        $ipAddress = hash('sha256', $ipAddress);


        // Aktualna data i czas
        $currentTimestamp = time();

        // Odczyt bieżących odwiedzających z pliku tymczasowego
        $currentVisitors = [];


        // Sprawdzenie, czy odwiedzający istnieje na liście bieżących odwiedzających i usunięcie go
        if (array_key_exists($ipAddress, $currentVisitors)) {
            unset($currentVisitors[$ipAddress]);
        }


        // Dodanie lub aktualizacja informacji o bieżącym odwiedzającym
        $currentVisitors[$ipAddress] = $currentTimestamp;


        // Odczyt istniejących odwiedzających z pliku XML
        $allVisitors = [];
        $visitors7Days = [];
        $visitors30Days = [];
        $visitors24Hours = [];

        if (file_exists($visitorsFile)) {
            $xml = simplexml_load_file($visitorsFile);
            foreach ($xml->visitor as $visitor) {
                $visitorIp = (string) $visitor->ip;
                $visitorTimestamp = (int) $visitor->timestamp;
                $allVisitors[] = $visitorIp;

                if ($currentTimestamp - $visitorTimestamp <= 7 * 24 * 60 * 60) {
                    $visitors7Days[] = $visitorIp;
                }

                if ($currentTimestamp - $visitorTimestamp <= 30 * 24 * 60 * 60) {
                    $visitors30Days[] = $visitorIp;
                }

                if ($currentTimestamp - $visitorTimestamp <= 24 * 60 * 60) {
                    $visitors24Hours[] = $visitorIp;
                }
            }
        }

        // Dodanie nowego odwiedzającego do pliku XML
        if (!in_array($ipAddress, $allVisitors)) {
            $xml = new SimpleXMLElement('<visitors></visitors>');
            foreach ($allVisitors as $visitorIp) {
                $visitor = $xml->addChild('visitor');
                $visitor->addChild('ip', $visitorIp);
                $visitor->addChild('timestamp', $currentTimestamp);
            }
            $newVisitor = $xml->addChild('visitor');
            $newVisitor->addChild('ip', $ipAddress);
            $newVisitor->addChild('timestamp', $currentTimestamp);
            $xml->asXML($visitorsFile);
        }

        // Pobranie liczby unikalnych odwiedzających
        $uniqueAllVisitors = count(array_unique($allVisitors));
        $uniqueVisitors7Days = count(array_unique($visitors7Days));
        $uniqueVisitors30Days = count(array_unique($visitors30Days));
        $uniqueVisitors24Hours = count(array_unique($visitors24Hours));

        // Pobranie liczby obecnie odwiedzających
        $currentVisitorsCount = count($currentVisitors);



        $pagesFile = GSDATAOTHERPATH . 'easyStats/pagesCount.xml';


        // Pobranie bieżącego adresu URL
        $currentUrl = $_SERVER['REQUEST_URI'];

        // Pobranie adresu IP odwiedzającego
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $ipAddress = hash('sha256', $ipAddress);


        // Aktualna data i czas
        $currentTimestamp = time();

        // Data 30 dni wstecz
        $thirtyDaysAgo = strtotime('-30 days');

        // Odczyt istniejących odwiedzanych stron z pliku XML
        $pages = [];
        if (file_exists($pagesFile)) {
            $xml = simplexml_load_file($pagesFile);
            foreach ($xml->page as $page) {
                $pageUrl = (string) $page->url;
                $pageVisits = (int) $page->visits;
                $pageUniqueVisitors = explode(',', (string) $page->unique_visitors);
                $pageTimestamps = explode(',', (string) $page->timestamps);
                $pages[$pageUrl] = [
                    'visits' => $pageVisits,
                    'unique_visitors' => $pageUniqueVisitors,
                    'timestamps' => $pageTimestamps
                ];
            }
        }

        // Sprawdzenie, czy bieżący adres URL zawiera "?search="
        $isSearchPage = strpos($currentUrl, '?search=') !== false;

        // Zwiększenie licznika odwiedzin i unikalnych odwiedzin dla aktualnej strony (jeśli to nie jest strona z wyszukiwaniem)
        if (!$isSearchPage) {
            if (array_key_exists($currentUrl, $pages)) {
                $pages[$currentUrl]['visits']++;

                if (!in_array($ipAddress, $pages[$currentUrl]['unique_visitors'])) {
                    $pages[$currentUrl]['unique_visitors'][] = $ipAddress;
                }

                $pages[$currentUrl]['timestamps'][] = $currentTimestamp;
            } else {
                $pages[$currentUrl] = [
                    'visits' => 1,
                    'unique_visitors' => [$ipAddress],
                    'timestamps' => [$currentTimestamp]
                ];
            }
        }

        // Usunięcie starszych znaczników czasu niż 30 dni temu
        foreach ($pages as $pageUrl => $pageData) {
            $filteredTimestamps = array_filter($pageData['timestamps'], function ($timestamp) use ($thirtyDaysAgo) {
                return $timestamp >= $thirtyDaysAgo;
            });
            $pages[$pageUrl]['timestamps'] = $filteredTimestamps;
        }

        // Aktualizacja pliku XML z informacjami o odwiedzanych stronach
        $xml = new SimpleXMLElement('<pages></pages>');
        foreach ($pages as $pageUrl => $pageData) {
            $page = $xml->addChild('page');
            $page->addChild('url', $pageUrl);
            $page->addChild('visits', $pageData['visits']);
            $page->addChild('unique_visitors', implode(',', $pageData['unique_visitors']));
            $page->addChild('timestamps', implode(',', $pageData['timestamps']));
        }
        $xml->asXML($pagesFile);

        // Sortowanie stron według liczby odwiedzin w odwrotnej kolejności
        uasort($pages, function ($a, $b) {
            return $b['visits'] <=> $a['visits'];
        });

        // Pobranie listy 100 najczęściej odwiedzanych stron z ostatnich 30 dni
        $top100Pages = $pages;
    };
};
