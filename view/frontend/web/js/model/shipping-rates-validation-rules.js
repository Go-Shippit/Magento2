define(
    [],
    function () {
        'use strict';
        return {
            getRules: function() {
                return {
                    'city': {
                        'required': true
                    },
                    'region_id': {
                        'required': true
                    },
                    'postcode': {
                        'required': true
                    },
                    'country_id': {
                        'required': true
                    }
                };
            }
        };
    }
)