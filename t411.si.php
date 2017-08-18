<?php
require('simple_html_dom.php');
$base_url = 'https://t411.si';
$site_name = 't411.si';
$uri = [
	'films' => '/torrents/search/?search=&category=1',
	'series' => '/torrents/search/?search=&category=2&subcategory=&submit=',
	'animes' => '/torrents/search/?search=&category=3&subcategory=&submit=',
	'musique' => '/torrents/search/?search=&category=4&subcategory=&submit=',
	'livres' => '/torrents/search/?search=&category=5&subcategory=&submit='
];

function _get_URI() {
	return ($_SERVER['HTTPS']?'https':'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
}

function file_url($url){
  $parts = parse_url($url);
  $path_parts = array_map('rawurldecode', explode('/', $parts['path']));

  return
    $parts['scheme'] . '://' .
    $parts['host'] .
    implode('/', array_map('rawurlencode', $path_parts))
  ;
}

header('Content-type: application/rss+xml; charset=utf-8');
echo '<?xml version="1.0" encoding="utf-8"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
  <channel>
';
echo '    <atom:link href="'._get_URI().'" rel="self" type="application/rss+xml" />
';
if(isset($_GET['f'])) $_POST['f'] = $_GET['f'];
if(!isset($_POST['f']) || empty($_POST['f']) || !$uri[strtolower($_POST['f'])]) $_POST['f'] = 'films';

echo "    <title>$site_name - ".ucfirst($_POST['f'])."</title>
    <description>".ucfirst($_POST['f'])." de $site_name</description>
";
$url = $base_url.$uri[strtolower($_POST['f'])];
echo "    <link>".file_url($url)."</link>
";
$html = file_get_html($url);

$i = 0;
foreach($html->find('tr[class=bordergrey isItem isItemDesk] td[class=grid-title f12 wb m-name] a') as $element) {
	if($i++ >= 20 ) break;
	$detail = file_get_html(file_url($base_url.$element->href));
	foreach($detail->find('a[class=dp b100 flex align-center aic nounderline]') as $lien) {
		//if($lien->title != 'Download torrent file') continue;
		$mylink = $base_url.$lien->href;
		break;
	}
	foreach($detail->find('h1[class=noh ww]') as $titre) {
		$mytitle = str_replace('Torrent: ','',$titre->plaintext);
		break;
	}
	foreach($detail->find('div[class=full-width greyback column mauto w m8 noradiustop ww]') as $info) {
		$mydescription = $info->innertext;
	}
	$j = 0;
	foreach($detail->find('div[class=b85]') as $info) {
		$j++;
		if($j != 3) continue;
		if(isset($info->plaintext))
			$mytitle .= " (".$info->plaintext.")";
	}

	echo "    <item>
	      <title>",htmlspecialchars(stripslashes($mytitle),ENT_QUOTES,'UTF-8'),"</title>
	      <description>",(isset($mydescription)?htmlspecialchars(stripslashes($mydescription),ENT_QUOTES,'UTF-8'):""),"</description>
	      <link>".(isset($mylink)?$mylink:"")."</link>
	      <guid>".(isset($mylink)?$mylink:"")."</guid>
    </item>
";
$detail->clear(); 
unset($detail);
}

?>
  </channel>
</rss>
