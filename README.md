# Web de la Fundación Carmen Arias

Tema de WordPress a medida para [carmenarias.es](https://carmenarias.es).

- `carmenarias/`: el tema.
- `dist/carmenarias.zip`: el tema empaquetado, listo para subir a WordPress.
- `herramientas/convertir_divi.py`: el script que pasó las páginas hechas con Divi a bloques normales de WordPress.
- `propuesta/`: la maqueta inicial de la portada.

## Instalación

1. **Haz una copia de seguridad completa** (base de datos y archivos), por ejemplo con UpdraftPlus o desde el hosting.
2. Lo ideal es probarlo antes en una copia de la web (staging). Si el hosting no lo ofrece, el plugin WP Staging la crea.
3. En WordPress, ve a **Apariencia → Temas → Añadir nuevo → Subir tema**, sube `dist/carmenarias.zip` y actívalo.
4. Ve a **Apariencia → Migrar contenido** y pulsa **Migrar contenido**. Esto hace tres cosas:
   - Pasa las 24 páginas al nuevo diseño. Las páginas de Divi se cambian por bloques normales con los mismos textos, fotos, desplegables y galerías. WordPress guarda una revisión de cada página antes de cambiarla.
   - Crea el menú principal y el menú legal del pie.
   - Pone «Inicio» como portada.
5. Revisa la web. Si todo está bien, ya puedes **desactivar el plugin Divi Builder** si lo tenéis instalado, y borrar el tema Divi.

Las **noticias no se tocan**. Unas 540 se escribieron con Divi; el tema las muestra limpias sin modificar la base de datos.

## Qué se edita y dónde

| Qué | Dónde |
| --- | --- |
| Textos de la portada: titular, franja en movimiento, cifras, historia y cita | Apariencia → Personalizar → Fundación Carmen Arias → Portada |
| Teléfono, email, dirección y redes sociales | Apariencia → Personalizar → Fundación Carmen Arias → Datos de contacto |
| Logo | Apariencia → Personalizar → Identidad del sitio |
| Centros: nombre corto, etiqueta (p. ej. «40 plazas») y resumen | Al editar la página de cada centro, en la caja lateral «Centro de la Fundación» |
| Orden de los centros | En cada página, Atributos de página → Orden |
| Menús | Apariencia → Menús |
| Noticias | Entradas, igual que ahora. Las 3 últimas salen solas en la portada. |

### Plantillas de página

- **Centro de la Fundación**: cabecera con la etiqueta de plazas y un lateral para llamar y para ir a los otros centros.
- **Lista de centros**: usada en «Servicios».
- **Listado de noticias**: muestra la categoría con el mismo nombre que la página («Noticias» → categoría NOTICIAS, «Actividades» → ACTIVIDADES).
- **Contacto**: datos de contacto y formulario.

### Formularios

- El formulario de contacto envía a la dirección de **Datos de contacto → Email de contacto**. Se puede poner en cualquier página con `[carmenarias_contacto]`.
- El del Canal de Información (`[carmenarias_contacto tipo="canal"]`) permite comunicaciones anónimas y envía al **Email del Canal de Información**.
- Para que los correos lleguen bien, instala un plugin SMTP (por ejemplo WP Mail SMTP) con la cuenta de correo de la Fundación.

## Detalles del tema

- Fuentes alojadas en el propio tema (Bricolage Grotesque y Atkinson Hyperlegible). Atkinson Hyperlegible está diseñada para personas con baja visión, y al no cargar nada de Google Fonts se cumple mejor el RGPD.
- Botón **«Letra grande»** en la barra superior. Se recuerda en cada navegador.
- Las animaciones se desactivan solas si el dispositivo tiene activado «reducir movimiento».
- Visor de fotos para las galerías: se puede navegar con las flechas del teclado o deslizando el dedo.
- Sin dependencias: no hace falta ningún plugin ni constructor visual.

## Volver a generar el contenido de las páginas

El archivo `carmenarias/inc/migracion/paginas.json` se generó así:

```bash
curl -s "https://carmenarias.es/wp-json/wp/v2/pages?per_page=100" -o paginas.json
mkdir medios && for p in $(seq 1 34); do curl -s "https://carmenarias.es/wp-json/wp/v2/media?per_page=100&page=$p" -o medios/m$p.json; sleep 2; done
python3 herramientas/convertir_divi.py paginas.json carmenarias/inc/migracion/paginas.json medios
```
