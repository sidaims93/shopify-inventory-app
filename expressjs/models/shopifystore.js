module.exports = function(sequelize, DataTypes) {

    const ShopifyStore = sequelize.define('shopify_store', {
        table_id: {
            autoIncrement: true,
            primaryKey: true,
            type: DataTypes.INTEGER
        },
        id: {
            type: DataTypes.INTEGER
        },
        myshopify_domain: {
            type: DataTypes.STRING,
            allowNull: false,
            unique: true
        },
        accessToken: {
            type: DataTypes.STRING
        },
        name: {
            type: DataTypes.STRING
        },
        plan_name: {
            type: DataTypes.STRING            
        },
        currency: {
            type: DataTypes.STRING            
        },
        shop_owner: {
            type: DataTypes.STRING            
        },
        email: {
            type: DataTypes.STRING
        },
        customer_email: {
            type: DataTypes.STRING
        },
        phone: {
            type: DataTypes.STRING
        },
        eligible_for_card_reader_giveaway: {
            type: DataTypes.INTEGER
        },
        createdAt: {
            field: 'created_at',
            type: DataTypes.DATE,
        },
        updatedAt: {
            field: 'updated_at',
            type: DataTypes.DATE,
        }        
    }, {
        tableName: 'shopify_stores',
        freezeTableName: true
    });
    
    return ShopifyStore;
}