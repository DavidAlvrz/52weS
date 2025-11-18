class Carrusel {
    constructor() {
        this.busqueda = "Circuito Ricardo Tormo";
        this.actual = 0;
        this.maximo = 4;
        this.flickrAPI = "https://api.flickr.com/services/feeds/photos_public.gne?jsoncallback=?";

        this.mostrarFotografias();
    }

    async mostrarFotografias() {
        const data = await this.getFotografias();
        this.images = this.procesarJSONFotografias(data);

        this.cambiarFotografia();
        setInterval(() => {
            this.cambiarFotografia();
        }, 3000);
    }

    async getFotografias() {
        return $.getJSON(this.flickrAPI,
            {
                tags: this.busqueda,
                tagmode: "any",
                format: "json"
            })
            .done(function (data) {
                return data;
            });
    }

    procesarJSONFotografias(data) {
        const images = data.items.map(i => i.media.m.replace("_m", "_z")).splice(0, this.maximo + 1);
        return images;
    }

    cambiarFotografia() {
        $("main article img").remove();
        $("main article").append(`<img src="${this.images[this.actual]}" alt="Fotografía del circuito">`);
        this.actual = this.actual < this.maximo ? this.actual + 1 : 0;
    }

}