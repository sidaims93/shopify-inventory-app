const express = require('express')
const app = express()
const port = 3000;
const bodyParser = require('body-parser'); // middleware
app.use(bodyParser.urlencoded({ extended: true }));
//const Redis = require("ioredis");
//const { MongoClient } = require('mongodb');
//var cronHandler = require('node-cron');
/*
const redis = new Redis({
  port: 6379, // Redis port
  host: "127.0.0.1" // Redis host
});

try {
  redis.ping();
  console.log('Redis Up and running');
} catch (error) {
  console.log('Error with redis '+error.message);
}
*/

// Connection URL
// const url = 'mongodb://0.0.0.0:27017/';
// const client = new MongoClient(url);

// Database Name
//const dbName = 'inventory_app';
// Use connect method to connect to the server
//client.connect();
//console.log('mongodb Connected successfully');
//const mongoDbClient = client.db(dbName);

var passport = require('passport');
var session = require('express-session');
var env = require('dotenv').config(); 
app.use(
  express.urlencoded({
    extended: true 
  })
);

// For Passport 
app.use(session({
  secret: 'someverylargestringthatwecannotsimplyguess',
  resave: true, 
  saveUninitialized:true
})); 
// session secret 
app.use(passport.initialize());
app.use(passport.session()); 

app.use(express.json());

//Models 
var models = require("./models");
const { Sequelize, Model, DataTypes, DATE } = require('sequelize');
//Sync Database 
models.sequelize.sync().then(function() {
  console.log('Nice! All is looking good');
}).catch(function(err) {
  console.log(err, "Something went wrong with the Database Update!");
});

//Load mysql
const mysqlAPI = require('./src/mysql-api')(Sequelize, DataTypes);

//Load Traits 
const FunctionTrait = require('./traits/functions');
const RequestTrait = require('./traits/requests');
const traits = {FunctionTrait, RequestTrait}

//load passport strategies 
require('./passport/passport.js')(passport, models.user);
require('./auth.js')(app, passport, mysqlAPI, traits);

app.get('/', (req, res) => {
  res.json({
    "status": true,
    "message": "Hello World" 
  }).status(200);
});

app.listen(port, () => {
  console.log(`Example app listening on port ${port}`)
})