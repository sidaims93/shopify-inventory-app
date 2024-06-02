
module.exports = (Sequelize, DataTypes) => {
    var env = process.env.NODE_ENV;
    var config = require('../config.json')[env];
    var sequelize = new Sequelize(config.database, config.username, config.password, config);
    
    const Users = require('../models/users')(sequelize, DataTypes);
    const UserStores = require('../models/userstores')(sequelize, DataTypes);
    const ShopifyStores = require('../models/shopifystore')(sequelize, DataTypes);
    const Orders = require('../models/orders')(sequelize, DataTypes);
    const Products = require('../models/products')(sequelize, DataTypes);
    const ProductCollections = require('../models/productCollections')(sequelize, DataTypes);
    const StoreLocations = require('../models/locations')(sequelize, DataTypes);
    const moment = require('moment');

    //const {Op} = require('sequelize');
    
    return {
        getShopifyStoreData: async function (user) {
            var userStoreData = await UserStores.findOne({
                where: {"user_id": user.id},
                order: [['id', 'DESC']]
            });

            if(userStoreData !== null) {
                var storeData = await ShopifyStores.findOne({
                    where: { "table_id": userStoreData.store_id }
                })
                return storeData;
            }

            return null;
        },

        getAllShopifyStoresAssociatedWithUser: async function (user) {
            var userStores = await UserStores.findAll({
                where: {"user_id": user.id},
                order: [['id', 'DESC']]
            });

            if(userStores !== null) {
                var storeIds = new Array();
                for await(var data of userStores) {
                    storeIds.push(data.store_id);
                }

                var stores = await ShopifyStores.findAll({
                    where: {"table_id": storeIds}
                });

                return stores;
            }
            return null;
        },

        getNoOfOrdersForStore: async function (store, start_date = null, end_date = null) {
            if(start_date !== null && end_date !== null) {
                return await Orders.count({
                    where: {
                        store_id: store.table_id,
                        order_created_at: {
                            $gt: start_date,
                            $lt: end_date,
                        }
                    }
                })    
            }          
            return await Orders.count({
                where: {
                    store_id: store.table_id,
                }
            });
        },

        getStoreRevenueForStore: async function (store, start_date = null, end_date = null) {
            if(start_date !== null && end_date !== null) {
                return await Orders.findOne({
                    where: {
                        store_id: store.table_id,
                        order_created_at: {
                            $gt: start_date,
                            $lt: end_date,
                        }
                    },
                    attributes: [
                      [sequelize.fn('sum', sequelize.col('total_price')), 'total_revenue'],
                    ]
                })    
            }          
            return await Orders.findOne({
                where: {
                    store_id: store.table_id,
                },
                attributes: [
                  [sequelize.fn('sum', sequelize.col('total_price')), 'total_revenue'],
                ]
            });
        },

        getAllStores: async function (selectionFields) {
            return await ShopifyStores.findAll({
                attributes:selectionFields
            })
        },

        getStoreByDomain: async function (shop) {
            return await ShopifyStores.findOne({where: {myshopify_domain: shop}});
        },

        updateOrCreateUserRecord: async function (userBody) {
            return await this.updateOrCreateOnModel(Users, {"email": userBody.email}, userBody);
        },

        updateOrCreateStoreRecord: async function (storeBody) {
            return await this.updateOrCreateOnModel(ShopifyStores, {"myshopify_domain": storeBody.myshopify_domain}, storeBody);
        },

        updateOrCreateUserStoreMapping: async function (userRecord, storeRecord) {
            var obj = {
                "user_id": userRecord.id,
                "store_id": storeRecord.table_id
            };
            return await this.updateOrCreateOnModel(UserStores, obj, obj);
        },

        updateOrCreateShopifyProductCollection: async function (collection) {
            return this.updateOrCreateOnModel(ProductCollections, {'id': collection.id, 'store_id': collection.store_id}, collection);
        },

        updateOrCreateShopifyProduct: async function (product) {
            return this.updateOrCreateOnModel(Products, {'id': product.id, 'store_id': product.store_id}, product);
        },

        updateOrCreateShopifyOrder: async function (order) {
            return this.updateOrCreateOnModel(Orders, {'id': order.id, 'store_id': order.store_id}, order);
        },
        
        updateOrCreateShopifyLocation: async function (location) {
            return await this.updateOrCreateOnModel(StoreLocations, {'id': location.id, 'store_id': location.store_id}, location)
        },

        updateOrCreateOnModel: async function (Model, where, newItem) {
            // First try to find the record
            const dbOperation = await Model.findOne({where: where, raw: true})
            .then(function (foundItem) {
                if (!foundItem) {
                    return Model.create(newItem).then(function (item) { return { item: item, created: true }; })
                }
                 // Found an item, update it
                return Model.update(newItem, {where: where} ).then(function (item) { console.log(item); return {item: item, created: false} }) ;
            });

            console.log('dbOperation');
            console.log(dbOperation);

            return Model.findOne({where: where, raw: true});
        },

        getStoreProducts: async function (store_id, filterArr) {
            return Products.findAll({
                where: {'store_id': store_id},
                order: [
                    ['created_at_date', 'DESC'],
                ]
            });
        },

        getOrdersDataWithOpts: async function (opts) {
            return Orders.findAll(opts);
        },

        getProductsDataWithOpts: async function (opts) {
            return Products.findAll(opts);
        },

        getProductsCountWithOpts: async function (opts) {
            return Products.count({ where: opts.where });
        },

        getProductCollectionsDataWithOpts: async function (opts) {
            return ProductCollections.findAll(opts);
        },

        getProduct: async function (opts) {
            return Products.findOne(opts);
        },

        insertAppLogs: async function (store, logs) {
            const collectionDb = 'store_app_logs_'+store.id;
            console.log(collectionDb+' collection here');
            const appLogConnection = mongoDbClient.collection(collectionDb);
            const insertResult = await appLogConnection.insertMany(logs);
            console.log('Inserted documents =>', insertResult);
        },

        getAppLogs: async function (store, reqParams) {
            const collectionDb = 'store_app_logs_'+store.id;
            const appLogConnection = mongoDbClient.collection(collectionDb);
            const filter = reqParams.hasOwnProperty('timeDiff') ? { timestamp: { $gt: moment().subtract(reqParams.timeDiff, 'days').unix() } } : {};
            const findResult = await appLogConnection.find(filter).sort({ timestamp: -1 }).limit(5).toArray();
            return findResult;
        }
    };
}