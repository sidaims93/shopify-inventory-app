module.exports = (mysqlAPI, traits) => {
    const functionTrait = traits.FunctionTrait;
    const requestTrait = traits.RequestTrait;
  
    return {
        listOrders: async function (req, res) {
            try {
                var authUser = req.user;
                var storeData = await mysqlAPI.getShopifyStoreData(authUser);
                var orders = await functionTrait.listOrdersForStore(authUser, storeData, req.body);
                return res.json(orders);
            } catch (error) {
                return res.json({
                    data: null,
                    count: 0,
                    query: null,
                    message: error.message
                })
            }
        },
    }
}