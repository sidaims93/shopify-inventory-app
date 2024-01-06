
const jwt = require('jsonwebtoken');

module.exports = function(app, passport, mysqlAPI, traits, redis) {

    var authController = require('./controllers/authController');
    var dashboardController = require('./controllers/dashboardController')(mysqlAPI, traits, redis);
    var storeController = require('./controllers/storeController')(mysqlAPI, traits, redis);
    var productsController = require('./controllers/productsController')(mysqlAPI, traits, redis);
    var ordersController = require('./controllers/ordersController')(mysqlAPI, traits, redis);

    function apiAuth(req, res, next) {
        if (req.headers['authorization']) {
            try {
                let authorization = req.headers['authorization'].split(' ');
                if (authorization[0] == 'Bearer') {
                    req.user = jwt.verify(authorization[1], process.env.APP_KEY);
                    return next();
                } 
            } catch (err) {
                return res.status(401).json({
                    "status": false, 
                    "message": "Invalid/Expired token",
                    "debug": err.message
                });
            }
        } 
        return res.status(401).json({
            "status": false, 
            "message":"Invalid request header or token"
        });
    }
    const apiRoutePrefix = '/api/'; // This is so if we do versioning like /api/v1 or /api/v2 
    
    //Sync data APIs
    const syncPrefix = apiRoutePrefix +'sync/';
    app.get(syncPrefix+'orders', storeController.syncOrders);
    app.get(syncPrefix+'products', storeController.syncProducts);
    app.get(syncPrefix+'products/collections', storeController.syncProductCollections);
    app.get(syncPrefix+'locations', storeController.syncStoreLocations);
    
    //Show APIs
    app.get(apiRoutePrefix + 'product/show', apiAuth, productsController.listProductDetails);
    app.get(apiRoutePrefix + 'stores', apiAuth, storeController.listStores);

    //AJAX APIs
    const ajaxPrefix = apiRoutePrefix + 'ajax/';
    app.post(ajaxPrefix+'orders', apiAuth, ordersController.listOrders);
    app.post(ajaxPrefix+'products', apiAuth, productsController.listProducts);
    app.post(ajaxPrefix+'product/collections', apiAuth, productsController.listProductCollections);

    //Authenticated APIs
    const dashboardPrefix = apiRoutePrefix + 'dashboard';
    app.get(dashboardPrefix, apiAuth, dashboardController.index);
    app.get(dashboardPrefix+'/sales/card/info', apiAuth, dashboardController.getSalesCardInfo);
    
    //Shopify APIs for app usage billing
    app.get(apiRoutePrefix+'setupAppUsageBilling', apiAuth, dashboardController.setupAppUsageBilling);
    app.get(apiRoutePrefix+'createActionOnBilling', apiAuth, dashboardController.createActionOnBilling);
    
    //Login API
    app.post(apiRoutePrefix+'login', passport.authenticate('local-signin'), authController.login);

    //MongoDB routes
    app.post(apiRoutePrefix + 'insertAppLogs', apiAuth, storeController.insertAppLogs);
    app.get(apiRoutePrefix + 'getAppLogs', apiAuth, storeController.getAppLogs);

    //Theme app extension routes
    app.post(apiRoutePrefix + 'store/liveTheme', apiAuth, storeController.getLiveThemeForStore);
    app.post(apiRoutePrefix + 'checkStoreSetup', apiAuth, storeController.checkStoreSetup);
}