# -*- coding: utf-8 -*-
import xml.etree.ElementTree as ET

class Html:
    """Genera un archivo HTML básico con soporte para etiquetas, listas y enlaces"""
    def __init__(self, title="Info del circuito"):
        self.lines = []
        self.lines.append('<!DOCTYPE html>')
        self.lines.append('<html lang="es">')
        self.lines.append('<head>')
        self.lines.append(f'    <meta charset="UTF-8">')
        self.lines.append(f'    <meta name="viewport" content="width=device-width, initial-scale=1.0">')
        self.lines.append(f'    <title>{title}</title>')
        self.lines.append('    <link rel="stylesheet" href="estilo.css">')
        self.lines.append('</head>')
        self.lines.append('<body>')
        self.lines.append(f'<h1>{title}</h1>')

    def addParagraph(self, text):
        self.lines.append(f'<p>{text}</p>')

    def addHeading(self, text, level=2):
        self.lines.append(f'<h{level}>{text}</h{level}>')

    def addList(self, items):
        self.lines.append('<ul>')
        for item in items:
            self.lines.append(f'  <li>{item}</li>')
        self.lines.append('</ul>')

    def addLink(self, text, href):
        self.lines.append(f'<a href="{href}">{text}</a>')

    def finish(self):
        self.lines.append('</body>')
        self.lines.append('</html>')

    def write(self, filename):
        self.finish()
        with open(filename, 'w', encoding='utf-8') as f:
            f.write("\n".join(self.lines))


# ----------------- Código para generar InfoCircuito.html -----------------
xml_file = input("Ingrese el nombre del archivo XML: ")
html_file = input("Ingrese el nombre del archivo HTML a generar: ")

# Leer XML
ns = {'ns': 'http://www.uniovi.es'}
tree = ET.parse(xml_file)
root = tree.getroot()

html = Html("Información del circuito")

# Nombre del circuito
nombre = root.find('ns:nombre', ns).text
html.addHeading(nombre, level=1)

# Medidas
longitud = root.find('ns:medidas/ns:longitud', ns).text
anchura = root.find('ns:medidas/ns:anchura', ns).text
html.addHeading("Medidas", level=2)
html.addParagraph(f"Longitud: {longitud} m")
html.addParagraph(f"Anchura: {anchura} m")

# Fecha y hora
fecha = root.find('ns:fecha', ns)
fecha_text = fecha.text
hora = root.find('ns:hora', ns).text
html.addHeading("Fecha y hora", level=2)
html.addParagraph(f"Fecha: {fecha_text}")
html.addParagraph(f"Hora: {hora}")

# Vueltas
vueltas = root.find('ns:vueltas', ns).text
html.addParagraph(f"Vueltas: {vueltas}")

# Localidad, país y patrocinador
localidad = root.find('ns:localidad', ns).text
pais = root.find('ns:pais', ns).text
patrocinador = root.find('ns:patrocinador', ns).text
html.addHeading("Información adicional", level=2)
html.addParagraph(f"Localidad: {localidad}")
html.addParagraph(f"País: {pais}")
html.addParagraph(f"Patrocinador: {patrocinador}")

# Referencias
referencias = root.findall('ns:referencias/ns:referencia', ns)
if referencias:
    html.addHeading("Referencias", level=2)
    items = []
    for ref in referencias:
        tipo = ref.attrib.get('tipo', '')
        enlace = ref.attrib.get('enlace', '')
        texto = ref.text.strip() if ref.text else ''
        items.append(f'{tipo}: <a href="{enlace}">{texto}</a>')
    html.addList(items)

# Galería de fotos
fotos = root.findall('ns:galeriaFotos/ns:foto', ns)
if fotos:
    html.addHeading("Galería de fotos", level=2)
    items = []
    for foto in fotos:
        archivo = foto.attrib.get('archivo', '')
        desc = foto.attrib.get('descripcion', '')
        items.append(f'{desc}: {archivo}')
    html.addList(items)

# Galería de videos
videos = root.findall('ns:galeriaVideos/ns:video', ns)
if videos:
    html.addHeading("Galería de videos", level=2)
    items = []
    for video in videos:
        archivo = video.attrib.get('archivo', '')
        duracion = video.attrib.get('duracion', '')
        items.append(f'{archivo} ({duracion})')
    html.addList(items)

# Vencedor
vencedor = root.find('ns:vencedor/ns:nombreVencedor', ns)
tiempo = root.find('ns:vencedor/ns:tiempo', ns)
if vencedor is not None and tiempo is not None:
    html.addHeading("Vencedor", level=2)
    html.addParagraph(f"{vencedor.text} - Tiempo: {tiempo.text}")

# Clasificación mundial
pilotos = root.findall('ns:mundialClasificacion/ns:piloto', ns)
if pilotos:
    html.addHeading("Clasificación Mundial", level=2)
    items = []
    for piloto in pilotos:
        nombre_p = piloto.text
        pos = piloto.attrib.get('posicion', '')
        equipo = piloto.attrib.get('equipo', '')
        puntos = piloto.attrib.get('puntos', '')
        items.append(f"{pos}. {nombre_p} ({equipo}) - {puntos} puntos")
    html.addList(items)

# Guardar HTML
html.write(html_file)
print(f"Archivo HTML generado: {html_file}")
