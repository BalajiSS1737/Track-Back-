const { DataTypes } = require('sequelize');
const sequelize = require('../config/db');

const LostItem = sequelize.define('LostItem', {
  title: DataTypes.STRING,
  description: DataTypes.TEXT,
  location: DataTypes.STRING,
  imageUrl: DataTypes.TEXT,
  lostDate: DataTypes.DATE,
  status: { type: DataTypes.ENUM('open', 'found', 'claimed'), defaultValue: 'open' },
});

module.exports = LostItem;
