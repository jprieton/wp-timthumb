# Getting Started #

## Basic Usage ##

### Show the featured image path ###

In the loop:
```
<?php if (have_posts()) : ?>
  <?php while (have_posts()) : the_post(); ?>
    <!-- do stuff ... -->
    <img src="<?php the_featured_image() ?>">
  <?php endwhile; ?>
<?php endif; ?>
```

This return:
```
<!-- the loop -->
<img src="http://your-site/wp-content/uploads/2012/11/your-image.jpg">
<!-- end loop -->
```

### Get featured image object ###

In the loop, this return an **WP\_Post** object
```
<?php
if (have_posts()) :
  while (have_posts()) :
    the_post();
    // do stuff ...
    $image = get_featured_image();
    // do more stuff ...
  endwhile;
endif;
?>
```

### Show the first image image path ###

In the loop.

```
<?php if (have_posts()) : ?>
  <?php while (have_posts()) : the_post(); ?>
    <!-- do stuff ... -->
    <img src="<?php the_first_image() ?>">
  <?php endwhile; ?>
<?php endif; ?>
```

This return:
```
<img src="http://your-site/wp-content/uploads/2012/11/your-image.jpg">
```

### Get the first image object ###

In the loop, this return an **WP\_Post** object

```
<?php
if (have_posts()) :
  while (have_posts()) :
    the_post();
    // do stuff ...
    $image = get_first_image();
    // do more stuff ...
  endwhile;
endif;
?>
```

### Get all attachments from post ###

This return an array of **WP\_Post** object
```
<?php
if (have_posts()) :
  while (have_posts()) :
    the_post();
    // do stuff ...
    $attachments = get_post_attachments();
    foreach ($attachments as $item) {
      // do something with the attachment
    }

    // do more stuff ...
  endwhile;
endif;
?>
```

### Get all images from post ###

This return an array of **WP\_Post** object
```
<?php
if (have_posts()) :
  while (have_posts()) :
    the_post();
    // do stuff ...

    $images = get_post_images();
    foreach ($images as $item) {
      // do something with the image
    }

    // do more stuff ...
  endwhile;
endif;
?>
```

**Note:**
```
<?php get_post_images() ?>
```
is an shortcut for
```
<?php
$params['mime_type'] = 'image';
get_post_attachments($params);
?>
```
and/or
```
<?php
$params['mime_type'] = array('image/gif', 'image/jpeg', 'image/png');
get_post_attachments($params)
?>
```

Example:

```
<?php if (have_posts()) : ?>
  <?php while (have_posts()) : the_post(); ?>
    <?php $images = get_post_images(); ?>
    <? foreach ($images as $item) : ?>
      <!-- do stuff ... -->
      <img src="<?php echo $item->thumbnail ?>">
    <?php endforeach; ?>
  <?php endwhile; ?>
<?php endif; ?>
```

This return something as
```
<img src="http://your-site/wp-content/uploads/2012/11/your-image-1.jpg">
<img src="http://your-site/wp-content/uploads/2012/11/your-image-2.jpg">
<img src="http://your-site/wp-content/uploads/2012/11/your-image-3.jpg">
<!-- more images -->
<img src="http://your-site/wp-content/uploads/2012/11/your-image-end.jpg">
```


### Get galleries from content ###

```
<?php
if (have_posts()) :
  while (have_posts()) :
    the_post();
    // do stuff ...
    $galleries = get_post_galleries(); // Return an array of galleries

    foreach ($galleries as $gallery) {
      // Loop for each gallery in case of more than one

      foreach ($gallery as $image) {
        // Loop for each image as WP_Post object
        // do something with the image
      }
    }
    // do more stuff ...
  endwhile;
endif;
?>
```

This return an array of galleries with an array of **WP\_Post** object