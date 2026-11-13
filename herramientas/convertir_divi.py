#!/usr/bin/env python3
"""Convierte el HTML renderizado por Divi de las páginas de carmenarias.es
en bloques limpios de Gutenberg.

Uso:
    python3 convertir_divi.py paginas.json salida.json [carpeta_medios]

`carpeta_medios` (opcional) contiene las respuestas de /wp-json/wp/v2/media
y sirve para recuperar el ID de las imágenes de las galerías clásicas.

`paginas.json` es la respuesta de /wp-json/wp/v2/pages?per_page=100.
La salida es una lista de páginas con su contenido en bloques, que el tema
carga desde Apariencia → Migrar contenido.
"""
import html
import json
import re
import sys

from bs4 import BeautifulSoup, NavigableString, Tag

OLD_DOMAIN = "carmenarias.artconsulting.es"
NEW_DOMAIN = "carmenarias.es"

# Páginas que el tema genera por sí mismo (portada, listados, contacto).
SKIP = {"inicio", "sample-page"}

# Plantilla y datos de cada centro para la portada y la cabecera.
CENTROS = {
    "residencia": dict(orden=1, etiqueta="146 plazas", titulo="Residencia Ntra. Sra. del Carmen",
        resumen="Instalaciones amplias y modernas y un equipo multidisciplinar: enfermería, medicina, fisioterapia, terapia ocupacional, logopedia e integración social."),
    "dano-cerebral-sobrevenido": dict(orden=2, etiqueta="Desde 2008", titulo="Daño Cerebral Sobrevenido",
        resumen="Módulo para personas jóvenes, en un entorno sin barreras, con rehabilitación funcional e integración social."),
    "problemas-de-salud-mental": dict(orden=3, etiqueta="Desde 2018", titulo="Salud Mental",
        resumen="Plazas residenciales permanentes con la Fundación Sociosanitaria de Castilla-La Mancha, plenamente integradas en la vida del centro."),
    "centro-de-dia": dict(orden=4, etiqueta="40 plazas", titulo="Centro de Día",
        resumen="Fisioterapia, terapia ocupacional y animación sociocultural para mantener la autonomía y volver a casa cada tarde."),
    "sepap": dict(orden=5, etiqueta="65 plazas", titulo="SEPAP",
        resumen="Servicio de Promoción de la Autonomía Personal, para seguir viviendo en el propio hogar el mayor tiempo posible."),
    "ayuda-a-domicilio": dict(orden=6, etiqueta="+100 personas", titulo="Ayuda a Domicilio",
        resumen="Con el Ayuntamiento de Socuéllamos desde 2012. 30 auxiliares cuidan a cada persona en su propia casa."),
    "ciclo-de-grado-medio-de-atencion": dict(orden=7, etiqueta="FP Dual", titulo="Formación",
        resumen="Ciclo de Grado Medio de Atención a Personas en Situación de Dependencia, en formación dual con el I.E.S. Fernando de Mena."),
}

# Títulos más legibles que los que había en mayúsculas.
TITULOS = {
    "residencia": "Residencia Nuestra Señora del Carmen",
    "dano-cerebral-sobrevenido": "Daño Cerebral Sobrevenido",
    "problemas-de-salud-mental": "Plazas residenciales para personas con problemas de salud mental",
    "centro-de-dia": "Centro de Día",
    "sepap": "SEPAP · Servicio de Promoción de la Autonomía Personal",
    "ayuda-a-domicilio": "Servicio de Ayuda a Domicilio",
    "ciclo-de-grado-medio-de-atencion": "Ciclo de Grado Medio de Atención a Personas en Situación de Dependencia",
    "mision": "Misión", "vision": "Visión", "valores": "Valores",
    "contacto": "Contacto", "organigrama": "Organigrama", "calidad": "Calidad",
    "transparencia": "Transparencia", "noticias": "Noticias", "actividades": "Actividades",
    "canal-de-informacion": "Canal de información", "canal-de-informacion-2": "Sistema Interno de Información",
    "colabora-con-nosotros": "Colabora con nosotros", "aviso-legal": "Aviso legal",
    "politica-privacidad": "Política de privacidad", "politica-de-cookies": "Política de cookies",
    "talleres-en-semana-santa": "Talleres en Semana Santa",
}

LISTADOS = {"noticias": "noticias", "actividades": "actividades"}

MEDIA = {}  # url -> (id, url a tamaño completo)


def cargar_medios(carpeta):
    import glob, os
    for f in glob.glob(os.path.join(carpeta, "*.json")):
        for m in json.load(open(f, encoding="utf-8")):
            full = m.get("source_url", "")
            MEDIA[full] = (m["id"], full)
            for sz in ((m.get("media_details") or {}).get("sizes") or {}).values():
                if sz.get("source_url"):
                    MEDIA[sz["source_url"]] = (m["id"], full)


def buscar_medio(url):
    url = fix_url((url or "").split("?")[0])
    return MEDIA.get(url)


INLINE_OK = {"strong", "b", "em", "i", "a", "br", "sup", "sub", "u", "s", "code"}


def fix_url(u):
    return (u or "").replace(OLD_DOMAIN, NEW_DOMAIN).replace("http://" + NEW_DOMAIN, "https://" + NEW_DOMAIN)


def esc(s):
    return html.escape(s, quote=True)


def clean_inline(node):
    """Devuelve el HTML interior de `node` con solo etiquetas en línea permitidas."""
    out = []
    for c in node.children:
        if isinstance(c, NavigableString):
            out.append(esc(str(c)))
            continue
        if not isinstance(c, Tag):
            continue
        name = c.name.lower()
        inner = clean_inline(c)
        if name == "br":
            out.append("<br>")
        elif name in ("b", "strong"):
            if inner.strip():
                out.append(f"<strong>{inner}</strong>")
        elif name in ("i", "em"):
            if inner.strip():
                out.append(f"<em>{inner}</em>")
        elif name == "a":
            href = fix_url(c.get("href", ""))
            if href.startswith("mailto:"):
                href = href.rstrip(".")
            attrs = f' href="{esc(href)}"' if href else ""
            if c.get("target") == "_blank":
                attrs += ' target="_blank" rel="noreferrer noopener"'
            out.append(f"<a{attrs}>{inner}</a>")
        elif name in INLINE_OK:
            out.append(f"<{name}>{inner}</{name}>")
        elif name == "img":
            pass  # las imágenes en línea se tratan aparte
        else:
            out.append(inner)
    return "".join(out)


def norm_text(s):
    s = s.replace("\xa0", " ")
    s = re.sub(r"[ \t\r\n]+", " ", s)
    return s.strip()


def is_empty_html(s):
    t = re.sub(r"<br\s*/?>", "", s)
    t = re.sub(r"<[^>]+>", "", t)
    return not norm_text(html.unescape(t))


def tidy(s):
    s = re.sub(r"\s+", " ", s.replace("\xa0", " ")).strip()
    s = re.sub(r"(?<![.])\.\.(?![.])", ".", s)
    s = re.sub(r"(<br>\s*)+$", "", s)
    s = re.sub(r"^(\s*<br>)+", "", s)
    return s.strip()


def b_par(inner, align=None):
    inner = tidy(inner)
    if is_empty_html(inner):
        return ""
    return f"<!-- wp:paragraph -->\n<p>{inner}</p>\n<!-- /wp:paragraph -->"


def b_heading(inner, level):
    inner = tidy(inner)
    if is_empty_html(inner):
        return ""
    # quita negritas redundantes dentro de títulos
    inner = re.sub(r"</?strong>", "", inner)
    # los títulos en MAYÚSCULAS se pasan a frase
    plain = norm_text(re.sub(r"<[^>]+>", "", html.unescape(inner)))
    if plain.isupper() and len(plain) > 3 and "<a" not in inner:
        inner = esc(sentence_case(plain))
    elif plain[:1].islower() and "<" not in inner:
        inner = inner[:1].upper() + inner[1:]
    lvl = "" if level == 2 else f' {{"level":{level}}}'
    return f"<!-- wp:heading{lvl} -->\n<h{level} class=\"wp-block-heading\">{inner}</h{level}>\n<!-- /wp:heading -->"


ACRONYMS = {"SEPAP", "SII", "FSCLM", "ACESCAM", "IES", "I.E.S.", "ONCE", "SAD", "FP", "PDF", "LOPD", "RGPD", "MEJORAT", "FCA"}


def sentence_case(t):
    words = t.split(" ")
    out = []
    for i, w in enumerate(words):
        core = w.strip(".,:;()«»\"'")
        if core in ACRONYMS:
            out.append(w)
        elif i == 0:
            out.append(w[:1].upper() + w[1:].lower())
        else:
            out.append(w.lower())
    s = " ".join(out)
    for name in ("carmen arias", "socuéllamos", "castilla-la mancha", "castilla la mancha", "ntra. sra.", "nuestra señora del carmen", "fernando de mena", "ciudad real"):
        s = re.sub(re.escape(name), lambda m: m.group(0).title(), s, flags=re.I)
    return s


def b_list(tag):
    items = []
    for li in tag.find_all("li", recursive=False):
        inner = tidy(clean_inline(li))
        if is_empty_html(inner):
            continue
        items.append(f"<!-- wp:list-item -->\n<li>{inner}</li>\n<!-- /wp:list-item -->")
    if not items:
        return ""
    ordered = tag.name == "ol"
    attrs = ' {"ordered":true}' if ordered else ""
    t = "ol" if ordered else "ul"
    return f"<!-- wp:list{attrs} -->\n<{t} class=\"wp-block-list\">" + "\n".join(items) + f"</{t}>\n<!-- /wp:list -->"


def best_src(img):
    src = img.get("src", "")
    srcset = img.get("srcset", "")
    best, bw = src, 0
    for part in srcset.split(","):
        bits = part.strip().split(" ")
        if len(bits) == 2 and bits[1].endswith("w"):
            try:
                w = int(bits[1][:-1])
            except ValueError:
                continue
            if w > bw and w <= 1600:
                best, bw = bits[0], w
    return fix_url(best)


def img_id(img):
    m = re.search(r"wp-image-(\d+)", " ".join(img.get("class", [])))
    if m:
        return int(m.group(1))
    hit = buscar_medio(img.get("src", ""))
    return hit[0] if hit else None


def b_image(img, caption=""):
    src = best_src(img)
    if not src:
        return ""
    alt = esc(img.get("alt", "") or "")
    iid = img_id(img)
    attrs = {"sizeSlug": "large", "linkDestination": "none"}
    cls = "wp-block-image size-large"
    imgcls = ""
    if iid:
        attrs = {"id": iid, **attrs}
        imgcls = f' class="wp-image-{iid}"'
    cap = f'<figcaption class="wp-element-caption">{caption}</figcaption>' if caption else ""
    return (f"<!-- wp:image {json.dumps(attrs)} -->\n<figure class=\"{cls}\"><img src=\"{esc(src)}\" alt=\"{alt}\"{imgcls}/>{cap}</figure>\n<!-- /wp:image -->")


def b_gallery(imgs):
    inner = []
    for a_img in imgs:
        img = a_img
        src = best_src(img)
        a = img.find_parent("a")
        hit = buscar_medio(img.get("src", ""))
        if a and a.get("href", "").lower().endswith((".jpg", ".jpeg", ".png", ".webp")):
            src = fix_url(a["href"])
        elif hit:
            src = hit[1]
        iid = img_id(img)
        attrs = {"sizeSlug": "large", "linkDestination": "none"}
        imgcls = ""
        if iid:
            attrs = {"id": iid, **attrs}
            imgcls = f' class="wp-image-{iid}"'
        alt = esc(img.get("alt", "") or "")
        inner.append(f"<!-- wp:image {json.dumps(attrs)} -->\n<figure class=\"wp-block-image size-large\"><img src=\"{esc(src)}\" alt=\"{alt}\"{imgcls}/></figure>\n<!-- /wp:image -->")
    if not inner:
        return ""
    return ('<!-- wp:gallery {"linkTo":"none"} -->\n<figure class="wp-block-gallery has-nested-images columns-default is-cropped">'
            + "\n".join(inner) + "</figure>\n<!-- /wp:gallery -->")


def b_button(text, href):
    href = fix_url(href)
    return ('<!-- wp:buttons -->\n<div class="wp-block-buttons"><!-- wp:button -->\n'
            f'<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="{esc(href)}">{esc(text)}</a></div>\n'
            '<!-- /wp:button --></div>\n<!-- /wp:buttons -->')


def b_details(summary, inner_blocks):
    if not inner_blocks.strip():
        return ""
    if summary.isupper() and len(summary) > 3:
        summary = sentence_case(summary)
    return (f"<!-- wp:details -->\n<details class=\"wp-block-details\"><summary>{esc(summary)}</summary>{inner_blocks}</details>\n<!-- /wp:details -->")


def b_html(raw):
    return f"<!-- wp:html -->\n{raw}\n<!-- /wp:html -->"


def b_table(tag):
    rows = []
    for tr in tag.find_all("tr"):
        cells = []
        for td in tr.find_all(["td", "th"]):
            cells.append(f"<td>{tidy(clean_inline(td))}</td>")
        rows.append("<tr>" + "".join(cells) + "</tr>")
    return ('<!-- wp:table -->\n<figure class="wp-block-table"><table><tbody>' + "".join(rows)
            + "</tbody></table></figure>\n<!-- /wp:table -->")


def flow_to_blocks(node):
    """Convierte contenido de flujo (texto de un módulo) en bloques."""
    blocks = []
    buf = []  # texto suelto entre bloques

    def flush():
        if buf:
            s = "".join(buf)
            buf.clear()
            for chunk in re.split(r"(?:<br>\s*){2,}", s):
                b = b_par(chunk)
                if b:
                    blocks.append(b)

    for c in list(node.children):
        if isinstance(c, NavigableString):
            if str(c).strip():
                buf.append(esc(str(c)))
            continue
        if not isinstance(c, Tag):
            continue
        name = c.name.lower()
        if name in INLINE_OK or name == "span":
            imgs = c.find_all("img")
            if imgs and name in ("a", "span"):
                flush()
                for im in imgs:
                    blocks.append(b_image(im))
                continue
            buf.append(clean_inline(c) if name == "span" else clean_inline(BeautifulSoup(f"<x>{c}</x>", "html.parser").x))
            continue
        flush()
        if name == "p":
            imgs = c.find_all("img")
            for im in imgs:
                blocks.append(b_image(im))
            b = b_par(clean_inline(c))
            if b:
                blocks.append(b)
        elif name in ("h1", "h2"):
            blocks.append(b_heading(clean_inline(c), 2))
        elif name in ("h3", "h4"):
            blocks.append(b_heading(clean_inline(c), int(name[1])))
        elif name in ("h5", "h6"):
            # en la web antigua se usaban como texto con otro tamaño
            blocks.append(b_par(clean_inline(c)))
        elif name in ("ul", "ol"):
            blocks.append(b_list(c))
        elif name == "table":
            blocks.append(b_table(c))
        elif name == "img":
            blocks.append(b_image(c))
        elif name == "blockquote":
            inner = flow_to_blocks(c)
            blocks.append(f"<!-- wp:quote -->\n<blockquote class=\"wp-block-quote\">{inner}</blockquote>\n<!-- /wp:quote -->")
        elif name in ("iframe", "video", "embed", "object"):
            src = c.get("src", "")
            blocks.append(b_html(f'<iframe src="{esc(fix_url(src))}" loading="lazy" style="width:100%;aspect-ratio:16/9;border:0" allowfullscreen></iframe>'))
        elif name == "div" and "gallery" in (c.get("class") or []):
            blocks.append(b_gallery(c.find_all("img")))
        elif name in ("div", "section", "article", "center", "font", "dl", "dt", "dd"):
            blocks.append(flow_to_blocks(c))
        elif name in ("hr",):
            blocks.append('<!-- wp:separator -->\n<hr class="wp-block-separator has-alpha-channel-opacity"/>\n<!-- /wp:separator -->')
        elif name in ("script", "style", "form", "input", "button", "noscript"):
            continue
        else:
            b = b_par(clean_inline(c))
            if b:
                blocks.append(b)
    flush()
    return "\n\n".join(b for b in blocks if b)


def classes(t):
    return t.get("class", []) if isinstance(t, Tag) else []


def module_kind(m):
    cl = classes(m)
    for k in ("et_pb_fullwidth_header", "et_pb_text", "et_pb_image", "et_pb_toggle", "et_pb_accordion",
              "et_pb_tabs", "et_pb_gallery", "et_pb_contact_form_container", "et_pb_social_media_follow",
              "et_pb_blog_grid_wrapper", "et_pb_blog", "et_pb_button_module_wrapper", "et_pb_map_container",
              "et_pb_promo", "et_pb_divider", "et_pb_space", "et_pb_post_title", "et_pb_code", "et_pb_fullwidth_code",
              "et_pb_video", "et_pb_blurb"):
        if k in cl:
            return k
    return None


def convert_module(m, ctx):
    k = module_kind(m)
    if k == "et_pb_fullwidth_header":
        h = m.find(class_="et_pb_module_header")
        sub = m.find(class_="et_pb_fullwidth_header_subhead")
        if h and not ctx.get("hero_title"):
            ctx["hero_title"] = norm_text(h.get_text())
        if sub and norm_text(sub.get_text()):
            ctx["subtitulo"] = norm_text(sub.get_text())
        wrap = m.find(class_="et_pb_header_content_wrapper")
        return flow_to_blocks(wrap) if wrap else ""
    if k == "et_pb_text":
        inner = m.find(class_="et_pb_text_inner") or m
        txt = norm_text(inner.get_text()).upper()
        if "SÍGUENOS EN REDES" in txt or "SIGUENOS EN REDES" in txt:
            return ""
        return flow_to_blocks(inner)
    if k == "et_pb_image":
        img = m.find("img")
        return b_image(img) if img else ""
    if k in ("et_pb_toggle", "et_pb_accordion"):
        items = [m] if k == "et_pb_toggle" else m.find_all(class_="et_pb_toggle")
        out = []
        for t in items:
            title = t.find(class_="et_pb_toggle_title")
            content = t.find(class_="et_pb_toggle_content")
            out.append(b_details(norm_text(title.get_text()) if title else "Más información",
                                 flow_to_blocks(content) if content else ""))
        return "\n\n".join(out)
    if k == "et_pb_tabs":
        titles = [norm_text(a.get_text()) for a in m.select(".et_pb_tabs_controls li")]
        tabs = m.find_all(class_="et_pb_tab")
        out = []
        for i, t in enumerate(tabs):
            content = t.find(class_="et_pb_tab_content") or t
            title = titles[i] if i < len(titles) else f"Apartado {i+1}"
            out.append(b_details(title, flow_to_blocks(content)))
        return "\n\n".join(out)
    if k == "et_pb_gallery":
        return b_gallery(m.find_all("img"))
    if k == "et_pb_contact_form_container":
        ctx["tiene_formulario"] = True
        return '<!-- wp:shortcode -->\n[carmenarias_contacto]\n<!-- /wp:shortcode -->'
    if k in ("et_pb_social_media_follow", "et_pb_blog_grid_wrapper", "et_pb_blog", "et_pb_divider", "et_pb_space", "et_pb_post_title"):
        return ""
    if k == "et_pb_button_module_wrapper":
        a = m.find("a")
        return b_button(norm_text(a.get_text()).capitalize(), a.get("href", "")) if a else ""
    if k == "et_pb_map_container":
        return b_par('<a href="https://www.google.com/maps/search/?api=1&amp;query=C%2F+Bonillo+106%2C+13630+Socu%C3%A9llamos" target="_blank" rel="noreferrer noopener">Ver la ubicación en Google Maps</a>')
    if k == "et_pb_promo":
        out = []
        h = m.find(["h1", "h2", "h3", "h4"])
        if h:
            out.append(b_heading(clean_inline(h), 2))
        d = m.find(class_="et_pb_promo_description")
        if d:
            for hh in d.find_all(["h1", "h2", "h3", "h4"]):
                hh.decompose()
            out.append(flow_to_blocks(d))
        a = m.find("a", class_="et_pb_promo_button")
        if a:
            out.append(b_button(norm_text(a.get_text()), a.get("href", "")))
        return "\n\n".join(x for x in out if x)
    if k in ("et_pb_code", "et_pb_fullwidth_code"):
        inner = m.find(class_="et_pb_code_inner") or m
        return b_html(inner.decode_contents().strip())
    if k == "et_pb_video":
        f = m.find(["iframe", "video", "source"])
        if f and f.get("src"):
            return b_html(f'<iframe src="{esc(fix_url(f["src"]))}" loading="lazy" style="width:100%;aspect-ratio:16/9;border:0" allowfullscreen></iframe>')
        return ""
    if k == "et_pb_blurb":
        out = []
        img = m.find("img")
        if img:
            out.append(b_image(img))
        h = m.find(["h3", "h4", "h2"])
        if h:
            out.append(b_heading(clean_inline(h), 3))
        d = m.find(class_="et_pb_blurb_description")
        if d:
            out.append(flow_to_blocks(d))
        return "\n\n".join(x for x in out if x)
    # módulo desconocido: aprovecha el texto
    return flow_to_blocks(m)


def top_modules(scope):
    mods = []
    for m in scope.find_all(class_="et_pb_module"):
        p = m.parent
        nested = False
        while p is not None and p is not scope:
            if "et_pb_module" in classes(p):
                nested = True
                break
            p = p.parent
        if not nested:
            mods.append(m)
    return mods


def convert_page(raw_html):
    soup = BeautifulSoup(raw_html, "html.parser")
    ctx = {}
    out = []
    sections = soup.find_all(class_="et_pb_section")
    if not sections:
        return flow_to_blocks(soup), ctx
    for sec in sections:
        if sec.find_parent(class_="et_pb_section"):
            continue
        rows = [r for r in sec.find_all(class_="et_pb_row") if not r.find_parent(class_="et_pb_row")]
        if not rows:
            for m in top_modules(sec):
                b = convert_module(m, ctx)
                if b:
                    out.append(b)
            continue
        for row in rows:
            cols = [c for c in row.find_all(class_="et_pb_column", recursive=False)]
            col_blocks = []
            for col in cols:
                bs = [convert_module(m, ctx) for m in top_modules(col)]
                bs = [b for b in bs if b and b.strip()]
                if bs:
                    col_blocks.append("\n\n".join(bs))
            if len(col_blocks) > 1:
                inner = "".join(f"<!-- wp:column -->\n<div class=\"wp-block-column\">{cb}</div>\n<!-- /wp:column -->" for cb in col_blocks)
                out.append(f"<!-- wp:columns -->\n<div class=\"wp-block-columns\">{inner}</div>\n<!-- /wp:columns -->")
            elif col_blocks:
                out.append(col_blocks[0])
    return "\n\n".join(out), ctx


# ---------------------------------------------------------------------------
# Páginas institucionales con diseño propio.
# Se escriben a mano (con los textos de la web antigua) como bloques de
# WordPress con clases del tema, para que sigan siendo editables.
# ---------------------------------------------------------------------------

U = "https://carmenarias.es/wp-content/uploads/"


def g(clase, *hijos, anchor=None, tag="div"):
    attrs = {"className": clase}
    if anchor:
        attrs = {"anchor": anchor, **attrs}
    if tag != "div":
        attrs["tagName"] = tag
    idattr = f' id="{anchor}"' if anchor else ""
    inner = "\n\n".join(h for h in hijos if h)
    return (f"<!-- wp:group {json.dumps(attrs, ensure_ascii=False)} -->\n"
            f"<{tag}{idattr} class=\"wp-block-group {clase}\">{inner}</{tag}>\n<!-- /wp:group -->")


def h(texto, nivel=2, clase="", anchor=None):
    attrs = {}
    if nivel != 2:
        attrs["level"] = nivel
    if clase:
        attrs["className"] = clase
    if anchor:
        attrs["anchor"] = anchor
    a = (" " + json.dumps(attrs, ensure_ascii=False)) if attrs else ""
    cls = "wp-block-heading" + (f" {clase}" if clase else "")
    idattr = f' id="{anchor}"' if anchor else ""
    return f"<!-- wp:heading{a} -->\n<h{nivel}{idattr} class=\"{cls}\">{texto}</h{nivel}>\n<!-- /wp:heading -->"


def p(texto, clase=""):
    if clase:
        return (f"<!-- wp:paragraph {{\"className\":\"{clase}\"}} -->\n<p class=\"{clase}\">{texto}</p>\n<!-- /wp:paragraph -->")
    return f"<!-- wp:paragraph -->\n<p>{texto}</p>\n<!-- /wp:paragraph -->"


def lista(items):
    li = "\n".join(f"<!-- wp:list-item -->\n<li>{i}</li>\n<!-- /wp:list-item -->" for i in items)
    return f"<!-- wp:list -->\n<ul class=\"wp-block-list\">{li}</ul>\n<!-- /wp:list -->"


def foto(url, alt="", clase=""):
    hit = buscar_medio(url)
    attrs = {"sizeSlug": "large", "linkDestination": "none"}
    imgcls = ""
    if hit:
        attrs = {"id": hit[0], **attrs}
        imgcls = f' class="wp-image-{hit[0]}"'
        url = hit[1]
    if clase:
        attrs["className"] = clase
    fcls = "wp-block-image size-large" + (f" {clase}" if clase else "")
    return (f"<!-- wp:image {json.dumps(attrs)} -->\n<figure class=\"{fcls}\"><img src=\"{esc(url)}\" alt=\"{esc(alt)}\"{imgcls}/></figure>\n<!-- /wp:image -->")


def tarjeta(titulo, texto, etiqueta=""):
    return g("ca-tarjeta",
             p(etiqueta, "ca-tarjeta-etiqueta") if etiqueta else "",
             h(titulo, 3),
             p(texto))


def indice(enlaces):
    items = " ".join(f'<a href="#{a}">{t}</a>' for a, t in enlaces)
    return p(items, "ca-indice")


def doc(texto, url):
    return p(f'<a href="{esc(url)}" target="_blank" rel="noreferrer noopener">{texto}</a>', "ca-doc")


def pagina_sector_solidario():
    return "\n\n".join([
        p("Somos una entidad sin ánimo de lucro, inspirada en los valores del humanismo cristiano y miembro de <strong>ACESCAM</strong>, la Asociación de Residencias y Servicios de Atención a los Mayores del sector solidario. Esto es lo que nos mueve, hacia dónde vamos y en qué creemos.", "ca-entradilla"),
        indice([("mision", "Misión"), ("vision", "Visión"), ("valores", "Valores")]),

        g("ca-seccion",
          g("ca-seccion-cab",
            p("Misión", "ca-antetitulo"),
            h("Atendemos a las necesidades de las personas mayores más vulnerables."),
            ),
          g("ca-tarjetas",
            tarjeta("Nuestra inspiración", "Somos una organización inspirada en los valores del humanismo cristiano que tiene por finalidad principal atender a las necesidades de las personas mayores más vulnerables, orientando preferentemente nuestra actividad hacia los ámbitos sociales de más acuciante necesidad y de más escasos recursos. En consonancia con los cambios de nuestra sociedad, ampliamos nuestra actividad a la atención a personas dependientes."),
            tarjeta("Definición", "Nos define el empeño común de prestar a las personas destinatarias una atención de calidad y calidez, personalizada, de forma que vean satisfechas sus necesidades y expectativas, y cubiertas todas sus dimensiones como personas. Fruto de esa actitud ante el servicio es nuestro compromiso expreso con la mejora continua."),
            tarjeta("Consideración", "Consideramos clave el valor añadido que aportan las personas en la prestación del servicio y en la gestión de nuestra institución. Conscientes del origen de nuestros recursos, actuamos con rigor y con criterios de eficiencia y de compromiso social."),
            ),
          g("ca-fotos",
            foto(U + "2021/04/mision-fundacion-carmen-arias-4-1280x857.jpeg", "Residentes y profesionales de la Fundación"),
            foto(U + "2021/04/mision-fundacion-carmen-arias-5-1280x857.jpeg", "Actividad en la residencia"),
            foto(U + "2021/04/mision-fundacion-carmen-arias-3-1280x857.jpeg", "Atención personalizada"),
            ),
          anchor="mision"),

        g("ca-seccion",
          g("ca-seccion-cab",
            p("Visión", "ca-antetitulo"),
            h("Queremos ser referencia entre las instituciones que sirven a las personas dependientes."),
            ),
          g("ca-tarjetas ca-tarjetas-2",
            tarjeta("Calidad humana", "En la calidad humana y técnica de nuestros servicios, en su individualización e integralidad y en su adecuación a las necesidades de las personas, usuarias o potenciales."),
            tarjeta("Consideración", "En el valor que damos a las personas que prestan el servicio, impulsando su capacitación, sus cualidades humanas, su motivación para el servicio y su implicación en la organización."),
            tarjeta("Cohesión", "Como organización cohesionada, capaz de compartir recursos y promover alianzas para conseguir un sector solidario, sólido y significativo, que proyecte sus valores hacia la sociedad y contribuya a transformarla."),
            tarjeta("Organización abierta", "Abierta a la colaboración entre agentes sociales públicos y privados, para mejorar el sistema de servicios sociales y conseguir mayores cotas de justicia social."),
            ),
          g("ca-fotos ca-fotos-2",
            foto(U + "2021/05/Foto-para-vision-1280x960.jpg", "Profesionales de la Fundación"),
            foto(U + "2021/04/vision-2.jpeg", "Vida en el centro"),
            ),
          anchor="vision"),

        g("ca-seccion",
          g("ca-seccion-cab",
            p("Valores", "ca-antetitulo"),
            h("La persona es la razón de ser de nuestro servicio."),
            ),
          g("ca-tarjetas ca-valores",
            tarjeta("Dignidad", "Estamos comprometidos con la defensa de la dignidad y los derechos de cada persona, más aún cuanto más vulnerable sea su situación.", "La dignidad de toda persona"),
            tarjeta("Plenitud", "Reconocemos la vejez como una etapa de plenitud de vida y experiencia, capaz de aportar a la sociedad y a otras personas, con sentido de solidaridad y corresponsabilidad.", "La vejez, etapa de plenitud"),
            tarjeta("Convencimiento", "Atendemos a la persona en todas sus dimensiones: física, psíquica, emocional, social y espiritual. Cada persona es una realidad única e irrepetible.", "Cada persona, única"),
            tarjeta("Calidad", "Buscamos la excelencia en los servicios e incorporamos a la mejora los avances tecnológicos y las mejores prácticas.", "Calidad asistencial y relacional"),
            tarjeta("Compromiso", "Nuestras decisiones profesionales se basan en el respeto a la autonomía de las personas, la búsqueda de su bienestar y la justicia.", "Compromiso ético"),
            tarjeta("Solidaridad", "La solidaridad nos configura como red: compartimos nuestro saber hacer, nos apoyamos mutuamente y fortalecemos el sector solidario del que formamos parte.", "Trabajar en red"),
            ),
          g("ca-fotos",
            foto(U + "2021/04/valores-1.jpeg", "Fiesta en la residencia"),
            foto(U + "2021/04/valores-3.jpeg", "Taller de cocina"),
            foto(U + "2021/05/Foto-para-VAlores-1280x768.jpg", "Residentes de la Fundación"),
            ),
          anchor="valores"),
    ])


def pagina_organigrama():
    return "\n\n".join([
        p("Así se organiza la Fundación Carmen Arias: del Patronato a cada uno de los equipos que cuidan, día a día, de las personas que atendemos.", "ca-entradilla"),
        foto(U + "2022/10/Organigrama-FCA-scaled.jpg", "Organigrama de la Fundación Carmen Arias", "ca-organigrama"),
        p("Pulsa sobre el organigrama para verlo a tamaño completo.", "ca-nota"),
    ])


def pagina_calidad():
    return "\n\n".join([
        p("La filosofía de gestión de este Centro se basa en el cumplimiento de los servicios que oferta, buscando siempre que la atención se dé de manera profesional y con garantías de calidad suficientes.", "ca-entradilla"),
        g("ca-dos-col",
          g("ca-col",
            p("La calidad se traduce en que cada residente pueda tener cubiertas todas las necesidades que se detecten en la valoración de los profesionales del Equipo Interdisciplinar y las que las personas usuarias refieran o demanden. Tras esa valoración se pautan las medidas de <strong>Atención Individualizada</strong> para cada persona, y esa especificación del trabajo es la que da garantía de calidad en la atención sociosanitaria."),
            p("Se trata de cubrir la <strong>esfera bio-psico-social</strong> de nuestros usuarios, trabajando tanto con residentes como con familiares y buscando canales continuos de comunicación abierta, de forma que los servicios mejoren con las apreciaciones de las personas y con las medidas de autoevaluación internas."),
            ),
          g("ca-col",
            p("Nuestra política de trabajo incluye ofrecer una respuesta de <strong>calidad, rápida y eficaz</strong> a las entidades con las que nos relacionamos: la Administración Pública, otros agentes sociales y las personas que pueden necesitar nuestros servicios."),
            p("Para cumplir este compromiso, la Fundación se apoya en un <strong>equipo humano preparado, formado y motivado</strong>, y es también compromiso del Centro trabajar en ese sentido."),
            ),
          ),
        p("No buscamos solo cumplir las expectativas de las personas, sino la <strong>excelencia</strong> en el servicio.", "ca-frase"),
        h("Objetivos generales"),
        p("La Dirección marca cada año unos objetivos específicos y cuantificables que se revisan anualmente para alcanzar estos objetivos generales:"),
        g("ca-tarjetas",
          tarjeta("Satisfacción", "Cumplir la oferta socioasistencial del Centro, aportando satisfacción a cada persona."),
          tarjeta("Normativa e ISO 9001", "Cumplir la legislación vigente de referencia y la norma UNE-EN ISO 9001."),
          tarjeta("Mejora continua", "Mejorar de forma continua la eficacia del Sistema de Gestión de Calidad."),
          ),
        p("La Dirección del Centro, a través del Responsable de Calidad, se asegura de que esta política está disponible para todas las personas usuarias y partes interesadas que lo requieran, y de que el personal la conoce, la entiende y la aplica.", "ca-nota"),
    ])


def pagina_transparencia():
    docs = [
        ("2024", [("Informe de auditoría", "2026/02/G13046818-informe-de-auditoria-2024.pdf"), ("Memoria y cuentas anuales", "2026/02/Cuentas-anuales-2024.pdf")]),
        ("2023", [("Informe de auditoría", "2026/02/Informe-Auditoria-de-Cuentas-Fundacion-Carmen-Arias-2023.pdf"), ("Memoria y cuentas anuales", "2026/02/Cuentas-anuales-2023.pdf")]),
        ("2022", [("Informe de auditoría", "2026/02/Informe-de-Auditoria-Cuentas-2022.pdf"), ("Memoria y cuentas anuales", "2026/02/Cuentas-anuales-2022.pdf")]),
        ("2021", [("Informe de auditoría", "2023/03/Informe-Auditoria-2021.pdf"), ("Memoria y cuentas anuales", "2023/03/Memoria-y-Cuentas-Anuales-2021.pdf")]),
        ("2020", [("Memoria y cuentas anuales", "2023/03/Memoria-y-Cuentas-Anuales-2020.pdf")]),
        ("2019", [("Memoria y cuentas anuales", "2023/03/Memoria-y-Cuentas-Anuales-2019.pdf")]),
    ]
    anios = [g("ca-anio", h(a, 3), *[doc(t, U + f) for t, f in lista_docs]) for a, lista_docs in docs]
    return "\n\n".join([
        p("Rendimos cuentas de cómo usamos nuestros recursos. Aquí tienes las memorias, las cuentas anuales y los informes de auditoría de cada año. Pulsa en un documento para verlo o descargarlo.", "ca-entradilla"),
        g("ca-docs", *anios),
    ])


def pagina_canal():
    return "\n\n".join([
        p("La Fundación Carmen Arias cuenta con un <strong>Sistema Interno de Información (SII)</strong>, aprobado por su Patronato, para que cualquier persona que se relacione con nosotros (personal, residentes, familias, proveedores, donantes o la sociedad en general) pueda comunicar posibles infracciones administrativas, penales o de la normativa interna del centro.", "ca-entradilla"),
        p("Lo establece la Ley 2/2023, de 20 de febrero, reguladora de la protección de las personas que informen sobre infracciones normativas y de lucha contra la corrupción. La persona Responsable del SII recibe cada información y le da el tratamiento oportuno, iniciando, si procede, una investigación sobre los hechos."),
        g("ca-aviso",
          h("Puedes comunicarlo de forma anónima", 3),
          p("No es obligatorio dar tu nombre, apellidos ni correo electrónico, ni ningún otro dato que te identifique, por ninguna de las vías."),
          ),
        h("Documentos que lo regulan"),
        g("ca-docs ca-docs-2",
          g("ca-anio", h("Estatuto", 3), doc("Estatuto del Sistema Interno de Información", U + "2024/09/Estatuto-SII-Fundacion-Carmen-Arias.pdf")),
          g("ca-anio", h("Reglamento", 3), doc("Reglamento del Sistema Interno de Información", U + "2024/09/Reglamento-SII-Fundacion-Carmen-Arias.pdf")),
          ),
        h("Tres formas de comunicarlo"),
        g("ca-tarjetas",
          tarjeta("Por correo postal", "Escribe a la sede de la Fundación indicando en el destinatario «Sistema Interno de Información» o «Al Responsable del Sistema Interno de Información»: C/ Bonillo, 106 · 13630 Socuéllamos (Ciudad Real).", "Vía 1"),
          tarjeta("Por correo electrónico", 'Escribe a <a href="mailto:sistemadeinformacion@carmenarias.es">sistemadeinformacion@carmenarias.es</a>.', "Vía 2"),
          tarjeta("Con el formulario", 'Rellena el <a href="#formulario-canal">formulario de esta página</a>. Solo es obligatorio describir los hechos.', "Vía 3"),
          ),
        h("Formulario", anchor="enviar"),
        "<!-- wp:shortcode -->\n[carmenarias_contacto tipo=\"canal\"]\n<!-- /wp:shortcode -->",
    ])


PAGINAS_DISENO = {
    "organigrama": ("page-templates/secciones.php", pagina_organigrama, "Organigrama", "Cómo se organiza la Fundación."),
    "calidad": ("page-templates/secciones.php", pagina_calidad, "Calidad", "Nuestra política de calidad: atención profesional, individualizada y en mejora continua."),
    "transparencia": ("page-templates/secciones.php", pagina_transparencia, "Transparencia", "Memorias, cuentas anuales e informes de auditoría."),
    "canal-de-informacion-2": ("page-templates/secciones.php", pagina_canal, "Canal de información", "Sistema Interno de Información de la Fundación Carmen Arias (Ley 2/2023)."),
}

# Páginas antiguas que ahora redirigen (el tema hace la redirección).
REDIRECCIONES = {
    "mision": "sector-solidario#mision",
    "vision": "sector-solidario#vision",
    "valores": "sector-solidario#valores",
    "canal-de-informacion": "canal-de-informacion-2",
}


def main(src, dst, medios=None):
    if medios:
        cargar_medios(medios)
    pages = json.load(open(src, encoding="utf-8"))
    result = []
    for p in pages:
        slug = p["slug"]
        if slug in SKIP:
            continue
        content, ctx = convert_page(p["content"]["rendered"])
        item = {
            "id": p["id"],
            "slug": slug,
            "titulo": TITULOS.get(slug) or html.unescape(p["title"]["rendered"]).strip().capitalize(),
            "subtitulo": ctx.get("subtitulo", ""),
            "plantilla": "",
            "contenido": content,
        }
        if slug in CENTROS:
            item["plantilla"] = "page-templates/centro.php"
            item["centro"] = CENTROS[slug]
        if slug in LISTADOS:
            item["plantilla"] = "page-templates/listado.php"
            item["categoria"] = LISTADOS[slug]
            item["contenido"] = ""
        if slug == "servicios":
            item["titulo"] = "Nuestros centros"
            item["plantilla"] = "page-templates/centros.php"
            item["contenido"] = ""
        if slug == "canal-de-informacion-2":
            item["contenido"] = item["contenido"].replace("[carmenarias_contacto]", '[carmenarias_contacto tipo="canal"]')
        if slug == "contacto":
            item["plantilla"] = "page-templates/contacto.php"
            item["contenido"] = ""
        if slug in PAGINAS_DISENO:
            plantilla, fn, titulo, sub = PAGINAS_DISENO[slug]
            item.update(plantilla=plantilla, contenido=fn(), titulo=titulo, subtitulo=sub)
        if slug in REDIRECCIONES:
            item["redirige"] = REDIRECCIONES[slug]
        result.append(item)
    result.append({
        "id": 0, "slug": "sector-solidario", "titulo": "Sector solidario",
        "subtitulo": "Nuestra misión, nuestra visión y nuestros valores.",
        "plantilla": "page-templates/secciones.php", "contenido": pagina_sector_solidario(),
    })
    json.dump(result, open(dst, "w", encoding="utf-8"), ensure_ascii=False, indent=1)
    for r in result:
        print(f"{r['slug']:38} {len(r['contenido']):6} {r['plantilla']}")


if __name__ == "__main__":
    main(sys.argv[1], sys.argv[2], sys.argv[3] if len(sys.argv) > 3 else None)
