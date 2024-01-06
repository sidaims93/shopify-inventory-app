module.exports = function(sequelize, DataTypes) {

    const Products = sequelize.define('products', {
        table_id: {
            autoIncrement: true,
            primaryKey: true,
            type: DataTypes.INTEGER
        },
        store_id: {
            type: DataTypes.INTEGER,
            notEmpty: true
        },
        id: {
            type: DataTypes.INTEGER,
            notEmpty: false
        },
        title: {
            type: DataTypes.TEXT('medium'),
            notEmpty: false
        },
        body_html: {
            type: DataTypes.TEXT('long'),
            notEmpty: false
        },
        vendor: {
            type: DataTypes.TEXT('medium'),
            notEmpty: false
        },
        product_type: {
            type: DataTypes.STRING,
            notEmpty: false
        },
        published_at: {
            type: DataTypes.STRING,
            notEmpty: false
        },
        template_suffix: {
            type: DataTypes.STRING,
            notEmpty: false
        },
        published_scope: {
            type: DataTypes.STRING,
            notEmpty: false
        },
        status: {
            type: DataTypes.STRING,
            notEmpty: false
        },
        admin_graphql_api_id: {
            type: DataTypes.STRING,
            notEmpty: false
        },
        image: {
            type: DataTypes.STRING,
            notEmpty: false
        },
        created_at: {
            type: DataTypes.STRING,
            notEmpty: true
        },
        updated_at: {
            type: DataTypes.STRING,
            notEmpty: true
        },
        deleted_at: {
            type: DataTypes.STRING,
            notEmpty: true
        }, 
        createdAt: {
            field: 'created_at_date',
            type: DataTypes.DATE,
        },
        updatedAt: {
            field: 'updated_at_date',
            type: DataTypes.DATE,
        }
    });
    
    return Products;
}