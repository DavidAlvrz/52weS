class Memoria {
    constructor() {
        this.reiniciarAtributos();
        this.barajarCartas();

        this.cronometro = new Cronometro();
        this.cronometro.arrancar();
    }

    reiniciarAtributos() {
        this.tablero_bloqueado = false;
        this.primera_carta = null;
        this.segunda_carta = null;
    }

    voltearCarta(carta) {
        const deshabilitada = carta.dataset.estado === "revelada";
        const volteada = carta.dataset.estado === "volteada";

        if (this.tablero_bloqueado || deshabilitada || volteada)
            return;

        carta.dataset.estado = "volteada";

        if(!this.primera_carta) {
            this.primera_carta = carta;
            return;
        }

        this.segunda_carta = carta;
        this.comprobarPareja();
    }

    barajarCartas() {
        const main = document.querySelector("main");
        const hijos = main.children;

        const cartas = Array.from(hijos).filter(el => el.tagName.toLowerCase() === "article");

        for (let i = cartas.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [cartas[i], cartas[j]] = [cartas[j], cartas[i]];
        }

        for (let carta of cartas) {
            main.appendChild(carta);
        }
    }

    deshabilitarCartas() {
        this.primera_carta.dataset.estado = "revelada";
        this.segunda_carta.dataset.estado = "revelada";
        this.comprobarJuego();
        this.reiniciarAtributos();
    }

    comprobarJuego() {
        const main = document.querySelector("main");
        const cartas = main.querySelectorAll("article");
        for (let carta of cartas) {
            if (carta.dataset.estado !== "revelada") {
                return false;
            }
        }

        this.cronometro.parar();
        return true;
    }

    cubrirCartas() {
        this.tablero_bloqueado = true;

        setTimeout(() => {
            this.primera_carta.dataset.estado = "";
            this.segunda_carta.dataset.estado = "";
            this.reiniciarAtributos();
        }, 1500);
    }

    comprobarPareja() {
        if (!this.primera_carta || !this.segunda_carta) return;

        const img1 = this.primera_carta.children[1];
        const img2 = this.segunda_carta.children[1];

        const src1 = img1.getAttribute("src");
        const src2 = img2.getAttribute("src");

        src1 === src2 ? this.deshabilitarCartas() : this.cubrirCartas();
    }

}