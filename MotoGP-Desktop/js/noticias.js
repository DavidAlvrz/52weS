class Noticias {
    constructor() {
        this.busqueda = "MotoGP";
        this.url = "https://api.thenewsapi.com/v1/news/all";
        this.apiKey = "sFHhsfap4rG59GeVqWWgLIkhx5AQK36S1UbF5z7T";
    }

    async buscar() {
        const url = `${this.url}?api_token=${this.apiKey}&search=${this.busqueda}&language=es&limit=3`;
        const respuesta = await fetch(url)
        const datos = await respuesta.json();
        return this.procesarInformacion(datos);
    }

    procesarInformacion(datos) {
        console.log(datos);
        const noticias = datos.data;

        const container = document.querySelector("section");


        for (let noticia of noticias) {

            const h3 = document.createElement("h3");
            h3.textContent = noticia.title;
            const p = document.createElement("p");
            p.textContent = noticia.description;

            const a = document.createElement("a");
            a.href = noticia.url;
            a.textContent = "Leer más";

            const pSource = document.createElement("p");
            pSource.textContent = "Fuente: " + noticia.source;

            container.appendChild(h3);
            container.appendChild(p);
            container.appendChild(a);
            container.appendChild(pSource);
        }
    }



}