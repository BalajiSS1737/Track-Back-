const LostItem = require('../models/lostItem');

exports.reportLost = async (req, res) => {
  const { title, description, location, lostDate, imageUrl } = req.body;
  const lostItem = await LostItem.create({
    userId: req.user.id,
    title,
    description,
    location,
    lostDate,
    imageUrl
  });
  res.status(201).json(lostItem);
};

exports.getLostItems = async (req, res) => {
  const items = await LostItem.findAll({ where: { status: 'open' } });
  res.json(items);
};
