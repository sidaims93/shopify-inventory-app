const { Sequelize, Model, DataTypes, DATE } = require('sequelize');
const mysqlAPI = require('../src/mysql-api')(Sequelize, DataTypes);
var crypto = require('crypto');
const nodeCache = require('node-cache');
const cacheInstance = new nodeCache();
module.exports = {
    getStoreByDomain: async function (shop) {
        return await mysqlAPI.getStoreByDomain(shop);
    },

    isRequestFromShopify: async function (req, clientSecret) {
        var hmac = req.hmac;
        delete(req.hmac);
        var data = new Array();
        for (var key in req){
            key = key.replace("%", "%25");
            key = key.replace("&", "%26");
            key = key.replace("=", "%3D");
            
            var val = req[key];
            val = val.replace("%","%25");
            val = val.replace("&","%26");
            data.push(key+'='+val);
        }
        data = data.join('&');
        const genHash = crypto.createHmac("sha256", clientSecret).update(data).digest("hex");
        return genHash === hmac;
    },

    /**
     * @param {string} path 
     * @param {object} store
     * @returns {string} URL - Format the same as Shopify API URLs
     */
    getShopifyAPIURLForStore(path, store) {
        var API_VERSION = process.env.SHOPIFY_API_VERSION;
        if(API_VERSION.length < 1) 
            API_VERSION = '2024-01';
        return `https://${store.myshopify_domain}/admin/api/${API_VERSION}/${path}`;
    },

    /**
     * @param {object} store 
     * @returns {object} headers - Used to make authenticated Shopify API calls
     */
    getShopifyAPIHeadersForStore(store) {
        return {
            "Content-Type": "application/json",
            "X-Shopify-Access-Token": store.accessToken
        }
    },

    /**
     * 
     * @param {*} user 
     * @param {*} storeData
     * @returns {int} Store's revenue 
     */
    async getStoreRevenue(user, storeData, redis, filterArr) {
        //Redis/ElasticSearch if they have it
        //If they do, return it from here
        //Otherwise, set the values there and retrieve from it and return
        var redisKey = `Store:Revenue:Card:${storeData.table_id}`;
        let existsInRedis = await redis.exists(redisKey);
        if(existsInRedis) {
            return parseFloat(await redis.get(redisKey));
        }
        var start_date = filterArr !== null && Object.hasOwnProperty('start_date', filterArr) ? filterArr.start_date : null
        var end_date = filterArr !== null && Object.hasOwnProperty('end_date', filterArr) ? filterArr.end_date : null
        var storeRevenue = await mysqlAPI.getStoreRevenueForStore(storeData, start_date, end_date);
        storeRevenue = storeRevenue.total_revenue == null ? 0 : storeRevenue.total_revenue;
        await redis.set(redisKey, storeRevenue, 'EX', 300); //300 seconds
        return storeRevenue;
    },

    /**
     * 
     * @param {*} user 
     * @param {*} storeData
     * @returns {int} How much sales has the store done 
     */
    async getSalesCardVal(user, storeData, redis, filterArr) {
        //Redis/ElasticSearch if they have it
        //If they do, return it from here
        //Otherwise, set the values there and retrieve from it and return
        var start_date = filterArr !== null && Object.hasOwnProperty('start_date', filterArr) ? filterArr.start_date : null
        var end_date = filterArr !== null && Object.hasOwnProperty('end_date', filterArr) ? filterArr.end_date : null
        var redisKey = `Store:Sales:Card:${storeData.table_id}:${start_date}:${end_date}`;
        let existsInRedis = await redis.exists(redisKey);
        if(existsInRedis) {
            return parseFloat(await redis.get(redisKey));
        }
        await redis.set(redisKey, 350, 'EX', 300); //300 seconds
        var storeCountOrders = await mysqlAPI.getNoOfOrdersForStore(storeData, start_date, end_date);
        console.log('storeCountOrders '+JSON.stringify(storeCountOrders));
        storeCountOrders = storeCountOrders == null ? 0 : storeCountOrders;
        await redis.set(redisKey, storeCountOrders, 'EX', 300); //300 seconds
        return storeCountOrders;
    },

    /**
     * 
     * @param {*} user 
     * @param {*} storeData
     * @returns {int} How many customers interacted with the app 
     */
    async getCustomersCardVal(user, storeData, redis) {
        //Redis/ElasticSearch if they have it
        //If they do, return it from here
        //Otherwise, set the values there and retrieve from it and return
        var redisKey = `Store:Customers:Card:${storeData.table_id}`;
        let existsInRedis = await redis.exists(redisKey);
        if(existsInRedis) {
            return parseFloat(await redis.get(redisKey));
        }
        await redis.set(redisKey, 2570, 'EX', 300); //300 seconds
        return 2570;
    },

    async getSalesInfo(salesCardVal) {
        return {
            "label": "Today",
            "displayVal": salesCardVal,
            "displayValformatted": salesCardVal.toLocaleString('en-US', {}),
            "class": "bi bi-cart",
            "trend": {
                "label": "12%",
                "color": "green",
                "text": "increase",
                "comparedTo": "Yesterday"
            },
            "filters": {
                "today": new Date(),
                "thisMonth": "this month start and end date",
                "thisYear": "this year start and today date",
            }
        }
    },

    async getRevenueInfo(revenueCardVal) {
        return {
            "label": "This Month",
            "prefix": "$",
            "displayVal": revenueCardVal,
            "displayValformatted": revenueCardVal.toLocaleString('en-US', {}),
            "class": "bi bi-currency-dollar",
            "trend": {
                "label": "8%",
                "color": "green",
                "text": "increase",
                "comparedTo": "Last month"
            }
        }
    },

    async getCustomerInfo(customersCardVal) {
        return {
            "label": "This Year",
            "displayVal": customersCardVal,
            "displayValformatted": customersCardVal.toLocaleString('en-US', {}),
            "class": "bi bi-people",
            "trend": {
                "label": "12%",
                "color": "red",
                "text": "decrease",
                "comparedTo": "Last Year"
            }
        }
    },

    /** 
     * @param {object} user - The authenticated user
     * @param {object} storeData - The Shopify Store object
     * @returns {object} - Containing access to the Shop's summary
     * 
     */
    async getDashboardSummary(user, storeData, redis) {
        var revenueCardVal = await this.getStoreRevenue(user, storeData, redis);
        var salesCardVal = await this.getSalesCardVal(user, storeData, redis);
        var customersCardVal = await this.getCustomersCardVal(user, storeData, redis); 

        var salesInfo = await this.getSalesInfo(salesCardVal);
        var revenueInfo = await this.getRevenueInfo(revenueCardVal);
        var customerInfo = await this.getCustomerInfo(customersCardVal);
        return {
            "Sales": salesInfo,
            "Revenue": revenueInfo,    
            "Customers": customerInfo
        }
    },

    async getReportData(authUser, storeData) {
        return {
            "dateRangeFormatted": "Last 15 days",
            "salesCurve": {
                "values": [31, 40, 28, 51, 42, 82, 56]
            },
            "customersCurve": {
                "values": [15, 11, 32, 18, 9, 24, 11],
            },
            "revenueCurve": {
                "values": [14, 41, 12, 8, 19, 4, 112]
            },
            "chartSettings": {
                "height": 350,
                "type": 'area',
                "toolbarShow": true
            },
            "markerSize": 4,
            "colors": ['#4154f1', '#2eca6a', '#ff771d'],
            "tooltipDateTimeFormat": 'dd/MM/yy HH:mm',
            "categories": ["2018-09-19T00:00:00.000Z", "2018-09-18T01:30:00.000Z", "2018-09-17T02:30:00.000Z", "2018-09-16T03:30:00.000Z", "2018-09-15T04:30:00.000Z", "2018-09-14T05:30:00.000Z", "2018-09-13T06:30:00.000Z"]
        };
    },

    async getRecentActivity(user, storeData) {
        return {};
    },

    async getBudgetReport(user, storeData) {
        return {};
    },

    async getRecentSales(user, storeData) {
        return {
            "dateRangeFormatted": "Last 15 days",
            "table": {
                "headers": ['#', 'Customer', 'Product', 'Price', 'Status'],
                "rows": [
                    {
                        "#": '#257',
                        "orderLink":"#",
                        "productLink":'#',
                        "customer": 'Customer 1',
                        "product": "Product 1",
                        "price": {
                            "prefix": "$",
                            "value": 450
                        },
                        "status": {
                            "bg-color": "purple",
                            "value": "In Transit"
                        }
                    },
                    {
                        "#": '#227',
                        "orderLink":"#",
                        "productLink":'#',
                        "customer": 'Customer 2',
                        "product": "Product 18",
                        "price": {
                            "prefix": "$",
                            "value": 550
                        },
                        "status": {
                            "bg-color": "black",
                            "value": "Pending"
                        }
                    },
                    {
                        "#": '#256',
                        "orderLink":"#",
                        "productLink":'#',
                        "customer": 'Customer 12',
                        "product": "Product 14",
                        "price": {
                            "prefix": "$",
                            "value": 250
                        },
                        "status": {
                            "bg-color": "green",
                            "value": "Delivered"
                        }
                    },
                    {
                        "#": '#299',
                        "orderLink":"#",
                        "productLink":'#',
                        "customer": 'Customer 11',
                        "product": "Product 16",
                        "price": {
                            "prefix": "$",
                            "value": 100
                        },
                        "status": {
                            "bg-color": "red",
                            "value": "Rejected"
                        }
                    },
                ]
            }
        };
    },

    async getWebsiteTraffic(user, storeData) {
        return {};
    },

    async getTopSellingProducts(user, storeData) {
        return {};
    },

    async getProductDefaultImage(product) {
        try {
            var returnVal = null;
            var image = product.image;
            if(typeof(image) == 'object') {
                returnVal = '<image src="'+image.src+'" alt="In object type" class="img-responsive" style="max-height:30px" />'  
            } 
            if(typeof(image) == 'string') {
                image = JSON.parse(image);
                returnVal = '<image src="'+image.src+'" alt="In string type" class="img-responsive" style="max-height:30px" />'  
            }
        } catch (error) {
            returnVal = null;
        }
        return returnVal;
    },

    async getProductsForStore(user, store, request) {
        var returnVal = {
            data: [],
            count: null,
            query: null,
            message: null
        }
        try {   
            var opts = {limit: parseInt(request.length), offset: parseInt(request.start)};
            //Select fields
            opts.attributes = [
                'table_id', 'id', 'title', 'vendor', 'product_type', 'status', 'created_at', 'image'
            ];

            opts.where = {
                'store_id': store.table_id
            }
    
            if(request.search && request.search.value) {
                console.log('Search value here '+request.search.value);
                //LIKE Query
                opts.where.push({
                    title: { [Op.like]: `%${request.search.value}%` }
                })
                
                /*
                opts.where.push({
                    [Op.or]: [{
                        title: {
                            [Op.like]: '%'+request.search.value+'%'
                        } 
                    }, {
                        vendor: {
                            [Op.like]: '%'+request.search.value+'%'
                        }
                    }]
                })*/
            }

            var dbData = await mysqlAPI.getProductsDataWithOpts(opts)
            if(dbData !== null) {
                for await(var product of dbData) {
                    returnVal.data.push({
                        '#': product.id,
                        'title': product.title,
                        'vendor': product.vendor || 'N/A',
                        'image': await this.getProductDefaultImage(product),
                        'status': product.status || 'N/A',
                        'created_at': product.created_at || '',
                        'actions': `
                            <a href="#" class="btn btn-link showProduct" data-product-id="${product.id}">
                                <i class="bi bi-eye"></i>
                            </a>&nbsp;&nbsp;
                            <a href="#" class="btn btn-link shopifyProduct" data-product-id="${product.id}">
                                <i class="bi bi-shop"></i>
                            </a>
                        `
                    })
                }
            }
            const dbCount = await mysqlAPI.getProductsCountWithOpts(opts);
            returnVal.count = dbCount;
            returnVal.message = 'Done';
            return returnVal;    
        
        } catch (error) {
            returnVal.message = error.message;
            return returnVal;
        }
    },

    async listOrdersForStore(user, store, request) {
        var returnVal = {
            data: [],
            count: null,
            query: null,
            message: null
        }
        try {   
            var opts = {limit: parseInt(request.length), offset: parseInt(request.start)};
            //Select fields
            opts.attributes = [
                'table_id', 'id', 'name', 'email', 'financial_status', 'phone', 'created_at'
            ];

            opts.where = {
                'store_id': store.table_id
            }
    
            if(request.search && request.search.value) {
                //LIKE Query
                opts.where.push({
                    [Op.or]: [{
                        name: {
                            [Op.like]: '%'+request.search.value+'%'
                        } 
                    }, {
                        id: {
                            [Op.like]: '%'+request.search.value+'%'
                        }
                    }]
                })
            }

            var dbData = await mysqlAPI.getOrdersDataWithOpts(opts)
            if(dbData !== null) {
                for await(var order of dbData) {
                    returnVal.data.push({
                        '#': order.id,
                        'name': order.name,
                        'email': order.email || '',
                        'payment_status': order.financial_status,
                        'phone': order.phone || '',
                        'created_at': order.created_at || ''
                    })
                }
            }

            returnVal.count = dbData.length;
            returnVal.message = 'Done';
            return returnVal;    
        
        } catch (error) {
            returnVal.message = error.message;
            return returnVal;
        }
    },

    async listProductCollections(user, store, request) {
        var returnVal = {
            data: [],
            count: null,
            query: null,
            message: null
        }

        try {
            var opts = {limit: parseInt(request.length), offset: parseInt(request.start)};
            //Select fields
            opts.attributes = [
                'table_id', 'id', 'title', 'collection_type', 'handle', 'image'
            ];

            // opts.limit = request.length;
            // opts.offset =

            opts.where = {
                'store_id': store.table_id
            }

            if(request.search && request.search.value) {
                console.log('Search value here '+request.search.value);
                //LIKE Query
                opts.where.push({
                    title: { [Op.like]: `%${request.search.value}%` }
                })
                
                /*
                opts.where.push({
                    [Op.or]: [{
                        title: {
                            [Op.like]: '%'+request.search.value+'%'
                        } 
                    }, {
                        vendor: {
                            [Op.like]: '%'+request.search.value+'%'
                        }
                    }]
                })*/
            }

            var dbData = await mysqlAPI.getProductCollectionsDataWithOpts(opts)
            if(dbData !== null) {
                for await(var collection of dbData) {
                    returnVal.data.push({
                        '#': collection.id,
                        'title': collection.title,
                        'collection_type': collection.collection_type || 'N/A',
                        'image': collection.image,
                        'handle': collection.handle || 'N/A'
                    })
                }
            }

            returnVal.count = dbData.length;
            returnVal.message = 'Done';
            return returnVal; 
        } catch (error) {
            returnVal.message = error.message;
            return returnVal;
        }
    }
}