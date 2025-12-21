class Circuito {
    constructor() {
        this.comprobarApiFile();
    }

    // Comprobar soporte de File API
    comprobarApiFile() {
        if (!window.File || !window.FileReader || !window.FileList || !window.Blob) {
            alert("El navegador no soporta la API File. Algunas funcionalidades no estarán disponibles.");
            return false;
        }
        return true;
    }

    // Método para leer archivo HTML desde el cliente
    leerArchivoHTML(fileInput) {
        const archivo = fileInput.files[0];
        const lector = new FileReader();

        // Evento al terminar de leer el archivo
        lector.onload = (evento) => {
            const contenido = lector.result;
            this.procesarHTML(contenido);
        };

        lector.readAsText(archivo);
    }

    procesarHTML(contenidoHTML) {
        const parser = new DOMParser();
        const doc = parser.parseFromString(contenidoHTML, "text/html");

        let main = document.querySelector("main");

        /* --- Transformar galería de fotos y vídeos --- */
        const encabezados = Array.from(doc.querySelectorAll("h2"));

        encabezados.forEach(h2 => {
            const titulo = h2.textContent.trim();
            const ul = h2.nextElementSibling;

            if (!ul || ul.tagName.toLowerCase() !== "ul") return;

            // Galería de fotos
            if (titulo === "Galería de fotos") {
                const lis = Array.from(ul.querySelectorAll("li"));
                ul.innerHTML = "";

                lis.forEach(li => {
                    const ruta = li.textContent.trim().replace("../", "./");
                    if (ruta) {
                        const nuevoLi = doc.createElement("li");
                        const img = doc.createElement("img");
                        img.setAttribute("src", ruta);
                        img.setAttribute("alt", "Imagen del circuito");
                        nuevoLi.appendChild(img);
                        ul.appendChild(nuevoLi);
                    }
                });
            }

            // Galería de vídeos
            if (titulo === "Galería de videos") {
                const lis = Array.from(ul.querySelectorAll("li"));
                ul.innerHTML = "";

                lis.forEach(li => {
                    const ruta = li.textContent.trim().replace("../", "./");
                    if (ruta) {
                        const nuevoLi = doc.createElement("li");
                        const video = doc.createElement("video");
                        video.setAttribute("src", ruta);
                        video.setAttribute("controls", "controls");
                        nuevoLi.appendChild(video);
                        ul.appendChild(nuevoLi);
                    }
                });
            }

        });

        /* --- Mover contenido al <main> existente --- */
        const bodyChildren = Array.from(doc.body.children);
        bodyChildren.forEach(el => main.appendChild(el));

        /* --- Bajar un nivel los encabezados --- */
        function bajarEncabezados(element) {
            const headers = element.querySelectorAll("h1, h2, h3, h4, h5");
            headers.forEach(h => {
                const nivel = parseInt(h.tagName[1], 10);
                const nuevoNivel = Math.min(nivel + 1, 6);
                const nuevoH = doc.createElement("h" + nuevoNivel);
                nuevoH.innerHTML = h.innerHTML;
                h.replaceWith(nuevoH);
            });
        }

        bajarEncabezados(main);
    }



}


class CargadorSVG {
    constructor() {
        this.contenidoSVG = null;
    }

    leerArchivoSVG(fileInput) {
        const archivo = fileInput.files[0];
        if (!archivo) return;

        const lector = new FileReader();

        lector.onload = (evento) => {
            this.contenidoSVG = evento.target.result;
            this.insertarSVG();
        };

        lector.readAsText(archivo);
    }

    insertarSVG() {
        if (!this.contenidoSVG) return;

        const parser = new DOMParser();
        const docSVG = parser.parseFromString(this.contenidoSVG, "image/svg+xml");
        const svg = docSVG.documentElement;

        svg.setAttribute("width", "100%");
        svg.setAttribute("height", "400");
        svg.setAttribute("viewBox", "0 0 1000 400");
        svg.setAttribute("preserveAspectRatio", "xMidYMid meet");

        const main = document.querySelector("main");
        if (!main) return;

        main.appendChild(svg);
    }

}

class CargadorKML {
    constructor() {
        this.contenidoKML = null;
        this.mapa = null;
        this.coordenadas = [];
        this.origen = null;
    }

    leerArchivoKML(fileInput) {
        const archivo = fileInput.files[0];
        if (!archivo) return;

        const lector = new FileReader();
        lector.onload = (evento) => {
            this.contenidoKML = evento.target.result;
            this.procesarKML();
        };
        lector.readAsText(archivo);
    }

    procesarKML() {
        if (!this.contenidoKML) return;

        const parser = new DOMParser();
        const xml = parser.parseFromString(this.contenidoKML, "application/xml");

        const ns = "http://www.opengis.net/kml/2.2";

        const puntos = xml.getElementsByTagNameNS(ns, "Point");
        if (puntos.length > 0) {
            const coords = puntos[0].getElementsByTagNameNS(ns, "coordinates");
            if (coords.length > 0) {
                const [lon, lat] = coords[0].textContent.trim().split(",").map(Number);
                this.origen = { lat: lat, lng: lon };
            }
        }
        if (!this.origen) {
            const lineas = xml.getElementsByTagNameNS(ns, "LineString");
            if (lineas.length > 0) {
                const coords = lineas[0].getElementsByTagNameNS(ns, "coordinates");
                if (coords.length > 0) {
                    const primerPar = coords[0].textContent.trim().split(/\s+/)[0];
                    const [lon, lat] = primerPar.split(",").map(Number);
                    this.origen = { lat: lat, lng: lon };
                }
            }
        }

        this.coordenadas = [];
        const lineas = xml.getElementsByTagNameNS(ns, "LineString");
        for (let linea of lineas) {
            const coords = linea.getElementsByTagNameNS(ns, "coordinates");
            for (let c of coords) {
                const pares = c.textContent.trim().split(/\s+/);
                pares.forEach(p => {
                    const [lon, lat] = p.split(",").map(Number);
                    this.coordenadas.push({ lat: lat, lng: lon });
                });
            }
        }

        this.insertarCapaKML();
    }



    insertarCapaKML() {
        const main = document.querySelector("main");

        const contenedorMapa = document.createElement("div");
        main.appendChild(contenedorMapa);

        this.mapa = new google.maps.Map(contenedorMapa, {
            mapTypeId: google.maps.MapTypeId.ROADMAP
        });

        const bounds = new google.maps.LatLngBounds();

        if (this.origen) {
            new google.maps.Marker({
                position: this.origen,
                map: this.mapa,
                title: "Punto de origen"
            });
            bounds.extend(this.origen);
        }

        if (this.coordenadas.length > 0) {
            const trazado = new google.maps.Polyline({
                path: this.coordenadas,
                geodesic: true,
                strokeColor: "#FF0000",
                strokeOpacity: 1.0,
                strokeWeight: 3
            });
            trazado.setMap(this.mapa);

            this.coordenadas.forEach(c => bounds.extend(c));
        }

        this.mapa.fitBounds(bounds);
    }

}



