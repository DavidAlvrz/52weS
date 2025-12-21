# -*- coding: utf-8 -*-
import xml.etree.ElementTree as ET

class Svg:
    """Genera archivos SVG válidos (SVG 1.1)"""

    def __init__(self):
        self.raiz = ET.Element(
            'svg',
            xmlns="http://www.w3.org/2000/svg",
            version="1.1"
        )

    def addRect(self, x, y, width, height, fill, stroke_width, stroke):
        ET.SubElement(self.raiz, 'rect', {
            'x': x,
            'y': y,
            'width': width,
            'height': height,
            'fill': fill,
            'stroke-width': stroke_width,
            'stroke': stroke
        })

    def addCircle(self, cx, cy, r, fill):
        ET.SubElement(self.raiz, 'circle', {
            'cx': cx,
            'cy': cy,
            'r': r,
            'fill': fill
        })

    def addLine(self, x1, y1, x2, y2, stroke, stroke_width):
        ET.SubElement(self.raiz, 'line', {
            'x1': x1,
            'y1': y1,
            'x2': x2,
            'y2': y2,
            'stroke': stroke,
            'stroke-width': stroke_width
        })

    def addPolyline(self, points, stroke, stroke_width, fill):
        ET.SubElement(self.raiz, 'polyline', {
            'points': points,
            'stroke': stroke,
            'stroke-width': stroke_width,
            'fill': fill
        })

    def addText(self, texto, x, y, fontFamily, fontSize, extra_style=""):
        style = f"font-family:{fontFamily}; font-size:{fontSize}px; fill:black;"
        if extra_style:
            style += extra_style

        ET.SubElement(self.raiz, 'text', {
            'x': x,
            'y': y,
            'style': style
        }).text = texto

    def escribir(self, nombreArchivoSVG):
        arbol = ET.ElementTree(self.raiz)
        ET.indent(arbol)
        arbol.write(nombreArchivoSVG, encoding='utf-8', xml_declaration=True)


# ----------------- Código para generar altimetría -----------------

xml_file = input("Ingrese el nombre del archivo XML de la pista: ")
svg_file = input("Ingrese el nombre del archivo SVG de salida: ")

# Leer XML
ns = {'ns': 'http://www.uniovi.es'}
tree = ET.parse(xml_file)
root = tree.getroot()
tramos = root.findall('.//ns:tramo', ns)

altitudes = []
for tramo in tramos:
    alt = float(tramo.find('ns:puntoFinal/ns:altitudCoord', ns).text)
    altitudes.append(alt)

# Dimensiones SVG
svg_width = 1000
svg_height = 400
margen = 40

altura_max = max(altitudes)
altura_min = min(altitudes)

escala_y = (svg_height - 2 * margen) / (altura_max - altura_min)
escala_x = (svg_width - 2 * margen) / (len(altitudes) - 1)

puntos_svg = []

# Punto inicial para cerrar por abajo
puntos_svg.append(f"{margen},{svg_height - margen}")

# Línea de altimetría
for i, alt in enumerate(altitudes):
    x = margen + i * escala_x
    y = svg_height - margen - (alt - altura_min) * escala_y
    puntos_svg.append(f"{x},{y}")

# Punto final para cerrar por abajo
x_final = margen + (len(altitudes) - 1) * escala_x
puntos_svg.append(f"{x_final},{svg_height - margen}")

# Crear SVG
svg = Svg()
svg.addPolyline(
    " ".join(puntos_svg),
    stroke="red",
    stroke_width="4",
    fill="none"
)

# Etiquetas de altitud (excepto la primera)
for i, alt in enumerate(altitudes):
    if i == 0:
        continue
    x = margen + i * escala_x
    y = svg_height - margen - (alt - altura_min) * escala_y - 5
    svg.addText(f"{alt:.1f} m", str(x), str(y), "Verdana", "12")

svg.escribir(svg_file)
print(f"SVG de altimetría generado: {svg_file}")
