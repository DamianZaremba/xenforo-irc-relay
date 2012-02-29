<?php
if( !isset($_GET) || !array_key_exists('key', $_GET) || $_GET['key'] != 'something' ) {
	die();
}
require_once( 'library/config.php' );

mysql_connect( $config['db']['host'], $config['db']['username'], $config['db']['password'] );
mysql_select_db( $config['db']['dbname'] );

if( array_key_exists('lasttid', $_GET) ) {
	$sql = "SELECT * FROM `xf_thread`";
	$sql .= " WHERE `thread_id` > '" . mysql_real_escape_string( $_GET['lasttid'] ) . "'";
	$sql .= " ORDER BY `post_date` DESC";
} else {
	$sql = "SELECT * FROM `xf_thread`";
	$sql .= " ORDER BY `post_date` DESC";
	$sql .= " LIMIT 1";
}
$query = mysql_query( $sql );

$threads = array();
while( $row = mysql_fetch_assoc( $query ) ) {
	$url_title = str_replace(' ', '-', $row['title']);
	$url_title = str_replace('.', '', $url_title);
	$url_title = str_replace('?', '', $url_title);
	$url_title = str_replace('/', '-', $url_title);
	$url_title = str_replace('\\', '-', $url_title);
	$url_title = str_replace('"', '', $url_title);
	$url_title = str_replace('--', '-', $url_title);
	$url = "https://forum.com/threads/" . $url_title . "." . $row['thread_id'] . "/";

	$threads[] = array(
		'id' => $row['thread_id'],
		'url' => $url,
		'title' => $row['title'],
		'username' => $row['username'],
	);
}

if( array_key_exists('lastpid', $_GET) ) {
	$sql = "SELECT `xf_post`.*,`xf_thread`.`title` FROM `xf_post`,`xf_thread`";
	$sql .= " WHERE `xf_post`.`thread_id`=`xf_thread`.`thread_id`";
	$sql .= " AND `post_id` > '" . mysql_real_escape_string( $_GET['lastpid'] ) . "'";
	$sql .= " ORDER BY `post_date` DESC";
} else {
	$sql = "SELECT `xf_post`.*,`xf_thread`.`title` FROM `xf_post`,`xf_thread`";
	$sql .= " WHERE `xf_post`.`thread_id`=`xf_thread`.`thread_id`";
	$sql .= " ORDER BY `post_date` DESC";
	$sql .= " LIMIT 1";
}
$query = mysql_query( $sql );

$posts = array();
while( $row = mysql_fetch_assoc( $query ) ) {
	$url = "https://forum.com/posts/" . $row['post_id'] . "/";
	$posts[] = array(
		'id' => $row['post_id'],
		'url' => $url,
		'title' => $row['title'],
		'username' => $row['username'],
	);
}

die( json_encode( array( 't' => $threads, 'p' => $posts ) ) );
?>
