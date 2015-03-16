# How obtain all images #

## Object ##

```
<?php
$tt = new WP_Timthumb();
$params = array();
$tt->get_post_images($params);
foreach ($tt->post_attachments as $attachment) {
	//...//
}
?>
```

It's same result as...

```
<?php
$tt = new WP_Timthumb();
$params = array(
		'mime_type' => array('image/gif', 'image/jpeg', 'image/png')
);
$tt->get_post_attachments($params);
foreach ($tt->post_attachments as $attachment) {
	//...//
}
?>
```


## Method ##
```
<?php
$params = array();
$attachments = get_post_attachments($params);
foreach ($attachments as $attachment) {
	//...//
}
?>
```

```
<?php
$params = array(
		'object' => true,
		'post_id' => null,
		'limit' => -1
);
?> 
```