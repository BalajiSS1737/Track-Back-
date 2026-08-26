const express = require('express');
const router = express.Router();
const { reportLost, getLostItems } = require('../controllers/itemController');
const auth = require('../middleware/auth');

router.post('/lost', auth, reportLost);
router.get('/lost', getLostItems);

module.exports = router;
