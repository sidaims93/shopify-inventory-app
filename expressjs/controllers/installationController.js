module.exports = (mysqlAPI, traits) => {
    const functionTrait = traits.FunctionTrait;
    const requestTrait = traits.RequestTrait;

    var apiVersion = '2024-01';
    var accessScopes = 'read-products,read-orders';
    var clientId = '96a0fe624ed58749bb66a89ce7869f7e';
    var clientSecret = 'c8035b4d207c51d8ea50fd01dbb568b0';
    var redirectUri = 'https://9f66-2405-201-a408-6114-c1a9-8533-6f54-ee2f.ngrok-free.app/shopify/auth/redirect';

    return {
        index: async function (req, res) {
            try {
                if(req.query.hasOwnProperty('shop') && req.query.hasOwnProperty('hmac') && req.query.shop.length && req.query.hmac.length) {
                    var hmacValid = await functionTrait.isRequestFromShopify(req.query, clientSecret);
                    if(hmacValid) {
                        var shop = req.query.shop;
                        var dbRecord = await functionTrait.getStoreByDomain(shop);
                        if(dbRecord !== null && dbRecord.length) {
                            var tokenValid = await checkStoreTokenValidity(dbRecord);
                            if(tokenValid) {
                                //var userShop = await mysqlAPI.findUserForStoreId(dbRecord);
                                //var user = await mysqlAPI.findUser(userShop);

                                //ADD SOME LOGIC TO LOG THEM IN AUTOMATICALLY
                                return res.redirect('/dashboard');
                            } 
                        }

                        var endpoint = `https://${shop}/admin/oauth/authorize?client_id=${clientId}&scope=${accessScopes}&redirect_uri=${redirectUri}`;
                        return res.redirect(endpoint);
                    }
                } 

                return res.json({
                    'status': false, 
                    'message': 'Invalid request.'
                });
            } catch (error) {
                return res.json({
                    data: null,
                    count: 0,
                    query: null,
                    message: error.message
                })
            }
        },

        redirect: async function (req, res) {
            try {
                if(req.query.hasOwnProperty('shop') && req.query.hasOwnProperty('code')) {
                    var hmacValid = await functionTrait.isRequestFromShopify(req.query, clientSecret);
                    if(hmacValid) {
                        var accessToken = await requestAccessTokenFromShopify(req.query);
                        if(accessToken !== null) {
                            var shopifyStore = await getShopifyStoreDetails(req.query, accessToken);
                            await saveDetailsToDatabase(shopifyStore, accessToken, req.query);

                            return res.redirect('/dashboard');
                            //Check if user is logged in. 
                            //var check = await this.checkIfUserIsLoggedIn(req);
                            //return check ? res.redirect('/dashboard') : res.redirect('login'); 
                        }
                    }
                } 

                return res.json({
                    'status': false, 
                    'message': 'Invalid request.',
                    'request': req.query
                });
            } catch (error) {
                return res.json({
                    data: null,
                    count: 0,
                    query: req.query,
                    message: error.message
                })
            }
        },

        checkStoreTokenValidity: async function (dbRecord) {
            if(dbRecord == null || dbRecord.accessToken == null) return false;
    
            var endpoint = functionTrait.getShopifyAPIURLForStore('shop.json', dbRecord);
            var headers = functionTrait.getShopifyAPIHeadersForStore(dbRecord);
            var response = await requestTrait.makeAnAPICallToShopify('GET', endpoint, headers);
            return response.status && response.respBody.hasOwnProperty('shop');
        }
        
    }

    async function requestAccessTokenFromShopify(query) {
        var endpoint = `https://${query.shop}/admin/oauth/access_token`;
        var body = {
            'client_id': clientId,
            'client_secret': clientSecret,
            'code': query.code
        };
        var headers = {
            'Content-Type': 'application/json'
        };

        var response = await requestTrait.makeAnAPICallToShopify('POST', endpoint, headers, body);
        console.log('response from oauth access token');
        console.log(response);

        if(response.status) {
            return response.respBody.access_token;
        }

        return null;
    }

    async function getShopifyStoreDetails(query, accessToken) {
        var endpoint = functionTrait.getShopifyAPIURLForStore('shop.json', {"myshopify_domain": query.shop});
        var headers = functionTrait.getShopifyAPIHeadersForStore({"accessToken": accessToken});
        var response = await requestTrait.makeAnAPICallToShopify('GET', endpoint, headers);

        // console.log('response from getting shop details');
        // console.log(response.respBody.shop);

        if(response.status) 
            return response.respBody.shop;

        return null;
    }

    async function saveDetailsToDatabase(shopifyStore, accessToken) {
        try {
            const { hash } = require("bcryptjs");
            var storeBody = {
                "id": shopifyStore.id,
                "myshopify_domain": shopifyStore.domain,
                "name": shopifyStore.name,
                "accessToken": accessToken,
                "currency": shopifyStore.currency,
                "email": shopifyStore.email,
                "phone": shopifyStore.phone
            };

            var userBody = {
                "name": shopifyStore.name,
                "email": shopifyStore.email,
                "password": await hash('123456', 8)
            };

            var userRecord = await mysqlAPI.updateOrCreateUserRecord(userBody);
            var storeRecord = await mysqlAPI.updateOrCreateStoreRecord(storeBody);

            var userStoreRecord = await mysqlAPI.updateOrCreateUserStoreMapping(userRecord, storeRecord);

            return true;
        } catch(error) {
            console.log('error in saving details to database '+error.message);
        }
    }
}