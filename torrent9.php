<?php
require('simple_html_dom.php');
$base_url = 'http://www.torrent9.cc';
$uri = [
	'films' => '/torrents_films.html',
	'series' => '/torrents_series.html',
	'musique' => '/torrents_musique.html',
	'ebook' => '/torrents_ebook.html',
	'logiciels' => '/torrents_logiciels.html',
	'jeux-pc' => '/torrents_jeux-pc.html',
	'jeux-consoles' => 'torrents_jeux-consoles.html'
];

/*function _is_curl() {
    return  (in_array  ('curl', get_loaded_extensions()))?true:false;
}
*/
function _get_URI() {
	return ($_SERVER['HTTPS']?'https':'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
}
/*
if(!_is_curl())  {
	echo "<b>Install and load <a href='http://php.net/manual/en/book.curl.php'>curl extension</a></b>";
	exit;
}*/

header('Content-type: application/rss+xml; charset=utf-8');
echo '<?xml version="1.0" encoding="utf-8"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
  <channel>
';
echo '    <atom:link href="'._get_URI().'" rel="self" type="application/rss+xml" />
';
if(isset($_GET['f'])) $_POST['f'] = $_GET['f'];
if(!isset($_POST['f']) || empty($_POST['f']) || !$uri[strtolower($_POST['f'])]) $_POST['f'] = 'films';

echo "    <title>Torrent9 - ".ucfirst($_POST['f'])."</title>
    <description>".ucfirst($_POST['f'])." de Torrent9</description>
";
$url = $base_url.$uri[strtolower($_POST['f'])];
echo "    <link>"._get_URI()."</link>
";
$html = file_get_html($url);
/*exec('/usr/bin/python /www/reader/scraping/cf.py "'.$url.'"', $htmla); //file_get_html($url);
var_dump($htmla);
die;
$html = implode('
	', $htmla);
$html = str_get_html($html);
var_dump($html);*/
//die;
$i = 0;
foreach($html->find('td a') as $element) {
	if($i++ >= 10 ) break;
	$htmlaa = array();
      //echo $element->class. ' - '. $element->title. ' - '. $element->href . '<br>';
      exec('python /www/reader/scraping/cf.py "'.$base_url.$element->href.'"', $htmlaa);
      $detail = str_get_html(implode('
	', $htmlaa));
      if(!$detail) break;
	/*$detail = file_get_html($base_url.$element->href);*/
	foreach($detail->find('div[class=left-tab-section] a[class=btn btn-danger download]') as $lien) {
		$mylink = $lien->href;
		break;
	}
	foreach($detail->find('h5') as $titre) {
		$mytitle = $titre->plaintext;
		break;
	}
	foreach($detail->find('div[class=movie-information]') as $info) {
		$mydescription = $info->last_child () ->innertext;
	}
	foreach($detail->find('div[class=movie-information] ul li',9) as $info) {
		if(isset($info->innertext))
			$mydescription .= "<br /><br />Poids : ".$info->last_child () ->innertext."<br />";
	}

	echo "    <item>
	      <title>",htmlspecialchars(stripslashes($mytitle),ENT_QUOTES,'UTF-8'),"</title>
	      <description>",(isset($mydescription)?htmlspecialchars(stripslashes($mydescription),ENT_QUOTES,'UTF-8'):""),"</description>
	      <link>$base_url".(isset($mylink)?$mylink:"")."</link>
	      <guid>$base_url".(isset($mylink)?$mylink:"")."</guid>
    </item>
";
$detail->clear(); 
unset($detail);
}
/*   exit;


$p = file_get_contents($url);
//echo $p;
preg_match_all('/<a title="Télécharger .*?" href="(.*)?" /im', $p, $m);

$i=0;
$mh = curl_multi_init();
$ch = array();
//$dd = array();
$i = 0;

while(isset($m[1][$i])) {
	$ch[$i] = curl_init();
	curl_setopt_array($ch[$i],
		Array(
			CURLOPT_URL => $base_url.$m[1][$i],
			CURLOPT_USERAGENT => 'GheopReader',
			CURLOPT_TIMEOUT => 5,
			CURLOPT_CONNECTTIMEOUT => 10,
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_ENCODING => 'UTF-8'
			)
		);
	curl_multi_add_handle($mh, $ch[$i]);
	//$dd[$i] = $m[2][$i];
	$i++;
}

$running=null;

do {
	curl_multi_exec($mh,$running);
	//usleep (1000);
} while ($running > 0);

for($j=0;$j<$i;$j++) {
	//if($_POST['f'] == 'films')
		preg_match('/<h5 class="pull-left" style="max-width:inherit"><i class="fa fa-.*"><\/i> (.*)<\/h5>.*<\/strong><\/p>.*<p>(.*)<\/p>.*<a class="btn btn-danger download" href="(.*)">/smiU', curl_multi_getcontent($ch[$j]), $z);

		if(isset($z[1]))
	echo "    <item>
      <title>",htmlspecialchars(stripslashes($z[1]),ENT_QUOTES,'UTF-8'),"</title>
      <description>",(isset($z[2])?htmlspecialchars(stripslashes($z[2]),ENT_QUOTES,'UTF-8'):""),"</description>
      <link>$base_url".(isset($z[3])?$z[3]:"")."</link>
      <guid>$base_url".(isset($z[3])?$z[3]:"")."</guid>
    </item>
";
}*/
?>
  </channel>
</rss>
