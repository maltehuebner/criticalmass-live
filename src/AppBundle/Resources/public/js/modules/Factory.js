define(['RideEntity', 'CityEntity', 'PositionEntity'], function () {

    Factory = function () {
    };

    Factory.prototype.createRide = function (rideJson) {
        var rideEntity = new RideEntity();

        rideEntity = this._transferProperties(rideEntity, rideJson);

        return rideEntity;
    };


    Factory.prototype.createCity = function (cityJson) {
        var cityEntity = new CityEntity();

        cityEntity = this._transferProperties(cityEntity, cityJson);

        return cityEntity;
    };

    Factory.prototype.createPosition = function (positionJson) {
        var positionEntity = new PositionEntity();

        positionEntity = this._transferProperties(positionEntity, positionJson);

        return positionEntity;
    };

    Factory.prototype._transferProperties = function (entity, data) {
        var object = null;

        if (data !== null && typeof data === 'object') {
            object = data;
        } else {
            object = JSON.parse(data);
        }

        for (var property in object) {
            if (object.hasOwnProperty(property)) {
                entityProperty = property.charAt(0).toLowerCase() + property.slice(1);

                var prefix = '';

                if (entityProperty.charAt(0) != '_') {
                    prefix = '_';
                }

                if (entityProperty == 'timestamp') {
                    entity[prefix + entityProperty] = new Date(object[property] * 1000);
                } else if (entityProperty == 'city') {
                    entity[prefix + entityProperty] = this.createCity(object[property]);
                } else {
                    entity[prefix + entityProperty] = object[property];
                }
            }
        }

        return entity;
    };

    return new Factory;
});
