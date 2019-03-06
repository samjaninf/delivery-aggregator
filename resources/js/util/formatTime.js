const moment = require("moment");
moment.locale("it");

const formatTime = d =>
  moment
    .unix(d)
    .utc()
    .format("H:mm");

const formatDate = d =>
  moment
    .unix(d)
    .utc()
    .format("LL");

module.exports = { formatTime, formatDate };
