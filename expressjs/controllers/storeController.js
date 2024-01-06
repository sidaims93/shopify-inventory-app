module.exports = (mysqlAPI, traits, redis) => {
  const functionTrait = traits.FunctionTrait;
  const requestTrait = traits.RequestTrait;

  async function saveCollectionsForStore(store, type) {
    var headers = functionTrait.getShopifyAPIHeadersForStore(store);
    var since_id = null;
    var collections = null;
    do {
      var sinceIdPrefix = since_id !== null ? '?since_id='+since_id : '';
      var endpointPrefix = type == 'smart' ? 'smart_collections.json':'custom_collections.json';
      var endpoint = functionTrait.getShopifyAPIURLForStore(endpointPrefix+sinceIdPrefix, store);
      var response = await requestTrait.makeAnAPICallToShopify('GET', endpoint, headers);
      console.log(response);
      if(response.status) {
        collections = type == 'smart' ? response.respBody.smart_collections : response.respBody.custom_collections;
        if(collections !== null && collections.length > 0) {
          for await (var collection of collections) {
            collection.store_id = store.table_id;
            collection.collection_type = type;
            collection.image = collection.image != undefined ? collection.image.src : null;
            await mysqlAPI.updateOrCreateShopifyProductCollection(collection);
            since_id = collection.id 
          }
        }
      } else {
        collections = null;
      }
    } while (collections !== null && collections.length > 0);
    return true;
  }

  async function saveLocationsForStore(store) {
    var headers = functionTrait.getShopifyAPIHeadersForStore(store);
    var since_id = null;
    var locations = null;
    do {
      var sinceIdPrefix = since_id !== null ? '?since_id='+since_id : '';
      var endpointPrefix = 'locations.json';
      var endpoint = functionTrait.getShopifyAPIURLForStore(endpointPrefix+sinceIdPrefix, store);
      var response = await requestTrait.makeAnAPICallToShopify('GET', endpoint, headers);
      console.log(response);
      if(response.status) {
        locations = response.respBody.locations;
        if(locations !== null && locations.length > 0) {
          for await (var location of locations) {
            location.store_id = store.table_id;
            await mysqlAPI.updateOrCreateShopifyLocation(location);
            since_id = location.id 
          }
        }
      } else {
        locations = null;
      }
    } while (locations !== null && locations.length > 0);
    return true;
  }

  return {
    /**
     * Dashboard API where basically the dashboard info is shown
     * @param {*} req 
     * @param {*} res 
     * @returns {object} - For dashboard purposes
    */
    
    syncProducts: async function (req, res) {
      var returnVal;
      try {
        var stores = await mysqlAPI.getAllStores(['table_id', 'id', 'myshopify_domain', 'accessToken']);
        if(stores) {
          for await(var store of stores) {
            const redisKey = `sync:products:store:${store.table_id}`;
            const redisKeyExists = await redis.exists(redisKey);
            if(redisKeyExists) {
              //do nothing, I guess. Sync already ran
            } else {
              //await redis.set(redisKey, '1', 'EX', '300'); //5 minutes
              try {
                var since_id = null;
                var headers = functionTrait.getShopifyAPIHeadersForStore(store);
                var products = null;
                do {
                  var sinceIdPrefix = since_id !== null ? '?since_id='+since_id : '';
                  var endpoint = functionTrait.getShopifyAPIURLForStore(`products.json`+sinceIdPrefix, store);
                  var response = await requestTrait.makeAnAPICallToShopify('GET', endpoint, headers);
                  if(response.status) {
                    products = response.respBody.products;
                    if(products !== null && products.length > 0) {
                      for await (var product of products) {
                        product.store_id = store.table_id;
                        product.image = JSON.stringify(product.image);
                        await mysqlAPI.updateOrCreateShopifyProduct(product);
                        since_id = product.id 
                      }
                    }
                  } else {
                    products = null;
                  }
                } while (products !== null);
              } catch (error) {
                console.log('in error block '+error.message);
              }
            }
          }
        }

        returnVal = {
          'status': true,
          'message': 'Done!'
        };
      } catch (error) {
        returnVal = {
          "status": false,
          "message": "Something went wrong. If the issue persists, please contact Customer support.",
          "debug": {
            "error_message": error.message
          }
        }
      }

      console.log('returnVal');
      console.log(returnVal);
      return res.json(returnVal);
    },

    syncOrders: async function (req, res) {
      var returnVal;
      try {
        var stores = await mysqlAPI.getAllStores(['table_id', 'id', 'myshopify_domain', 'accessToken']);
        if(stores) {
          for await(var store of stores) {
            const redisKey = `sync:orders:store:${store.table_id}`;
            const redisKeyExists = await redis.exists(redisKey);
            if(redisKeyExists) {
              //do nothing, I guess. Sync already ran
            } else {
              //await redis.set(redisKey, '1', 'EX', '300'); //5 minutes
              try {
                var since_id = null;
                var headers = functionTrait.getShopifyAPIHeadersForStore(store);
                var orders = null;
                do {
                  const respFields = [ //I have to do it because Shopify won't give me protected data access
                    "name", 
                    "total_price", 
                    "number", 
                    "id", 
                    "cart_token", 
                    "checkout_token", 
                    "checkout_id", 
                    "currency", 
                    "financial_status" , 
                    "fulfillment_status" , 
                    "note" , 
                    "order_number" , 
                    "subtotal_price" , 
                    "total_price" , 
                    "total_tax",
                    "line_items",
                    "tags" 
                  ];

                  var urlParams = '?fields='+respFields.join(',') + (since_id !== null ? '&since_id='+since_id : '');
                  var endpoint = functionTrait.getShopifyAPIURLForStore(`orders.json`+urlParams, store);
                  var response = await requestTrait.makeAnAPICallToShopify('GET', endpoint, headers);
                  if(response.status) {
                    orders = response.respBody.orders;
                    if(orders.length > 0) {
                      for await (var order of orders) {
                        order.store_id = store.table_id;
                        order.line_items = JSON.stringify(order.line_items);
                        await mysqlAPI.updateOrCreateShopifyOrder(order); 
                        since_id = order.id;
                      }
                    } else {
                      orders = null;
                    } 
                  } else {
                    orders = null;
                  }
                } while (orders !== null);
              } catch (error) {
                console.log('in error block '+error.message);
              }
            }
          }
        }

        returnVal = {
          'status': true,
          'message': 'Done!'
        };
      } catch (error) {
        returnVal = {
          "status": false,
          "message": "Something went wrong. If the issue persists, please contact Customer support.",
          "debug": {
            "error_message": error.message
          }
        }
      }

      return res.json(returnVal);
    },

    syncProductCollections: async function (req, res) {
      var returnVal;
      try {
        var stores = await mysqlAPI.getAllStores(['table_id', 'id', 'myshopify_domain', 'accessToken']);
        if(stores) {
          for await(var store of stores) {
            const redisKey = `sync:collections:store:${store.table_id}`;
            const redisKeyExists = await redis.exists(redisKey);
            if(redisKeyExists) {
              //do nothing, I guess. Sync already ran
            } else {
              //await redis.set(redisKey, '1', 'EX', '300'); //5 minutes
              await saveCollectionsForStore(store, 'custom');
              await saveCollectionsForStore(store, 'smart');
            }
          }
        }

        return res.json({"status": true, 'message': 'Done'}).status(200);
      } catch(err) {
        console.log(err.message);
      }
    },

    syncStoreLocations: async function (req, res) {
      var returnVal;
      try {
        var stores = await mysqlAPI.getAllStores(['table_id', 'id', 'myshopify_domain', 'accessToken']);
        if(stores) {
          for await(var store of stores) {
            const redisKey = `sync:locations:store:${store.table_id}`;
            const redisKeyExists = await redis.exists(redisKey);
            if(redisKeyExists) {
              //do nothing, I guess. Sync already ran
            } else {
              //await redis.set(redisKey, '1', 'EX', '300'); //5 minutes
              await saveLocationsForStore(store);
            }
          }
        }
        returnVal = {"status": true, 'message': 'Done'};
      } catch(err) {
        console.log(err.message);
        returnVal = {"status": false, 'message': err.message};
      }
      return res.json(returnVal).status(200);
    },

    listStores: async function (req, res) {
      try {
        var authUser = req.user;
        var storeData = await mysqlAPI.getShopifyStoreData(authUser);
        return res.json({
          'status': true,
          'storeData': storeData
        });
      } catch (error) {
        return res.json({
          'status': false,
          'message': err.message
        })
      }
    },

    getLiveThemeForStore: async function (req, res) {
      const store = req.body.store;
      var endpoint = functionTrait.getShopifyAPIURLForStore('themes.json', store);
      var headers = functionTrait.getShopifyAPIHeadersForStore(store);
      var response = await requestTrait.makeAnAPICallToShopify('GET', endpoint, headers);
      if(response.status) {
        for(var i in response.respBody.themes) {
          if(response.respBody.themes[i].role == 'main') {
            return res.json({
              "status": true,
              "theme": response.respBody.themes[i]
            })
          }
        }
      }
    },

    checkStoreSetup: async function (req, res) {
      try {
        const store = req.body.store;
        const theme = req.body.theme;
        var scriptAsset = 'asset[key]=config/settings_data.json';
        var homepageBlockAsset = 'asset[key]=templates/index.json';
        var returnVal = {};

        var scriptEndpoint = functionTrait.getShopifyAPIURLForStore('themes/'+theme.id+'/assets.json?'+scriptAsset, store);
        var homePageEndpoint = functionTrait.getShopifyAPIURLForStore('themes/'+theme.id+'/assets.json?'+homepageBlockAsset, store);
        var headers = functionTrait.getShopifyAPIHeadersForStore(store);
        
        returnVal.scriptResponse = await requestTrait.makeAnAPICallToShopify('GET', scriptEndpoint, headers);
        returnVal.homePageResponse = await requestTrait.makeAnAPICallToShopify('GET', homePageEndpoint, headers);

        return res.json({'status': true, 'response': returnVal, 'endpoints': {scriptEndpoint, homePageEndpoint}});  
      } catch (error) {
        return res.json({'status': false, 'err': error.message});
      }
    },

    insertAppLogs: async function (req, res) {
      try {
        const logs = req.body.logs;
        const authUser = req.user;
        var storeData = await mysqlAPI.getShopifyStoreData(authUser);
        await mysqlAPI.insertAppLogs(storeData, logs);
        return res.json({'status': true});  
      } catch (error) {
        return res.json({'status': false, 'message': error.message});
      }
    },

    /**
     * @param {*} req 
     * @param {*} res 
     * @returns Logs data from MongoDB database
     */
    getAppLogs: async function (req, res) {
      try {
        const authUser = req.user;
        const reqParams = req.query;
        var storeData = await mysqlAPI.getShopifyStoreData(authUser);
        const result = await mysqlAPI.getAppLogs(storeData, reqParams);
        return res.json({'status': true, 'data': result, 'params': req.params, 'query': req.query});  
      } catch (error) {
        return res.json({'status': false, 'message': error.message});
      }
    }
  }
}


