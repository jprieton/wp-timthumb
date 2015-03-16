# Descripción #

Devuelve la primera imagen del post.

# Uso #

```
<?php $image = get_featured_image($params); ?>
```

# Uso por defecto #

```
<?php
$args = array(
	 'object'    => true,
	 'size'      => 'large',
	 'post_id'   => null,
	 'default'   => ''
);
?> 
```

# Parámetros #

**object**
> (bool) Define si devuelve un objeto con todos los atributos del adjunto o sólo la url.
    * true - default, devuelve el objeto con todos los atributos del adjunto.
    * false - devuelve un string con el url del adjunto.

**size**
> (string) Sobrescribe el valor de la imagen a recortar por defecto, por defecto tomará la imagen más aproximada autogenerada por WordPress y redimensionarla/recortarla a los parámetros de TimThumb especificados.

**post\_id**
> (int) El ID del post, por defecto tomará el valor que devuelve la función <a href='http://codex.wordpress.org/Function_Reference/get_the_ID'>get_the_ID()</a>

**default**
> (string) La imagen que mostrará por defecto en caso de que no haya ninguna imagen adjunta al post.

# Ejemplos #

```
<?php $image = get_featured_image(); ?>
```

# Relacionado #
[TheFirstImage](TheFirstImage.md)