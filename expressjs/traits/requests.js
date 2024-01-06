const axios = require('axios');
    
module.exports = {
    makeAnAPICallToShopify: async function (method = 'GET', endpoint, headers, payload) {
        let reqResult = null;
        try {
            if(method == 'GET') {
                reqResult = await axios.get(endpoint, {headers: headers})
                .then((res) => {
                    return {
                        "status": true,
                        "respBody": res.data
                    };
                })
                .catch(function (error) {
                    if (error.response) {
                        return {
                            "status": false,
                            "respBody": error.response.data,
                            "statusCode": error.response.status
                        }
                    } else {
                        return {
                            "status": false,
                            "message": "ERROR",
                            "respBody": error
                        }
                    }
                })
            } else {
                reqResult = await axios.post(endpoint, payload, {headers: headers})
                .then((res) => {
                    return {
                        "status": true,
                        "respBody": res.data
                    };
                })
                .catch(function (error) {
                    if (error.response) {
                        return {
                            "status": false,
                            "respBody": error.response.data,
                            "statusCode": error.response.status
                        }
                    } else {
                        return {
                            "status": false,
                            "message": "ERROR",
                            "respBody": error
                        }
                    }
                });
        
            }
            
        } catch (error) {
            reqResult = {
                "status": false,
                "respBody": null,
                "message": error.message
            }
        }
        return reqResult;
    }
}