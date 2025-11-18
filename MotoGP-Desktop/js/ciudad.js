class Ciudad {
    constructor(nombre, pais, gentilicio) {
        this.nombre = nombre;
        this.pais = pais;
        this.gentilicio = gentilicio;
    }

    poblarAtributos() {
        this.poblacion = 825950;
        this.coordenadas = { latitud: 39.4699, longitud: -0.3763 };
    }

    getNombre() {
        return this.nombre;
    }

    getPais() {
        return this.pais;
    }

    getInfoSecundaria() {
        return "<ul><li>Gentilicio: " + this.gentilicio + "</li><li>Población: " + this.poblacion + "</li></ul>";
    }

    writeCoordenadas() {
        const container = document.body;

        const p = document.createElement("p");
        p.textContent = "Latitud: " + this.coordenadas.latitud + ", Longitud: " + this.coordenadas.longitud;
        container.appendChild(p);

    }

    mostrarInfo() {
        const container = document.body;

        const h3 = document.createElement("h3");
        h3.textContent = "Información de la ciudad";
        container.appendChild(h3);

        const pNombre = document.createElement("p");
        pNombre.textContent = "Nombre: " + ciudad.getNombre();
        container.appendChild(pNombre);

        const pPais = document.createElement("p");
        pPais.textContent = "País: " + ciudad.getPais();
        container.appendChild(pPais);

        ciudad.writeCoordenadas(container);

        const pInfo = document.createElement("p");
        pInfo.innerHTML = ciudad.getInfoSecundaria();
        container.appendChild(pInfo);
    }

}