# Configuración de imágenes locales

Este proyecto ahora guarda **todas** las fotos directamente dentro de `Public/img`, sin dependencias externas ni capas adicionales. A continuación se detallan los puntos clave para mantener el flujo funcionando:

## Carpetas esperadas

```
Public/
  img/
    imgDispositivos/
    imgPerfil/
    imgInventario/
    ticket/
```

Si alguna carpeta no existe, el helper `Core/ImageHelper.php` la crea automáticamente cuando se sube la primera imagen. Aun así, en entornos Linux conviene crearlas con los permisos correctos (por ejemplo `mkdir -p Public/img/ticket && chmod 775 Public/img/ticket`).

## Permisos sugeridos

- Usuario/grupo del servidor web con escritura sobre `Public/img` y subcarpetas.
- Umask que permita que los archivos queden con permisos `664` y los directorios con `775`.

## Diagnóstico rápido

Los administradores pueden revisar el estado del árbol de imágenes entrando a:

```
index.php?route=Default/MediaDiag
```

Este endpoint lista cada carpeta manejada por `ImageHelper`, incluyendo cantidad de archivos y espacio ocupado.

## Rutas guardadas en la BD

Los modelos solamente almacenan rutas **relativas** (por ejemplo `img/imgDispositivos/7/foto.jpg`). Las vistas consumen estos valores mediante funciones como `device_image_url`, `profile_image_url` o `inventory_image_url`, garantizando que siempre apunten dentro de `Public/img`.

## Limpieza y reemplazo

Cuando se actualiza una foto (perfil, inventario, etc.) los helpers eliminan el archivo anterior para evitar residuos. Si se necesita purgar manualmente, basta con borrar el archivo correspondiente bajo `Public/img` y actualizar la ruta en la base de datos.
