<?php

header('Content-Type: text/xml; charset=utf-8', true);

echo '<?xml version="1.0" encoding="utf-8"?'.'>'; ?>
<rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
	xmlns:slash="http://purl.org/rss/1.0/modules/slash/">

<channel>
	<title>Shared Items of <?php echo $name ?></title>
	<atom:link href="<?php echo $rss_url; ?>" rel="self" type="application/rss+xml" />
	<link><?php echo $rss_url; ?></link>
	<description>Shared Items of <?php echo $name; ?></description>
	<lastBuildDate><?php echo date('D, d M Y H:i:s +0000', $lastdate); ?></lastBuildDate>
	<?php foreach($items as $item): ?>
	<item>
		<title><?php echo $item->title; ?></title>
		<link><?php echo $item->link; ?>#from=<?php echo $name ?></link>
		<pubDate><?php echo date('Y-m-d H:i:s', (int) $item->pubDate); ?></pubDate>
		<dc:creator><?php echo $item->author; ?></dc:creator>
		<description><![CDATA[<?php echo $item->description; ?>]]></description>
	</item>
	<?php endforeach; ?>
</channel>
</rss>
