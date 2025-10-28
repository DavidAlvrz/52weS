class Ciudad {
    constructor(nombre,pais,gentilicio){
        this.nombre = nombre;
        this.pais = pais;
        this.gentilicio = gentilicio;
    }

    poblarAtributos(){
        this.poblacion = 825950;
        this.coordenadas = {latitud: 39.4699, longitud: -0.3763};
    }

    getNombre(){
        return this.nombre;
    }
    
    getPais(){
        return this.pais;
    }

    getInfoSecundaria(){
        return "<ul><li>Gentilicio: " + this.gentilicio + "</li><li>Población: " + this.poblacion + "</li></ul>";
    }

    writeCoordenadas(){
        document.write("<p>Latitud: " + this.coordenadas.latitud + ", Longitud: " + this.coordenadas.longitud + "</p>");
    }
    

}