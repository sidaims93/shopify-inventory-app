module.exports = function(sequelize, DataTypes) {

    const ShopifyStore = sequelize.define('product_collections', {
        table_id: {
            autoIncrement: true,
            primaryKey: true,
            type: DataTypes.INTEGER
        },
        id: {
            type: DataTypes.INTEGER
        },
        store_id: {
            type: DataTypes.INTEGER,
            notEmpty: true
        },
        collection_type: {
            type: DataTypes.STRING,
        },
        handle: {
            type: DataTypes.TEXT('medium'),
        },
        title: {
            type: DataTypes.TEXT('medium')
        },
        sort_order: {
            type: DataTypes.TEXT('medium')
        },
        admin_graphql_api_id: {
            type: DataTypes.TEXT('medium')            
        },
        image: {
            type: DataTypes.TEXT('medium')            
        },
        createdAt: {
            field: 'created_at_date',
            type: DataTypes.DATE,
        },
        updatedAt: {
            field: 'updated_at_date',
            type: DataTypes.DATE,
        }        
    }, {
        tableName: 'product_collections',
        freezeTableName: true
    });
    
    return ShopifyStore;
}