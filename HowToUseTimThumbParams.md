# Using the parameters of TimThumb Image Resize #

In addition to the parameters required by each method of obtaining images / attachments may include additional parameters specific to the TimThumb library,
these parameters can be reviewed on the site of the project: [Timthumb 101: Parameters listing](http://www.binarymoon.co.uk/2012/02/complete-timthumb-parameters-guide/)

# Example #

Display the first image with a crop to 100x100px:
```
<?php
$params = array(
	'w' => 100,
  'h' => 100,
);
?>
<img src=" <?php the_first_image($params); ?>
```