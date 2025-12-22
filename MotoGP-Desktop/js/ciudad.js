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
        return "Gentilicio: " + this.gentilicio + ", Población: " + this.poblacion;
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

    async getMeteorologiaCarrera() {
        const url = "https://api.open-meteo.com/v1/forecast";
        const params = {
            daily: ["sunrise", "sunset"],
            latitude: 39.4699,
            longitude: -0.3763,
            hourly: ["temperature_2m", "apparent_temperature", "rain", "relative_humidity_2m", "wind_speed_10m", "wind_direction_10m"],
            timezone: "auto",
            start_date: "2025-11-16",
            end_date: "2025-11-16",
        };

        return $.ajax({
            url: url,
            data: params,
            dataType: "json"
        })

    }

    procesarJSONCarrera(data) {
        const processedData = {};
        const raceTime = 14;

        processedData.temperature = data.hourly.temperature_2m[raceTime];
        processedData.apparentTemperature = data.hourly.apparent_temperature[raceTime];
        processedData.rain = data.hourly.rain[raceTime];
        processedData.humidity = data.hourly.relative_humidity_2m[raceTime];
        processedData.windSpeed = data.hourly.wind_speed_10m[raceTime];
        processedData.windDirection = data.hourly.wind_direction_10m[raceTime];
        processedData.sunshine = new Date(data.daily.sunrise[0]);
        processedData.sunset = new Date(data.daily.sunset[0]);

        return processedData;
    }

    async mostrarInfoCarrera() {
        const data = await this.getMeteorologiaCarrera();
        const processedData = this.procesarJSONCarrera(data);

        const container = document.body;

        const h3 = document.createElement("h3");
        h3.textContent = "Información meteorológica de la carrera";
        container.appendChild(h3);

        const pTemperature = document.createElement("p");
        pTemperature.textContent = "Temperatura: " + processedData.temperature + " °C";
        container.appendChild(pTemperature);

        const pApparentTemperature = document.createElement("p");
        pApparentTemperature.textContent = "Sensación térmica: " + processedData.apparentTemperature + " °C";
        container.appendChild(pApparentTemperature);

        const pRain = document.createElement("p");
        pRain.textContent = "Lluvia: " + processedData.rain + " mm";
        container.appendChild(pRain);

        const pHumidity = document.createElement("p");
        pHumidity.textContent = "Humedad relativa: " + processedData.humidity + " %";
        container.appendChild(pHumidity);

        const pWindSpeed = document.createElement("p");
        pWindSpeed.textContent = "Velocidad del viento: " + processedData.windSpeed + " km/h";
        container.appendChild(pWindSpeed);

        const pWindDirection = document.createElement("p");
        pWindDirection.textContent = "Dirección del viento: " + processedData.windDirection + " °";
        container.appendChild(pWindDirection);

        const pSunrise = document.createElement("p");
        pSunrise.textContent = "Amanecer: " + processedData.sunshine.getHours() + ":" + processedData.sunshine.getMinutes();
        container.appendChild(pSunrise);

        const pSunset = document.createElement("p");
        pSunset.textContent = "Atardecer: " + processedData.sunset.getHours() + ":" + processedData.sunset.getMinutes();
        container.appendChild(pSunset);
    }

    async getMeteorologiaEntrenos() {
        const url = "https://api.open-meteo.com/v1/forecast";
        const params = {
            daily: ["sunrise", "sunset"],
            latitude: 39.4699,
            longitude: -0.3763,
            hourly: ["temperature_2m", "apparent_temperature", "rain", "relative_humidity_2m", "wind_speed_10m", "wind_direction_10m"],
            timezone: "auto",
            start_date: "2025-11-14",
            end_date: "2025-11-15",
        };

        return $.ajax({
            url: url,
            data: params,
            dataType: "json"
        })

    }

    procesarJSONEntrenos(data) {
        console.log(data);
        const processedData = {};
        const trainTime = 10;

        for (const key in data.hourly) {
            const firstDayValue = data.hourly[key].slice(0, 24)[trainTime];
            const secondDayValue = data.hourly[key].slice(24, 48)[trainTime];

            processedData[key] = ((firstDayValue + secondDayValue) / 2).toFixed(2);
        }

        // Average sunrise and sunset times by taking only hours and minutes
        const averageTimeOfDay = (t1, t2) => {
            const d1 = new Date(t1);
            const d2 = new Date(t2);

            const minutes1 = d1.getHours() * 60 + d1.getMinutes();
            const minutes2 = d2.getHours() * 60 + d2.getMinutes();

            const avgMinutes = (minutes1 + minutes2) / 2;

            let avgHours = Math.floor(avgMinutes / 60) % 24;
            let avgMins = Math.round(avgMinutes % 60);
            if (avgMins === 60) { avgMins = 0; avgHours = (avgHours + 1) % 24; }

            const out = new Date(d1);
            out.setHours(avgHours, avgMins, 0, 0);
            return out;
        };

        const avgSunrise = averageTimeOfDay(data.daily.sunrise[0], data.daily.sunrise[1]);
        const avgSunset = averageTimeOfDay(data.daily.sunset[0], data.daily.sunset[1]);

        processedData.sunrise = avgSunrise;
        processedData.sunset = avgSunset;


        return processedData;
    }

    async mostrarInfoEntrenos() {
        const data = await this.getMeteorologiaEntrenos();
        const processedData = this.procesarJSONEntrenos(data);
        console.log(processedData);

        const container = document.body;

        const h3 = document.createElement("h3");
        h3.textContent = "Información meteorológica de los entrenos";
        container.appendChild(h3);

        const pTemperature = document.createElement("p");
        pTemperature.textContent = "Temperatura media: " + processedData.temperature_2m + " °C";
        container.appendChild(pTemperature);

        const pApparentTemperature = document.createElement("p");
        pApparentTemperature.textContent = "Sensación térmica media: " + processedData.apparent_temperature + " °C";
        container.appendChild(pApparentTemperature);

        const pRain = document.createElement("p");
        pRain.textContent = "Lluvia media: " + processedData.rain + " mm";
        container.appendChild(pRain);

        const pHumidity = document.createElement("p");
        pHumidity.textContent = "Humedad relativa media: " + processedData.relative_humidity_2m + " %";
        container.appendChild(pHumidity);

        const pWindSpeed = document.createElement("p");
        pWindSpeed.textContent = "Velocidad del viento media: " + processedData.wind_speed_10m + " km/h";
        container.appendChild(pWindSpeed);

        const pWindDirection = document.createElement("p");
        pWindDirection.textContent = "Dirección del viento media: " + processedData.wind_direction_10m + " °";
        container.appendChild(pWindDirection);

        const pSunrise = document.createElement("p");
        pSunrise.textContent = "Amanecer medio: " + processedData.sunrise.getHours() + ":" + processedData.sunrise.getMinutes();
        container.appendChild(pSunrise);

        const pSunset = document.createElement("p");
        pSunset.textContent = "Atardecer medio: " + processedData.sunset.getHours() + ":" + processedData.sunset.getMinutes();
        container.appendChild(pSunset);
    }

}