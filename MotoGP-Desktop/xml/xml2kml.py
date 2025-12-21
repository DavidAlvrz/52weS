import xml.etree.ElementTree as ET

# Namespace del XML
NS = {'ns': 'http://www.uniovi.es'}

def generarCoordenadasCerradas(archivoXML):
    try:
        arbol = ET.parse(archivoXML)
    except IOError:
        print('No se encuentra el archivo', archivoXML)
        exit()
    except ET.ParseError:
        print('Error procesando el archivo XML =', archivoXML)
        exit()

    raiz = arbol.getroot()

    # Buscar todos los tramos usando namespace
    tramos = raiz.findall('.//ns:tramo', NS)
    if not tramos:
        print("No se encontraron tramos en el XML")
        return []

    coordenadas = []

    # Tomar puntoFinal del último tramo como primer punto para cerrar el circuito
    ultimo = tramos[-1].find('ns:puntoFinal', NS)
    lon = ultimo.find('ns:longitudCoord', NS).text
    lat = ultimo.find('ns:latitudCoord', NS).text
    alt = ultimo.find('ns:altitudCoord', NS).text
    coordenadas.append(f"{lon},{lat},{alt}")

    # Añadir puntoFinal de cada tramo
    for tramo in tramos:
        pf = tramo.find('ns:puntoFinal', NS)
        lon = pf.find('ns:longitudCoord', NS).text
        lat = pf.find('ns:latitudCoord', NS).text
        alt = pf.find('ns:altitudCoord', NS).text
        coordenadas.append(f"{lon},{lat},{alt}")

    return coordenadas

def guardarKML(coordenadas, archivoKML):
    kml_content = f"""<?xml version="1.0" encoding="UTF-8"?>
<kml xmlns="http://www.opengis.net/kml/2.2">
  <Document>
    <name>Circuito</name>
    <Placemark>
      <name>Recorrido</name>
      <LineString>
        <tessellate>1</tessellate>
        <coordinates>
          {' '.join(coordenadas)}
        </coordinates>
      </LineString>
    </Placemark>
  </Document>
</kml>
"""
    with open(archivoKML, 'w', encoding='utf-8') as f:
        f.write(kml_content)
    print(f"Archivo KML guardado en {archivoKML}")

def main():
    archivoXML = input("Introduzca un archivo XML = ")
    archivoKML = input("Introduzca el nombre del archivo KML a guardar = ")
    coords = generarCoordenadasCerradas(archivoXML)
    if coords:
        guardarKML(coords, archivoKML)

if __name__ == "__main__":
    main()
