const lineItemId = "gid://shopify/AppSubscriptionLineItem/31562236177?v=1&index=0"
const nodeCache = require('node-cache');
const myCache = new nodeCache();

module.exports = (mysqlAPI, traits) => {
  const functionTrait = traits.FunctionTrait;
  const requestTrait = traits.RequestTrait;

  return {

    getSalesCardInfo: async function (req, res) {
      try {
        var user = req.user;
        var storeData = await mysqlAPI.getShopifyStoreData(user);
        
        var filterArr = {
          "start_date": req.query.start_date || null,
          "end_date": req.query.end_date || null,
        }

        var salesCardVal = await functionTrait.getSalesCardVal(user, storeData, filterArr);
        var response = await functionTrait.getSalesInfo(salesCardVal);

        return res.json({
          "status": true,
          "data": {
            "cardVal": salesCardVal,
            "response": response
          }
        })
      } catch(err) {
        console.log(err.message);
        return res.json({
          "status": false,
          "message": err.message
        })
      }
    },
    /**
     * Dashboard API where basically the dashboard info is shown
     * @param {*} req 
     * @param {*} res 
     * @returns {object} - For dashboard purposes
    */
    
    index: async function (req, res) {
      try {
        console.log('In dashboard api');
        return res.json({'status': true, 'message': 'In dashboard'});
        /*
        var authUser = req.user;
        var storeData = await mysqlAPI.getShopifyStoreData(authUser);
        var storesAvailable = await mysqlAPI.getAllShopifyStoresAssociatedWithUser(authUser);
        
        var returnVal;
        */

        /**check if cache already has the value */
        /*
        var cacheKey = `dashboard:${authUser.id}`;
        var cacheHasData = false;
        //var cacheHasData = await redis.exists(cacheKey);
        //var cacheHasData = myCache.has(cacheKey);

        if(cacheHasData) {
          // console.log('reading from cache');
          // returnVal = JSON.parse(await myCache.get(cacheKey));
          
          //console.log('reading from redis');
          returnVal = null;
        } else {
          var dashboardData = {
            "summary": await functionTrait.getDashboardSummary(authUser, storeData),
            "recentActivity": await functionTrait.getRecentActivity(authUser, storeData),
            "reports": await functionTrait.getReportData(authUser, storeData),
            "budgetReport": await functionTrait.getBudgetReport(authUser, storeData),
            "recentSales": await functionTrait.getRecentSales(authUser, storeData),
            "websiteTraffic": await functionTrait.getWebsiteTraffic(authUser, storeData),
            "topSellingProducts": await functionTrait.getTopSellingProducts(authUser, storeData)
          };

          returnVal = {
            "status": true,
            "message": "Welcome back!",
            "activeStore": storeData,
            "storesAvailable": storesAvailable,
            "dashboardData": dashboardData
          }

          //Set in cache for 2 minutes
          // console.log('setting in cache');
          // myCache.set(cacheKey, JSON.stringify(returnVal), 120);
          
          //console.log('setting in redis');
          //await redis.set(cacheKey, JSON.stringify(returnVal), 'EX', 120);
        }
        return res.json(returnVal)
        */  
      } catch (error) {
        return res.json({
          "status": false,
          "message": "Something went wrong. If the issue persists, please contact Customer support.",
          "debug": {
            "error_message": error.message
          }
        })
      }
    },

    /**
     * Setup App Usage Charges API - To setup the merchants billing based on how much they use the app
     * Cap Amount is set to limit their max spendings per month
     * 
     * @param {*} req 
     * @param {*} res 
     * @returns {object} - Object from Shopify API
    */

    setupAppUsageBilling: async function (req, res) {
      try {
        var user = req.user;
        var store = await mysqlAPI.getShopifyStoreData(user);
        var endpoint = functionTrait.getShopifyAPIURLForStore('graphql.json', store);
        var headers = functionTrait.getShopifyAPIHeadersForStore(store);
      
        //GraphQL Mutation
        var mutation = `
          mutation {
            appSubscriptionCreate(
              name: "LiveStream App Pricing plan",
              returnUrl: "https://www.google.com/",
              test: true,
              lineItems: [
                {
                  plan: {
                    appUsagePricingDetails: {
                      terms: "$1 for 1 action",
                      cappedAmount: {
                        amount: 100.00,
                        currencyCode: USD
                      }
                    }
                  }
                }
              ]
            ) {
              userErrors {
                field,
                message
              },
              confirmationUrl,
              appSubscription {
                id,
                lineItems {
                  id,
                  plan {
                    pricingDetails {
                      __typename
                    }
                  }
                }
              }
            }
          }
        `;

        var payload = {'query': mutation};
        const result = await requestTrait.makeAnAPICallToShopify('POST', endpoint, headers, payload);
        return res.json(result);
      } catch (error) {
        return res.json({"status": false, "message": error.message});
      }
    },

    /**
     * API to put a charge on merchant for the action they have done.
     * This case we are just binding it to the API call from Postman
     * 
     * @param {*} req 
     * @param {*} res 
     * @returns {object} - Response from Shopify API
     */
    createActionOnBilling: async function (req, res) {
      try {
        var user = req.user;
        var store = await mysqlAPI.getShopifyStoreData(user);
        var endpoint = functionTrait.getShopifyAPIURLForStore('graphql.json', store);
        var headers = functionTrait.getShopifyAPIHeadersForStore(store);
        var mutation = `
          mutation {
            appUsageRecordCreate(
              subscriptionLineItemId: "${lineItemId}",
              description: "App charge from ExpressJS API",
              price: {
                amount: 10.00,
                currencyCode: USD
              }
            ) {
              userErrors {
                field,
                message
              },
              appUsageRecord {
                id
              }
            }
          }
        `;
        var payload = {'query': mutation};
        const result = await requestTrait.makeAnAPICallToShopify('POST', endpoint, headers, payload);
        return res.json(result);  
      } catch (error) {
        return res.json({"status": false, "message": error.message});
      }
    },

    getReturnObject: async function (req, res) {
      try {
        var user = req.user;
        var store = await mysqlAPI.getShopifyStoreData(user);
        var endpoint = functionTrait.getShopifyAPIURLForStore('graphql.json', store);
        var headers = functionTrait.getShopifyAPIHeadersForStore(store);
        var mutation = `query returnableFulfillmentsQuery {
          returnableFulfillments(orderId: "gid://shopify/Order/${req.query.order_id}", first: 10) {
            edges {
              node {
                id
                fulfillment {
                  id
                }
                returnableFulfillmentLineItems(first: 10) {
                  edges {
                    node {
                      fulfillmentLineItem {
                        id
                      }
                      quantity
                    }
                  }
                }
              }
            }
          }
        }
        `;

        var payload = {'query': mutation};
        console.log('payload');
        console.log(payload);
        const result = await requestTrait.makeAnAPICallToShopify('POST', endpoint, headers, payload);
        return res.json(result);  
      } catch(error) {
        return res.json({"status": false, "message": error.message});
      }
    }
  }
}