const moment = require("moment");
moment.locale("it");

const formatTime = d => moment.utc(d).format("H:mm");

const formatDate = d => moment.utc(d).format("LL");

module.exports = { formatTime, formatDate };
