class storeValidityHandler {
    
    constructor(mysqlAPI, traits) {
        this.mysqlAPI = mysqlAPI;
        //this.redis = redis;
        this.traits = traits;

        this.FunctionTrait = traits.FunctionTrait;
        this.RequestTrait = traits.RequestTrait;
    }

    async work () {
        try {
            var stores = await this.mysqlAPI.getAllStores();
            if(stores !== null && stores.length > 0) {
                for await (var store of stores) {
                    var redisKey = 'Store:Validation:Result:'+store.id;
                    var result = await this.checkValidityForStore(store);
                    //await this.redis.set(redisKey, result, 'EX', parseInt(5*60));
                }
            }
        } catch (error) {
            console.log('In check store validity function');
            console.log(error.message);
        }
    }

    async checkValidityForStore(store) {
        var endpoint = this.FunctionTrait.getShopifyAPIURLForStore('shop.json', store);
        var headers = this.FunctionTrait.getShopifyAPIHeadersForStore(store);
        var result = await this.RequestTrait.makeAnAPICallToShopify('GET', endpoint, headers);
        return result.status && result.respBody && result.respBody.shop && result.respBody.shop.id; //Store ID returned which means it's a valid request            
    }
}

module.exports = storeValidityHandler