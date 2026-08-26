const express = require('express');
const cors = require('cors');
const sequelize = require('./config/db');
const User = require('./models/user');
const LostItem = require('./models/lostItem');

const app = express();
app.use(cors());
app.use(express.json());

app.use('/auth', require('./routes/auth'));
app.use('/items', require('./routes/items'));

// Setup relations
User.hasMany(LostItem);
LostItem.belongsTo(User);

sequelize.sync({ alter: true });

module.exports = app;
