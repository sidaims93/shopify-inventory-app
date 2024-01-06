const jwt = require('jsonwebtoken');

const { Sequelize, DataTypes } = require("sequelize");
var env = process.env.NODE_ENV || "development";
var config = require('../config.json')[env];
var sequelize = new Sequelize(config.database, config.username, config.password, config);

const Users = require('../models/users')(sequelize, DataTypes);

module.exports = {
    /**
     * 
     * @param {object} req - Authenticated user 
     * @param {object} res - Response
     * @returns {json} 
     */
    login: async function (req, res) {
        var user = req.user; 
        
        //This is needed because it will keep increasing the token length if you comment it
        delete(user.authtoken);
        
        const token = jwt.sign(user, process.env.APP_KEY);
        var returnResponse = {
            "user": {
                "id": user.id,
                "email": user.email,
                "name": user.name,
                "created_at": user.createdAt,
                "updated_at": user.updatedAt
            },
            "authToken": token
        };

        var dbmodel = await Users.findOne({'id': user.id});
        await dbmodel.update({'authtoken': token});
        
        return res.json(returnResponse).status(200);
    }
}