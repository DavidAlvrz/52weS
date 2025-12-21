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
