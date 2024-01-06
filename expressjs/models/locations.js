module.exports = function(sequelize, DataTypes) {

    const Locations = sequelize.define('store_locations', {
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
        name: {
            type: DataTypes.TEXT('medium'),
        },
        legacy: {
            type: DataTypes.BOOLEAN
        },
        active: {
            type: DataTypes.BOOLEAN
        },
        address1: {
            type: DataTypes.TEXT('medium'),
        },
        address2: {
            type: DataTypes.TEXT('medium'),
        },
        zip: {
            type: DataTypes.STRING,
        },
        city: {
            type: DataTypes.STRING,
        },
        country: {
            type: DataTypes.STRING,
        },
        province: {
            type: DataTypes.STRING,
        },
        created_at: {
            type: DataTypes.STRING,
        }, 
        updated_at: {
            type: DataTypes.STRING,
        }, 
        country_code: {
            type: DataTypes.STRING,
        }, 
        country_name: {
            type: DataTypes.STRING,
        }, 
        province_code: {
            type: DataTypes.STRING,
        }, 
        localized_country_name: {
            type: DataTypes.STRING,
        }, 
        localized_province_name: {
            type: DataTypes.STRING,
        }, 
        admin_graphql_api_id: {
            type: DataTypes.STRING,
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
    
    return Locations;
}