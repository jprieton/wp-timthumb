# How obtain all attachments #

# Use #

## Object ##

```
<?php
$tt = new WP_Timthumb();
$params = array();
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

# Defaults #

```
<?php
$params = array(
		'object' => true,
		'post_id' => null,
		'limit' => -1
);
?> 
```