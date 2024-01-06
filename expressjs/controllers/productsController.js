module.exports = (mysqlAPI, traits, redis) => {
    const functionTrait = traits.FunctionTrait;
    const requestTrait = traits.RequestTrait;

    async function getProductDataFromShopify(store, dbData) {
        try {
            var returnVal = {};
            
            var productEndpoint = functionTrait.getShopifyAPIURLForStore('products/'+dbData.id+'.json', store);
            var headers = functionTrait.getShopifyAPIHeadersForStore(store);
            var productResponse = await requestTrait.makeAnAPICallToShopify('GET', productEndpoint, headers);
            returnVal.shopifyProduct = productResponse;
            
            

            return productResponse;
        } catch (error) {
            return error.message;
        }
    }
  
    return {
        listProducts: async function (req, res) {
            try {
                var authUser = req.user;
                var storeData = await mysqlAPI.getShopifyStoreData(authUser);
                var products = await functionTrait.getProductsForStore(authUser, storeData, req.body);
                return res.json(products);
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

        listProductDetails: async function (req, res) {
            var authUser = req.user;
            var storeData = await mysqlAPI.getShopifyStoreData(authUser);

            const productId = req.query.product_id;
            console.log('Product ID '+productId);
            

            const opts = {
                where: {
                    'store_id': storeData.table_id,
                    'id': parseInt(productId)
                }
            }

            opts.attributes = [
                'table_id', 'id', 'title', 'vendor', 'product_type', 'status', 'created_at', 'image'
            ];

            const dbData = await mysqlAPI.getProduct(opts);
            const shopifyData = await getProductDataFromShopify(storeData, dbData);

            return res.json({
                'status': true,
                'productId': productId,
                'dbData': dbData,
                'shopifyData': shopifyData
            })
        },

        listProductCollections: async function (req, res) {
            try {
                var authUser = req.user;
                var storeData = await mysqlAPI.getShopifyStoreData(authUser);
                var productCollections = await functionTrait.listProductCollections(authUser, storeData, req.body);
                return res.json(productCollections);
            } catch (error) {
                return res.json({
                    "status": false,
                    "message": "Something went wrong. If the issue persists, please contact Customer support.",
                    "debug": {
                        "error_message": error.message
                    }
                })
            }
        }
    }
}