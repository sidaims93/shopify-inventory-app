module.exports = function(sequelize, DataTypes) {

    const Orders = sequelize.define('order', {
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
            notEmpty: true
        },
        name: {
            type: DataTypes.STRING,
            notEmpty: false
        },
        email: {
            type: DataTypes.STRING,
            notEmpty: false
        },
        cart_token: {
            type: DataTypes.STRING,
            notEmpty: false
        },
        checkout_id: {
            type: DataTypes.STRING,
            notEmpty: false
        },
        checkout_token: {
            type: DataTypes.STRING,
            notEmpty: false
        },
        order_created_at: {
            type: DataTypes.DATE,
            notEmpty: false
        },
        currency: {
            type: DataTypes.STRING,
            notEmpty: false
        },
        financial_status: {
            type: DataTypes.STRING,
            notEmpty: false
        },
        fulfillment_status: {
            type: DataTypes.STRING,
            notEmpty: false
        },
        note: {
            type: DataTypes.STRING,
            notEmpty: false
        },
        order_number: {
            type: DataTypes.STRING,
            notEmpty: false
        },
        phone: {
            type: DataTypes.STRING,
            notEmpty: false
        },
        subtotal_price: {
            type: DataTypes.STRING,
            notEmpty: false
        },
        total_price: {
            type: DataTypes.STRING,
            notEmpty: false
        },
        total_tax: {
            type: DataTypes.STRING,
            notEmpty: false
        },
        customer: {
            type: DataTypes.TEXT('long'),
            notEmpty: false
        },
        line_items: {
            type: DataTypes.TEXT('long'),
            notEmpty: false
        },
        shipping_address: {
            type: DataTypes.TEXT('long'),
            notEmpty: false
        },
        number: {
            type: DataTypes.STRING,
            notEmpty: false
        },
        tags: {
            type: DataTypes.STRING,
            notEmpty: false
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
    
    return Orders;
}