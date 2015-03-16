## Parametros ##
```
$params = array(
  'limit' => -1,  // Todos los adjuntos
  'object' => TRUE,  // Devuelve un objeto
  'df' => NULL // Valor por defecto cuando no exista un adjunto
  'mime_type' => NULL // Tipo de adjunto
)
```

# Mostrar la imagen destacada del post #

Busca la imagen destacada del post e imprime un string con la url de la imagen.

```
<div>
  <?php while(have_posts()) : ?>
    <?php the_post(); ?>
    <img src="<?php the_featured_image()?>" alt="">
  <?php } ?>
<div>
```

# Obtener la imagen destacada del post #

Busca la imagen destacada del post y devuelve un objeto u string
```
<?php
while(have_posts()) {
  the_post();
  $image = get_featured_image()
}
?>
```

# Obtener la primera imagen #

```
<?php
while(have_posts()) {
  the_post();
  $image = get_first_image()
}
?>
```

# Obtener todas las imágenes #

```
<?php
while(have_posts()) {
  the_post();
  $image = get_all_images()
}
?>
```

# Obtener todos los adjuntos #

```
<?php
while(have_posts()) {
  the_post();
  $image = get_all_attachments()
}
?>
```