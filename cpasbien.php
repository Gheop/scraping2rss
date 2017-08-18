<?php
$base_url = 'http://www.cpasbien.cm';
$uri = [
	'films' => '/view_cat.php?categorie=films',
	'series' => '/view_cat.php?categorie=series',
	'musique' => '/view_cat.php?categorie=musique',
	'ebook' => '/view_cat.php?categorie=ebook',
	'logiciels' => '/view_cat.php?categorie=logiciels',
	'jeux-pc' => '/view_cat.php?categorie=jeux-pc',
	'jeux-consoles' => '/view_cat.php?categorie=jeux-consoles'
];
/*$uri_films = '/view_cat.php?categorie=films';
$uri_series = ;
$uri_musique = ;
*/
function _is_curl() {
    return  (in_array  ('curl', get_loaded_extensions()))?true:false;
}

function _get_URI() {
	return ($_SERVER['HTTPS']?'https':'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
}

if(!_is_curl())  {
	echo "<b>Install and load <a href='http://php.net/manual/en/book.curl.php'>curl extension</a></b>";
	exit;
}

header('Content-type: application/rss+xml; charset=utf-8');
echo '<?xml version="1.0" encoding="utf-8"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
  <channel>
';
echo '    <atom:link href="'._get_URI().'" rel="self" type="application/rss+xml" />
';
if(isset($_GET['f'])) $_POST['f'] = $_GET['f'];
if(!isset($_POST['f']) || empty($_POST['f']) || !$uri[$_POST['f']]) $_POST['f'] = 'films';

echo "    <title>Cpasbien - ".ucfirst($_POST['f'])."</title>
    <description>".ucfirst($_POST['f'])." de Cpasbien</description>
";
$url = $base_url.$uri[$_POST['f']];
echo "    <link>"._get_URI()."</link>
";
$p = file_get_contents($url);
preg_match_all('/<a href="(http:\/\/www.cpasbien.cm\/dl-torrent\/[^"]*)"[^>]*>[^>]*>([^<]+)/im', $p, $m);
$i=0;
$mh = curl_multi_init();
$ch = array();
$dd = array();
$i = 0;

while(isset($m[1][$i])) {
	$ch[$i] = curl_init();
	curl_setopt_array($ch[$i],
		Array(
			CURLOPT_URL => $m[1][$i],
			CURLOPT_USERAGENT => 'GheopReader',
			CURLOPT_TIMEOUT => 5,
			CURLOPT_CONNECTTIMEOUT => 10,
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_ENCODING => 'UTF-8'
			)
		);
	curl_multi_add_handle($mh, $ch[$i]);
	$dd[$i] = $m[2][$i];
	$i++;
}

$running=null;

do {
	curl_multi_exec($mh,$running);
	//usleep (1000);
} while ($running > 0);

for($j=0;$j<$i;$j++) {
	preg_match('/<\/strong><\/p>(<p>.*<strong>.*<\/strong>.*<\/p>)?.*<p>(.*)<\/p>.*<b>.*<\/b>.*<a href="(.*\.torrent)"/smi', curl_multi_getcontent($ch[$j]), $z);
//	preg_match('/<a href="(.*)"/smi', curl_multi_getcontent($ch[$j]), $z);
	echo "    <item>
      <title>",htmlspecialchars(stripslashes($dd[$j]),ENT_QUOTES,'UTF-8'),"</title>
      <description>",(isset($z[2])?htmlspecialchars(stripslashes($z[2]),ENT_QUOTES,'UTF-8'):""),"</description>
      <link>$base_url".(isset($z[3])?$z[3]:"")."</link>
      <guid>$base_url".(isset($z[3])?$z[3]:"")."</guid>
    </item>
";
}
?>
  </channel>
</rss>
