module.exports = function(sequelize, DataTypes) {

    const Users = sequelize.define('user_store', {
        id: {
            autoIncrement: true,
            primaryKey: true,
            type: DataTypes.INTEGER
        },
        store_id: {
            type: DataTypes.INTEGER,
            notEmpty: true
        },
        user_id: {
            type: DataTypes.INTEGER,
            notEmpty: true
        },
        createdAt: {
            field: 'created_at',
            type: DataTypes.DATE,
        },
        updatedAt: {
            field: 'updated_at',
            type: DataTypes.DATE,
        }
    });
    
    return Users;
}