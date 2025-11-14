class Cronometro {
    constructor() {
        this.tiempo = 0;
        this.actualizar = this.actualizar.bind(this);
    }

    arrancar() {
        try {
            this.inicio = Temporal.Now.instant();
        } catch (e) {
            this.inicio = Date.now();
        }

        this.corriendo = setInterval(this.actualizar, 100);
    }

    actualizar() {
        try {
            this.tiempo = Temporal.Now.instant().since(this.inicio).total('milliseconds');
        } catch (e) {
            this.tiempo = Date.now() - this.inicio;
        }

        this.mostrar();
    }

    parar() {
        clearInterval(this.corriendo);
    }

    reiniciar() {
        clearInterval(this.corriendo);
        this.tiempo = 0;
        this.mostrar();
    }

    mostrar() {
        const totalSegundos = this.tiempo / 1000;
        const minutos = parseInt(totalSegundos / 60);
        const segundos = parseInt(totalSegundos % 60);
        const decimas = parseInt((this.tiempo % 1000) / 100);

        const minutosStr = String(minutos).padStart(2, '0');
        const segundosStr = String(segundos).padStart(2, '0');
        const decimasStr = String(decimas);

        const formato = `${minutosStr}:${segundosStr}.${decimasStr}`;

        const parrafo = document.querySelector('main p');
        if (parrafo) {
            parrafo.textContent = formato;
        }
    }
}
